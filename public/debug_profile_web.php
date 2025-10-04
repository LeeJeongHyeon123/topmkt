<?php
/**
 * 웹에서 프로필 페이지 디버깅
 */

// 오류 표시 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/html/topmkt/debug_web.log');

// 상수 정의
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

// 세션 시작
session_start();

// DevLoginHelper와 같은 세션 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_CORPORATE';
$_SESSION['nickname'] = '우리집탄이';

echo "<h1>프로필 페이지 웹 디버깅</h1>";
echo "<p>사용자 ID: " . $_SESSION['user_id'] . "</p>";

try {
    require_once SRC_PATH . '/controllers/UserController.php';

    echo "<p>✅ UserController 로드 성공</p>";

    $controller = new UserController();
    echo "<p>✅ UserController 인스턴스 생성 성공</p>";

    echo "<h2>프로필 페이지 실행:</h2>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

    $controller->showMyProfile();

    echo "</div>";
    echo "<p>✅ showMyProfile() 실행 완료</p>";

} catch (Exception $e) {
    echo "<div style='background: #ffe6e6; border: 1px solid #ff0000; padding: 20px; margin: 20px;'>";
    echo "<h3>오류 발생</h3>";
    echo "<p><strong>메시지:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>파일:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre><strong>스택 추적:</strong>\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<h2>디버그 로그 확인:</h2>";
if (file_exists('/var/www/html/topmkt/debug_web.log')) {
    echo "<pre>" . htmlspecialchars(file_get_contents('/var/www/html/topmkt/debug_web.log')) . "</pre>";
} else {
    echo "<p>디버그 로그 파일 없음</p>";
}
?>