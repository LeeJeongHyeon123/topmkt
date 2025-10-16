<?php
define('SRC_PATH', dirname(__DIR__) . '/src');

echo "<!DOCTYPE html><html><head><title>Test</title></head><body>";
echo "<h1>Admin Layout Component Test</h1>";

echo "<h2>1. Toast Component</h2>";
echo "<pre>";
require_once SRC_PATH . '/views/includes/toast.js.php';
echo "</pre>";

echo "<h2>2. ApiClient Component</h2>";
echo "<pre>";
require_once SRC_PATH . '/views/includes/api-client.js.php';
echo "</pre>";

echo "<p>✅ If you see JavaScript code above, components are loading correctly.</p>";
echo "</body></html>";
?>
