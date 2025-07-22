<?php
/**
 * 라우터 디버깅
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/routes.php';

echo "<h1>라우터 디버깅</h1>";

try {
    // 1. 라우터 인스턴스 생성
    $router = new Router();
    
    // 2. 현재 요청 시뮬레이션
    $testUrl = '/events/detail';
    $method = 'GET';
    
    echo "<h2>🔍 1. 테스트 요청: {$method} {$testUrl}</h2>";
    
    // 3. 라우트 키 생성 로직 시뮬레이션
    $routeKey = $method . ':' . $testUrl;
    echo "<p><strong>생성된 라우트 키:</strong> {$routeKey}</p>";
    
    // 4. routes 배열 확인
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    echo "<h2>📋 2. 등록된 라우트들</h2>";
    
    // 이벤트 관련 라우트만 필터링
    $eventRoutes = [];
    foreach ($routes as $pattern => $route) {
        if (strpos($pattern, 'events') !== false) {
            $eventRoutes[$pattern] = $route;
        }
    }
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>패턴</th><th>컨트롤러</th><th>액션</th><th>매치</th></tr>";
    
    foreach ($eventRoutes as $pattern => $route) {
        $matches = ($pattern === $routeKey) ? '✅' : '❌';
        echo "<tr>";
        echo "<td>{$pattern}</td>";
        echo "<td>{$route[0]}</td>";
        echo "<td>{$route[1]}</td>";
        echo "<td>{$matches}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 5. 직접 라우트 존재 확인
    echo "<h2>🎯 3. 직접 라우트 매칭 테스트</h2>";
    
    if (isset($routes[$routeKey])) {
        $route = $routes[$routeKey];
        echo "<div style='background: #f0fdf4; padding: 15px; border-radius: 8px;'>";
        echo "<h3>✅ 라우트 매칭 성공!</h3>";
        echo "<ul>";
        echo "<li><strong>컨트롤러:</strong> {$route[0]}</li>";
        echo "<li><strong>액션:</strong> {$route[1]}</li>";
        echo "</ul>";
        echo "</div>";
        
        // 6. 컨트롤러 파일 존재 확인
        $controllerPath = SRC_PATH . '/controllers/' . $route[0] . '.php';
        echo "<h3>컨트롤러 파일 확인:</h3>";
        
        if (file_exists($controllerPath)) {
            echo "<p>✅ 컨트롤러 파일 존재: {$controllerPath}</p>";
            
            // 컨트롤러 클래스 존재 확인
            require_once $controllerPath;
            if (class_exists($route[0])) {
                echo "<p>✅ 컨트롤러 클래스 존재: {$route[0]}</p>";
                
                // 액션 메소드 존재 확인
                if (method_exists($route[0], $route[1])) {
                    echo "<p>✅ 액션 메소드 존재: {$route[1]}</p>";
                } else {
                    echo "<p>❌ 액션 메소드 없음: {$route[1]}</p>";
                }
            } else {
                echo "<p>❌ 컨트롤러 클래스 없음: {$route[0]}</p>";
            }
        } else {
            echo "<p>❌ 컨트롤러 파일 없음: {$controllerPath}</p>";
        }
        
    } else {
        echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px;'>";
        echo "<h3>❌ 라우트 매칭 실패!</h3>";
        echo "<p>찾으려는 라우트: {$routeKey}</p>";
        echo "</div>";
    }
    
    // 7. 실제 dispatch 시뮬레이션
    echo "<h2>🚀 4. 실제 dispatch 시뮬레이션</h2>";
    
    // $_SERVER 변수 설정
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $testUrl . '?id=194';
    $_SERVER['QUERY_STRING'] = 'id=194';
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
    echo "<h3>시뮬레이션 환경:</h3>";
    echo "<ul>";
    echo "<li><strong>REQUEST_METHOD:</strong> " . $_SERVER['REQUEST_METHOD'] . "</li>";
    echo "<li><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</li>";
    echo "<li><strong>QUERY_STRING:</strong> " . $_SERVER['QUERY_STRING'] . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // 8. 라우터 내부 로직 확인
    echo "<h2>🔧 5. 라우터 내부 로직 확인</h2>";
    
    // dispatch 메소드 호출 (시뮬레이션)
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    $routeKey = $method . ':' . $path;
    
    echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px;'>";
    echo "<h3>내부 처리 과정:</h3>";
    echo "<ul>";
    echo "<li><strong>원본 URI:</strong> " . $_SERVER['REQUEST_URI'] . "</li>";
    echo "<li><strong>파싱된 경로:</strong> " . $path . "</li>";
    echo "<li><strong>최종 라우트 키:</strong> " . $routeKey . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // 9. 직접 테스트 링크
    echo "<h2>🌐 6. 직접 테스트</h2>";
    
    $testLinks = [
        '/events/detail?id=194' => 'Event Detail 194',
        '/events/detail?id=193' => 'Event Detail 193',
        '/events/detail?id=192' => 'Event Detail 192'
    ];
    
    echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px;'>";
    echo "<h3>테스트 링크:</h3>";
    echo "<ul>";
    foreach ($testLinks as $link => $description) {
        echo "<li><a href='https://www.topmktx.com{$link}' target='_blank'>{$description}</a></li>";
    }
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px;'>";
    echo "<h3>❌ 오류 발생</h3>";
    echo "<p>오류: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "</div>";
}
?>