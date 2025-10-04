<?php
/**
 * 웹에서 세션 상태 확인
 */

session_start();

echo "<h1>웹 세션 상태 확인</h1>";

echo "<h2>1. 세션 변수 확인</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>2. 쿠키 확인</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<h2>3. AuthMiddleware 테스트</h2>";

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

try {
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

    $isLoggedIn = AuthMiddleware::isLoggedIn();
    $currentUserId = AuthMiddleware::getCurrentUserId();

    echo "<p><strong>로그인 상태:</strong> " . ($isLoggedIn ? "✅ 로그인됨" : "❌ 로그인 안됨") . "</p>";
    echo "<p><strong>현재 사용자 ID:</strong> " . ($currentUserId ?? 'null') . "</p>";

    if (!$isLoggedIn) {
        echo "<h3>DevLoginHelper로 로그인 시도</h3>";
        echo "<p><a href='/dev/login_helper.php?user_id=4'>우리집탄이 계정으로 로그인</a></p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>오류: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>4. UserController 테스트</h2>";

try {
    require_once SRC_PATH . '/controllers/UserController.php';

    $controller = new UserController();
    echo "<p>✅ UserController 인스턴스 생성 성공</p>";

    if ($isLoggedIn) {
        echo "<h3>프로필 데이터 조회 테스트</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

        // 출력 버퍼링
        ob_start();
        $controller->showMyProfile();
        $output = ob_get_contents();
        ob_end_clean();

        echo "<p><strong>출력 크기:</strong> " . strlen($output) . " bytes</p>";

        if (strpos($output, '프로필을 불러오는 중 오류가 발생했습니다') !== false) {
            echo "<p style='color: red;'>❌ 오류 메시지 발견</p>";
        } elseif (strpos($output, '우리집탄이') !== false) {
            echo "<p style='color: green;'>✅ 프로필 내용 정상 출력</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ 프로필 내용 확인 불가</p>";
        }

        echo "</div>";
    } else {
        echo "<p>로그인이 필요합니다.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>UserController 오류: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>