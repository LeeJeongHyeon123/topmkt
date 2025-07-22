<?php
echo "<h1>라우터 디스패치 테스트</h1>";

try {
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    
    require_once SRC_PATH . '/config/config.php';
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/config/routes.php';
    
    echo "<p>현재 URI: " . ($_SERVER['REQUEST_URI'] ?? '/') . "</p>";
    echo "<p>현재 메서드: " . ($_SERVER['REQUEST_METHOD'] ?? 'GET') . "</p>";
    
    // 홈 라우트 테스트
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    echo "<h2>라우터 디스패치 실행...</h2>";
    
    $router = new Router();
    $router->dispatch();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>오류: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>