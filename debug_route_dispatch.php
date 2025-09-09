<?php
/**
 * 라우트 디스패치 디버깅
 */

define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

// 실제 요청을 시뮬레이션
$_SERVER['REQUEST_URI'] = '/auth/forgot-password';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "<h1>🔍 라우트 디스패치 디버깅</h1>";
echo "<p><strong>시뮬레이션 요청:</strong> GET /auth/forgot-password</p>";

try {
    require_once SRC_PATH . '/config/routes.php';
    
    echo "<h2>✅ 라우터 클래스 로드 성공</h2>";
    
    // Router 인스턴스 생성
    $router = new Router();
    echo "<h2>✅ 라우터 인스턴스 생성 성공</h2>";
    
    // 수동으로 경로 확인
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    $targetKey = 'GET:/auth/forgot-password';
    if (isset($routes[$targetKey])) {
        echo "<div style='background: lightgreen; padding: 10px; margin: 10px 0;'>";
        echo "<h3>✅ 라우트 발견!</h3>";
        echo "<p><strong>키:</strong> $targetKey</p>";
        echo "<p><strong>컨트롤러:</strong> {$routes[$targetKey][0]}</p>";
        echo "<p><strong>액션:</strong> {$routes[$targetKey][1]}</p>";
        echo "</div>";
        
        // 컨트롤러 파일 확인
        $controllerPath = SRC_PATH . '/controllers/' . $routes[$targetKey][0] . '.php';
        echo "<h3>📁 컨트롤러 파일 확인:</h3>";
        echo "<p><strong>경로:</strong> $controllerPath</p>";
        echo "<p><strong>존재:</strong> " . (file_exists($controllerPath) ? '✅ 예' : '❌ 아니오') . "</p>";
        
        if (file_exists($controllerPath)) {
            require_once $controllerPath;
            if (class_exists($routes[$targetKey][0])) {
                echo "<p><strong>클래스 존재:</strong> ✅ 예</p>";
                
                $controller = new $routes[$targetKey][0]();
                if (method_exists($controller, $routes[$targetKey][1])) {
                    echo "<p><strong>메서드 존재:</strong> ✅ 예</p>";
                    
                    echo "<h3>🚀 메서드 실행 테스트:</h3>";
                    try {
                        // 세션 시작
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        
                        // CSRF 토큰 생성
                        if (!isset($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        
                        ob_start();
                        $controller->{$routes[$targetKey][1]}();
                        $output = ob_get_clean();
                        
                        echo "<div style='background: lightgreen; padding: 10px;'>";
                        echo "<h4>✅ 메서드 실행 성공!</h4>";
                        echo "<p>출력 길이: " . strlen($output) . " 바이트</p>";
                        if (strlen($output) > 0) {
                            echo "<details><summary>출력 내용 (처음 500자)</summary>";
                            echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
                            echo "</details>";
                        }
                        echo "</div>";
                        
                    } catch (Exception $e) {
                        echo "<div style='background: lightcoral; padding: 10px;'>";
                        echo "<h4>❌ 메서드 실행 실패</h4>";
                        echo "<p><strong>오류:</strong> " . $e->getMessage() . "</p>";
                        echo "<p><strong>파일:</strong> " . $e->getFile() . "</p>";
                        echo "<p><strong>라인:</strong> " . $e->getLine() . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p><strong>메서드 존재:</strong> ❌ 아니오</p>";
                }
            } else {
                echo "<p><strong>클래스 존재:</strong> ❌ 아니오</p>";
            }
        }
        
    } else {
        echo "<div style='background: lightcoral; padding: 10px;'>";
        echo "<h3>❌ 라우트를 찾을 수 없습니다!</h3>";
        echo "<p>등록된 라우트 개수: " . count($routes) . "</p>";
        echo "</div>";
    }
    
    echo "<h2>🧪 실제 디스패치 테스트</h2>";
    try {
        ob_start();
        $router->dispatch();
        $dispatchOutput = ob_get_clean();
        
        echo "<div style='background: lightgreen; padding: 10px;'>";
        echo "<h4>✅ 디스패치 성공!</h4>";
        echo "<p>출력 길이: " . strlen($dispatchOutput) . " 바이트</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: lightcoral; padding: 10px;'>";
        echo "<h4>❌ 디스패치 실패</h4>";
        echo "<p><strong>오류:</strong> " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: lightcoral; padding: 15px;'>";
    echo "<h3>❌ 오류 발생</h3>";
    echo "<p><strong>메시지:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>파일:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>라인:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>스택 트레이스:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>