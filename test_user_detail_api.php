<?php
/**
 * 사용자 상세 정보 API 테스트
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/controllers/AdminController.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "🔍 사용자 상세 정보 API 테스트\n\n";

try {
    // 세션 시뮬레이션 (관리자 권한)
    session_start();
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    
    // GET 요청 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/admin/users/5/detail';
    
    echo "1. 관리자 세션 설정 완료\n";
    echo "2. GET 요청 시뮬레이션: /admin/users/5/detail\n\n";
    
    // AdminController 인스턴스 생성
    $authMiddleware = new AuthMiddleware();
    $adminController = new AdminController($authMiddleware);
    
    echo "3. AdminController 인스턴스 생성 완료\n";
    echo "4. getUserDetail 메서드 호출...\n\n";
    
    // 출력 버퍼링 시작
    ob_start();
    
    try {
        $adminController->getUserDetail(5);
        $output = ob_get_contents();
    } catch (Exception $e) {
        $output = "API 오류: " . $e->getMessage();
    }
    
    ob_end_clean();
    
    echo "📊 API 응답:\n";
    echo $output . "\n\n";
    
    // JSON 응답 파싱 시도
    if (strpos($output, '{') !== false) {
        $jsonStart = strpos($output, '{');
        $jsonData = substr($output, $jsonStart);
        
        $decoded = json_decode($jsonData, true);
        
        if ($decoded) {
            echo "📋 파싱된 응답:\n";
            echo "  - 성공 여부: " . ($decoded['success'] ? '성공' : '실패') . "\n";
            
            if (isset($decoded['user'])) {
                $user = $decoded['user'];
                echo "  - 사용자 ID: " . ($user['id'] ?? '없음') . "\n";
                echo "  - 닉네임: " . ($user['nickname'] ?? '없음') . "\n";
                echo "  - 이메일: " . ($user['email'] ?? '없음') . "\n";
                echo "  - 상태: " . ($user['status'] ?? '없음') . "\n";
                echo "  - 권한: " . ($user['role'] ?? '없음') . "\n";
                echo "  - 가입일: " . ($user['created_at'] ?? '없음') . "\n";
            }
            
            if (isset($decoded['error'])) {
                echo "  - 오류 메시지: " . $decoded['error'] . "\n";
            }
        } else {
            echo "❌ JSON 파싱 실패\n";
        }
    } else {
        echo "❌ JSON 응답이 아닙니다\n";
    }
    
} catch (Exception $e) {
    echo "❌ 테스트 실패: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>