<?php
// DELETE THIS FILE after debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<pre>';
echo 'PHP version: ' . PHP_VERSION . "\n\n";

echo "Testing config.inc.php...\n";
include 'config.inc.php';
echo "OK\n\n";

echo "Testing functions.inc.php...\n";
include 'functions.inc.php';
echo "OK\n\n";

echo "Testing validation.inc.php...\n";
include 'validation.inc.php';
echo "OK\n\n";

echo "All includes loaded successfully.\n";
echo '</pre>';
