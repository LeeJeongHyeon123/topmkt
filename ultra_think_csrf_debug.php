<?php
/**
 * Ultra Think: CSRF 토큰 문제 정확한 진단
 */

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

// 에러 표시 활성화
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔥 Ultra Think: CSRF 토큰 문제 정확한 진단</h1>\n";

// DevLoginHelper를 통한 JWT 토큰 생성 및 설정
echo "<h2>1단계: JWT 토큰 설정</h2>\n";
try {
    require_once SRC_PATH . '/helpers/JWTHelper.php';
    require_once SRC_PATH . '/models/User.php';
    
    // 우리집탄이 (user_id=4) JWT 토큰 생성
    $userId = 4;
    $userModel = new User();
    $user = $userModel->findById($userId);
    
    if ($user) {
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'exp' => time() + (7 * 24 * 60 * 60) // 7일
        ];
        
        $jwt = JWTHelper::createToken($payload);
        
        // JWT 토큰을 쿠키로 설정 (헤더가 아직 전송되기 전)
        setcookie('auth_token', $jwt, [
            'expires' => time() + (7 * 24 * 60 * 60),
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true
        ]);
        
        // $_COOKIE에도 직접 설정 (즉시 사용 가능하도록)
        $_COOKIE['auth_token'] = $jwt;
        
        echo "✅ JWT 토큰 생성 및 설정 완료<br>\n";
        echo "사용자: {$user['nickname']} ({$user['email']})<br>\n";
        echo "권한: {$user['role']}<br>\n";
    } else {
        throw new Exception("사용자 ID 4를 찾을 수 없습니다");
    }
} catch (Exception $e) {
    echo "❌ JWT 토큰 설정 실패: " . $e->getMessage() . "<br>\n";
}
echo "<br>\n";

// 2단계: 인증 상태 확인
echo "<h2>2단계: 인증 상태 확인</h2>\n";
try {
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "isLoggedIn(): " . ($isLoggedIn ? "✅ 성공" : "❌ 실패") . "<br>\n";
    
    if ($isLoggedIn) {
        $currentUser = AuthMiddleware::getCurrentUser();
        $isAdmin = AuthMiddleware::isAdmin();
        echo "현재 사용자: " . json_encode($currentUser, JSON_UNESCAPED_UNICODE) . "<br>\n";
        echo "관리자 권한: " . ($isAdmin ? "✅ 있음" : "❌ 없음") . "<br>\n";
    }
} catch (Exception $e) {
    echo "❌ 인증 확인 실패: " . $e->getMessage() . "<br>\n";
}
echo "<br>\n";

// 3단계: CSRF 토큰 생성 테스트
echo "<h2>3단계: CSRF 토큰 생성 및 검증 테스트</h2>\n";
try {
    require_once SRC_PATH . '/controllers/AdminController.php';
    
    // 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $adminController = new AdminController();
    
    // CSRF 토큰 생성 테스트
    $reflection = new ReflectionClass($adminController);
    
    // generateCsrfToken 메서드 호출
    if ($reflection->hasMethod('generateCsrfToken')) {
        $generateMethod = $reflection->getMethod('generateCsrfToken');
        $generateMethod->setAccessible(true);
        $generatedToken = $generateMethod->invoke($adminController);
        echo "✅ CSRF 토큰 생성: " . substr($generatedToken, 0, 20) . "...<br>\n";
        echo "전체 토큰: $generatedToken<br>\n";
        
        // verifyCsrfToken 메서드 테스트
        if ($reflection->hasMethod('verifyCsrfToken')) {
            $verifyMethod = $reflection->getMethod('verifyCsrfToken');
            $verifyMethod->setAccessible(true);
            
            // $_POST에 토큰 설정
            $_POST['csrf_token'] = $generatedToken;
            
            $isValid = $verifyMethod->invoke($adminController);
            echo "CSRF 토큰 검증: " . ($isValid ? "✅ 성공" : "❌ 실패") . "<br>\n";
            
            // 세션에 저장된 토큰 확인
            echo "세션 토큰: " . ($_SESSION['csrf_token'] ?? '없음') . "<br>\n";
            echo "POST 토큰: " . ($_POST['csrf_token'] ?? '없음') . "<br>\n";
        }
    }
} catch (Exception $e) {
    echo "❌ CSRF 테스트 실패: " . $e->getMessage() . "<br>\n";
    echo "스택 추적: <pre>" . $e->getTraceAsString() . "</pre><br>\n";
}
echo "<br>\n";

// 4단계: editUser 메서드 실제 호출 테스트
echo "<h2>4단계: editUser 메서드 실제 호출</h2>\n";

// 환경 변수 설정
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/admin/users/5/edit';

// POST 데이터 설정
$_POST = [
    'nickname' => 'UltraDebugTest',
    'email' => 'ultra_debug@topmktx.com', 
    'phone' => '010-8888-8888',
    'status' => 'active',
    'role' => 'ROLE_USER',
    'csrf_token' => $_SESSION['csrf_token'] ?? 'no_token'
];

echo "📤 POST 데이터:<br>\n";
foreach ($_POST as $key => $value) {
    echo "  $key: $value<br>\n";
}
echo "<br>\n";

try {
    if (isset($adminController)) {
        echo "🔄 editUser(5) 메서드 호출...<br>\n";
        
        // JSON 응답 캡처를 위한 출력 버퍼링
        ob_start();
        
        // 실제 editUser 메서드 호출
        $adminController->editUser(5);
        
        $output = ob_get_clean();
        
        echo "📄 메서드 출력:<br>\n";
        echo "<pre>" . htmlspecialchars($output) . "</pre><br>\n";
        
        // JSON 파싱 시도
        if (!empty($output) && $output[0] === '{') {
            $jsonData = json_decode($output, true);
            if ($jsonData) {
                echo "✅ JSON 응답 파싱 성공:<br>\n";
                echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre><br>\n";
            }
        }
        
    } else {
        echo "❌ AdminController 인스턴스가 없습니다<br>\n";
    }
} catch (Exception $e) {
    echo "❌ editUser 실행 실패: " . $e->getMessage() . "<br>\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "<br>\n";
    echo "스택 추적: <pre>" . $e->getTraceAsString() . "</pre><br>\n";
}

echo "<hr>\n";
echo "🔍 이 테스트를 통해 CSRF 토큰 생성/검증의 정확한 문제점을 파악할 수 있습니다.<br>\n";
?>