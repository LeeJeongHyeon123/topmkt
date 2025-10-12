<?php
/**
 * 현재 인증 상태 확인
 */

// 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

// 세션 시작
session_start();

require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "=== 인증 상태 확인 ===\n\n";

echo "1. 세션 ID: " . session_id() . "\n";
echo "2. 세션 데이터:\n";
print_r($_SESSION);

echo "\n3. AuthMiddleware 체크:\n";
echo "   - isLoggedIn(): " . (AuthMiddleware::isLoggedIn() ? 'TRUE' : 'FALSE') . "\n";

if (AuthMiddleware::isLoggedIn()) {
    echo "   - getCurrentUserId(): " . AuthMiddleware::getCurrentUserId() . "\n";
    echo "   - getCurrentUserRole(): " . AuthMiddleware::getCurrentUserRole() . "\n";
}

echo "\n4. 쿠키 정보:\n";
if (isset($_COOKIE['PHPSESSID'])) {
    echo "   - PHPSESSID: " . $_COOKIE['PHPSESSID'] . "\n";
}

if (isset($_COOKIE['jwt_token'])) {
    echo "   - JWT Token: " . substr($_COOKIE['jwt_token'], 0, 50) . "...\n";
}

echo "\n5. CSRF 토큰:\n";
if (isset($_SESSION['csrf_token'])) {
    echo "   - CSRF Token: " . $_SESSION['csrf_token'] . "\n";
} else {
    echo "   - CSRF Token: NOT SET\n";
}
?>