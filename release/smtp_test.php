<?php
/**
 * SMTP connectivity test — DELETE THIS FILE after testing.
 * Visit https://release.taylorhelmantattoo.com/smtp_test.php in a browser.
 */
define('ALLOWED_IP', ''); // leave blank to allow any IP, or set to your IP for safety

if (ALLOWED_IP && $_SERVER['REMOTE_ADDR'] !== ALLOWED_IP) {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/smtp_config.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';
require_once __DIR__ . '/phpmailer/Exception.php';

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
    $mail->addAddress(SMTP_USER); // sends the test email back to Taylor
    $mail->Subject = 'SMTP Test — Release Form (' . date('H:i:s') . ')';
    $mail->Body    = 'SMTP is working. You can delete smtp_test.php from the server.';
    $mail->send();
    echo '<p style="font-family:sans-serif;color:green;font-size:18px;">&#10003; <strong>Success!</strong> Test email sent to ' . htmlspecialchars(SMTP_USER) . '. Check your Gmail inbox.</p>';
    echo '<p style="font-family:sans-serif;color:#555;font-size:14px;"><strong>Remember:</strong> Delete smtp_test.php from the server once confirmed.</p>';
} catch (\PHPMailer\PHPMailer\Exception $e) {
    echo '<p style="font-family:sans-serif;color:red;font-size:18px;">&#10007; <strong>Failed:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre style="font-family:monospace;font-size:13px;background:#f5f5f5;padding:12px;">' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
}
