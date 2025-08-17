<?php
/**
 * 웹 환경에서 editUser 기능 테스트
 */

// 세션 시작 (헤더 전에)
session_start();

// 에러 리포팅 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Content-Type 설정
header('Content-Type: application/json; charset=utf-8');

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

try {
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/controllers/AdminController.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    // 관리자 세션 설정
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    $_SESSION['csrf_token'] = 'web_test_token_' . uniqid();
    
    // POST 요청 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/admin/users/5/edit';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    
    // POST 데이터 설정
    $_POST = [
        'nickname' => '안계현_웹테스트_' . date('His'),
        'email' => 'web_test_' . date('His') . '@topmktx.com',
        'phone' => '010-7777-' . date('His'),
        'status' => 'active',
        'role' => 'ROLE_USER',
        'status_reason' => '웹 환경 테스트',
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    echo json_encode([
        'debug' => 'POST 데이터 설정 완료',
        'post_data' => array_merge($_POST, ['csrf_token' => '[SET]']),
        'session_user' => $_SESSION['user'],
        'step' => 'AdminController 생성 중...'
    ]);
    
    // AdminController 인스턴스 생성 및 editUser 호출
    $adminController = new AdminController();
    $adminController->editUser(5);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>