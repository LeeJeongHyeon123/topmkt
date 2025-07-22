<?php
/**
 * 라우팅 디버그 테스트
 */

echo "<h1>라우팅 디버그 테스트</h1>";

// 환경 변수 시뮬레이션
$_SERVER['REQUEST_URI'] = '/lectures/196';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/topmkt/index.php';

echo "<h2>현재 환경 변수</h2>";
echo "<ul>";
echo "<li>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</li>";
echo "<li>REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "</li>";
echo "<li>HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "</li>";
echo "<li>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "</li>";
echo "</ul>";

echo "<h2>라우팅 테스트</h2>";

// 상수 정의
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// 라우터 로드
require_once SRC_PATH . '/config/routes.php';

$router = new Router();

// 디스패치 테스트
echo "<p>라우터 디스패치 시작...</p>";

try {
    ob_start();
    $router->dispatch();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<h3>라우터 출력:</h3>";
    echo "<div style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow: auto;'>";
    echo htmlspecialchars(substr($output, 0, 2000)) . (strlen($output) > 2000 ? "...[truncated]" : "");
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>라우터 오류: " . $e->getMessage() . "</p>";
}

echo "<h2>이용 가능한 라우트 확인</h2>";

$routes = new ReflectionClass('Router');
$routesProperty = $routes->getProperty('routes');
$routesProperty->setAccessible(true);
$routesArray = $routesProperty->getValue(new Router());

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>라우트 패턴</th><th>컨트롤러</th><th>액션</th></tr>";

foreach ($routesArray as $pattern => $route) {
    if (strpos($pattern, 'lecture') !== false || strpos($pattern, 'event') !== false) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($pattern) . "</td>";
        echo "<td>" . htmlspecialchars($route[0]) . "</td>";
        echo "<td>" . htmlspecialchars($route[1]) . "</td>";
        echo "</tr>";
    }
}

echo "</table>";
?>