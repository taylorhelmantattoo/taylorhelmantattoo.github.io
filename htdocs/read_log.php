<?php
// DELETE after debugging.
// Shows crash.log and php.log written by index.php's shutdown handler.
header('Content-Type: text/html; charset=utf-8');
foreach (['crash.log', 'php.log'] as $f) {
    $path = __DIR__ . '/' . $f;
    echo '<h3 style="font-family:monospace">' . $f . '</h3>';
    echo '<pre style="background:#111;color:#0f0;padding:10px;font-size:13px;white-space:pre-wrap">';
    echo file_exists($path) ? htmlspecialchars(file_get_contents($path)) : '(no file — nothing written yet)';
    echo '</pre>';
}
