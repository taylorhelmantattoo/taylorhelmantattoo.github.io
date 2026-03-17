<?php
require_once __DIR__ . '/functions.inc.php';

header('Content-Type: application/json');

$type      = $_POST['type']      ?? '';
$link      = trim($_POST['link']      ?? '');
$recipient = trim($_POST['recipient'] ?? '');

// Basic validation
if (!$link || !$recipient || !in_array($type, ['email', 'sms'], true)) {
    echo json_encode(['ok' => false, 'error' => 'Missing or invalid parameters.']);
    exit;
}

// Ensure the link points only to this server (prevent SSRF)
$parsed      = parse_url($link);
$server_host = $_SERVER['HTTP_HOST'] ?? '';
if (empty($parsed['host']) || $parsed['host'] !== $server_host) {
    echo json_encode(['ok' => false, 'error' => 'Invalid link.']);
    exit;
}

if ($type === 'email') {
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'error' => 'Invalid email address.']);
        exit;
    }
    echo json_encode(send_link_email($recipient, $link));
} else {
    // Strip everything except digits, +, spaces, dashes, parens
    $phone = preg_replace('/[^\d+\-\(\) ]/', '', $recipient);
    echo json_encode(send_link_sms($phone, $link));
}
