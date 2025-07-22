<?php
/**
 * 커뮤니티 페이지 라이브 테스트 (에러 표시 활성화)
 */

// 에러 표시 강제 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 상대 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

try {
    echo "<h1>커뮤니티 페이지 라이브 테스트</h1>";
    
    // 설정 파일 로드
    require_once CONFIG_PATH . '/config.php';
    echo "<p>✅ config.php 로드</p>";
    
    require_once CONFIG_PATH . '/database.php';
    echo "<p>✅ database.php 로드</p>";
    
    require_once CONFIG_PATH . '/routes.php';
    echo "<p>✅ routes.php 로드</p>";
    
    // 헬퍼 클래스 로드
    require_once SRC_PATH . '/helpers/WebLogger.php';
    echo "<p>✅ WebLogger 로드</p>";
    
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    echo "<p>✅ ResponseHelper 로드</p>";
    
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    echo "<p>✅ GlobalErrorHandler 로드</p>";
    
    require_once SRC_PATH . '/helpers/LogAnalyzer.php';
    echo "<p>✅ LogAnalyzer 로드</p>";
    
    // 글로벌 에러 핸들러 등록
    GlobalErrorHandler::register();
    echo "<p>✅ GlobalErrorHandler 등록</p>";
    
    // 로깅 시스템 초기화
    WebLogger::init();
    echo "<p>✅ WebLogger 초기화</p>";
    
    // 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<p>✅ 세션 시작</p>";
    
    // 라우터 생성
    $router = new Router();
    echo "<p>✅ Router 인스턴스 생성</p>";
    
    // 커뮤니티 요청 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/community';
    
    echo "<p>🔄 커뮤니티 라우팅 처리 중...</p>";
    
    // 라우터 처리 (실제 index.php와 동일한 로직)
    $router->dispatch();
    
    echo "<p>✅ 라우팅 처리 완료</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ 예외 발생</h2>";
    echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error</h2>";
    echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1 { color: #333; }
h2 { color: #666; }
p { margin: 5px 0; }
</style>