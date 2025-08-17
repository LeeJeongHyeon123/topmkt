<?php
/**
 * 웹 환경에서 관리자 라우트 테스트
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>🔍 관리자 라우트 웹 테스트</h1>";
echo "<h2>현재 시간: " . date('Y-m-d H:i:s') . "</h2>";

try {
    // 라우터 로드
    require_once CONFIG_PATH . '/routes.php';
    echo "<p>✅ Router 클래스 로드 성공</p>";
    
    $router = new Router();
    echo "<p>✅ Router 인스턴스 생성 성공</p>";
    
    // 리플렉션으로 라우트 확인
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    echo "<h3>📋 admin/users 관련 등록된 라우트:</h3>";
    echo "<ul>";
    foreach ($routes as $key => $route) {
        if (strpos($key, 'admin/users') !== false) {
            echo "<li><strong>{$key}</strong> → {$route[0]}::{$route[1]}</li>";
        }
    }
    echo "</ul>";
    
    // 테스트용 $_SERVER 설정
    $_SERVER['REQUEST_URI'] = '/admin/users';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = urldecode($uri);
    $uri = rtrim($uri, '/');
    if (empty($uri)) {
        $uri = '/';
    }
    $method = $_SERVER['REQUEST_METHOD'];
    $routeKey = $method . ':' . $uri;
    
    echo "<h3>🔧 라우팅 테스트:</h3>";
    echo "<p><strong>요청 URI:</strong> {$_SERVER['REQUEST_URI']}</p>";
    echo "<p><strong>파싱된 URI:</strong> {$uri}</p>";
    echo "<p><strong>생성된 라우트 키:</strong> {$routeKey}</p>";
    
    if (isset($routes[$routeKey])) {
        echo "<p>✅ 라우트 매치 성공!</p>";
        echo "<p><strong>실행할 컨트롤러:</strong> {$routes[$routeKey][0]}::{$routes[$routeKey][1]}</p>";
        
        // AdminController 직접 테스트
        try {
            session_start();
            $_SESSION['user_id'] = 1;
            $_SESSION['user_role'] = 'ROLE_SUPER_ADMIN';
            
            require_once SRC_PATH . '/config/database.php';
            require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
            require_once SRC_PATH . '/controllers/AdminController.php';
            
            echo "<p>✅ AdminController 로드 성공</p>";
            
            $controller = new AdminController();
            echo "<p>✅ AdminController 인스턴스 생성 성공</p>";
            
            if (method_exists($controller, 'userList')) {
                echo "<p>✅ userList 메서드 존재</p>";
                
                echo "<h3>🚀 userList 메서드 실행 테스트:</h3>";
                ob_start();
                $controller->userList();
                $output = ob_get_contents();
                ob_end_clean();
                
                echo "<p>✅ userList 실행 완료</p>";
                echo "<p><strong>출력 길이:</strong> " . strlen($output) . " bytes</p>";
                
                if (strlen($output) > 100) {
                    echo "<details><summary>🔍 출력 내용 미리보기 (처음 500자)</summary>";
                    echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
                    echo "</details>";
                }
                
            } else {
                echo "<p>❌ userList 메서드 없음</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ AdminController 실행 오류: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>오류 위치:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
        }
        
    } else {
        echo "<p>❌ 라우트 매치 실패</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ 치명적 오류: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>오류 위치:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p>🎯 테스트 완료: " . date('Y-m-d H:i:s') . "</p>";
?>