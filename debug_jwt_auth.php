<?php
/**
 * JWT 인증 디버깅 스크립트
 */

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/JWTHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

// 에러 표시 활성화
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 JWT 인증 디버깅</h2>";

// 쿠키에서 토큰 확인
$authToken = $_COOKIE['auth_token'] ?? null;
$accessToken = $_COOKIE['access_token'] ?? null;
$jwtToken = $_COOKIE['jwt_token'] ?? null;

echo "<h3>📋 쿠키 상태</h3>";
echo "auth_token: " . ($authToken ? "있음 (" . strlen($authToken) . "자)" : "없음") . "<br>";
echo "access_token: " . ($accessToken ? "있음 (" . strlen($accessToken) . "자)" : "없음") . "<br>";
echo "jwt_token: " . ($jwtToken ? "있음 (" . strlen($jwtToken) . "자)" : "없음") . "<br>";
echo "전체 쿠키: " . json_encode($_COOKIE) . "<br><br>";

if ($authToken) {
    echo "<h3>🔐 JWT 토큰 검증</h3>";
    
    // JWT 토큰 디버깅
    $debugInfo = JWTHelper::debugToken($authToken);
    echo "<pre>" . json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
    // JWT 토큰에서 사용자 정보 추출
    echo "<h3>👤 사용자 정보 추출</h3>";
    $userData = JWTHelper::getUserFromToken($authToken);
    if ($userData) {
        echo "<pre>" . json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    } else {
        echo "❌ 사용자 정보 추출 실패<br>";
    }
    
    // AuthMiddleware 테스트
    echo "<h3>🛡️ AuthMiddleware 테스트</h3>";
    
    try {
        $isLoggedIn = AuthMiddleware::isLoggedIn();
        echo "isLoggedIn(): " . ($isLoggedIn ? "✅ 성공" : "❌ 실패") . "<br>";
        
        if ($isLoggedIn) {
            $currentUser = AuthMiddleware::getCurrentUser();
            echo "getCurrentUser(): " . json_encode($currentUser) . "<br>";
            
            $isAdmin = AuthMiddleware::isAdmin();
            echo "isAdmin(): " . ($isAdmin ? "✅ 관리자" : "❌ 일반 사용자") . "<br>";
        }
    } catch (Exception $e) {
        echo "❌ AuthMiddleware 오류: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ JWT 토큰이 없습니다.<br>";
}

echo "<hr>";
echo "<a href='/dev/login_helper.php?user_id=4'>🔑 DevLoginHelper로 로그인</a><br>";
echo "<a href='/admin/users'>👥 관리자 페이지</a><br>";
?>