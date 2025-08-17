<?php
/**
 * 기업 회원 권한 변경 문제 디버깅
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

echo "🔍 기업 회원 권한 변경 문제 디버깅\n\n";

try {
    // JWT 토큰 및 인증 설정
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
    $_COOKIE['auth_token'] = $jwt;
    
    echo "✅ JWT 인증 설정 완료: {$user['nickname']}\n\n";
    
    // AdminController 및 CSRF 토큰 생성
    echo "2단계: AdminController 및 CSRF 토큰 생성\n";
    require_once SRC_PATH . '/controllers/AdminController.php';
    
    $adminController = new AdminController();
    
    $reflection = new ReflectionClass($adminController);
    $generateMethod = $reflection->getMethod('generateCsrfToken');
    $generateMethod->setAccessible(true);
    $csrfToken = $generateMethod->invoke($adminController);
    
    echo "✅ CSRF 토큰 생성 완료\n\n";
    
    // 테스트 케이스 1: ROLE_CORP로 변경 시도
    echo "3단계: ROLE_CORP로 권한 변경 테스트\n";
    
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/admin/users/5/edit';
    
    $_POST = [
        'nickname' => '기업권한테스트1',
        'email' => 'corp_test1@topmktx.com',
        'phone' => '010-1111-1111',
        'status' => 'active',
        'role' => 'ROLE_CORP',  // 첫 번째 케이스
        'csrf_token' => $csrfToken
    ];
    
    echo "📤 POST 데이터 (ROLE_CORP): " . json_encode($_POST['role']) . "\n";
    
    ob_start();
    try {
        $adminController->editUser(5);
        $result1 = ob_get_clean();
        echo "📄 결과: " . $result1 . "\n";
    } catch (Exception $e) {
        ob_get_clean();
        echo "❌ 에러: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // 테스트 케이스 2: ROLE_CORPORATE로 변경 시도
    echo "4단계: ROLE_CORPORATE로 권한 변경 테스트\n";
    
    $_POST['role'] = 'ROLE_CORPORATE';  // 두 번째 케이스
    $_POST['nickname'] = '기업권한테스트2';
    $_POST['email'] = 'corp_test2@topmktx.com';
    
    echo "📤 POST 데이터 (ROLE_CORPORATE): " . json_encode($_POST['role']) . "\n";
    
    ob_start();
    try {
        $adminController->editUser(5);
        $result2 = ob_get_clean();
        echo "📄 결과: " . $result2 . "\n";
    } catch (Exception $e) {
        ob_get_clean();
        echo "❌ 에러: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // AdminController 소스 분석
    echo "5단계: AdminController 권한 검증 로직 분석\n";
    $adminFile = SRC_PATH . '/controllers/AdminController.php';
    $content = file_get_contents($adminFile);
    
    // ROLE_CORP 위치 찾기
    $corpPos1 = strpos($content, 'ROLE_CORP');
    $corporatePos = strpos($content, 'ROLE_CORPORATE');
    
    echo "AdminController에서 권한 값 발견:\n";
    if ($corpPos1 !== false) {
        $line1 = substr_count(substr($content, 0, $corpPos1), "\n") + 1;
        echo "- ROLE_CORP 발견: 라인 $line1\n";
    }
    if ($corporatePos !== false) {
        $line2 = substr_count(substr($content, 0, $corporatePos), "\n") + 1;
        echo "- ROLE_CORPORATE 발견: 라인 $line2\n";
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