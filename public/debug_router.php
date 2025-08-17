<?php
/**
 * 라우터 디버깅 도구
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 요청 정보 표시
echo "<h1>🔍 라우터 디버깅 도구</h1>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>요청 URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT_SET') . "</p>";
echo "<p>요청 메서드: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT_SET') . "</p>";

try {
    // 필요한 파일들 로드
    require_once CONFIG_PATH . '/paths.php';
    echo "<p>✅ paths.php 로드 성공</p>";
    
    require_once CONFIG_PATH . '/database.php';
    echo "<p>✅ database.php 로드 성공</p>";
    
    require_once CONFIG_PATH . '/routes.php';
    echo "<p>✅ routes.php 로드 성공</p>";
    
    $router = new Router();
    echo "<p>✅ Router 인스턴스 생성 성공</p>";
    
    // 라우터의 내부 상태 확인
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    echo "<p><strong>등록된 전체 라우트 수:</strong> " . count($routes) . "</p>";
    
    // 몇 가지 주요 라우트 확인
    $testRoutes = ['GET:/', 'GET:/admin/users', 'GET:/auth/login'];
    echo "<h3>주요 라우트 확인:</h3><ul>";
    foreach ($testRoutes as $testRoute) {
        if (isset($routes[$testRoute])) {
            echo "<li>✅ {$testRoute} → {$routes[$testRoute][0]}::{$routes[$testRoute][1]}</li>";
        } else {
            echo "<li>❌ {$testRoute} → 라우트 없음</li>";
        }
    }
    echo "</ul>";
    
    // 실제 라우팅 시뮬레이션
    echo "<h3>라우팅 시뮬레이션:</h3><ul>";
    
    $testUris = ['/', '/admin/users', '/auth/login'];
    foreach ($testUris as $testUri) {
        $uri = parse_url($testUri, PHP_URL_PATH);
        $uri = urldecode($uri);
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        $routeKey = 'GET:' . $uri;
        
        echo "<li>URI: {$testUri} → 파싱: {$uri} → 키: {$routeKey}";
        
        if (isset($routes[$routeKey])) {
            echo " → ✅ 매치</li>";
        } else {
            echo " → ❌ 매치 실패</li>";
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ 오류 발생: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>파일: " . $e->getFile() . ":" . $e->getLine() . "</p>";
}

echo "<p>🎯 디버깅 완료!</p>";
?>