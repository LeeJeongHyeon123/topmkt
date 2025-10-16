<?php
/**
 * RegistrationController 직접 테스트
 */

// 오류 표시 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 RegistrationController 직접 테스트</h1>";

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

try {
    echo "<h2>📂 파일 로드 테스트</h2>";
    
    // 기본 설정 로드
    echo "1. paths.php 로드 중...<br>";
    require_once CONFIG_PATH . '/paths.php';
    echo "✅ paths.php 로드 완료<br>";
    
    echo "2. config.php 로드 중...<br>";
    require_once CONFIG_PATH . '/config.php';
    echo "✅ config.php 로드 완료<br>";
    
    echo "3. database.php 로드 중...<br>";
    require_once CONFIG_PATH . '/database.php';
    echo "✅ database.php 로드 완료<br>";
    
    echo "4. BaseController.php 로드 중...<br>";
    require_once SRC_PATH . '/controllers/BaseController.php';
    echo "✅ BaseController.php 로드 완료<br>";
    
    echo "5. AuthMiddleware.php 로드 중...<br>";
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    echo "✅ AuthMiddleware.php 로드 완료<br>";
    
    echo "6. ResponseHelper.php 로드 중...<br>";
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    echo "✅ ResponseHelper.php 로드 완료<br>";
    
    echo "7. ValidationHelper.php 로드 중...<br>";
    require_once SRC_PATH . '/helpers/ValidationHelper.php';
    echo "✅ ValidationHelper.php 로드 완료<br>";
    
    echo "8. EmailService.php 로드 중...<br>";
    require_once SRC_PATH . '/services/EmailService.php';
    echo "✅ EmailService.php 로드 완료<br>";
    
    echo "<h2>🎯 RegistrationController 로드 테스트</h2>";
    echo "RegistrationController.php 로드 중...<br>";
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    echo "✅ RegistrationController.php 로드 완료<br>";
    
    echo "<h2>🏭 클래스 인스턴스 생성 테스트</h2>";
    if (class_exists('RegistrationController')) {
        echo "✅ RegistrationController 클래스 존재<br>";
        
        echo "인스턴스 생성 중...<br>";
        $controller = new RegistrationController();
        echo "✅ 인스턴스 생성 완료<br>";
        
        echo "<h3>📋 사용 가능한 메소드들:</h3>";
        $methods = get_class_methods($controller);
        foreach ($methods as $method) {
            echo "- $method<br>";
        }
        
        if (method_exists($controller, 'createRegistration')) {
            echo "✅ createRegistration 메소드 존재<br>";
        } else {
            echo "❌ createRegistration 메소드 없음<br>";
        }
        
    } else {
        echo "❌ RegistrationController 클래스 존재하지 않음<br>";
    }
    
} catch (ParseError $e) {
    echo "<h2>❌ 파싱 오류 발생</h2>";
    echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
    echo "<strong>파싱 오류:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error 발생</h2>";
    echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "<h4>스택 추적:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<h2>❌ 예외 발생</h2>";
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
    echo "<strong>예외:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "<h4>스택 추적:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>