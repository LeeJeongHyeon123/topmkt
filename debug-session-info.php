<?php
// 세션 시작 전 정보
echo "=== 세션 시작 전 ===\n";
echo "세션 상태: " . session_status() . " (1=disabled, 2=enabled, 3=active)\n";
echo "세션 ID: " . (session_id() ?: 'NOT SET') . "\n";

// 세션 시작
session_start();

echo "\n=== 세션 시작 후 ===\n";
echo "세션 ID: " . session_id() . "\n";
echo "세션 저장 경로: " . session_save_path() . "\n";
echo "세션 쿠키 이름: " . session_name() . "\n";

// 세션 쿠키 설정 확인
$cookieParams = session_get_cookie_params();
echo "\n=== 세션 쿠키 설정 ===\n";
echo "lifetime: " . $cookieParams['lifetime'] . "\n";
echo "path: " . $cookieParams['path'] . "\n";
echo "domain: " . $cookieParams['domain'] . "\n";
echo "secure: " . ($cookieParams['secure'] ? 'true' : 'false') . "\n";
echo "httponly: " . ($cookieParams['httponly'] ? 'true' : 'false') . "\n";
if (isset($cookieParams['samesite'])) {
    echo "samesite: " . $cookieParams['samesite'] . "\n";
}

// CSRF 토큰 생성 및 확인
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "\n=== CSRF 토큰 정보 ===\n";
echo "토큰: " . $_SESSION['csrf_token'] . "\n";
echo "토큰 길이: " . strlen($_SESSION['csrf_token']) . "\n";

// 세션 파일 존재 확인
$sessionFile = session_save_path() . '/sess_' . session_id();
echo "\n=== 세션 파일 ===\n";
echo "세션 파일 경로: " . $sessionFile . "\n";
echo "세션 파일 존재: " . (file_exists($sessionFile) ? 'YES' : 'NO') . "\n";
if (file_exists($sessionFile)) {
    echo "파일 크기: " . filesize($sessionFile) . " bytes\n";
    echo "파일 권한: " . substr(sprintf('%o', fileperms($sessionFile)), -4) . "\n";
}

// PHP 설정 확인
echo "\n=== PHP 설정 ===\n";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "\n";
echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "\n";

?>