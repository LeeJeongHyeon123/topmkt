<?php
/**
 * 커뮤니티 라우팅 직접 테스트
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>커뮤니티 라우팅 직접 테스트</h1>";

try {
    // 1. 필수 파일들 로드
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once CONFIG_PATH . '/routes.php';
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    
    echo "<p>✅ 필수 파일 로드 완료</p>";
    
    // 2. 글로벌 에러 핸들러 등록
    GlobalErrorHandler::register();
    WebLogger::init();
    
    echo "<p>✅ 시스템 초기화 완료</p>";
    
    // 3. 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<p>✅ 세션 시작 완료</p>";
    
    // 4. 라우터 생성
    $router = new Router();
    echo "<p>✅ 라우터 생성 완료</p>";
    
    // 5. 커뮤니티 요청 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/community';
    $_SERVER['HTTP_HOST'] = 'www.topmktx.com';
    $_SERVER['HTTPS'] = 'off';
    
    echo "<p>🔄 커뮤니티 라우팅 시작...</p>";
    echo "<p>요청: GET /community</p>";
    
    // 6. 실제 dispatch 호출
    ob_start();
    $router->dispatch();
    $output = ob_get_clean();
    
    echo "<p>✅ 라우팅 처리 완료</p>";
    echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;'>";
    echo "<strong>라우팅 결과:</strong><br>";
    echo htmlspecialchars(substr($output, 0, 1000));
    if (strlen($output) > 1000) {
        echo "<br><em>... (결과가 길어서 첫 1000자만 표시)</em>";
    }
    echo "</div>";
    
} catch (ParseError $e) {
    echo "<h2>❌ 파싱 에러</h2>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error</h2>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<h2>❌ 예외 발생</h2>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; }
</style>