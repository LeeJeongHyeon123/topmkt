<?php
session_start();
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');

// Mock user session for admin access
$_SESSION['user_id'] = 1;
$_SESSION['user'] = ['role' => 'ROLE_ADMIN', 'nickname' => 'Test Admin'];

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';

$page_title = 'Test Page';
$page_description = 'Testing admin layout';

$content = '<div class="test-content"><h1>Test Content</h1></div>';

$additional_scripts = <<<'SCRIPTS'
<script>
</script>
SCRIPTS;

include SRC_PATH . '/views/templates/admin_layout.php';
?>
