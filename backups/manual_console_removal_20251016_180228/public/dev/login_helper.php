<?php
/**
 * 개발용 자동 로그인 헬퍼
 * JWT 토큰 없이도 관리자 계정으로 자동 로그인
 */

// 개발 환경에서만 사용
if (!in_array($_SERVER['HTTP_HOST'], ['www.topmktx.com', 'topmktx.com', 'localhost', 'localhost:8000'])) {
    http_response_code(403);
    exit('이 기능은 개발 환경에서만 사용할 수 있습니다.');
}

session_start();

define('ROOT_PATH', dirname(__DIR__, 2)); // public/dev에서 2단계 위로
define('SRC_PATH', ROOT_PATH . '/src');

// 에러 출력 활성화 (디버깅용)
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔧 DevLoginHelper 디버깅 시작...<br>";
echo "ROOT_PATH: " . ROOT_PATH . "<br>";
echo "SRC_PATH: " . SRC_PATH . "<br>";

try {
    require_once SRC_PATH . '/config/database.php';
    echo "✅ database.php 로딩 성공<br>";
    
    require_once SRC_PATH . '/models/User.php';
    echo "✅ User.php 로딩 성공<br>";
    
    require_once SRC_PATH . '/helpers/JWTHelper.php';
    echo "✅ JWTHelper.php 로딩 성공<br>";
} catch (Exception $e) {
    echo "❌ 파일 로딩 오류: " . $e->getMessage() . "<br>";
    exit;
}

$userId = $_GET['user_id'] ?? 4; // 기본값: 우리집탄이

try {
    $userModel = new User();
    $user = $userModel->findById($userId);
    
    if (!$user) {
        echo "❌ 사용자 ID {$userId}를 찾을 수 없습니다.";
        exit;
    }
    
    // JWT 토큰 생성
    $payload = [
        'user_id' => $user['id'],
        'nickname' => $user['nickname'],
        'role' => $user['role']
    ];
    
    $token = JWTHelper::createToken($payload, time() + (24 * 60 * 60)); // 24시간 유효
    
    // JWT 쿠키 설정 (개발환경 최적화)
    setcookie('auth_token', $token, [
        'expires' => time() + (24 * 60 * 60),
        'path' => '/',
        'domain' => '',
        'secure' => false,      // HTTPS 아닐 때도 작동하도록
        'httponly' => false,    // JavaScript에서 접근 가능하도록
        'samesite' => 'Lax'
    ]);
    
    // 추가 쿠키 설정 방법들
    setcookie('jwt_token', $token, time() + (24 * 60 * 60), '/', '', false, false);
    header("Set-Cookie: auth_token={$token}; Path=/; Max-Age=86400; SameSite=Lax");
    
    // JavaScript에서도 설정할 수 있도록
    echo "<script>
        document.cookie = 'auth_token={$token}; path=/; max-age=86400; samesite=lax';
        console.log('🍪 JavaScript 쿠키 설정:', document.cookie);
    </script>";
    
    // 세션에도 설정 (호환성)
    $_SESSION['user'] = [
        'id' => $user['id'],
        'nickname' => $user['nickname'],
        'role' => $user['role']
    ];
    
    echo "✅ {$user['nickname']} ({$user['role']}) 계정으로 자동 로그인 완료!<br>";
    echo "🔄 관리자 페이지로 리다이렉트 중...<br>";
    echo "<script>
        setTimeout(function() {
            window.location.href = '/admin/users';
        }, 2000);
    </script>";
    
    // 로그 기록
    error_log("🔧 DevLoginHelper: 사용자 ID {$userId} ({$user['nickname']}) 자동 로그인");
    
} catch (Exception $e) {
    echo "❌ 로그인 실패: " . $e->getMessage();
    error_log("DevLoginHelper 오류: " . $e->getMessage());
}
?>