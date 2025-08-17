<?php
/**
 * 라우팅 시스템 최종 테스트
 */

echo "<h1>🔍 라우팅 시스템 최종 테스트</h1>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";

// 상수 정의
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('SRC_PATH')) {
    define('SRC_PATH', ROOT_PATH . '/src');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', SRC_PATH . '/config');
}

try {
    // 라우터 로드
    require_once CONFIG_PATH . '/routes.php';
    echo "<p>✅ Router 클래스 로드 성공</p>";
    
    $router = new Router();
    echo "<p>✅ Router 인스턴스 생성 성공</p>";
    
    // 라우터의 내부 상태 확인
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    echo "<p><strong>등록된 전체 라우트 수:</strong> " . count($routes) . "</p>";
    
    // 주요 라우트 확인
    $testRoutes = [
        'GET:/' => 'HomeController::index',
        'GET:/admin/users' => 'AdminController::userList', 
        'GET:/auth/login' => 'AuthController::showLogin'
    ];
    
    echo "<h3>주요 라우트 확인:</h3><ul>";
    foreach ($testRoutes as $route => $expected) {
        if (isset($routes[$route])) {
            $actual = $routes[$route][0] . '::' . $routes[$route][1];
            if ($actual === $expected) {
                echo "<li>✅ {$route} → {$actual}</li>";
            } else {
                echo "<li>⚠️ {$route} → {$actual} (예상: {$expected})</li>";
            }
        } else {
            echo "<li>❌ {$route} → 라우트 없음</li>";
        }
    }
    echo "</ul>";
    
    // HomeController 존재 확인
    echo "<h3>HomeController 확인:</h3>";
    $homeControllerPath = SRC_PATH . '/controllers/HomeController.php';
    
    if (file_exists($homeControllerPath)) {
        echo "<p>✅ HomeController.php 파일 존재</p>";
        
        require_once $homeControllerPath;
        if (class_exists('HomeController')) {
            echo "<p>✅ HomeController 클래스 로드 성공</p>";
            
            $homeController = new HomeController();
            if (method_exists($homeController, 'index')) {
                echo "<p>✅ index 메서드 존재</p>";
            } else {
                echo "<p>❌ index 메서드 없음</p>";
            }
        } else {
            echo "<p>❌ HomeController 클래스 로드 실패</p>";
        }
    } else {
        echo "<p>❌ HomeController.php 파일 없음</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ 오류 발생</h3>";
    echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p>🎯 테스트 완료: " . date('Y-m-d H:i:s') . "</p>";
?>