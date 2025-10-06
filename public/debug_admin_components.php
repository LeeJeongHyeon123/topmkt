<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('SRC_PATH', dirname(__DIR__) . '/src');

echo "<!DOCTYPE html><html><head><title>Debug</title></head><body>";
echo "<h1>Admin Components Debug</h1>";

echo "<h2>1. SRC_PATH</h2>";
echo "<pre>SRC_PATH: " . SRC_PATH . "</pre>";

echo "<h2>2. Toast Component</h2>";
$toast_path = SRC_PATH . '/views/includes/toast.js.php';
echo "<pre>Path: $toast_path</pre>";
echo "<pre>Exists: " . (file_exists($toast_path) ? 'YES' : 'NO') . "</pre>";
if (file_exists($toast_path)) {
    echo "<pre>First 200 chars:</pre>";
    echo "<pre>" . htmlspecialchars(substr(file_get_contents($toast_path), 0, 200)) . "...</pre>";
}

echo "<h2>3. ApiClient Component</h2>";
$api_path = SRC_PATH . '/views/includes/api-client.js.php';
echo "<pre>Path: $api_path</pre>";
echo "<pre>Exists: " . (file_exists($api_path) ? 'YES' : 'NO') . "</pre>";
if (file_exists($api_path)) {
    echo "<pre>First 200 chars:</pre>";
    echo "<pre>" . htmlspecialchars(substr(file_get_contents($api_path), 0, 200)) . "...</pre>";
}

echo "<h2>4. Try Loading ApiClient</h2>";
echo "<script>";
require_once $api_path;
echo "</script>";

echo "<h2>5. Check Window Object</h2>";
echo "<script>";
echo "console.log('ApiClient type:', typeof window.ApiClient);";
echo "console.log('ApiClient object:', window.ApiClient);";
echo "</script>";

echo "</body></html>";
?>
