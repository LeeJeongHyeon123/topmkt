<?php
/**
 * AdminController editUser 메서드 직접 디버깅
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/controllers/AdminController.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "🔍 AdminController editUser 메서드 디버깅\n\n";

try {
    // 세션 시뮬레이션 (관리자 권한)
    session_start();
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    $_SESSION['csrf_token'] = 'test_token_' . uniqid();
    
    // POST 요청 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/admin/users/5/edit';
    
    // POST 데이터 설정
    $_POST = [
        'nickname' => '안계현_테스트편집_' . date('His'),
        'email' => 'test_edit_' . date('His') . '@example.com',
        'bio' => '관리자 테스트 편집 - ' . date('Y-m-d H:i:s'),
        'status' => 'active',
        'role' => 'ROLE_USER',
        'status_reason' => 'API 디버깅 테스트',
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    echo "1. 관리자 세션 설정 완료\n";
    echo "2. POST 요청 및 데이터 시뮬레이션 완료\n";
    echo "3. POST 데이터:\n";
    foreach ($_POST as $key => $value) {
        if ($key !== 'csrf_token') {
            echo "   - {$key}: {$value}\n";
        }
    }
    echo "\n";
    
    // AdminController 인스턴스 생성
    $authMiddleware = new AuthMiddleware();
    
    echo "4. AuthMiddleware 인스턴스 생성 완료\n";
    echo "5. AdminController 인스턴스 생성 시도...\n";
    
    $adminController = new AdminController($authMiddleware);
    
    echo "6. AdminController 인스턴스 생성 완료\n";
    echo "7. editUser 메서드 호출...\n\n";
    
    // 출력 버퍼링 시작 (JSON 응답 캡처용)
    ob_start();
    
    try {
        $adminController->editUser(5);
        $output = ob_get_contents();
    } catch (Exception $e) {
        $output = "editUser 메서드 오류: " . $e->getMessage();
    }
    
    ob_end_clean();
    
    echo "📊 editUser 메서드 응답:\n";
    echo $output . "\n\n";
    
    // JSON 응답 파싱 시도
    if (strpos($output, '{') !== false) {
        $jsonStart = strpos($output, '{');
        $jsonData = substr($output, $jsonStart);
        
        $decoded = json_decode($jsonData, true);
        
        if ($decoded) {
            echo "📋 파싱된 응답:\n";
            echo "  - 성공 여부: " . ($decoded['success'] ? '성공' : '실패') . "\n";
            
            if (isset($decoded['message'])) {
                echo "  - 메시지: " . $decoded['message'] . "\n";
            }
            
            if (isset($decoded['changes'])) {
                echo "  - 변경 사항: " . implode(', ', $decoded['changes']) . "\n";
            }
            
            if (isset($decoded['error'])) {
                echo "  - 오류: " . $decoded['error'] . "\n";
            }
        } else {
            echo "❌ JSON 파싱 실패\n";
        }
    } else {
        echo "❌ JSON 응답이 아닙니다\n";
    }
    
} catch (Exception $e) {
    echo "❌ 테스트 실패: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📋 스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>