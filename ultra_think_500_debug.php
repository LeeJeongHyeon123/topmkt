<?php
/**
 * Ultra Think 7단계 접근법: 500 에러 완전 분석
 */

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

// 에러 표시 활성화
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔥 Ultra Think 7단계: 500 에러 완전 분석</h1>";

// 1단계: 환경 확인
echo "<h2>1단계: 환경 확인</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "ROOT_PATH: " . ROOT_PATH . "<br>";
echo "SRC_PATH: " . SRC_PATH . "<br>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set') . "<br><br>";

// 2단계: 필수 파일 확인
echo "<h2>2단계: 필수 파일 확인</h2>";
$requiredFiles = [
    SRC_PATH . '/config/database.php',
    SRC_PATH . '/middlewares/AuthMiddleware.php', 
    SRC_PATH . '/controllers/AdminController.php',
    SRC_PATH . '/helpers/JWTHelper.php',
    SRC_PATH . '/models/User.php'
];

foreach ($requiredFiles as $file) {
    $exists = file_exists($file);
    $readable = $exists ? is_readable($file) : false;
    echo "📁 " . basename($file) . ": " . ($exists ? "✅ 존재" : "❌ 없음") . 
         ($readable ? " + 읽기 가능" : ($exists ? " - 읽기 불가" : "")) . "<br>";
}
echo "<br>";

// 3단계: JWT 토큰 확인
echo "<h2>3단계: JWT 토큰 확인</h2>";
$authToken = $_COOKIE['auth_token'] ?? null;
if ($authToken) {
    require_once SRC_PATH . '/helpers/JWTHelper.php';
    $tokenInfo = JWTHelper::debugToken($authToken);
    echo "<pre>" . json_encode($tokenInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
} else {
    echo "❌ JWT 토큰이 없습니다.<br>";
}
echo "<br>";

// 4단계: 인증 테스트
echo "<h2>4단계: 인증 테스트</h2>";
try {
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "isLoggedIn(): " . ($isLoggedIn ? "✅ 성공" : "❌ 실패") . "<br>";
    
    if ($isLoggedIn) {
        $currentUser = AuthMiddleware::getCurrentUser();
        $isAdmin = AuthMiddleware::isAdmin();
        echo "현재 사용자: " . json_encode($currentUser) . "<br>";
        echo "관리자 권한: " . ($isAdmin ? "✅ 있음" : "❌ 없음") . "<br>";
    }
} catch (Exception $e) {
    echo "❌ 인증 테스트 실패: " . $e->getMessage() . "<br>";
    echo "스택 추적: <pre>" . $e->getTraceAsString() . "</pre><br>";
}
echo "<br>";

// 5단계: AdminController 인스턴스 테스트
echo "<h2>5단계: AdminController 인스턴스 테스트</h2>";
try {
    require_once SRC_PATH . '/controllers/AdminController.php';
    $adminController = new AdminController();
    echo "✅ AdminController 인스턴스 생성 성공<br>";
} catch (Exception $e) {
    echo "❌ AdminController 생성 실패: " . $e->getMessage() . "<br>";
    echo "스택 추적: <pre>" . $e->getTraceAsString() . "</pre><br>";
}
echo "<br>";

// 6단계: 라우팅 시뮬레이션
echo "<h2>6단계: editUser 메서드 직접 호출 테스트</h2>";

// POST 데이터 시뮬레이션
$_POST = [
    'nickname' => 'UltraTestDebug',
    'email' => 'ultra_debug@topmktx.com',
    'phone' => '010-9999-9999',
    'status' => 'active',
    'role' => 'ROLE_USER',
    'csrf_token' => 'debug_token_test'
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/admin/users/5/edit';

try {
    if (isset($adminController)) {
        echo "📤 POST 데이터: " . json_encode($_POST) . "<br>";
        echo "🔄 editUser(5) 메서드 호출 시도...<br>";
        
        ob_start(); // 출력 버퍼링 시작
        $adminController->editUser(5);
        $output = ob_get_clean(); // 출력 캡처
        
        echo "✅ editUser 메서드 실행 완료<br>";
        echo "📄 출력 내용:<br><pre>" . htmlspecialchars($output) . "</pre>";
    } else {
        echo "❌ AdminController 인스턴스가 없어서 메서드 테스트 불가<br>";
    }
} catch (Exception $e) {
    echo "❌ editUser 메서드 실행 실패: " . $e->getMessage() . "<br>";
    echo "스택 추적: <pre>" . $e->getTraceAsString() . "</pre><br>";
}
echo "<br>";

// 7단계: 결론 및 해결책
echo "<h2>7단계: 결론 및 해결책</h2>";
echo "이 테스트를 통해 500 에러의 정확한 원인을 파악할 수 있습니다.<br>";
echo "각 단계별 결과를 확인하여 문제가 발생하는 지점을 찾으세요.<br>";
echo "<br>";

echo "<hr>";
echo "<a href='/admin/users'>🔙 관리자 페이지로 돌아가기</a><br>";
echo "<a href='/debug_jwt_auth.php'>🔍 JWT 인증 디버깅</a><br>";
?>