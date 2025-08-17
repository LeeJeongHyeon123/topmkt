<?php
/**
 * Ultra Think: 최종 CSRF 및 편집 기능 완전 테스트
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

echo "🔥 Ultra Think: 최종 CSRF 및 편집 기능 완전 테스트\n\n";

try {
    // 1단계: JWT 토큰 및 인증 설정
    echo "1단계: JWT 토큰 및 인증 설정\n";
    require_once SRC_PATH . '/helpers/JWTHelper.php';
    require_once SRC_PATH . '/models/User.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    $userId = 4; // 우리집탄이
    $userModel = new User();
    $user = $userModel->findById($userId);
    
    if (!$user) {
        throw new Exception("사용자 ID 4를 찾을 수 없습니다");
    }
    
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'exp' => time() + (7 * 24 * 60 * 60)
    ];
    
    $jwt = JWTHelper::createToken($payload);
    $_COOKIE['auth_token'] = $jwt; // 직접 설정
    
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    $isAdmin = AuthMiddleware::isAdmin();
    
    if (!$isLoggedIn || !$isAdmin) {
        throw new Exception("인증 실패 - isLoggedIn: $isLoggedIn, isAdmin: $isAdmin");
    }
    
    echo "✅ JWT 인증 성공: {$user['nickname']} (관리자)\n\n";
    
    // 2단계: AdminController 인스턴스 및 CSRF 토큰 생성
    echo "2단계: AdminController 및 CSRF 토큰 생성\n";
    require_once SRC_PATH . '/controllers/AdminController.php';
    
    $adminController = new AdminController();
    
    // CSRF 토큰 생성
    $reflection = new ReflectionClass($adminController);
    $generateMethod = $reflection->getMethod('generateCsrfToken');
    $generateMethod->setAccessible(true);
    $csrfToken = $generateMethod->invoke($adminController);
    
    echo "✅ CSRF 토큰 생성: " . substr($csrfToken, 0, 20) . "...\n";
    echo "세션 CSRF 토큰: " . ($_SESSION['csrf_token'] ?? 'null') . "\n\n";
    
    // 3단계: POST 데이터 설정 및 editUser 호출
    echo "3단계: editUser 메서드 테스트\n";
    
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/admin/users/5/edit';
    
    $_POST = [
        'nickname' => 'UltraFinalTest',
        'email' => 'ultra_final@topmktx.com',
        'phone' => '010-9999-0000',
        'status' => 'active', 
        'role' => 'ROLE_USER',
        'csrf_token' => $csrfToken
    ];
    
    echo "📤 POST 데이터 설정 완료\n";
    echo "CSRF 토큰 일치: " . ($_POST['csrf_token'] === $_SESSION['csrf_token'] ? '✅' : '❌') . "\n\n";
    
    // editUser 메서드 호출
    echo "🔄 editUser(5) 메서드 호출...\n";
    
    $adminController->editUser(5);
    
    echo "\n✅ editUser 메서드 실행 완료! 500 에러 없음\n";
    
} catch (Exception $e) {
    echo "❌ 에러 발생: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
}

// 출력 버퍼 내용을 플레인 텍스트로 출력
$output = ob_get_clean();
header('Content-Type: text/plain; charset=utf-8');
echo $output;
?>