<?php
/**
 * WebLogger 클래스 존재 여부 확인
 */

// 오류 표시 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 WebLogger 클래스 확인</h1>";

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

try {
    echo "<h2>📂 기본 파일 로드</h2>";
    
    // 기본 설정만 로드
    if (file_exists(CONFIG_PATH . '/paths.php')) {
        require_once CONFIG_PATH . '/paths.php';
        echo "✅ paths.php 로드 완료<br>";
    }
    
    if (file_exists(CONFIG_PATH . '/config.php')) {
        require_once CONFIG_PATH . '/config.php';
        echo "✅ config.php 로드 완료<br>";
    }
    
    echo "<h2>🔍 WebLogger 클래스 검색</h2>";
    
    // WebLogger 클래스 존재 여부 확인
    if (class_exists('WebLogger')) {
        echo "✅ WebLogger 클래스가 존재합니다<br>";
        
        $reflection = new ReflectionClass('WebLogger');
        echo "📍 WebLogger 클래스 파일: " . $reflection->getFileName() . "<br>";
        
        $methods = $reflection->getMethods();
        echo "📋 WebLogger 메소드 목록:<br>";
        foreach ($methods as $method) {
            echo "- " . $method->getName() . "<br>";
        }
    } else {
        echo "❌ WebLogger 클래스가 존재하지 않습니다<br>";
    }
    
    echo "<h2>📂 WebLogger 파일 검색</h2>";
    
    // WebLogger 파일 직접 검색
    $possiblePaths = [
        SRC_PATH . '/helpers/WebLogger.php',
        SRC_PATH . '/utils/WebLogger.php',
        SRC_PATH . '/services/WebLogger.php',
        SRC_PATH . '/loggers/WebLogger.php',
        ROOT_PATH . '/vendor/autoload.php'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            echo "✅ 발견: $path<br>";
        } else {
            echo "❌ 없음: $path<br>";
        }
    }
    
    echo "<h2>🔍 관련 파일 검색</h2>";
    
    // logger 관련 파일 검색
    $srcFiles = glob(SRC_PATH . '/**/*.php', GLOB_BRACE);
    $loggerFiles = [];
    
    foreach ($srcFiles as $file) {
        $content = file_get_contents($file);
        if (strpos($content, 'class WebLogger') !== false || 
            strpos($content, 'WebLogger::') !== false) {
            $loggerFiles[] = $file;
        }
    }
    
    if (!empty($loggerFiles)) {
        echo "📁 WebLogger 관련 파일들:<br>";
        foreach ($loggerFiles as $file) {
            echo "- $file<br>";
        }
    } else {
        echo "❌ WebLogger 관련 파일을 찾을 수 없습니다<br>";
    }
    
    echo "<h2>📋 ResponseHelper 로드 테스트</h2>";
    
    if (file_exists(SRC_PATH . '/helpers/ResponseHelper.php')) {
        echo "✅ ResponseHelper.php 파일 존재<br>";
        
        try {
            require_once SRC_PATH . '/helpers/ResponseHelper.php';
            echo "✅ ResponseHelper.php 로드 성공<br>";
            
            if (class_exists('ResponseHelper')) {
                echo "✅ ResponseHelper 클래스 존재<br>";
                
                // 간단한 메소드 호출 테스트
                $methods = get_class_methods('ResponseHelper');
                echo "📋 ResponseHelper 메소드 수: " . count($methods) . "<br>";
            }
            
        } catch (Exception $e) {
            echo "❌ ResponseHelper 로드 실패: " . htmlspecialchars($e->getMessage()) . "<br>";
        } catch (Error $e) {
            echo "❌ ResponseHelper Fatal Error: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 예외 발생</h2>";
    echo "<strong>예외:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error</h2>";
    echo "<strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
</style>