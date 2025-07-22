<?php
/**
 * 긴급 라우터 디버깅
 */

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

echo "Content-Type: text/plain\n\n";
echo "=== 긴급 라우터 디버깅 ===\n\n";

// 환경 변수 설정
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/lectures/167';

require_once 'src/config/routes.php';

$router = new Router();

echo "1. 라우터 생성 완료\n";
echo "2. REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "3. REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n\n";

// 라우트 키 생성 테스트
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$routeKey = $method . ':' . $uri;

echo "4. 생성된 라우트 키: " . $routeKey . "\n\n";

// 동적 라우트 테스트
echo "5. 동적 라우트 테스트:\n";

$testPattern = 'GET:/lectures/{id}';
$testRequest = 'GET:/lectures/167';

echo "- 패턴: " . $testPattern . "\n";
echo "- 요청: " . $testRequest . "\n";

// 정규식 변환 테스트
$regexPattern = preg_replace('/\{[^}]+\}/', '(\d+)', $testPattern);
$regexPattern = '#^' . str_replace('/', '\/', $regexPattern) . '$#u';

echo "- 정규식: " . $regexPattern . "\n";

$match = preg_match($regexPattern, $testRequest);
echo "- 매치 결과: " . ($match ? 'YES' : 'NO') . "\n\n";

// 모든 라우트 확인
echo "6. 사용 가능한 강의 관련 라우트:\n";

$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

foreach ($routes as $key => $route) {
    if (strpos($key, 'lectures') !== false) {
        echo "- " . $key . " => " . $route[0] . "::" . $route[1] . "\n";
    }
}

echo "\n7. 직접 dispatch 테스트:\n";

try {
    // 출력 버퍼링
    ob_start();
    $router->dispatch();
    $output = ob_get_clean();
    
    echo "- dispatch 실행 완료\n";
    echo "- 출력 길이: " . strlen($output) . " bytes\n";
    
    if (strpos($output, '404') !== false) {
        echo "- 결과: 404 페이지\n";
    } elseif (strpos($output, '<!DOCTYPE html') !== false) {
        echo "- 결과: HTML 페이지 (정상)\n";
    } else {
        echo "- 결과: 알 수 없음\n";
    }
    
} catch (Exception $e) {
    echo "- 오류: " . $e->getMessage() . "\n";
}
?>