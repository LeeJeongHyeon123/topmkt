<?php
/**
 * 관리자 세션 생성 및 편집 기능 테스트
 */

// 세션 시작
session_start();

// Content-Type 설정
header('Content-Type: application/json; charset=utf-8');

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

try {
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/models/User.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    // 관리자 세션 설정 (우리집탄이)
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    $_SESSION['csrf_token'] = 'admin_session_' . uniqid();
    
    // 세션 확인
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    $currentUser = $isLoggedIn ? AuthMiddleware::getCurrentUser() : null;
    
    $response = [
        'session_created' => true,
        'session_id' => session_id(),
        'is_logged_in' => $isLoggedIn,
        'current_user' => $currentUser,
        'csrf_token' => $_SESSION['csrf_token'],
        'message' => '관리자 세션이 생성되었습니다.'
    ];
    
    if ($isLoggedIn && $currentUser && $currentUser['role'] === 'ROLE_ADMIN') {
        $response['admin_access'] = true;
        $response['message'] .= ' 관리자 권한 확인됨.';
    } else {
        $response['admin_access'] = false;
        $response['error'] = '관리자 권한을 확인할 수 없습니다.';
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
?>