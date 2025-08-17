<?php
/**
 * 수정된 기업 회원 권한 변경 테스트
 */

// 헤더 출력 방지를 위해 출력 버퍼링 시작
ob_start();

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

// 에러 표시 활성화
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 세션 시작 (헤더 전송 전)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "🔍 수정된 기업 회원 권한 변경 테스트\n\n";

try {
    // JWT 토큰 및 인증 설정
    require_once SRC_PATH . '/helpers/JWTHelper.php';
    require_once SRC_PATH . '/models/User.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    $userId = 4; // 우리집탄이
    $userModel = new User();
    $user = $userModel->findById($userId);
    
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'exp' => time() + (7 * 24 * 60 * 60)
    ];
    
    $jwt = JWTHelper::createToken($payload);
    $_COOKIE['auth_token'] = $jwt;
    
    echo "✅ JWT 인증 설정 완료: {$user['nickname']}\n\n";
    
    // AdminController 및 CSRF 토큰 생성
    require_once SRC_PATH . '/controllers/AdminController.php';
    
    $adminController = new AdminController();
    
    $reflection = new ReflectionClass($adminController);
    $generateMethod = $reflection->getMethod('generateCsrfToken');
    $generateMethod->setAccessible(true);
    $csrfToken = $generateMethod->invoke($adminController);
    
    echo "✅ CSRF 토큰 생성 완료\n\n";
    
    // ROLE_CORPORATE로 권한 변경 테스트
    echo "🎯 ROLE_CORPORATE로 권한 변경 테스트\n";
    
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/admin/users/5/edit';
    
    $_POST = [
        'nickname' => '기업권한테스트_성공',
        'email' => 'corp_success@topmktx.com',
        'phone' => '010-2222-2222',
        'status' => 'active',
        'role' => 'ROLE_CORPORATE',  // 올바른 값
        'csrf_token' => $csrfToken
    ];
    
    echo "📤 POST 데이터 (ROLE_CORPORATE): " . json_encode($_POST['role']) . "\n";
    echo "📝 전체 데이터: " . json_encode($_POST, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    ob_start();
    try {
        $adminController->editUser(5);
        $result = ob_get_clean();
        echo "🎉 성공! 응답:\n" . $result . "\n\n";
        
        // JSON 파싱하여 성공 여부 확인
        $jsonData = json_decode($result, true);
        if ($jsonData && $jsonData['success']) {
            echo "✅ 기업 회원 권한 변경 완전 성공!\n";
            echo "💾 변경 내용: " . implode(', ', $jsonData['changes']) . "\n";
        }
    } catch (Exception $e) {
        ob_get_clean();
        echo "❌ 에러 발생: " . $e->getMessage() . "\n";
        echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ 테스트 실패: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 출력 버퍼 내용을 플레인 텍스트로 출력
$output = ob_get_clean();
header('Content-Type: text/plain; charset=utf-8');
echo $output;
?>