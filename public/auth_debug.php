<?php
session_start();
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "<h1>🔐 인증 상태 진단</h1>";
echo "<h2>쿠키</h2>";
foreach ($_COOKIE as $key => $value) {
    echo "<p>$key: " . ($key === 'jwt_token' ? substr($value, 0, 30) . '...' : $value) . "</p>";
}

echo "<h2>AuthMiddleware</h2>";
$isLoggedIn = AuthMiddleware::isLoggedIn();
echo "<p>로그인: " . ($isLoggedIn ? "✅" : "❌") . "</p>";

if ($isLoggedIn) {
    $userId = AuthMiddleware::getCurrentUserId();
    echo "<p>사용자 ID: $userId</p>";
} else {
    echo "<p>❌ 로그인 안됨 - 이것이 404 원인</p>";
}
?>