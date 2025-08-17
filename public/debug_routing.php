<?php
/**
 * 라우팅 실시간 디버깅
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 현재 요청 시뮬레이션
$_SERVER['REQUEST_URI'] = '/notices/10/edit';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "🔧 라우팅 실시간 디버깅\n\n";
echo "테스트 URI: {$_SERVER['REQUEST_URI']}\n";
echo "테스트 METHOD: {$_SERVER['REQUEST_METHOD']}\n\n";

try {
    // 라우터 로드
    require_once CONFIG_PATH . '/routes.php';
    $router = new Router();
    
    // 내부 routes 배열 접근
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    // URI 파싱 (라우터와 동일한 로직)
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = urldecode($uri);
    $uri = rtrim($uri, '/');
    if (empty($uri)) {
        $uri = '/';
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $routeKey = $method . ':' . $uri;
    
    echo "1. URI 처리 결과:\n";
    echo "   - 원본 URI: {$_SERVER['REQUEST_URI']}\n";
    echo "   - 처리된 URI: $uri\n";
    echo "   - 라우트 키: $routeKey\n\n";
    
    // 정적 라우트 체크
    echo "2. 정적 라우트 체크:\n";
    if (isset($routes[$routeKey])) {
        echo "   ✅ 정적 라우트 매치: {$routes[$routeKey][0]}::{$routes[$routeKey][1]}\n";
    } else {
        echo "   ❌ 정적 라우트 매치 안됨\n";
    }
    echo "\n";
    
    // 동적 라우트 체크
    echo "3. 동적 라우트 체크:\n";
    $matchFound = false;
    
    foreach ($routes as $pattern => $route) {
        if (strpos($pattern, '{') !== false) {
            echo "   📋 검사 중: $pattern\n";
            
            // 매칭 로직 (라우터와 동일)
            if (strpos($pattern, '{nickname}') !== false) {
                $regexPattern = preg_replace('/\{nickname\}/', '([^\/]+)', $pattern);
            } else {
                $regexPattern = preg_replace('/\{[^}]+\}/', '(\d+)', $pattern);
            }
            
            $regexPattern = '#^' . str_replace('/', '\/', $regexPattern) . '$#u';
            
            echo "      - 정규식: $regexPattern\n";
            echo "      - 요청 키: $routeKey\n";
            
            if (preg_match($regexPattern, $routeKey)) {
                echo "      ✅ 매치됨! → {$route[0]}::{$route[1]}\n";
                $matchFound = true;
                
                // 파라미터 추출
                if (preg_match($regexPattern, $routeKey, $matches)) {
                    echo "      🔗 추출된 파라미터: " . implode(', ', array_slice($matches, 1)) . "\n";
                }
                break;
            } else {
                echo "      ❌ 매치 안됨\n";
            }
            echo "\n";
        }
    }
    
    if (!$matchFound) {
        echo "   ❌ 매칭되는 동적 라우트 없음\n";
    }
    
    // 관련 라우트들 표시
    echo "4. 공지사항 관련 라우트들:\n";
    foreach ($routes as $pattern => $route) {
        if (strpos($pattern, 'notices') !== false) {
            echo "   - $pattern → {$route[0]}::{$route[1]}\n";
        }
    }
    
    echo "\n5. 실제 라우터 호출 테스트:\n";
    
    // 실제 라우터 호출 (출력 버퍼로 캡처)
    ob_start();
    
    try {
        $router->dispatch();
        $output = ob_get_clean();
        
        if (strlen($output) > 0) {
            echo "   ✅ 라우터 실행 성공 (" . strlen($output) . " bytes)\n";
            
            if (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false) {
                echo "   ❌ PHP 오류 발견:\n";
                echo "      " . substr($output, 0, 200) . "...\n";
            } else {
                echo "   ✅ 오류 없이 실행됨\n";
            }
        } else {
            echo "   ❌ 출력 없음 (리다이렉트 또는 오류)\n";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "   ❌ 예외 발생: " . $e->getMessage() . "\n";
        echo "      파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ 치명적 오류: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n⏰ 디버깅 완료: " . date('Y-m-d H:i:s') . "\n";
?>