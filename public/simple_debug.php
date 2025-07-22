<?php
/**
 * 간단한 디버깅 도구
 */

// 오류 표시 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 간단한 디버깅</h1>";

try {
    // 로그 파일 확인
    echo "<h2>📋 에러 로그 확인</h2>";
    
    $logPath = '/var/www/html/topmkt/logs/topmkt_errors.log';
    if (file_exists($logPath)) {
        echo "✅ 로그 파일 존재: $logPath<br>";
        $content = file_get_contents($logPath);
        $lines = explode("\n", $content);
        $recentLines = array_slice($lines, -20);
        
        echo "<h3>최근 20줄:</h3>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
        foreach ($recentLines as $line) {
            if (trim($line)) {
                echo htmlspecialchars($line) . "\n";
            }
        }
        echo "</pre>";
    } else {
        echo "❌ 로그 파일이 없습니다: $logPath<br>";
    }
    
    // PHP 오류 로그 확인
    echo "<h2>📋 PHP 오류 로그</h2>";
    $phpLogPath = '/var/log/php_errors.log';
    if (file_exists($phpLogPath)) {
        echo "✅ PHP 로그 파일 존재<br>";
        $phpContent = file_get_contents($phpLogPath);
        $phpLines = explode("\n", $phpContent);
        $recentPhpLines = array_slice($phpLines, -10);
        
        echo "<pre style='background: #fff0f0; padding: 10px; overflow-x: auto;'>";
        foreach ($recentPhpLines as $line) {
            if (trim($line)) {
                echo htmlspecialchars($line) . "\n";
            }
        }
        echo "</pre>";
    } else {
        echo "❌ PHP 로그 파일이 없습니다<br>";
    }
    
    // 직접 테스트
    echo "<h2>🧪 직접 라우팅 테스트</h2>";
    echo "강의 신청 라우트가 존재하는지 확인:<br>";
    
    // 상수 정의
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    define('CONFIG_PATH', SRC_PATH . '/config');
    
    require_once CONFIG_PATH . '/routes.php';
    
    $router = new Router();
    
    // routes 프로퍼티에 접근하기 위해 reflection 사용
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    echo "등록된 라우트 중 registration 관련:<br>";
    foreach ($routes as $key => $route) {
        if (strpos($key, 'registration') !== false) {
            echo "✅ $key => {$route[0]}::{$route[1]}<br>";
        }
    }
    
    // 파일 존재 확인
    echo "<h2>📁 파일 존재 확인</h2>";
    $regControllerPath = SRC_PATH . '/controllers/RegistrationController.php';
    echo "RegistrationController.php: " . (file_exists($regControllerPath) ? "✅ 존재" : "❌ 없음") . "<br>";
    
    if (file_exists($regControllerPath)) {
        require_once $regControllerPath;
        if (class_exists('RegistrationController')) {
            echo "✅ RegistrationController 클래스 로드 성공<br>";
            
            $methods = get_class_methods('RegistrationController');
            if (in_array('createRegistration', $methods)) {
                echo "✅ createRegistration 메소드 존재<br>";
            } else {
                echo "❌ createRegistration 메소드 없음<br>";
            }
        } else {
            echo "❌ RegistrationController 클래스 로드 실패<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { border-radius: 5px; font-size: 12px; }
</style>