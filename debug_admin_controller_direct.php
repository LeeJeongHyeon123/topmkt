<?php
/**
 * AdminController editUser 메서드 직접 테스트
 */

// 세션 시작 (다른 출력 전에)
session_start();

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

// 에러 리포팅 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/controllers/AdminController.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "🔍 AdminController editUser 메서드 직접 테스트\n\n";

try {
    // 세션 설정 (관리자 권한)
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    $_SESSION['csrf_token'] = 'test_token_' . uniqid();
    
    // POST 요청 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/admin/users/5/edit';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    
    // POST 데이터 설정
    $_POST = [
        'nickname' => '안계현_관리자편집_' . date('His'),
        'email' => 'admin_edit_' . date('His') . '@topmktx.com',
        'phone' => '010-8888-' . date('His'),
        'status' => 'active',
        'role' => 'ROLE_USER',
        'status_reason' => '직접 테스트',
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    echo "1. 세션 및 POST 데이터 설정 완료\n";
    echo "2. POST 데이터:\n";
    foreach ($_POST as $key => $value) {
        if ($key !== 'csrf_token') {
            echo "   - {$key}: {$value}\n";
        }
    }
    echo "   - csrf_token: [SET]\n\n";
    
    // AdminController 생성 시도
    echo "3. AdminController 생성 시도...\n";
    
    // 출력 버퍼링 시작
    ob_start();
    
    try {
        // AdminController 생성 (이 부분에서 권한 체크 때문에 오류가 날 수 있음)
        $adminController = new AdminController();
        echo "   ✅ AdminController 생성 성공\n";
        
        echo "4. editUser 메서드 호출...\n";
        $adminController->editUser(5);
        
    } catch (Exception $controllerError) {
        echo "   ❌ AdminController 오류: " . $controllerError->getMessage() . "\n";
        echo "   📍 파일: " . $controllerError->getFile() . ":" . $controllerError->getLine() . "\n";
        
        // 권한 체크 때문일 수 있으니 AuthMiddleware 직접 테스트
        echo "\n5. AuthMiddleware 직접 테스트...\n";
        
        try {
            $isLoggedIn = AuthMiddleware::isLoggedIn();
            echo "   - 로그인 상태: " . ($isLoggedIn ? '✅ 로그인됨' : '❌ 로그인 안됨') . "\n";
            
            if ($isLoggedIn) {
                $currentUser = AuthMiddleware::getCurrentUser();
                echo "   - 현재 사용자: ID {$currentUser['id']}, 역할 {$currentUser['role']}\n";
                
                $isAdmin = in_array($currentUser['role'], ['ROLE_ADMIN', 'SUPER_ADMIN']);
                echo "   - 관리자 권한: " . ($isAdmin ? '✅ 있음' : '❌ 없음') . "\n";
            }
            
        } catch (Exception $authError) {
            echo "   ❌ AuthMiddleware 오류: " . $authError->getMessage() . "\n";
        }
    }
    
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "\n📊 AdminController 출력:\n";
    echo $output . "\n\n";
    
    // JSON 응답 파싱 시도
    if (strpos($output, '{') !== false) {
        $jsonStart = strpos($output, '{');
        $jsonData = substr($output, $jsonStart);
        
        $decoded = json_decode($jsonData, true);
        
        if ($decoded) {
            echo "📋 파싱된 JSON 응답:\n";
            echo "  - 성공 여부: " . ($decoded['success'] ? '✅ 성공' : '❌ 실패') . "\n";
            
            if (isset($decoded['message'])) {
                echo "  - 메시지: " . $decoded['message'] . "\n";
            }
            
            if (isset($decoded['error'])) {
                echo "  - 오류: " . $decoded['error'] . "\n";
            }
            
            if (isset($decoded['changes'])) {
                echo "  - 변경 사항: " . implode(', ', $decoded['changes']) . "\n";
            }
        } else {
            echo "❌ JSON 파싱 실패\n";
        }
    } else {
        echo "❌ JSON 응답이 아닙니다\n";
    }
    
} catch (Exception $e) {
    echo "❌ 전체 테스트 실패: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📋 스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>