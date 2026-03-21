<?php

// â”€â”€ Two-step form: token helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Save signature image data to a temp file; return the key.
function save_sig_temp($signature_data) {
    $dir = __DIR__ . '/tmp';
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    // Write .htaccess to deny direct HTTP access on first use
    $htaccess = $dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "deny from all\n");
    }
    $key  = bin2hex(random_bytes(16)); // 32-char hex key
    $file = $dir . '/' . $key . '.dat';
    file_put_contents($file, $signature_data);
    return $key;
}

// Load signature data from temp file by key.
function load_sig_temp($key) {
    if (!preg_match('/^[0-9a-f]{32}$/', $key)) return '';
    $file = __DIR__ . '/tmp/' . $key . '.dat';
    if (!file_exists($file)) return '';
    return file_get_contents($file);
}

function generate_client_token($artist, $placement, $signature_data, $client_email, $client_phone, $release) {
    require_once __DIR__ . '/smtp_config.php';
    // Store the signature image server-side so it doesn't bloat the URL
    $sig_ref = save_sig_temp($signature_data);
    $payload = array(
        'artist'    => $artist,
        'placement' => $placement,
        'sig_ref'   => $sig_ref,   // reference key, not the image data
        'cemail'    => $client_email,
        'cphone'    => $client_phone,
        'release'   => $release,
        'exp'       => time() + (72 * 3600),
    );
    $json    = base64_encode(json_encode($payload));
    $hmac    = hash_hmac('sha256', $json, TOKEN_SECRET);
    return $json . '.' . $hmac;
}

function validate_client_token($token) {
    require_once __DIR__ . '/smtp_config.php';
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) return false;
    list($json, $hmac) = $parts;
    $expected = hash_hmac('sha256', $json, TOKEN_SECRET);
    if (!hash_equals($expected, $hmac)) return false;
    $payload = json_decode(base64_decode($json), true);
    if (!$payload || $payload['exp'] < time()) return false;
    // Resolve sig_ref â†’ actual image data
    if (!empty($payload['sig_ref']) && empty($payload['sig'])) {
        $payload['sig'] = load_sig_temp($payload['sig_ref']);
    }
    return $payload;
}

function check_artist_pin($pin) {
    require_once __DIR__ . '/smtp_config.php';
    return hash_equals(ARTIST_PIN, (string)$pin);
}

function has_artist_pin($name) {
    $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($name)));
    if (!$slug) return false;
    return file_exists(__DIR__ . '/configs/sigs/' . $slug . '_pin.dat');
}

function verify_artist_pin($name, $pin) {
    $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($name)));
    if (!$slug) return false;
    $path = __DIR__ . '/configs/sigs/' . $slug . '_pin.dat';
    if (!file_exists($path)) return false;
    $hash = trim(file_get_contents($path));
    return password_verify((string)$pin, $hash);
}

function save_artist_pin($name, $pin) {
    $pin = (string)$pin;
    if (!preg_match('/^[0-9]{4,8}$/', $pin)) return;
    $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($name)));
    if (!$slug) return;
    $dir = __DIR__ . '/configs/sigs';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($dir . '/' . $slug . '_pin.dat', password_hash($pin, PASSWORD_BCRYPT));
}

function save_artist_sig($artist_name, $sig_data) {
    // Persist the artist signature so it can be auto-filled next visit
    $dir  = __DIR__ . '/configs/sigs';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($artist_name)));
    if (!$slug) return;
    // store only the base64 payload (strip data:image/png;base64, prefix)
    $data = preg_replace('/^data:[^;]+;base64,/', '', $sig_data);
    file_put_contents($dir . '/' . $slug . '.dat', $data);
}

function get_artist_sig($artist_name) {
    $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($artist_name)));
    $path = __DIR__ . '/configs/sigs/' . $slug . '.dat';
    if (!$slug || !file_exists($path)) return null;
    return 'data:image/png;base64,' . trim(file_get_contents($path));
}

function send_link_email($to, $link) {
    require_once __DIR__ . '/smtp_config.php';
    require_once __DIR__ . '/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/phpmailer/SMTP.php';
    require_once __DIR__ . '/phpmailer/Exception.php';
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->setFrom(SMTP_FROM, SMTP_NAME);
        $mail->addAddress($to);
        $mail->Subject = 'Your Tattoo Release Form - Taylor Helman Tattoo';
        $mail->isHTML(true);
        $safeLink = htmlspecialchars($link);
        $mail->Body    = '<p>Hi,</p>'
            . '<p>Your tattoo release form is ready to complete. Please click the link below:</p>'
            . '<p><a href="' . $safeLink . '">' . $safeLink . '</a></p>'
            . '<p>This link expires in 72 hours.</p>'
            . '<p>- Taylor Helman Tattoo</p>';
        $mail->AltBody = "Your tattoo release form is ready.\n\nPlease complete it here:\n$link\n\nThis link expires in 72 hours.\n\n- Taylor Helman Tattoo";
        $mail->send();
        return ['ok' => true];
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

/**
 * validateTwilioConfiguration — pre-flight check for all required SMS constants.
 * Returns ['ok' => true] if all constants are defined and non-empty,
 * or ['ok' => false, 'error' => '...'] with a human-readable message.
 */
function validateTwilioConfiguration() {
    require_once __DIR__ . '/smtp_config.php';
    $missing = [];
    foreach (['TWILIO_SID', 'TWILIO_TOKEN', 'TWILIO_FROM'] as $k) {
        if (!defined($k) || !constant($k)) $missing[] = $k;
    }
    if ($missing) {
        return ['ok' => false, 'error' => 'SMS not configured -- missing: ' . implode(', ', $missing) . '. Add them to smtp_config.php'];
    }
    return ['ok' => true];
}

function send_link_sms($phone, $link) {
    $cfg = validateTwilioConfiguration();
    if (!$cfg['ok']) return $cfg;

    // Normalise to E.164 (+1XXXXXXXXXX for US numbers)
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($digits) === 10) {
        $e164 = '+1' . $digits;
    } elseif (strlen($digits) === 11 && $digits[0] === '1') {
        $e164 = '+' . $digits;
    } elseif (substr($phone, 0, 1) === '+') {
        $e164 = '+' . $digits;
    } else {
        return ['ok' => false, 'error' => 'Invalid phone number -- please enter a 10-digit US number'];
    }

    $message = "Your tattoo release form from Taylor Helman is ready.\n\nPlease complete it here:\n" . $link . "\n\nThis link expires in 72 hours.";
    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_SID . '/Messages.json';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query(['From' => TWILIO_FROM, 'To' => $e164, 'Body' => $message]));
    curl_setopt($ch, CURLOPT_USERPWD,        TWILIO_SID . ':' . TWILIO_TOKEN);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        15);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code >= 200 && $http_code < 300) {
        return ['ok' => true];
    }
    $data = json_decode($response, true);
    $twilio_msg = $data['message'] ?? 'SMS failed (HTTP ' . $http_code . ')';
    if ($http_code === 400 && isset($data['code']) && $data['code'] === 21608) {
        $twilio_msg = 'This Twilio trial account can only send to verified numbers. Verify the recipient at twilio.com or upgrade your account.';
    } elseif ($http_code === 401) {
        $twilio_msg = 'Twilio authentication failed -- check TWILIO_SID and TWILIO_TOKEN in smtp_config.php';
    }
    error_log('send_link_sms failed: HTTP ' . $http_code . ' -- ' . $twilio_msg . ' -- To: ' . $e164);
    return ['ok' => false, 'error' => $twilio_msg];
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

function debug_server()
{

$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

?><pre><?php
echo 'Max Upload: '.$max_upload;
?><br /><?php
echo 'Max Post: '.$max_post;
?><br /><?php
echo 'Mem Limit: '.$memory_limit;
?><br /><?php
echo 'Upload MB: '.$upload_mb;
?></pre><?php

}

function resizeImage($filename,$toWidth,$toHeight) {
	$image = new Imagick($filename);
	$image->resizeImage($toWidth,$toHeight,Imagick::FILTER_LANCZOS,1);
	$image->writeImage($filename);
	return true;
}

function resizeImage_old($originalImage,$toWidth,$toHeight){ 
    // Get the original geometry and calculate scales 
    list($width, $height) = getimagesize($originalImage); 
    $xscale=$width/$toWidth; 
    $yscale=$height/$toHeight; 

    if($width<=$toWidth && $height<=$toHeight)
	    return true;

    // Recalculate new size with default ratio 
    if ($yscale>$xscale){ 
        $new_width = round($width * (1/$yscale)); 
        $new_height = round($height * (1/$yscale)); 
    } 
    else { 
        $new_width = round($width * (1/$xscale)); 
        $new_height = round($height * (1/$xscale)); 
    } 
    $imageResized = imagecreatetruecolor($new_width, $new_height); 
    $imageTmp = imagecreatefromjpeg ($originalImage);

//    if(!$imageTmp)  return false;

    imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    $result = imagejpeg($imageResized, $originalImage, 75);
    return true;
} 

function file2base64($file, $toWidth=null, $toHeight=null) {
	$imgtype = array('jpg', 'gif', 'png');

	$filename = $file['tmp_name'];

	$filetype = $file['type'];
	$filetype = str_replace('jpeg', 'jpg', $filetype);
	$filetype = explode('/', $filetype);
	$filetype = $filetype[count($filetype)-1];

	if($toWidth || $toHeight)
		resizeImage($filename,$toWidth,$toHeight);

	if (in_array($filetype, $imgtype)){
		$imgbinary = fread(fopen($filename, "r"), filesize($filename));
		//echo '<img src="data:image/' . $filetype . ';base64,' . base64_encode($imgbinary).'" />';
		return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
	}
}



function is_ios()
{

	if(
		strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||
		strstr($_SERVER['HTTP_USER_AGENT'],'iPod') ||
		strstr($_SERVER['HTTP_USER_AGENT'],'iPad')
	) {
		return true;
	}

	return false;
}

function ios_ver()
{
	preg_match('/OS (.+?) like/', $_SERVER['HTTP_USER_AGENT'], $ret);
	$ver=$ret['1'];
	$ver=str_replace('_','.',$ver);
	$ver=intval($ver);
	return $ver;
}

function dob_to_age($birthDate=null)
{
	if(!$birthDate) {
		// age
		$birthDate=array(
			0=>$_REQUEST['dobM'],
			1=>$_REQUEST['dobD'],
			2=>$_REQUEST['dobY'],
		);
	}

	// if passing a string, not array
	if(is_string($birthDate)) {
		$birthDate = explode('-',$birthDate);
		if($birthDate[0]>12) { //must be backwards!
			$birthDate=array_reverse($birthDate);
		}
	}


        $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md") ? ((date("Y")-$birthDate[2])-1):(date("Y")-$birthDate[2]));
	
	return $age;
}

function send_pdf($html, $email) {
	global $settings;


	/* use attach class 
	include('attach_mailer_class.php');
 
	$subject=$settings['email_subject']." [".$_REQUEST['name']."]";
	$body = $settings['email_text'];
	$body = nl2br($body);
	$from = 'noreply@stabpad.com';
	$reply_to = $settings['email_from'];
	$name = explode('@', $settings['email_from']);
	$name = $name[1];
	$name = "joel Tron";

	// create pdf
	$doc = new DOMDocument();
	$doc->loadHTML($html);
	$html = $doc->saveHTML();

	// render html2pdf
	require_once("dompdf/dompdf_config.inc.php");
	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	$dompdf->render();

	// generate chunks and name
	$f_name="release_form_";
	$f_name.=preg_replace("/[^A-Za-z0-9 ]/", '_', $_REQUEST['name']);
	$f_name.=".pdf";

	$f_contents = chunk_split(base64_encode($dompdf->output())); 

	$my_mail = new attach_mailer($name, $from, $email, $reply_to, $cc = "", $bcc = "", $subject, $body); 
	$my_mail->attach_attachment_part($f_contents, $f_name);

//	echo 'force fail';

	if(!$my_mail->process_mail()) {
		echo "email didn't send....";
		return false;
	}
	return true;
*/

	// normal sending
	// clean up html
	/*$config = array('indent' => TRUE,
                'output-xhtml' => TRUE,
                'wrap' => 200);

	$tidy = tidy_parse_string($html, $config, 'UTF8');
	$tidy->cleanRepair();
	$html=$tidy;*/

    // use DOMPDF — guard against missing vendor/ on server
    $autoload_path = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload_path)) {
        error_log('send_pdf: vendor/autoload.php not found');
        return 'vendor/autoload.php missing - DOMPDF not installed on server. Upload the vendor/ directory.';
    }
    require_once $autoload_path;

    // render html2pdf
    try {
        $options = new \Dompdf\Options();
        $options->setChroot(__DIR__);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setBasePath(__DIR__ . '/');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();
    } catch (\Throwable $e) {
        error_log('send_pdf DOMPDF: ' . $e->getMessage());
        return 'DOMPDF render failed: ' . $e->getMessage();
    }

    // email pdf via PHPMailer + Gmail SMTP
    $pm_path = __DIR__ . '/phpmailer/PHPMailer.php';
    if (!file_exists($pm_path)) {
        error_log('send_pdf: phpmailer/PHPMailer.php not found');
        return 'phpmailer/ directory missing from server.';
    }
    require_once __DIR__ . '/smtp_config.php';
    require_once $pm_path;
    require_once __DIR__ . '/phpmailer/SMTP.php';
    require_once __DIR__ . '/phpmailer/Exception.php';

    $f_name  = 'release_form.pdf';
    $pdf     = $dompdf->output();
    $subject = $settings['email_subject'] . ' [' . $_REQUEST['name'] . ']';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->setFrom(SMTP_FROM, SMTP_NAME);
        $mail->addAddress($settings['email_to']);
        if ($email) { $mail->addCC($email); }
        $mail->addReplyTo($settings['email_from']);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = nl2br(htmlspecialchars($settings['email_text']));
        $mail->AltBody = $settings['email_text'];
        $mail->addStringAttachment(
            $pdf, $f_name,
            \PHPMailer\PHPMailer\PHPMailer::ENCODING_BASE64,
            'application/pdf'
        );
        $mail->send();
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('send_pdf PHPMailer: ' . $e->getMessage());
        return 'Email failed: ' . $e->getMessage();
    }
}

// ── Client-initiated form: data persistence ─────────────────────────────────

function save_client_data($data) {
    $dir = __DIR__ . '/tmp';
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    $htaccess = $dir . '/.htaccess';
    if (!file_exists($htaccess)) file_put_contents($htaccess, "deny from all\n");
    $key  = bin2hex(random_bytes(16));
    file_put_contents($dir . '/' . $key . '_cdata.dat', json_encode($data, JSON_UNESCAPED_UNICODE));
    return $key;
}

function load_client_data($key) {
    if (!preg_match('/^[0-9a-f]{32}$/', $key)) return null;
    $file = __DIR__ . '/tmp/' . $key . '_cdata.dat';
    if (!file_exists($file)) return null;
    $data = json_decode(file_get_contents($file), true);
    if (!$data || (isset($data['exp']) && $data['exp'] < time())) return null;
    return $data;
}

function delete_client_data($key) {
    if (!preg_match('/^[0-9a-f]{32}$/', $key)) return;
    @unlink(__DIR__ . '/tmp/' . $key . '_cdata.dat');
}

// ── Client-initiated form: Taylor notification email ─────────────────────────

function send_artist_notification_email($to, $completion_url, $client_name, $client_email = '') {
    require_once __DIR__ . '/smtp_config.php';
    require_once __DIR__ . '/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/phpmailer/SMTP.php';
    require_once __DIR__ . '/phpmailer/Exception.php';
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->setFrom(SMTP_FROM, SMTP_NAME);
        $mail->addAddress($to);
        $safe_name  = htmlspecialchars($client_name);
        $safe_email = htmlspecialchars($client_email);
        $safe_url   = htmlspecialchars($completion_url);
        $mail->Subject = 'Release Form Submitted — Complete Artist Section: ' . $client_name;
        $mail->isHTML(true);
        $mail->Body = '<p>A client has submitted their portion of the tattoo release form and is awaiting your completion.</p>'
            . '<p><strong>Client:</strong> ' . $safe_name
            . ($client_email ? '<br /><strong>Email:</strong> ' . $safe_email : '') . '</p>'
            . '<p>Open the link below to add the artist section and send the finalised PDF. <strong>Link valid 72 hours.</strong></p>'
            . '<p><a href="' . $safe_url . '">' . $safe_url . '</a></p>'
            . '<p>&mdash; Taylor Helman Tattoo</p>';
        $mail->AltBody = "Client {$client_name} has submitted their release form portion.\n\n"
            . "Complete the artist section here (link valid 72 h):\n{$completion_url}\n\n&mdash; Taylor Helman Tattoo";
        $mail->send();
        return ['ok' => true];
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('send_artist_notification_email: ' . $mail->ErrorInfo);
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

// ── Client-initiated form: final PDF (both stages complete) ──────────────────
// Called when Taylor completes the artist section for a client-initiated form.
// Renders a self-contained HTML document from merged $all_data, PDFs it, emails it.

function send_completion_pdf($all_data, $settings) {
    $esc = function($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); };

    // Provisions
    $prov_html  = '';
    $prov_count = 0;
    foreach (($settings['provisions'] ?? []) as $prov) {
        $val = $all_data['provisions'][$prov_count] ?? null;
        $prov_html .= '<tr><td class="lbl">' . $esc($prov['title']) . '</td><td>';
        switch ($prov['type'] ?? 'checkbox') {
            case 'yn':
            case 'yn_details':
                $ans = is_array($val) ? strtoupper($val[0] ?? '') : strtoupper((string)$val);
                $prov_html .= '<strong>' . ($ans === 'Y' ? 'YES' : ($ans === 'N' ? 'NO' : '&mdash;')) . '</strong>';
                if (($prov['type'] ?? '') === 'yn_details' && is_array($val) && !empty($val[1]))
                    $prov_html .= ' &mdash; ' . $esc($val[1]);
                break;
            case 'checkbox':
                $prov_html .= $val ? '&#9745; Agreed' : '&#9744; Not checked';
                break;
            default:
                $prov_html .= $esc(is_array($val) ? implode(', ', $val) : $val);
        }
        $prov_html .= '</td></tr>';
        $prov_count++;
    }

    $artist_sig_html = !empty($all_data['signature_artist_data'])
        ? '<img src="' . $esc($all_data['signature_artist_data']) . '" style="max-width:380px;max-height:110px;border:1px solid #bbb;" />'
        : '&mdash;';

    $client_sig_html = '';
    if (!empty($all_data['signature_client_data'])) {
        $client_sig_html = '<img src="' . $esc($all_data['signature_client_data']) . '" style="max-width:380px;max-height:110px;border:1px solid #bbb;" />';
    } elseif (!empty($all_data['signature_client_typed'])) {
        $client_sig_html = '<span style="font-size:20px;font-style:italic;font-family:Georgia,serif;color:#1a1a8c;">'
            . $esc($all_data['signature_client_typed']) . '</span>';
    } else {
        $client_sig_html = '&mdash;';
    }

    $dob = '';
    if (!empty($all_data['dobY']) && !empty($all_data['dobM']) && !empty($all_data['dobD']))
        $dob = sprintf('%04d-%02d-%02d', $all_data['dobY'], $all_data['dobM'], $all_data['dobD']);

    $form_name = $settings['name'] ?? 'Tattoo';
    $business  = $settings['business_name'] ?? ($settings['name'] ?? '');
    $header    = $settings['header'] ?? '';

    $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8" />'
        . '<title>' . $esc($form_name) . ' Release Form</title>'
        . '<style>'
        . 'body{font-family:Arial,sans-serif;font-size:12px;color:#111;margin:20px}'
        . 'h1{font-size:16px;margin-bottom:2px}'
        . 'h2{font-size:13px;border-bottom:1px solid #999;padding-bottom:3px;margin-top:16px}'
        . 'table{border-collapse:collapse;width:100%;margin-bottom:10px}'
        . 'td,th{padding:4px 8px;border:1px solid #ccc;vertical-align:top}'
        . 'th{background:#f0f0f0;text-align:left}'
        . 'td.lbl{font-weight:bold;width:140px;background:#fafafa}'
        . '</style></head><body>'
        . '<h1>' . $esc($form_name) . ' Release Form</h1>'
        . '<p style="font-size:11px;color:#555">' . $esc($business) . ($header ? ' &mdash; ' . $esc($header) : '') . '</p>'
        . '<p style="font-size:11px;color:#555">'
        . 'Client submitted: ' . $esc($all_data['submitted_at'] ?? '') . ' &nbsp;|&nbsp; '
        . 'Artist completed: ' . $esc($all_data['artist_completed_at'] ?? '') . '</p>'
        . '<h2>Client Information</h2><table>'
        . '<tr><td class="lbl">Name</td><td>'    . $esc($all_data['name']    ?? '') . '</td></tr>'
        . '<tr><td class="lbl">Address</td><td>' . $esc($all_data['address'] ?? '') . '</td></tr>'
        . '<tr><td class="lbl">Date of Birth</td><td>' . $esc($dob) . '</td></tr>'
        . '<tr><td class="lbl">Phone</td><td>'   . $esc($all_data['phone']   ?? '') . '</td></tr>'
        . '<tr><td class="lbl">Email</td><td>'   . $esc($all_data['email']   ?? '') . '</td></tr>'
        . '</table>'
        . '<h2>Artist Section</h2><table>'
        . '<tr><td class="lbl">Artist</td><td>'    . $esc($all_data['artist'] ?? '') . '</td></tr>'
        . '<tr><td class="lbl">Placement</td><td>' . $esc($all_data['fields'][0] ?? '') . '</td></tr>'
        . '</table>';
    if ($prov_html) {
        $html .= '<h2>Provisions</h2><table>'
            . '<tr><th>Provision</th><th>Response</th></tr>'
            . $prov_html . '</table>';
    }
    $html .= '<h2>Signatures</h2><table>'
        . '<tr><td class="lbl">Artist Signature</td><td>' . $artist_sig_html . '</td></tr>'
        . '<tr><td class="lbl">Client Signature</td><td>' . $client_sig_html . '</td></tr>'
        . '<tr><td class="lbl">E-Sign Consent</td><td>'  . (!empty($all_data['esign_consent']) ? '&#9745; Agreed' : '&mdash;') . '</td></tr>'
        . '</table></body></html>';

    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload)) return 'vendor/autoload.php missing — DOMPDF not installed on server';
    require_once $autoload;
    try {
        $opts = new \Dompdf\Options();
        $opts->setChroot(__DIR__);
        $dompdf = new \Dompdf\Dompdf($opts);
        $dompdf->setBasePath(__DIR__ . '/');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();
    } catch (\Throwable $e) {
        error_log('send_completion_pdf DOMPDF: ' . $e->getMessage());
        return 'DOMPDF: ' . $e->getMessage();
    }

    require_once __DIR__ . '/smtp_config.php';
    require_once __DIR__ . '/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/phpmailer/SMTP.php';
    require_once __DIR__ . '/phpmailer/Exception.php';
    $client_name  = $all_data['name'] ?? 'Client';
    $client_email = $all_data['email'] ?? '';
    $f_name  = 'release_form_' . preg_replace('/[^a-z0-9]+/i', '_', $client_name) . '.pdf';
    $subject = ($settings['email_subject'] ?? 'Signed Release Form') . ' [' . $client_name . '] — FINAL';
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->setFrom(SMTP_FROM, SMTP_NAME);
        $mail->addAddress($settings['email_to']);
        if ($client_email) $mail->addCC($client_email);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = '<p>The tattoo release form for <strong>' . htmlspecialchars($client_name) . '</strong> is fully completed and signed. The finalised form is attached.</p>';
        $mail->AltBody = "Release form for {$client_name} is fully completed. The finalised PDF is attached.";
        $mail->addStringAttachment($dompdf->output(), $f_name, \PHPMailer\PHPMailer\PHPMailer::ENCODING_BASE64, 'application/pdf');
        $mail->send();
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('send_completion_pdf mailer: ' . $e->getMessage());
        return 'Email failed: ' . $e->getMessage();
    }
}

function display_label($input, $title=null) {
	global $settings;

	if($input=='dob')
		$settings['require_'.$input]=true;

	if(!$title)
		$title=ucfirst(str_replace('_',' ',$input));

	?><?=$title?>:<?=(isset($settings['require_'.$input])&&$settings['require_'.$input])?'<span class="required">*</span>':''?><?php
}

function display_input($input,$skin=null) {
	global $submit, $settings;
	if($submit || $skin=="print") {
		echo '<div class="fake_input">'.$_REQUEST[$input].'</div>';
	} else {
		$type='text';
		// for tablet auto keyobardy stuff
		switch($input) {
			case 'phone':
				$type='tel';
				break;
			case 'email':
				$type=$input;
				break;
		}
		echo'<input id="'.$input.'" name="'.$input.'" type="'.$type.'" autocomplete="off"'.($input=='name' && $settings['autocomplete']?' onkeyup="this.onchange();" onchange="autocomplete_client(this.value);"':'').'/>';
	}
}

function display_date_year($val=null, $name='dobY', $onchange=null)
{
?>
<select class="date_year" size="1" name="<?=$name?>" <?=$onchange?'onchange="'.$onchange.'"':''?>  id="<?=$name?>">
	<option value="-1">-Year-</option>
<?php
$year_start=date('Y')-110;
$year_end=date('Y');//-1;
echo($year_start);
for($i=$year_end; $i>$year_start; $i--)
	echo '<option value="'.$i.'"'.($val==$i?' selected':'').'>'.$i.'</option>';
?>
</select>
<?php
}
	
function display_date_day($val=null, $name='dobD', $onchange=null)
{
?>
<select class="date_day" size="1" name="<?=$name?>" <?=$onchange?'onchange="'.$onchange.'"':''?>  id="<?=$name?>">
	<option value="-1">-Day-</option>
<?php
for($i=1; $i<=31; $i++)
	echo '<option value="'.$i.'"'.($val==$i?' selected':'').'>'.$i.'</option>';
?>
</select>
<?php
}

function display_date_month($val=null,$name='dobM', $onchange=null)
{
	?><select class="date_month" size="1" name="<?=$name?>" <?=$onchange?'onchange="'.$onchange.'"':''?>  id="<?=$name?>">
	<option value="-1">-Month-</option>
	<?php
	$months=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	$count=1;
	foreach($months as $month) {
		echo '<option value="'.$count.'"'.($val==$count?' selected':'').'>'.$month.'</option>';
		$count++;
	}
	?>
	</select>
<?php
}

function display_dob()
{
	global $submit, $settings;

	if($submit) {
		echo('<div class="fake_input">');
		echo(date('jS F Y',strtotime($_REQUEST['dobY'].'-'.$_REQUEST['dobM'].'-'.$_REQUEST['dobD'])));
		echo(' ('.$_REQUEST['dobY'].'/'.$_REQUEST['dobM'].'/'.$_REQUEST['dobD'].')');
		echo(' - Age:'.$_REQUEST['dob_age']);
		echo('</div>');
	} else {

		?>
		<input type="hidden" value="" name="dob_age" id="dob_age" />
		<?php
		display_date_month($_REQUEST['dobM'],'dobM','age('.$settings['age_limit'].')');
		display_date_day($_REQUEST['dobD'],'dobD','age('.$settings['age_limit'].')');
		display_date_year($_REQUEST['dobY'],'dobY','age('.$settings['age_limit'].')');	
		?><div class="dob_age" id="dob_age_display"></div><?php
	}
}

?>
