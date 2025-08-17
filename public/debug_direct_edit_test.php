<?php
/**
 * AdminController editUser 메서드 직접 호출 테스트
 * (인증을 임시로 우회하여 핵심 로직만 테스트)
 */

// 세션 시작
session_start();

// 에러 리포팅 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Content-Type 설정 (AdminController에서 설정하므로 여기서는 주석)
// header('Content-Type: application/json; charset=utf-8');

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
    $_SESSION['csrf_token'] = 'direct_test_' . uniqid();
    
    // POST 요청 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/admin/users/5/edit';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest'; // AJAX 요청으로 인식
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    
    // POST 데이터 설정
    $timestamp = date('His');
    $_POST = [
        'nickname' => '안계현_직접테스트_' . $timestamp,
        'email' => 'direct_test_' . $timestamp . '@topmktx.com',
        'phone' => '010-7777-' . substr($timestamp, 0, 4),
        'status' => 'active',
        'role' => 'ROLE_USER',
        'status_reason' => '직접 테스트',
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    echo "=== AdminController EditUser 직접 테스트 ===" . PHP_EOL;
    echo "세션 사용자: " . json_encode($_SESSION['user']) . PHP_EOL;
    echo "POST 데이터: " . json_encode(array_merge($_POST, ['csrf_token' => '[HIDDEN]'])) . PHP_EOL;
    echo "=== 응답 시작 ===" . PHP_EOL;
    
    // AdminController 인스턴스 생성 및 editUser 메서드 호출
    try {
        // AdminController 생성자 호출 (인증 체크 포함)
        $adminController = new AdminController();
        
        // editUser 메서드 호출
        $adminController->editUser(5);
        
    } catch (Exception $controllerError) {
        // 인증 실패나 다른 오류 처리
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => true,
            'message' => $controllerError->getMessage(),
            'file' => $controllerError->getFile(),
            'line' => $controllerError->getLine(),
            'debug' => '직접 테스트 중 오류 발생'
        ]);
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'fatal_error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'debug' => '직접 테스트 전체 오류'
    ]);
}
?>