<?php

// ── Two-step form: token helpers ─────────────────────────────────────────────

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
    // Resolve sig_ref → actual image data
    if (!empty($payload['sig_ref']) && empty($payload['sig'])) {
        $payload['sig'] = load_sig_temp($payload['sig_ref']);
    }
    return $payload;
}

function check_artist_pin($pin) {
    require_once __DIR__ . '/smtp_config.php';
    return hash_equals(ARTIST_PIN, (string)$pin);
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

function send_link_sms($phone, $link) {
    require_once __DIR__ . '/smtp_config.php';
    if (!defined('TWILIO_SID') || !TWILIO_SID) {
        return ['ok' => false, 'error' => 'SMS not configured — add Twilio credentials to smtp_config.php'];
    }
    $message = "Your tattoo release form from Taylor Helman is ready.\n\nPlease complete it here:\n" . $link . "\n\nThis link expires in 72 hours.";
    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_SID . '/Messages.json';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query(['From' => TWILIO_FROM, 'To' => $phone, 'Body' => $message]));
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
    return ['ok' => false, 'error' => $data['message'] ?? 'SMS failed (HTTP ' . $http_code . ')'];
}

// ─────────────────────────────────────────────────────────────────────────────

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

	// use DOMPDF
	require_once __DIR__ . '/vendor/autoload.php';

	// render html2pdf
	// Note: DOMDocument round-trip removed — it strips UTF-8 and breaks accented characters
	$options = new \Dompdf\Options();
	$options->setChroot(__DIR__);        // allow local image files (checkboxes, signatures)
	$dompdf = new \Dompdf\Dompdf($options);
	$dompdf->setBasePath(__DIR__ . '/'); // resolve relative image paths from htdocs/
	$dompdf->loadHtml($html, 'UTF-8');   // preserve UTF-8 characters
	$dompdf->render();

        // email pdf via PHPMailer + Gmail SMTP
        require_once __DIR__ . '/smtp_config.php';
        require_once __DIR__ . '/phpmailer/PHPMailer.php';
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
            return false;
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
