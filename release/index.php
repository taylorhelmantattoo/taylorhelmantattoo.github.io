<?php
$cam_width=600;
$cam_height=450;

/*	Na-na-na-na-nah-na-nah-naaaa StabPad!
 *	
 *	yo@joeltron.com     -     StabPad.com
 *
 */

// put into print mode, which creates a pdf
$skin=null;
if(isset($_GET['print'])) {
	$skin='print';
	ob_start();
}

include_once('functions.inc.php');
include_once('config.inc.php');
include_once('validation.inc.php');

ini_set('memory_limit', '96M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

if(!isset($redirect_url))
	$redirect_url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['REQUEST_URI'];

// â”€â”€ Two-step flow â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Only active when $settings['two_step'] is true (set in configs/tattoo.php)
if (!empty($settings['two_step']) && !isset($_GET['print']) && !isset($_GET['topdf'])) {

    // â”€â”€ Step A: artist POST - generate token and show link â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // get_artist_sig AJAX endpoint (called by JS auto-fill)
    if (isset($_GET['get_artist_sig'])) {
        header('Content-Type: application/json');
        $req_artist = trim($_POST['artist'] ?? '');
        $req_pin    = trim($_POST['pin'] ?? '');
        if ($req_artist && check_artist_pin($req_pin)) {
            $sig = get_artist_sig($req_artist);
            echo json_encode(['ok' => true, 'sig' => $sig]);
        } else {
            echo json_encode(['ok' => false]);
        }
        die;
    }

    if (isset($_POST['artist_step_submit'])) {
        if (!check_artist_pin($_POST['artist_pin'] ?? '')) {
            $pin_error = 'Incorrect PIN. Please try again.';
        } else {
            $tok_artist    = trim($_POST['tok_artist'] ?? '');
            $tok_placement = trim($_POST['tok_placement'] ?? '');
            $tok_sig       = trim($_POST['tok_sig_data'] ?? '');
            $tok_cemail    = trim($_POST['tok_client_email'] ?? '');
            $tok_cphone    = trim($_POST['tok_client_phone'] ?? '');
            if (!$tok_artist || !$tok_placement || !$tok_sig) {
                $pin_error = 'Please fill in Artist, Placement, and Signature before generating the link.';
            } else {
                save_artist_sig($tok_artist, $tok_sig);
                $token      = generate_client_token($tok_artist, $tok_placement, $tok_sig, $tok_cemail, $tok_cphone, $_GET['release'] ?? 'tattoo');
                $base_url   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
                $client_url = $base_url . '?release=' . ($_GET['release'] ?? 'tattoo') . '&client=' . urlencode($token);
                ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=700, minimum-scale=1.0, maximum-scale=1.5, user-scalable=0" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/default.css" />
<title>Client Link Ready</title>
<style>
.two-step-wrap{max-width:660px;margin:30px auto;font-family:Arial,sans-serif}
.two-step-card{background:#f9f9f9;border:1px solid #ddd;border-radius:8px;padding:24px 28px;margin-bottom:20px}
h2{margin-top:0}
.send-row{display:flex;gap:10px;align-items:center;margin-bottom:6px}
.send-row input{flex:1;padding:8px 10px;border:1px solid #bbb;border-radius:4px;font-size:14px;box-sizing:border-box}
.send-result{min-height:22px;font-size:13px;margin-bottom:14px}
.client-url{word-break:break-all;background:#fff;border:1px solid #bbb;padding:10px 14px;border-radius:4px;font-size:13px;margin:10px 0}
.btn{display:inline-block;padding:10px 18px;background:#556b5a;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:14px;text-decoration:none;white-space:nowrap}
.btn-copy{background:#3a5f7d}
.back-link{font-size:13px;color:#666;text-decoration:none}
hr{border:none;border-top:1px solid #e0e0e0;margin:16px 0}
details summary{cursor:pointer;font-size:13px;color:#888;user-select:none}
</style>
</head>
<body>
<div class="two-step-wrap">
  <div class="two-step-card">
    <h2>&#10003; Client Link Ready</h2>
    <p>The link expires in <strong>72 hours</strong>. Send it to your client below:</p>

    <label style="font-weight:bold;display:block;margin-bottom:6px">Client's Email Address:</label>
    <div class="send-row">
      <input type="email" id="send-email" value="<?=htmlspecialchars($tok_cemail)?>" placeholder="client@example.com" />
      <button class="btn" onclick="sendLink('email',this)">Send Release Form</button>
    </div>
    <div class="send-result" id="email-result"></div>

    <hr />

    <label style="font-weight:bold;display:block;margin-bottom:6px">Client's Cell Phone Number:</label>
    <div class="send-row">
      <input type="tel" id="send-phone" value="<?=htmlspecialchars($tok_cphone)?>" placeholder="+1 (555) 000-0000" />
      <button class="btn" onclick="sendLink('sms',this)">Send Release Form</button>
    </div>
    <div class="send-result" id="sms-result"></div>

    <hr />

    <details>
      <summary>Or copy link manually</summary>
      <div class="client-url" id="client-url-box"><?=htmlspecialchars($client_url)?></div>
      <button class="btn btn-copy" onclick="copyURL(event)">Copy Link</button>
    </details>
  </div>
  <a class="back-link" href="?step=artist&release=<?=htmlspecialchars($_GET['release'] ?? 'tattoo')?>">&larr; Generate another link</a>
</div>
<script>
var _clientUrl = <?=json_encode($client_url)?>;
function sendLink(type, btn) {
    var input = document.getElementById(type === 'email' ? 'send-email' : 'send-phone');
    var resultEl = document.getElementById(type === 'email' ? 'email-result' : 'sms-result');
    var recipient = input.value.trim();
    if (!recipient) {
        resultEl.innerHTML = '<span style="color:#c00">Please enter a ' + (type === 'email' ? 'email address' : 'phone number') + '.</span>';
        return;
    }
    btn.disabled = true;
    btn.textContent = 'Sending\u2026';
    var fd = new FormData();
    fd.append('type', type);
    fd.append('link', _clientUrl);
    fd.append('recipient', recipient);
    fetch('send_link.php', {method:'POST', body:fd})
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.ok) {
                resultEl.innerHTML = '<span style="color:green">&#10003; Sent!</span>';
            } else {
                resultEl.innerHTML = '<span style="color:#c00">Failed: ' + (d.error || 'unknown error') + '</span>';
            }
            btn.textContent = 'Send Release Form';
            btn.disabled = false;
        })
        .catch(function(){
            resultEl.innerHTML = '<span style="color:#c00">Error - please copy the link manually.</span>';
            btn.textContent = 'Send Release Form';
            btn.disabled = false;
        });
}
function copyURL(e) {
    navigator.clipboard.writeText(document.getElementById('client-url-box').innerText);
    e.target.textContent = 'Copied!';
    setTimeout(function(){ e.target.textContent = 'Copy Link'; }, 2000);
}
</script>
</body>
</html>
<?php
                die;
            }
        }
    }

    // â”€â”€ Step A: artist PIN + form page â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    if (isset($_GET['step']) && $_GET['step'] === 'artist') {
        $pin_error = $pin_error ?? null;
        ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=700, minimum-scale=1.0, maximum-scale=1.5, user-scalable=0" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/default.css" />
<link rel="shortcut icon" href="img/favicon.gif" type="image/x-icon" />
<link rel="icon" href="img/favicon.ico" />
<link rel="apple-touch-icon" href="img/favicon_big.png" />
<script type="text/javascript" src="js/signature.js"></script>
<title>Artist - <?=$settings['name']?> Release Form</title>
<style>
.two-step-wrap{max-width:660px;margin:30px auto;font-family:Arial,sans-serif}
.two-step-card{background:#f9f9f9;border:1px solid #ddd;border-radius:8px;padding:24px 28px;margin-bottom:20px}
h2{margin-top:0}
label{display:block;font-weight:bold;margin:14px 0 4px}
input[type=text],input[type=password],select{width:100%;padding:8px 10px;border:1px solid #bbb;border-radius:4px;font-size:14px;box-sizing:border-box}
.sig-wrap{border:1px solid #bbb;border-radius:4px;background:#fff;position:relative;overflow:hidden}
canvas.signature{display:block}
.btn{display:inline-block;padding:10px 22px;background:#556b5a;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:14px}
.error{color:#c00;background:#fff0f0;border:1px solid #fcc;padding:10px 14px;border-radius:4px;margin-bottom:16px}
.pin-section{margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #ddd}
.clear-btn{font-size:12px;color:#666;cursor:pointer;text-decoration:underline;margin-bottom:6px;display:inline-block}
.sig-status{font-size:12px;margin-top:5px;padding:3px 0}
.sig-status.ok{color:#1a7a1a;font-weight:bold}
.sig-status.err{color:#c00}
.sig-status.wait{color:#888}
</style>
</head>
<body>
<div class="two-step-wrap">
  <div class="two-step-card">
    <h2><?=$settings['name']?> Release Form - Artist Section</h2>
    <?php if ($pin_error) echo '<div class="error">'.htmlspecialchars($pin_error).'</div>'; ?>
    <form method="post" action="?step=artist&release=<?=htmlspecialchars($_GET['release'] ?? 'tattoo')?>">

      <div class="pin-section">
        <label for="artist_pin">Artist PIN:</label>
        <input type="password" name="artist_pin" id="artist_pin" autocomplete="off" required style="max-width:200px" />
      </div>

      <label for="tok_artist">Artist:</label>
      <select name="tok_artist" id="tok_artist" required>
        <option value="">-- Select --</option>
        <?php foreach ($settings['artists'] as $a) {
            $name = is_array($a) ? $a['title'] : $a;
            echo '<option value="'.htmlspecialchars($name).'">'.htmlspecialchars($name).'</option>';
        } ?>
      </select>

      <label for="tok_placement">Placement of Tattoo:</label>
      <input type="text" name="tok_placement" id="tok_placement" required />

      <label>Artist Signature: <span style="color:#c00">*</span></label>
      <span class="clear-btn" onclick="clearArtistDigitalSignature()">Clear</span>
      <div class="sig-wrap">
        <canvas class="signature" id="signature_artist" name="signature_artist"></canvas>
      </div>
      <div id="sig-status" class="sig-status wait">Waiting for valid PIN</div>
      <input type="hidden" name="tok_sig_data" id="tok_sig_data" />
      <input type="hidden" name="signature_artist_status" id="signature_artist_status" value="" />
      <input type="hidden" name="artist_signature_type" id="artist_signature_type" value="" />
      <input type="hidden" name="artist_signature_artist" id="artist_signature_artist" value="" />
      <input type="hidden" name="artist_signature_timestamp" id="artist_signature_timestamp" value="" />

      <label for="tok_client_email">Client's Email Address <em style="font-weight:normal;color:#888">(optional)</em>:</label>
      <input type="email" name="tok_client_email" id="tok_client_email" />

      <label for="tok_client_phone">Client's Cell Phone Number <em style="font-weight:normal;color:#888">(optional)</em>:</label>
      <input type="tel" name="tok_client_phone" id="tok_client_phone" />

      <br /><br />
      <button type="submit" name="artist_step_submit" class="btn">Generate Client Link &rarr;</button>
    </form>
  </div>
</div>
<script>
// canvas_status is normally in ajax.js which is not loaded on this artist step page
function canvas_status(id, status) {
    var el = document.getElementById(id + '_status');
    if (el) el.value = status ? 'yes' : '';
}

function updateArtistSignatureStatus(message, type) {
    var el = document.getElementById('sig-status');
    if (!el) return;
    el.textContent = message;
    el.className = 'sig-status ' + (type || 'wait');
}

function syncArtistSignatureValue() {
    var c = document.getElementById('signature_artist');
    document.getElementById('tok_sig_data').value = c.toDataURL('image/png');
    document.getElementById('signature_artist_status').value = 'yes';
}

function clearArtistDigitalSignature() {
    clearCanvas('signature_artist');
    document.getElementById('tok_sig_data').value = '';
    document.getElementById('signature_artist_status').value = '';
    document.getElementById('artist_signature_type').value = '';
    document.getElementById('artist_signature_artist').value = '';
    document.getElementById('artist_signature_timestamp').value = '';
    updateArtistSignatureStatus('Waiting for valid PIN', 'wait');
}

function applyArtistDigitalSignature(artistName) {
    var c   = document.getElementById('signature_artist');
    var ctx = c.getContext('2d');
    ctx.clearRect(0, 0, c.width, c.height);
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, c.width, c.height);
    // baseline rule
    ctx.strokeStyle = '#ccc';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(20, c.height - 38);
    ctx.lineTo(c.width - 20, c.height - 38);
    ctx.stroke();
    // artist name in cursive-style font
    ctx.fillStyle = '#1a1a8c';
    ctx.font = 'italic bold 30px Georgia, "Times New Roman", serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(artistName, c.width / 2, c.height / 2 - 16);
    // "Digitally Signed" sub-label
    ctx.fillStyle = '#444';
    ctx.font = '12px Arial, sans-serif';
    ctx.fillText('Digitally Signed', c.width / 2, c.height / 2 + 12);
    // timestamp
    var now = new Date();
    var ts  = now.getFullYear() + '-' +
              ('0' + (now.getMonth() + 1)).slice(-2) + '-' +
              ('0' + now.getDate()).slice(-2) + '  ' +
              ('0' + now.getHours()).slice(-2) + ':' +
              ('0' + now.getMinutes()).slice(-2);
    ctx.fillStyle = '#888';
    ctx.font = '11px Arial, sans-serif';
    ctx.fillText(ts, c.width / 2, c.height / 2 + 30);
    // persist metadata
    document.getElementById('artist_signature_type').value      = 'digitally_generated_after_pin_verification';
    document.getElementById('artist_signature_artist').value    = artistName;
    document.getElementById('artist_signature_timestamp').value = new Date().toISOString();
    syncArtistSignatureValue();
    updateArtistSignatureStatus('PIN verified \u2014 digital signature applied', 'ok');
}

function validateArtistPin(artistName, pin) {
    if (!artistName || !pin) { clearArtistDigitalSignature(); return; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '?get_artist_sig', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        try {
            var d = JSON.parse(xhr.responseText);
            if (d.ok) { applyArtistDigitalSignature(artistName); }
            else { clearArtistDigitalSignature(); updateArtistSignatureStatus('Invalid PIN \u2014 signature not applied', 'err'); }
        } catch(e) { updateArtistSignatureStatus('Verification error \u2014 try again', 'err'); }
    };
    xhr.onerror = function() { updateArtistSignatureStatus('Network error \u2014 try again', 'err'); };
    xhr.send('artist=' + encodeURIComponent(artistName) + '&pin=' + encodeURIComponent(pin));
}

document.addEventListener('DOMContentLoaded', function() {
    var c        = document.getElementById('signature_artist');
    var rect     = c.parentElement.getBoundingClientRect();
    c.width      = Math.floor(rect.width) || 472;
    c.height     = 150;
    initialize_signature('signature_artist');
    clearCanvas('signature_artist');
    updateArtistSignatureStatus('Waiting for valid PIN', 'wait');

    // Manual draw: sync hidden field on pen/mouse lift
    ['mouseup', 'touchend'].forEach(function(ev) {
        c.addEventListener(ev, function() {
            var st = document.getElementById('signature_artist_status');
            if (st && st.value === 'yes') {
                document.getElementById('tok_sig_data').value = c.toDataURL('image/png');
                document.getElementById('artist_signature_type').value      = 'manual';
                document.getElementById('artist_signature_timestamp').value = new Date().toISOString();
            }
        });
    });

    var pinInput  = document.getElementById('artist_pin');
    var artistSel = document.getElementById('tok_artist');
    var pinTimer  = null;

    pinInput.addEventListener('input', function() {
        clearTimeout(pinTimer);
        if (!pinInput.value) { clearArtistDigitalSignature(); return; }
        updateArtistSignatureStatus('Checking\u2026', 'wait');
        pinTimer = setTimeout(function() { validateArtistPin(artistSel.value, pinInput.value); }, 500);
    });

    artistSel.addEventListener('change', function() {
        clearArtistDigitalSignature();
        if (pinInput.value) { validateArtistPin(artistSel.value, pinInput.value); }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        var status = document.getElementById('signature_artist_status');
        if (!status || status.value !== 'yes') {
            alert('Please verify your PIN to apply your digital signature, or draw your signature manually.');
            e.preventDefault();
            return false;
        }
        document.getElementById('tok_sig_data').value = c.toDataURL('image/png');
    });
});
</script>
</body>
</html>
<?php
        die;
    }

    // â”€â”€ Step B: client opens token link â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    if (isset($_GET['client'])) {
        $tok_payload = validate_client_token($_GET['client']);
        if (!$tok_payload) {
            ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=700, minimum-scale=1.0, maximum-scale=1.5, user-scalable=0" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/default.css" />
<title>Link Expired</title>
<style>body{font-family:Arial,sans-serif;text-align:center;padding:60px 20px}.card{max-width:480px;margin:0 auto;background:#fff8f8;border:1px solid #fcc;padding:32px;border-radius:8px}</style>
</head>
<body>
<div class="card">
  <h2>This link has expired or is invalid.</h2>
  <p>Please ask Taylor to generate a new link for you.</p>
</div>
</body>
</html>
<?php
            die;
        }
        // Token is valid - store artist data for use in the form below
        define('TWO_STEP_CLIENT', true);
        $prefill_artist    = $tok_payload['artist'];
        $prefill_placement = $tok_payload['placement'];
        $prefill_sig       = $tok_payload['sig'];
        $prefill_token     = $_GET['client'];
    }
}
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€



?><!DOCTYPE html>
<html lang="en">
  <head>
<?php if(isset($base_dir)) {?>
  <base href="<?=$base_dir?>" />
<?php }?>
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="niceforms.css" />
<link rel="stylesheet" media="all and (orientation:portrait)" href="css/portrait.css">
<link rel="stylesheet" media="all and (orientation:landscape)" href="css/landscape.css">

<link rel="shortcut icon" href="img/favicon.gif" type="image/x-icon" />
<link rel="icon" href="img/favicon.ico">
<link rel="apple-touch-icon" href="img/favicon_big.png" />

<meta name="format-detection" content="telephone=no" />
<meta name="viewport" content="width=700, minimum-scale=1.0, maximum-scale=1.5, user-scalable=0" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="HandheldFriendly" content="true"/>
<meta name="MobileOptimized" content="width" />

<?php
	// load skin settings
	if($skin==null) $skin='default';
	if($skin!='print' && !$submit && $settings['skin'] && file_exists('css/'.$settings['skin'].'.css'))
		$skin=$settings['skin'];

	if($settings['skin']=='custom') {
		?><style type="text/css"><?=$settings['skin_custom']?></style><?php
	}

?>
<link rel="stylesheet" type="text/css" href="css/<?=$skin?>.css" />

    <title><?=$settings['name']?> Release Form</title>
  </head>
  <body>
<a name="top"></a>
  <div id="redirect_url" style="display: none;"><?=$redirect_url?></div>
<?php if(!$submit){?>
	<!-- Signature javascript -->
	<script type="text/javascript" src="js/signature.js"></script>
<?php
if(!isset($settings['disable_nice_forms']) && $skin!="print") {
	?><script language="javascript" type="text/javascript" src="niceforms.js"></script><?php
}?>
<script language="javascript" type="text/javascript" src="js/ajax.js"></script>
<?php }?>

<?php
if($settings['play_sound'] && $settings['play_sound'] !== 'none') {
?>
<audio id="email_sent_notification">
	<source src="audio/<?=$settings['play_sound']?>.mp3" type="audio/mpeg" />
	<source src="audio/<?=$settings['play_sound']?>.wav" type="audio/wav" />
</audio>
<?php
}
?>
<form name="MyForm" id="MyForm" action="" method="post" class="niceform">
<?php

	if($settings['title_image'] && $skin != "print") {
		?><img class="title_image" src="<?=$settings['title_image']?>" alt="<?=$settings['name']?> Release Form" /><?php
	} else {
		?>
		<fieldset class="action">
			<h1><?=$settings['name']?> Release Form</h1>
		</fieldset>
		<?php
	}
	
// artist
if(is_array($settings['artists']) && count($settings['artists'])>0) {
	$artist_input='<div class="label" id="input_artist">Artist:<span class="required">*</span></div>';
	// Two-step client view - show locked read-only artist name
	if (defined('TWO_STEP_CLIENT')) {
		$artist_input.='<div class="value"><div class="fake_input">'.htmlspecialchars($prefill_artist).'</div></div>';
		$artist_input.='<input type="hidden" name="artist" value="'.htmlspecialchars($prefill_artist).'" />';
	} elseif($submit) {
		$extra = (is_array($artist) && !empty($artist['extra'])) ? '&nbsp;-&nbsp;'.$artist['extra'] : '';
		$artist_input.='<div class="value">'.$_REQUEST['artist'].$extra.'</div>';
	} else {
		if($skin=="print") {
			$artist_input.='<div class="value"><div class="fake_input"></div></div>';
		} else {
			$artist_input.='<select onchange="artist_select(this)" size="1" name="artist"><option value="-1"> -- Select -- </option>';
			foreach($settings['artists'] as $artist) {
				$name=$artist;
				if(is_array($name)) $name=$name['title'];
				$extra = (is_array($artist) && !empty($artist['extra'])) ? '&nbsp;-&nbsp;'.$artist['extra'] : '';
				$artist_input.='<option value="'.$name.'"'.($_REQUEST['artist']==$name?' selected':'').'>'.$name.$extra.'</option>';
			}
			if($settings['allow_other_artist'])
				$artist_input.='<option value="!other">Other</option>';

			$artist_input.='</select>';
		}
	}
	$artist_input.='</div><div class="break"></div>';
}

// top header
?><fieldset id="top">
	<div class="legend">Let us do this part:</div>
<?php

//date
$date=date("D M j Y h:i:s");
?><div class="label"><?=display_label('Today\'s Date')?></div>
<?php if($submit) {
	if($_REQUEST['date_data'])
		$date=$_REQUEST['date'];
	echo($date);
} else {
	if($skin=="print") {
		?><span class="print_yn">&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/</span><?php
	} else {?>
		<input type="hidden" name="date" id="date_data" />
		<div class="value"><span id="todays_date"><?=$date?></span></div>
<?php
	}
}
?><div class="break"></div><?php

echo $artist_input;

// fields
if(count($settings['fields'])>0) {

	$count=0;
	if(is_array($settings['fields']))
	foreach($settings['fields'] as $field) {
		if(is_array($field)) {
			$array=$field;
			$field=$array['title'];
			$required=$array['required'];
		}
?>
		<div id="input_field_<?=$count?>" class="label"><?=$field?>:<?=$required?'<span class="required">*</span>':''?></div>
		<div class="value">
			<?php
			// Two-step: show locked placement from token
			if (defined('TWO_STEP_CLIENT') && $count === 0) {
				echo '<div class="fake_input">'.htmlspecialchars($prefill_placement).'</div>';
				echo '<input type="hidden" name="fields['.$count.']" value="'.htmlspecialchars($prefill_placement).'" />';
			} elseif($submit || $skin=="print") {?>
				<div class="fake_input"><?=$_POST['fields'][$count]?></div>
			<?php } else {
				if($skin=="print") {
					?><div class="fake_input"></div><?php
				} else {?>
				<input type="text" name="fields[<?=$count?>]" value="" />
				<?php }
			}?>
		</div>

		<div class="break"></div>
	<?php
	$count++;
	}
}

if($settings['artist_signature']) {
	if (defined('TWO_STEP_CLIENT')) {
		// Show locked read-only artist signature from token
		?><div id="input_artist_signature" class="label">Artist Signature:</div>
		<br />
		<img src="<?=htmlspecialchars($prefill_sig)?>" style="max-width:100%;border:1px solid #ddd;border-radius:4px;" />
		<input type="hidden" name="signature_artist_data" value="<?=htmlspecialchars($prefill_sig)?>" />
		<input type="hidden" name="signature_artist_status" value="1" />
		<input type="hidden" name="client_token" value="<?=htmlspecialchars($prefill_token)?>" />
		<?php
	} elseif(!$submit){
		if($skin=="print")
			echo '<div class="fake_signature"></div>';
		else {

		?>
		<div id="input_artist_signature" class="label"><?=display_label('artist signature')?><span class="required">*</span></div>
		<br />
		<span class="clear_signature_container"><input class="clear_signature" type="button" value="Clear" onclick="clearCanvas('signature_artist');" /></span>
		<input type="hidden" name="signature_artist_data" id="signature_artist_data" />
		<input type="hidden" name="signature_artist_status" id="signature_artist_status" value="" />
		<canvas class="signature" id="signature_artist" name="signature_artist"></canvas>
		<?php
		}
	} else {
		?><img src="<?=$_POST['signature_artist_data']?>"><?php
	}
}

if($settings['artist_lock'] && !defined('TWO_STEP_CLIENT')) {
	?><div class="lock"><input type="button" onclick="lock_down('top');" value="Lock this section" /></div><?php
}

?>
</fieldset>
<?php

// header
if(strlen($settings['header'])) {
	?><fieldset class="action">
	<?=$settings['header'];?>
	</fieldset><?php
}


if(count($settings['provisions'])>0 || strlen($settings['provisions_foot'])>0) {
	
?>
	<fieldset>
		<div class="legend">Please read &amp; answer:</div>
<?php	
$count=0;
if(is_array($settings['provisions']))
foreach($settings['provisions'] as $provision) {
	?><div id="input_provision_<?=$count?>"></div><?php
	switch($provision['type']) {
		case 'yn_details':
		case 'yn':
			if($skin=="print") {
				?><span class="print_yn">Y&nbsp;/&nbsp;N</span><?php
			} else
			if($submit) {
				?>
				<img src="img/yn_y_<?=$_POST['provisions'][$count][0]=='y'?'on':'off'?>.png" />
				&nbsp;
				<img src="img/yn_n_<?=$_POST['provisions'][$count][0]=='n'?'on':'off'?>.png" />
				<?php
			} else {
				?>
				<img id="yn_y_<?=$count?>" src="img/yn_y_off.png" onclick="yn_select(<?=$count?>, 'y');" />
				<img id="yn_n_<?=$count?>" src="img/yn_n_off.png" onclick="yn_select(<?=$count?>, 'n');" />
				<input type="hidden" id="provisions[<?=$count?>][0]" name="provisions[<?=$count?>][0]" value="" />
				<?php
			}
			break;
		case 'checklist':
		case 'dropdown':
		case 'text':
			?><div style="width:50px; float: left;">&nbsp;</div><?php
			break;
		case 'checkbox':
		default :
			if($skin=="print") {
				?><div class="fake_checkbox"></div><?php
			} else
			if($submit) {
				if(isset($_REQUEST['provisions'][$count]))
					echo('<img src="img/checked_box.png" />');	
				else
					echo('<img src="img/unchecked_box.png" />');	
			} else {
				?><span class="checkbox_container"><input type="checkbox" class="bigbox" name="provisions[<?=$count?>]" /></span><?php
			}
			break;
		case 'note':
		case 'dropdown':
			break;
	}

			?>
			<span class="provision_title"><?=$provision['title']?><?php if($provision['required']) echo('<span class="required">*</span>');?></span>
<div class="clear_line"></div><?php

if($skin=="print" && $provision['type'] == 'dropdown')
	$provision['type']='text';


switch($provision['type']) {
	case 'checklist':
		$cl_count=0;
		foreach(explode("\n",$provision['text']) as $prov) {
			?><span class="checklist"><?php
				if($skin=="print")
					echo '<div class="fake_checkbox"></div>';
				else 

				if(!$submit) {
					?><input type="checkbox" name="provisions[<?=$count?>][<?=$cl_count?>]" /><?php
				} else {
					if(isset($_POST['provisions'][$count][$cl_count])) {
						echo('<img src="img/checked_box.png" style="margin-right: 20px;"/>');	
					} else {
						echo('<img src="img/unchecked_box.png" style="margin-right: 20px;" />');	
					}
				}
				?><span><?=$prov?></span><?php
			?></span><?php
			$cl_count++;
		}
		?><div class="clear_line"></div><?php
		break;
	case 'dropdown':
		$options = explode("\n",$provision['text']);

		if($submit) {
			echo '<div class="fake_input">'.$options[$_POST['provisions'][$count]].'</div>';
		} else {
			?><select name="provisions[<?=$count?>]">
			<option value="-1">-- Select --</option><?php
			$ob_count=0;
			foreach($options as $prov) {
				echo '<option value="'.$ob_count.'">'.$prov.'</option>';
				$ob_count++;
			}
			?></select><br /><?php
		}
		break;
	case 'yn_details':
		?><div class="provision_text"><?php
		?><div class="label">Details:</div><?php
		if($submit)
			echo '<div class="fake_input">'.$_POST['provisions'][$count][1].'</div>';
		else {
			?><input name="provisions[<?=$count?>][1]" type="text" />&nbsp;<?php
		}
		?></div><?php
		break;
	case 'text':	
		echo nl2br($provision['text']);
		?><div class="provision_text"><?php
		if($submit || $skin=="print") {
			echo '<div class="fake_input">'.$_POST['provisions'][$count].'</div>';
		} else {
			?><input type="text" name="provisions[<?=$count?>]" value="" /><?php
		}
		?>&nbsp;</div><?php
		break;
	case 'note':
		echo '<div class="note">'.nl2br($provision['text']).'</div>';
		break;
	default:
		echo nl2br($provision['text']);
		break;
}
?>
<hr class="provision_break" />
<?php
	$count++;
}
if(strlen($settings['provisions_foot'])>0)
	echo(nl2br($settings['provisions_foot']).'<hr />');
?>
If any provision, section, subsection, clause or phrase of this release is found to be unenforceable or invalid, that portion shall be severed from this contract. The remainder of this contract will then be construed as though the unenforceable portion had never been contained in this document.
	</fieldset>

<?php }?>

	<fieldset>
		<!--<div class="sub_legend"><a href="javascript: force_guardian();">display guardian</a></div>-->
		<div class="legend">Client Information</div>
I hereby declare that I am of legal age (with valid proof of age) and am competent to sign this Agreement or, if not, that my parent or legal guardian shall sign on my behalf, and that my parent or legal guardian is in complete understanding and concurrence with this agreement.
		<div class="break"></div>
		<div id="input_name" class="label"><?=display_label('name')?></div>
		<div class="value"><?=display_input('name',$skin)?></div>
		<div class="break"></div>
			
		<div id="input_address" class="label"><?=display_label('address')?></div>
		<div class="value"><?=display_input('address',$skin)?></div>
		<div class="break"></div>

		<div id="input_dob" class="label"><?=display_label('dob','Date of birth')?></div>
		
			<?php if($skin=="print") {
				?><div class="value"><span class="print_yn">&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/</span><?php
			} else {?>
				<div class="value"><?=display_dob()?>
			<?php
			}
			if($settings['age_limit'] > 0)
				echo '<div id="age_note" class="under_age no_label">If you are under <b>'.$settings['age_limit'].'</b> your parent/guardian will also need to fill out the guardian section.</div>';
			else if ($settings['age_limit'] < 0)
				echo '<div id="age_note" class="under_age no_label">You must be '.-$settings['age_limit'].' or older to have this procedure done.';
?>
		</div>
		<div class="break"></div>

		<div id="input_phone" class="label"><?=display_label('phone','Phone #')?></div>
		<div class="value"><?=display_input('phone',$skin)?></div>
		<div class="break"></div>

		<div id="input_email" class="label"><?=display_label('email')?></div>
		<div class="value"><?=display_input('email',$skin)?></div>

			<?php
				if($settings['enable_newsletter'] && $skin !='print') {
				?><div class="no_label" id="input_newsletter" style="clear: both;"><?php
				if(!$submit){?>
					<input class="newsletter_check" type="checkbox" name="newsletter"<?=($settings['newsletter_checked']?' checked="checked"':'')?> />
				<?php } else {
					if(isset($_REQUEST['newsletter']) && $_REQUEST['newsletter']) {
						echo('<img src="img/checked_box.png" />');	
					} else {
						echo('<img src="img/unchecked_box.png" />');	
					}
				}?>
				<span style="position: relative; bottom: 8px;">Sign up for our newsletter.</span>
				</div>
			<?php }?>
			
			<div class="break"></div>

			<div id="input_signature" class="label"><?=display_label('signature')?><span class="required">*</span></div>
			<br />
			<?php if(!$submit){?>
			<span class="clear_signature_container"><input class="clear_signature" type="button" value="Clear" onclick="clearCanvas('signature_client')" /></span>
		<?php }?>
				<?php if(!$submit){
					if($skin=="print")
						echo '<div class="fake_signature"></div>';
					else {

?>
					<input type="hidden" name="signature_client_data" id="signature_client_data" />
					<input type="hidden" name="signature_client_status" id="signature_client_status" value="" />
					<canvas class="signature" id="signature_client" name="signature_client"></canvas>
					<?php }
				} else {
					?><img src="<?=$_POST['signature_client_data']?>"><?php
				}?>
		<div class="break"></div>
		<div class="label">Sign or type signature:</div>
		<div class="value">
			<?php if(!$submit){?>
                  <input type="text" name="signature_client_text" id="signature_client_text" oninput="if(typeof typeToCanvas==='function') typeToCanvas(this.value, 'signature_client')" />
			<?php } else { ?>
			<div class="fake_input"><?=htmlspecialchars($_POST['signature_client_text'] ?? '')?></div>
			<?php } ?>
		</div>
		<div class="break"></div>
	</fieldset>

	<input type="hidden" name="require_guardian" id="require_guardian" value="0" />

<?php if((!$submit && $settings['age_limit']) || ($skin=="print") ||  dob_to_age()<$settings['age_limit']){?>
	<fieldset <?=!$submit?' style="display: none;"':''?> id="parent">
		<div class="legend">Parent/Legal Guardian</div>
By the parental/guardian signature they, on my behalf, release all claims that both they and I have.<br />
		<?=$settings['parent_header']?>
		<hr />	
		<div id="input_parent_name" class="label"><?=display_label('parent_name')?><span class="required">*</span></div>
		<div class="value"><?=display_input('parent_name',$skin)?></div>
		<div class="break"></div>
					
		<div id="input_parent_signature" class="label"><?=display_label('signature')?><span class="required">*</span></div>
					<br />
					<?php if(!$submit){?>
					<span class="clear_signature_container"><input class="clear_signature" type="button" value="Clear" onclick="clearCanvas('signature_parent')" /></span>
					<?php }?>
				<?php if(!$submit){
					if($skin=="print")
						echo '<div class="fake_signature"></div>';
					else {
	

?>
					<input type="hidden" name="signature_parent_data" id="signature_parent_data" />
					<input type="hidden" name="signature_parent_status" id="signature_parent_status" value="" />
					<canvas class="signature" id="signature_parent" name="signature_parent"></canvas>
				<?php
					}
				} else {
					?><img class="signature" src="<?=$_POST['signature_parent_data']?>"><?php
				}?>
	</fieldset>
<?php }?>

<?php if($settings['enable_camera'] && $skin != "print") {?>
	<fieldset id="camera_container">
		<div id="input_photo" class="legend">Photo Identification<?=$settings['photo_required']?'<span class="required">*</span>':''?></div>

		<?php if($submit) {
			if($_POST['photo_data']) {
				// here we go! Crazy ass url->blob->img->resize->blob->url time!
				$img = new Imagick();
				$url_data=$_POST['photo_data'];
				$pre=substr($url_data,0,strpos($url_data,',')+1);
				$url_data=substr($url_data,strpos($url_data,',')+1);
				$decoded=$url_data;
				$decoded=base64_decode($url_data);
				$img->readimageblob($decoded);
				$img->resizeImage($cam_width,$cam_height,Imagick::FILTER_LANCZOS,1);
				$blob = $img->getImageBlob();
				$url_data=$pre.base64_encode($blob);
				
				?><img class="photo" src="<?=$url_data?>" /><?php
			}
		} else {

			// check browser/os support. Fuck you apple.
			if(is_ios())
				$camera = 'os';
			else
				$camera = 'live';
	
			//$camera = 'os';
			if(is_ios() && ios_ver()<6) {
				$camera_status = true;	// force it to go
				echo'This feature is only available for iOS 6+. <a href="http://support.apple.com/kb/HT4623">Consider upgrading</a>';
			}

			switch($settings['camera_mode']) {
				case 'live':
				case 'photo':
				case 'os':
					$camera = $settings['camera_mode'];
			}
			// keep 'legacy' option here, but if hidden if live view is selected
			?>
			<input type="hidden" name="photo_data" id="photo_data" />
			<input type="hidden" name="photo_status" id="photo_status" value="<?=$camera_status?>" />

			<p class="photo-helper-text">Please provide a clear photo of your <strong>government issued photo ID</strong>.</p>

			<div class="photo-mode-buttons">
				<button type="button" id="btn-upload-mode" class="photo-mode-btn photo-mode-active" onclick="switchPhotoMode('upload')">&#128206; Upload ID Photo</button>
				<button type="button" id="btn-camera-mode" class="photo-mode-btn" onclick="switchPhotoMode('camera')">&#128247; Take Photo</button>
			</div>

			<div id="uploadcontainer">
				<label id="os_photo_msg" for="os_photo" class="photo-drop-zone">
					<div class="photo-drop-icon">&#11014;</div>
					<span class="photo-drop-label">Tap to select an image</span>
					<span class="photo-drop-hint">JPG or PNG &middot; max 10 MB</span>
				</label>
				<input type="file" accept="image/jpeg,image/jpg,image/png" id="os_photo" name="os_photo" onchange="if(os_camera_upload()) canvas_status('photo',1);" />
				<div id="photo-preview-area" style="display:none">
					<img id="photo-preview-img" src="" alt="ID Preview" />
					<div class="photo-preview-actions">
						<span id="photo-file-info" class="photo-file-info"></span>
						<button type="button" class="photo-remove-btn" onclick="removeUploadedPhoto()">&#10005; Remove &amp; retake</button>
					</div>
				</div>
				<div id="photo-error-msg" class="photo-error" style="display:none"></div>
			</div>

			<div id="cameracontainer" style="display:none">
				<p class="camera-instruction">Ensure all 4 corners of your ID are visible within the frame.</p>
				<div class="camera-viewport">
					<video class="photo" id="monitor" autoplay playsinline></video>
					<canvas class="photo" id="photo" name="photo"></canvas>
					<div class="camera-id-overlay"></div>
				</div>
				<div id="snapbutton-area">
					<input type="button" id="snapbutton" class="photo-capture-btn" value="CAPTURE PHOTO" onclick="snapshot()" />
				</div>
				<div id="retake-btn-area" style="display:none">
					<input type="button" id="retake-btn" class="photo-retake-btn" value="&#8629; RETAKE" onclick="retakePhoto()" />
				</div>
			</div>

			<div id="photo-checklist" style="display:none">
				<ul class="id-checklist">
					<li><span class="chk-ok">&#10003;</span> ID fully visible</li>
					<li><span class="chk-ok">&#10003;</span> No glare or reflections</li>
					<li><span class="chk-ok">&#10003;</span> Sharp and in focus</li>
					<li><span class="chk-ok">&#10003;</span> All text is readable</li>
					<li><span class="chk-ok">&#10003;</span> No shadows</li>
				</ul>
			</div>

			<script type="text/javascript" src="js/camera.js"></script>
			<script>
			var photo_camera_mode = '<?=$camera?>';
			var photo_panel_initialized = false;
			function switchPhotoMode(mode) {
				var upl = document.getElementById('uploadcontainer');
				var cam = document.getElementById('cameracontainer');
				var btnU = document.getElementById('btn-upload-mode');
				var btnC = document.getElementById('btn-camera-mode');
				if (mode === 'camera') {
					upl.style.display = 'none';
					cam.style.display = 'block';
					btnC.classList.add('photo-mode-active');
					btnU.classList.remove('photo-mode-active');
					if (!photo_panel_initialized) {
						photo_panel_initialized = true;
						initializeCameraCapture(photo_camera_mode);
					}
				} else {
					upl.style.display = 'block';
					cam.style.display = 'none';
					btnU.classList.add('photo-mode-active');
					btnC.classList.remove('photo-mode-active');
				}
			}
			</script>
			<?php
		}
	?>
		<div class="break"></div>
	</fieldset><?php
}

	if(!$submit) {?>
		<fieldset class="action" id="submit">
			<span class="passcode" id="enter_passcode">
				<?php if($settings['enable_passcode']) {?>
					Enter passcode to submit:
					<?php for($a=0;$a<5;$a++) {
					?><span><input onClick="if(enterpasscode(this.value)) sendpdf();" type="button" value="<?=$a?>"></span><?php	
					}
				}?>
			</span>	

			<input type="button" name="complete" id="complete" value="I have completed this form" onClick="hide(); setTimeout('if(submit_form()){<?=$settings['enable_passcode']?'display_passcode();':' sendpdf();'?>}',10);" />
		</fieldset>
	<?php }?>
</form>

<div class="container" id="container"></div>
<!--<div class="containerClose" id="containerClose">x</div>-->

<div id="ResponseDiv"><!-- onclick="popup();">-->
	<div id="close_link" onclick="popup();"><img id="close_button" class="close" src="img/close.png" /></div>
	<div id="Response"></div>
</div>

<?php if(!$submit){?>
	<script language="javascript">draw_init();</script>
<?php }?>

<script language="javascript">

var inputs = document.getElementsByTagName('input');
for(a=0; a<inputs.length; a++)
{
/*	inputs[a].addEventListener('touchstart',	function (event) { event.preventDefault(); }, false);
	inputs[a].addEventListener('touchmove',		function (event) { event.preventDefault(); }, false);
inputs[a].addEventListener('touchend',		function (event) { event.preventDefault(); }, false);*/
}
</script>

  </body>
</html>

<?php
if($submit && !isset($_GET['debug'])) {
	// grab entire html
	$html=ob_get_contents();
	ob_end_clean();

	$client = array();

	// post to client info	
	$client['email'] 	= $_POST['email'];
	$client['name'] 	= $_POST['name'];
	$client['address'] 	= $_POST['address'];
	$client['phone'] 	= $_POST['phone'];
	$client['dob']		= $_POST['dobY'].'-'.$_POST['dobM'].'-'.$_POST['dobD'];
	$client['artist'] 	= $_POST['artist'];
	$client['release'] 	= $settings['name'];

	// news letter
	if($settings['enable_newsletter'])
		if($_POST['newsletter'])
			$client['newsletter']=true;

	// custom fields
	if(is_array($settings['fields'])) {
		for($a=0; $a<count($settings['fields']); $a++) {
			$client['custom_fields'][$a] = array(
				'key'=>$settings['fields'][$a]['title'],
				'value'=>$_POST['fields'][$a],
			);
		}
	}
//
   $pdf_result = send_pdf($html,$_POST['email']);
   if($pdf_result === true) {
      echo "sent";
   } else {
      http_response_code(200);
      echo 'error:' . (is_string($pdf_result) ? $pdf_result : 'PDF/email failed');
   }
}

if($skin=="print") {
	$html=ob_get_contents();
	ob_end_clean();

if(isset($_GET['debug1'])) {
	echo $html; die;
}

	// use DOMPDF
	require_once __DIR__ . '/vendor/autoload.php';
		
	// create pdf
/*	$doc = new DOMDocument();
	$doc->loadHTML($html);
	$html = $doc->saveHTML();*/

	// render html2pdf
	$file_name=$settings['name']." Release Form";
	$dompdf = new \Dompdf\Dompdf();
	$dompdf->loadHtml($html);
	$dompdf->render();
	$dompdf->stream($file_name.".pdf");

}
?>
