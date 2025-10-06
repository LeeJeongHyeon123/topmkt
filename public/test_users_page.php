<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');

// Mock admin user
$_SESSION['user_id'] = 1;
$_SESSION['user'] = ['role' => 'ROLE_ADMIN', 'nickname' => 'Test Admin'];
$_SESSION['csrf_token'] = 'test-token-123';

// Load required files
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "<!DOCTYPE html><html><head><title>Debug</title></head><body>";
echo "<h1>Admin Users Page Debug</h1>";

echo "<h2>1. Include admin/users/list.php</h2>";
echo "<pre>";

// Capture output
ob_start();
include SRC_PATH . '/views/admin/users/list.php';
$output = ob_get_clean();

// Check if ApiClient is in output
$hasApiClient = strpos($output, 'ApiClient') !== false;
$hasAdminLayout = strpos($output, 'admin_layout.php') !== false;
$hasHeadLog = strpos($output, '[Admin Layout HEAD]') !== false;

echo "ApiClient in output: " . ($hasApiClient ? 'YES' : 'NO') . "\n";
echo "admin_layout.php in output: " . ($hasAdminLayout ? 'YES' : 'NO') . "\n";
echo "[Admin Layout HEAD] log in output: " . ($hasHeadLog ? 'YES' : 'NO') . "\n";
echo "</pre>";

echo "<h2>2. First 1000 chars of output</h2>";
echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "</pre>";

echo "<h2>3. Search for 'ApiClient' in output</h2>";
echo "<pre>";
$lines = explode("\n", $output);
$apiClientLines = array_filter($lines, function($line) {
    return stripos($line, 'ApiClient') !== false;
});
echo count($apiClientLines) . " lines containing 'ApiClient'\n";
foreach (array_slice($apiClientLines, 0, 5) as $i => $line) {
    echo ($i+1) . ": " . htmlspecialchars(substr($line, 0, 100)) . "...\n";
}
echo "</pre>";

echo "</body></html>";
?>
