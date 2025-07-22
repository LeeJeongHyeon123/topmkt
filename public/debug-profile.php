<?php
/**
 * 프로필 페이지 디버그
 */

// 상대 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/helpers/JWTHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/models/User.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $debug = [];
    
    // 1. 인증 확인
    $debug['step1_auth'] = [
        'isLoggedIn' => AuthMiddleware::isLoggedIn(),
        'currentUserId' => AuthMiddleware::getCurrentUserId()
    ];
    
    if (!AuthMiddleware::isLoggedIn()) {
        $debug['error'] = 'Not logged in';
        echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $currentUserId = AuthMiddleware::getCurrentUserId();
    $debug['step2_user_id'] = $currentUserId;
    
    // 2. User 모델 인스턴스 생성
    $userModel = new User();
    $debug['step3_model_created'] = 'YES';
    
    // 3. getFullProfile 호출
    try {
        $user = $userModel->getFullProfile($currentUserId);
        $debug['step4_getFullProfile'] = [
            'success' => $user ? 'YES' : 'NO',
            'user_data' => $user ? 'DATA_EXISTS' : 'NULL'
        ];
        
        if ($user) {
            $debug['step5_user_info'] = [
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'phone' => $user['phone'],
                'status' => $user['status']
            ];
        }
    } catch (Exception $e) {
        $debug['step4_getFullProfile_error'] = [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ];
    }
    
    // 4. 데이터베이스 직접 조회 테스트
    try {
        $db = Database::getInstance();
        $directResult = $db->fetch("SELECT id, nickname, phone, status FROM users WHERE id = ? AND status = 'active'", [$currentUserId]);
        $debug['step6_direct_db_query'] = [
            'success' => $directResult ? 'YES' : 'NO',
            'data' => $directResult
        ];
    } catch (Exception $e) {
        $debug['step6_direct_db_error'] = [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ];
    }
    
    echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'fatal_error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>