<?php
/**
 * API 라우트 테스트
 */

echo "<h1>API 라우트 테스트</h1>";

// 라우트 설정 로드
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
require_once SRC_PATH . '/config/routes.php';

$router = new Router();

// 라우트 정보 확인
$reflection = new ReflectionClass($router);
$property = $reflection->getProperty('routes');
$property->setAccessible(true);
$routes = $property->getValue($router);

echo "<h2>등록된 API 라우트들</h2>";
echo "<table border='1'>";
echo "<tr><th>Method:Path</th><th>Controller</th><th>Action</th></tr>";
foreach ($routes as $route => $handler) {
    if (strpos($route, 'api') !== false) {
        echo "<tr>";
        echo "<td>{$route}</td>";
        echo "<td>{$handler[0]}</td>";
        echo "<td>{$handler[1]}</td>";
        echo "</tr>";
    }
}
echo "</table>";

// upload-event-image 라우트 확인
echo "<h2>upload-event-image 라우트 확인</h2>";
$targetRoute = 'POST:/api/upload-event-image';
if (isset($routes[$targetRoute])) {
    echo "<p style='color: green;'>✅ 라우트가 등록되어 있습니다: {$routes[$targetRoute][0]}::{$routes[$targetRoute][1]}</p>";
} else {
    echo "<p style='color: red;'>❌ 라우트가 등록되지 않았습니다</p>";
}

echo "<h2>현재 요청 정보</h2>";
echo "<p>REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routeKey = $method . ':' . $path;
echo "<p>생성된 라우트 키: {$routeKey}</p>";
?>