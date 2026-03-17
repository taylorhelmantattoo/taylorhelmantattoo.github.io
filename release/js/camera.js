var video           = document.getElementById('monitor');
var photo           = document.getElementById('photo');
var take_photo      = document.getElementById('take_photo');
var cdsplash        = document.getElementById('snapbutton');
var camera_has_init = false;

function cameraSetOrientation() {
    var landscape = screen.width > screen.height ||
                    (typeof window.orientation !== 'undefined' && window.orientation === 90);
    if (landscape) {
        if (photo) { photo.width = cam_width;  photo.height = cam_height; }
        if (video) { video.width = cam_width;  video.height = cam_height; }
    } else {
        if (photo) { photo.width = cam_height; photo.height = cam_width; }
        if (video) { video.width = cam_height; video.height = cam_width; }
    }
}

var supportsOrientationChange = ('onorientationchange' in window);
window.addEventListener(supportsOrientationChange ? 'orientationchange' : 'resize', cameraSetOrientation, false);

// ── noStream: hide camera container, show upload with friendly message ───────
function noStream(reason) {
    var cam = document.getElementById('cameracontainer');
    var upl = document.getElementById('uploadcontainer');
    if (cam) cam.style.display = 'none';
    if (upl) upl.style.display = 'block';

    var msgMap = {
        insecure:  'Camera requires a secure connection (HTTPS). Please use file upload below.',
        denied:    'Camera access was denied. Allow camera access in browser settings, or use file upload.',
        notfound:  'No camera detected on this device. Please use file upload.',
        busy:      'Camera is in use by another application. Please close it or use file upload.',
        unsupported: 'Camera capture is not supported in this browser. Please use file upload.'
    };
    var msg = msgMap[reason] || 'Camera not available. Please use file upload.';

    if (upl && !document.getElementById('cam-fallback-msg')) {
        var p = document.createElement('p');
        p.id = 'cam-fallback-msg';
        p.style.cssText = 'color:#c00;font-size:13px;margin:0 0 8px;padding:6px;background:#fff0f0;border-radius:4px';
        p.textContent = msg;
        upl.insertBefore(p, upl.firstChild);
    }
}

// ── gotStream: called when camera stream is available ────────────────────────
function gotStream(stream) {
    camera_has_init = true;
    video.srcObject = stream;   // Modern API — no createObjectURL needed
    video.play();
    video.onerror = function() {
        stream.getTracks().forEach(function(t) { t.stop(); });
        noStream('busy');
    };
    video.onloadedmetadata = function() {
        cameraSetOrientation();
    };
}

// ── initializeCameraCapture: start camera using modern mediaDevices API ──────
function initializeCameraCapture(mode) {
    if (mode === undefined) mode = 'live';
    cameraSetOrientation();


    // Secure context check (Chrome requires HTTPS for getUserMedia)
    if (window.isSecureContext === false) {
        noStream('insecure');
        return;
    }

    var constraints = { video: { facingMode: 'environment' }, audio: false };

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        // Modern API (Chrome 47+, Firefox 36+, Edge 12+)
        navigator.mediaDevices.getUserMedia(constraints)
            .then(gotStream)
            .catch(function(err) {
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    noStream('denied');
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    noStream('notfound');
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    noStream('busy');
                } else {
                    noStream();
                }
            });
    } else {
        // Legacy API fallback (Safari < 11, old Android)
        var legacyGetUserMedia = navigator.getUserMedia ||
                                  navigator.webkitGetUserMedia ||
                                  navigator.mozGetUserMedia;
        if (legacyGetUserMedia) {
            legacyGetUserMedia.call(navigator, { video: true },
                function(stream) {
                    // Legacy streams require createObjectURL
                    try {
                        video.src = window.URL ? window.URL.createObjectURL(stream) : stream;
                    } catch(e) {
                        video.srcObject = stream;
                    }
                    camera_has_init = true;
                    video.play();
                },
                function() { noStream(); }
            );
        } else {
            noStream('unsupported');
        }
    }
}
// Legacy alias kept for any external callers
var camera_init = initializeCameraCapture;

// ── snapshot / takesnapshot ───────────────────────────────────────────────────
function snapshot() {
    if (!camera_has_init) return;
    take_photo.style.display = 'none';
    photo.style.display = 'none';
    video.style.display = 'block';
    countdown(3);
}

function takesnapshot() {
    photo.getContext('2d').drawImage(video, 0, 0, photo.width, photo.height);
    photo.style.display = 'block';
    video.style.display = 'none';
    canvas_status('photo', true);
    // Show inline success feedback
    var upl = document.getElementById('uploadcontainer');
    if (upl) {
        var prev = document.getElementById('cam-fallback-msg');
        if (prev) prev.remove();
    }
}

function countdown(cdtime) {
    cdsplash.onclick = null;
    cdsplash.style.fontSize = '20px';
    if (cdtime === 0) {
        cdsplash.style.fontSize = '10px';
        cdsplash.value = 'CLICK TO TAKE PHOTO';
        cdsplash.onclick = function() { snapshot(); };
        takesnapshot();
        return;
    }
    cdsplash.value = cdtime;
    setTimeout(function() { countdown(cdtime - 1); }, 1000);
}