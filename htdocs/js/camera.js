var video		= document.getElementById('monitor');
var photo		= document.getElementById('photo');
var take_photo		= document.getElementById('take_photo');
var cdsplash		= document.getElementById('snapbutton');
var browser		= false;
var camera_has_init	= false;

function cameraSetOrientation()
{
	if(screen.width>screen.height || window.orientation=='landscape') {
		photo.width = video.width = cam_width;
		photo.height = video.height = cam_height;
	} else {
		photo.width = video.height = cam_height;
		photo.height = video.width = cam_width;
	}
}

// detects screen rotate and resizes camera canvas
var supportsOrientationChange = "onorientationchange" in window,
orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";
window.addEventListener(orientationEvent, function() {
	cameraSetOrientation();
}, false);


function gotPicture(stream) {
	canvas_init();
	camera_has_init = true;
	// If it doesn't like video to image, so just take image.
	cdsplash.onclick = Function("takephoto()");
}

function canvas_init()
{
//	context = photo.getContext('2d');
//	context.font = "20px sans-serif";
//	context.fillText("Click the button under to take a photo of your ID", 60, 160);

	/*var imageObj = new Image();
	imageObj.onload = function() {
		var left=(photo.width-imageObj.width)/2;
		var top=(photo.height-imageObj.height)/2;
		photo.getContext('2d').drawImage(imageObj, left, top, imageObj.width, imageObj.height );
		delete imageObj;
	};
	imageObj.src = "img/take_photo.png";*/

}

function gotStream(stream) {
	camera_has_init = true;

	var source;
	switch(browser) {
		case 'webkit':
			source = window.webkitURL.createObjectURL(stream);
			break
		default :
			source = stream;
	}	

	video.src = source;
	video.play();

	video.onerror = function () {
		stream.getTracks().forEach(function(t) { t.stop(); });
	};

	//stream.onended = noStream;
	video.onloadedmetadata = function () {
	//	photo.width = cam_width; //video.videoWidth;
	//	photo.height = cam_height; //video.videoHeight;

	//	cam_width=cam_width;
	//	video.style.width = photo.style.width = cam_width;
		//video.style.height = canvas.style.height = cam_width/canvas.width*canvas.height;
		
		canvas_init();
	};
}

function noStream() {
	var cam = document.getElementById('cameracontainer');
	var upl = document.getElementById('uploadcontainer');

//	upl.innerHTML='Your browser doesn\'t support camera functions. Try the file uploaded instead.<br /><br />'+upl.innerHTML;

	cam.style.display='none';
	upl.style.display='block';
}
					
function camera_init(mode) {
	if(mode == undefined)
		mode='live';

	cameraSetOrientation();
	switch(mode) {
		case 'live':
			if (navigator.getUserMedia) {
				// opera users (hopefully everyone else at some point)
				navigator.getUserMedia({video: true}, gotStream, noStream);
			} else if (navigator.webkitGetUserMedia) {
				// webkit users
				browser = 'webkit';
				navigator.webkitGetUserMedia({video: true}, gotStream, noStream);
			} else if (navigator.mozGetUserMedia) {
				// firefox
				browser = 'firefox';
				navigator.mozGetUserMedia({picture: true}, gotPicture, noStream);
				//noStream();
			} else {
				// not supported
				noStream();
			}
		break;
		case 'photo':
			if (navigator.getUserMedia) {
				// opera users (hopefully everyone else at some point)
				navigator.getUserMedia({picture: true}, gotPicture, noStream);
			} else if (navigator.webkitGetUserMedia) {
				// webkit users
				browser = 'webkit';
				navigator.webkitGetUserMedia({picture: true}, gotPicture, noStream);
			} else if (navigator.mozGetUserMedia) {
				// firefox
				browser = 'firefox';
				navigator.mozGetUserMedia({picture: true}, gotPicture, noStream);
				//noStream();
			} else {
				// not supported
				noStream();
			}
		break;
	}
}

function countdown(cdtime) {
	cdsplash.onclick = false;	//disable
	cdsplash.style.fontSize='20px';

	if(cdtime==0) {
		cdsplash.style.fontSize='10px';
		cdsplash.value = 'CLICK TO TAKE PHOTO';
		cdsplash.onclick = Function("snapshot()");		
		takesnapshot();
		return;
	}
	cdsplash.value = cdtime;

	cdtime=cdtime-1;
	setTimeout("countdown("+cdtime+")",1000);
}

function takephoto() {
	cdsplash.value = 'loading...';

	navigator.mozGetUserMedia({picture: true}, function(stream) {
		var blob = window.URL.createObjectURL(stream);

		context.fillStyle = "#CCC";
		context.fillRect(0, 0, photo.width, photo.height);

		myimage = new Image();
		myimage.onload = function() {
			//photo.getContext('2d').drawImage(myimage, 0, 0);
			photo.getContext('2d').drawImage(myimage, video.width, video.height, 0, 0, photo.width, photo.height );
		}
		myimage.src = blob;
		cdsplash.value = '[Take photo ID]';
	}, function(err) {  }
	);

	cdsplash.onclick = Function("takephoto()");
	return false;
}

function snapshot() {
	if(!camera_has_init)
		return true;

	take_photo.style.display='none';
	photo.style.display='none';
	video.style.display='block';
	
	countdown(4);
}
			
function takesnapshot() {
	//photo.getContext('2d').drawImage(video, 0, 0);
	photo.getContext('2d').drawImage(video, 0, 0, photo.width, photo.height);
	photo.style.display='block';
	video.style.display='none';				
	canvas_status('photo', true);
}
// see if we can use the camera or return error.
//camera_init();
