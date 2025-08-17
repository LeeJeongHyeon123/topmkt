<?php
/**
 * 실제 500 에러 추적을 위한 디버깅
 */

// 에러 로그 기록 활성화
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/html/topmkt/debug_error.log');

// 모든 에러 출력
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 세션 시작
session_start();

echo "=== 실제 500 에러 추적 테스트 ===" . PHP_EOL;
echo "시간: " . date('Y-m-d H:i:s') . PHP_EOL;

// Content-Type을 먼저 설정하지 않고 진행
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

try {
    echo "1. 기본 설정 완료" . PHP_EOL;
    
    // 실제 JWT 없이 관리자 세션 생성
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    $_SESSION['csrf_token'] = 'debug_token_' . uniqid();
    
    echo "2. 세션 설정 완료" . PHP_EOL;
    
    // POST 데이터 설정
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/admin/users/5/edit';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    
    $_POST = [
        'nickname' => '안계현_500에러추적_' . date('His'),
        'email' => 'debug500_' . date('His') . '@topmktx.com',
        'phone' => '010-5555-' . substr(date('His'), 0, 4),
        'status' => 'active',
        'role' => 'ROLE_USER',
        'status_reason' => '500 에러 추적',
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    echo "3. POST 데이터 설정 완료" . PHP_EOL;
    
    // 각 require 단계별로 에러 체크
    echo "4. 파일 로딩 시작..." . PHP_EOL;
    
    require_once SRC_PATH . '/config/database.php';
    echo "   - database.php 로딩 성공" . PHP_EOL;
    
    require_once SRC_PATH . '/models/User.php';
    echo "   - User.php 로딩 성공" . PHP_EOL;
    
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    echo "   - AuthMiddleware.php 로딩 성공" . PHP_EOL;
    
    require_once SRC_PATH . '/controllers/AdminController.php';
    echo "   - AdminController.php 로딩 성공" . PHP_EOL;
    
    echo "5. AdminController 인스턴스 생성 시도..." . PHP_EOL;
    
    // AdminController 생성자 호출 (여기서 문제가 발생할 가능성)
    $adminController = new AdminController();
    echo "   - AdminController 인스턴스 생성 성공" . PHP_EOL;
    
    echo "6. editUser 메서드 호출 시도..." . PHP_EOL;
    
    // Content-Type 헤더 설정
    header('Content-Type: application/json; charset=utf-8');
    
    // editUser 메서드 호출
    $adminController->editUser(5);
    
    echo "   - editUser 메서드 호출 성공" . PHP_EOL;
    
} catch (Error $e) {
    echo "❌ PHP Error 발생:" . PHP_EOL;
    echo "   메시지: " . $e->getMessage() . PHP_EOL;
    echo "   파일: " . $e->getFile() . PHP_EOL;
    echo "   라인: " . $e->getLine() . PHP_EOL;
    echo "   추적: " . $e->getTraceAsString() . PHP_EOL;
    
    // JSON 응답도 시도
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'fatal_error' => true,
        'type' => 'Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    
} catch (Exception $e) {
    echo "❌ Exception 발생:" . PHP_EOL;
    echo "   메시지: " . $e->getMessage() . PHP_EOL;
    echo "   파일: " . $e->getFile() . PHP_EOL;
    echo "   라인: " . $e->getLine() . PHP_EOL;
    echo "   추적: " . $e->getTraceAsString() . PHP_EOL;
    
    // JSON 응답도 시도
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'fatal_error' => true,
        'type' => 'Exception',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

echo PHP_EOL . "=== 디버깅 완료 ===" . PHP_EOL;
?>