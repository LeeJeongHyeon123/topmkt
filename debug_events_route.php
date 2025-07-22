<?php
/**
 * 이벤트 라우팅 디버깅 스크립트
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 필요한 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/routes.php';

echo "<h1>이벤트 라우팅 디버깅</h1>";

// 현재 REQUEST_URI 시뮬레이션
$_SERVER['REQUEST_URI'] = '/events';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "<h2>현재 요청 정보</h2>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "</p>";

// 라우터 인스턴스 생성
$router = new Router();

// 라우트 확인
echo "<h2>등록된 라우트 중 events 관련</h2>";
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

foreach ($routes as $key => $route) {
    if (strpos($key, 'events') !== false) {
        echo "<p>{$key} => {$route[0]}::{$route[1]}</p>";
    }
}

// URI 파싱 테스트
echo "<h2>URI 파싱 테스트</h2>";
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);
$uri = rtrim($uri, '/');
if (empty($uri)) {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'];
$routeKey = $method . ':' . $uri;

echo "<p>파싱된 URI: {$uri}</p>";
echo "<p>생성된 라우트 키: {$routeKey}</p>";

// 라우트 매칭 테스트
echo "<h2>라우트 매칭 테스트</h2>";
if (isset($routes[$routeKey])) {
    echo "<p style='color: green;'>✅ 라우트 매칭 성공: {$routeKey}</p>";
    echo "<p>실행할 컨트롤러: {$routes[$routeKey][0]}::{$routes[$routeKey][1]}</p>";
    
    // 컨트롤러 파일 존재 확인
    $controllerFile = SRC_PATH . '/controllers/' . $routes[$routeKey][0] . '.php';
    if (file_exists($controllerFile)) {
        echo "<p style='color: green;'>✅ 컨트롤러 파일 존재: {$controllerFile}</p>";
        
        // 컨트롤러 로드 테스트
        try {
            require_once $controllerFile;
            if (class_exists($routes[$routeKey][0])) {
                echo "<p style='color: green;'>✅ 컨트롤러 클래스 존재: {$routes[$routeKey][0]}</p>";
                
                $controller = new $routes[$routeKey][0]();
                if (method_exists($controller, $routes[$routeKey][1])) {
                    echo "<p style='color: green;'>✅ 컨트롤러 메서드 존재: {$routes[$routeKey][1]}</p>";
                    
                    // 실제 실행 테스트
                    echo "<h3>컨트롤러 실행 테스트</h3>";
                    ob_start();
                    try {
                        $controller->{$routes[$routeKey][1]}();
                        $output = ob_get_contents();
                        echo "<p style='color: green;'>✅ 컨트롤러 실행 성공</p>";
                        echo "<details><summary>실행 결과 (처음 500자)</summary>";
                        echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
                        echo "</details>";
                    } catch (Exception $e) {
                        echo "<p style='color: red;'>❌ 컨트롤러 실행 실패: " . $e->getMessage() . "</p>";
                        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                    }
                    ob_end_clean();
                    
                } else {
                    echo "<p style='color: red;'>❌ 컨트롤러 메서드 없음: {$routes[$routeKey][1]}</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ 컨트롤러 클래스 없음: {$routes[$routeKey][0]}</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ 컨트롤러 로드 실패: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ 컨트롤러 파일 없음: {$controllerFile}</p>";
    }
} else {
    echo "<p style='color: red;'>❌ 라우트 매칭 실패: {$routeKey}</p>";
    echo "<p>가능한 라우트들:</p>";
    foreach ($routes as $key => $route) {
        if (strpos($key, 'GET:') === 0) {
            echo "<p>  - {$key}</p>";
        }
    }
}

// 데이터베이스 연결 테스트
echo "<h2>데이터베이스 연결 테스트</h2>";
try {
    $db = Database::getInstance();
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM lectures WHERE content_type = 'event'");
    echo "<p style='color: green;'>✅ 데이터베이스 연결 성공</p>";
    echo "<p>이벤트 개수: {$result['count']}</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 데이터베이스 연결 실패: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/events'>실제 이벤트 페이지로 이동</a></p>";
?>