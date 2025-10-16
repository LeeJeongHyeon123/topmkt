<?php
/**
 * E2E 테스트용 간소화된 자동 로그인 헬퍼
 * 디버깅 메시지 최소화, 즉시 토큰 발급
 */

// 개발 환경에서만 사용
if (!in_array($_SERVER['HTTP_HOST'], ['www.topmktx.com', 'topmktx.com', 'localhost', 'localhost:8000'])) {
    http_response_code(403);
    exit('E2E 환경에서만 사용 가능');
}

session_start();

define('ROOT_PATH', dirname(__DIR__, 2));
define('SRC_PATH', ROOT_PATH . '/src');

// 에러 출력 비활성화 (E2E 테스트용)
ini_set('display_errors', 0);
error_reporting(0);

try {
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/models/User.php';
    require_once SRC_PATH . '/helpers/JWTHelper.php';
} catch (Exception $e) {
    http_response_code(500);
    exit('Server Error');
}

$userId = $_GET['user_id'] ?? 4; // 기본값: 우리집탄이

try {
    $userModel = new User();
    $user = $userModel->findById($userId);
    
    if (!$user) {
        http_response_code(404);
        exit('User not found');
    }
    
    // JWT 토큰 생성
    $payload = [
        'user_id' => $user['id'],
        'nickname' => $user['nickname'],
        'role' => $user['role']
    ];
    
    $token = JWTHelper::createToken($payload, time() + (24 * 60 * 60)); // 24시간 유효
    
    // JWT 쿠키 설정 (E2E 테스트 최적화)
    setcookie('auth_token', $token, [
        'expires' => time() + (24 * 60 * 60),
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => false,
        'samesite' => 'Lax'
    ]);
    
    // 추가 쿠키 설정
    setcookie('jwt_token', $token, time() + (24 * 60 * 60), '/', '', false, false);
    header("Set-Cookie: auth_token={$token}; Path=/; Max-Age=86400; SameSite=Lax");
    
    // 세션에도 설정
    $_SESSION['user'] = [
        'id' => $user['id'],
        'nickname' => $user['nickname'],
        'role' => $user['role']
    ];
    $_SESSION['user_id'] = $user['id'];
    
    // 성공 응답 (JSON)
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'role' => $user['role']
        ],
        'token' => $token,
        'message' => 'Login successful'
    ]);
    
    // 로그 기록 (간소화)
    error_log("E2E AutoLogin: User ID {$userId} ({$user['nickname']})");
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Login failed',
        'message' => $e->getMessage()
    ]);
    error_log("E2E AutoLogin Error: " . $e->getMessage());
}
?>