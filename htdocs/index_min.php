<?php
// Mirrors index.php boot sequence with visible output at each step.
// DELETE after testing.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<pre style="font-family:monospace;font-size:13px">';
echo "PHP " . PHP_VERSION . "\n";
echo "REQUEST_URI: " . (isset($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES) : '(not set)') . "\n";
echo "REDIRECT_URL: " . (isset($_SERVER['REDIRECT_URL']) ? htmlspecialchars($_SERVER['REDIRECT_URL'], ENT_QUOTES) : '(not set)') . "\n\n";

echo "Step 1: include functions.inc.php... ";
include_once('functions.inc.php');
echo "OK\n";

echo "Step 2: include config.inc.php... ";
include_once('config.inc.php');
echo "OK\n";
echo "  base_dir=[" . htmlspecialchars($base_dir, ENT_QUOTES) . "]\n";
echo "  site_prefix=[" . htmlspecialchars($site_prefix, ENT_QUOTES) . "]\n";

echo "Step 3: include validation.inc.php... ";
include_once('validation.inc.php');
echo "OK\n";

echo "\nAll boot steps passed.\n";
echo '</pre>';
