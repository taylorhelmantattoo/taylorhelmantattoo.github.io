<?php
// DELETE after testing.
// Tests includes one at a time. Comment/uncomment lines below.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<pre style="font-family:monospace;font-size:13px">';
echo "PHP " . PHP_VERSION . "\n";
echo "include_path: " . get_include_path() . "\n";
echo "__DIR__: " . __DIR__ . "\n\n";

// --- Uncomment one at a time ---

echo "Testing config.inc.php... ";
include 'config.inc.php';
echo "OK\n";

echo "Testing functions.inc.php... ";
include 'functions.inc.php';
echo "OK\n";

echo "Testing validation.inc.php... ";
include 'validation.inc.php';
echo "OK\n";

echo "\nAll includes passed.\n";
echo '</pre>';
