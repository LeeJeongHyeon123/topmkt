<?php
/**
 * JWT 토큰 디버그 페이지
 */

// 상대 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/helpers/JWTHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $debug = [
        'timestamp' => date('Y-m-d H:i:s'),
        'cookies' => [
            'access_token' => $_COOKIE['access_token'] ?? 'NOT_SET',
            'refresh_token' => $_COOKIE['refresh_token'] ?? 'NOT_SET'
        ],
        'session' => [
            'user_id' => $_SESSION['user_id'] ?? 'NOT_SET',
            'started' => session_status() === PHP_SESSION_ACTIVE ? 'YES' : 'NO'
        ]
    ];
    
    // 액세스 토큰 검증
    if (isset($_COOKIE['access_token'])) {
        $accessToken = $_COOKIE['access_token'];
        $userData = JWTHelper::getUserFromToken($accessToken);
        $debug['access_token_validation'] = [
            'valid' => $userData ? 'YES' : 'NO',
            'data' => $userData ?: 'INVALID'
        ];
    }
    
    // 리프레시 토큰 검증
    if (isset($_COOKIE['refresh_token'])) {
        $refreshToken = $_COOKIE['refresh_token'];
        $payload = JWTHelper::validateToken($refreshToken);
        $debug['refresh_token_validation'] = [
            'valid' => $payload ? 'YES' : 'NO',
            'type' => $payload['type'] ?? 'UNKNOWN',
            'data' => $payload ?: 'INVALID'
        ];
    }
    
    // AuthMiddleware 상태 확인
    $debug['auth_middleware'] = [
        'isLoggedIn' => AuthMiddleware::isLoggedIn() ? 'YES' : 'NO',
        'getCurrentUserId' => AuthMiddleware::getCurrentUserId() ?: 'NULL'
    ];
    
    echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>