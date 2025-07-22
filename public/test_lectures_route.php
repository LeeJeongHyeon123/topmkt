<?php
/**
 * 🧪 강의 라우트 직접 테스트
 * 404 오류 원인 파악
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>🧪 라우트 테스트</title>";
echo "<style>body{font-family:monospace;background:#000;color:#0f0;padding:20px;} .error{color:#f00;} .success{color:#0f0;} .warning{color:#fa0;} pre{background:#111;padding:15px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🧪 강의 라우트 직접 테스트</h1>";

echo "<h2>1️⃣ 파일 존재 확인</h2>";
echo "<pre>";

// 1. 기본 경로 설정
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('SRC_PATH', ROOT_PATH . '/src');

echo "ROOT_PATH: " . ROOT_PATH . "\n";
echo "SRC_PATH: " . SRC_PATH . "\n\n";

// 2. 핵심 파일들 존재 확인
$files_to_check = [
    'routes' => SRC_PATH . '/config/routes.php',
    'LectureController' => SRC_PATH . '/controllers/LectureController.php',
    'Database' => SRC_PATH . '/config/database.php',
    'index' => ROOT_PATH . '/public/index.php'
];

foreach ($files_to_check as $name => $path) {
    if (file_exists($path)) {
        echo "<span class='success'>✅ $name: $path</span>\n";
    } else {
        echo "<span class='error'>❌ $name: $path (누락)</span>\n";
    }
}

echo "</pre>";

echo "<h2>2️⃣ 라우터 직접 테스트</h2>";
echo "<pre>";

try {
    // 3. 라우터 클래스 로드
    if (file_exists(SRC_PATH . '/config/routes.php')) {
        require_once SRC_PATH . '/config/routes.php';
        echo "<span class='success'>✅ 라우터 클래스 로드 성공</span>\n";
        
        // 4. 라우터 인스턴스 생성
        $router = new Router();
        echo "<span class='success'>✅ 라우터 인스턴스 생성 성공</span>\n";
        
        // 5. 가상 요청으로 라우팅 테스트
        $_SERVER['REQUEST_URI'] = '/lectures';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        echo "\n가상 요청 설정:\n";
        echo "URI: " . $_SERVER['REQUEST_URI'] . "\n";
        echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
        
        // 6. 라우트 매칭 테스트 (dispatch 호출하지 않고 수동 확인)
        $uri = '/lectures';
        $method = 'GET';
        $routeKey = $method . ':' . $uri;
        
        echo "\n라우트 키: $routeKey\n";
        
        // Reflection을 사용해서 private routes 배열 접근
        $reflection = new ReflectionClass($router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($router);
        
        if (isset($routes[$routeKey])) {
            $route = $routes[$routeKey];
            echo "<span class='success'>✅ 라우트 매칭 성공: $routeKey</span>\n";
            echo "컨트롤러: " . $route[0] . "\n";
            echo "액션: " . $route[1] . "\n";
            
            // 7. 컨트롤러 파일 존재 확인
            $controllerPath = SRC_PATH . '/controllers/' . $route[0] . '.php';
            if (file_exists($controllerPath)) {
                echo "<span class='success'>✅ 컨트롤러 파일 존재: $controllerPath</span>\n";
                
                // 8. 컨트롤러 클래스 로드 테스트
                require_once $controllerPath;
                if (class_exists($route[0])) {
                    echo "<span class='success'>✅ 컨트롤러 클래스 존재: {$route[0]}</span>\n";
                    
                    // 9. 메소드 존재 확인
                    if (method_exists($route[0], $route[1])) {
                        echo "<span class='success'>✅ 메소드 존재: {$route[1]}</span>\n";
                        
                        echo "\n<span class='success'>🎉 모든 라우팅 구성 요소가 정상입니다!</span>\n";
                        
                    } else {
                        echo "<span class='error'>❌ 메소드 없음: {$route[1]}</span>\n";
                    }
                } else {
                    echo "<span class='error'>❌ 컨트롤러 클래스 없음: {$route[0]}</span>\n";
                }
            } else {
                echo "<span class='error'>❌ 컨트롤러 파일 없음: $controllerPath</span>\n";
            }
        } else {
            echo "<span class='error'>❌ 라우트 매칭 실패: $routeKey</span>\n";
            echo "사용 가능한 라우트들:\n";
            foreach ($routes as $key => $value) {
                if (strpos($key, 'GET:') === 0) {
                    echo "  $key => [{$value[0]}, {$value[1]}]\n";
                }
            }
        }
        
    } else {
        echo "<span class='error'>❌ 라우터 파일 없음</span>\n";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>💥 오류 발생: " . $e->getMessage() . "</span>\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";

echo "<h2>3️⃣ 실제 라우팅 시뮬레이션</h2>";
echo "<pre>";

try {
    echo "실제 라우팅 실행 시뮬레이션...\n";
    
    // 실제 라우터 dispatch 호출하되 출력을 캡처
    ob_start();
    
    // 새로운 라우터 인스턴스로 실제 dispatch 실행
    $_SERVER['REQUEST_URI'] = '/lectures';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    $router = new Router();
    $router->dispatch();
    
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "라우팅 결과 출력 길이: " . strlen($output) . " 바이트\n";
    
    if (strlen($output) > 0) {
        echo "<span class='success'>✅ 라우팅 성공 - 출력 생성됨</span>\n";
        echo "출력 시작 부분 (처음 200자):\n";
        echo htmlspecialchars(substr($output, 0, 200)) . "...\n";
    } else {
        echo "<span class='error'>❌ 라우팅 실패 - 출력 없음</span>\n";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>💥 라우팅 실행 오류: " . $e->getMessage() . "</span>\n";
}

echo "</pre>";

echo "<h2>4️⃣ 해결 방안</h2>";
echo "<div style='color:#fff;padding:15px;background:#222;border-radius:5px;'>";
echo "<h3>🔧 가능한 문제들:</h3>";
echo "<ul>";
echo "<li><strong>웹서버 설정 문제</strong> - .htaccess 또는 rewrite 규칙</li>";
echo "<li><strong>index.php 라우팅 오류</strong> - 메인 라우터 실행 실패</li>";
echo "<li><strong>파일 권한 문제</strong> - 웹서버가 파일에 접근 불가</li>";
echo "<li><strong>PHP 오류</strong> - 라우팅 중 치명적 오류 발생</li>";
echo "</ul>";
echo "<h3>🚀 직접 접근 테스트:</h3>";
echo "<p><a href='/lectures' style='color:#0f0;'>👉 /lectures 직접 접근</a></p>";
echo "<p><a href='/index.php/lectures' style='color:#0f0;'>👉 /index.php/lectures 직접 접근</a></p>";
echo "</div>";

echo "</body></html>";
?>