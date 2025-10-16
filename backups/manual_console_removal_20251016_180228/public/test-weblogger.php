<?php
/**
 * WebLogger 메서드 테스트
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/helpers/WebLogger.php';

echo "<h1>WebLogger 메서드 테스트</h1>";

try {
    // 초기화
    WebLogger::init();
    echo "<p>✅ WebLogger 초기화 성공</p>";
    
    // 클래스 메서드 확인
    $methods = get_class_methods('WebLogger');
    echo "<h2>사용 가능한 메서드:</h2>";
    echo "<ul>";
    foreach ($methods as $method) {
        echo "<li>{$method}</li>";
    }
    echo "</ul>";
    
    // info 메서드 테스트
    echo "<h2>메서드 테스트:</h2>";
    
    if (method_exists('WebLogger', 'info')) {
        WebLogger::info('테스트 메시지');
        echo "<p>✅ WebLogger::info() 성공</p>";
    } else {
        echo "<p>❌ WebLogger::info() 메서드 없음</p>";
    }
    
    if (method_exists('WebLogger', 'log')) {
        echo "<p>✅ WebLogger::log() 메서드 존재</p>";
        
        // log 메서드 파라미터 확인
        $reflection = new ReflectionMethod('WebLogger', 'log');
        echo "<p>log 메서드 파라미터 개수: " . $reflection->getNumberOfParameters() . "</p>";
        
        foreach ($reflection->getParameters() as $param) {
            echo "<p>파라미터: " . $param->getName() . ($param->isOptional() ? ' (선택)' : ' (필수)') . "</p>";
        }
    } else {
        echo "<p>❌ WebLogger::log() 메서드 없음</p>";
    }
    
    if (method_exists('WebLogger', 'getLogs')) {
        echo "<p>✅ WebLogger::getLogs() 메서드 존재</p>";
    } else {
        echo "<p>❌ WebLogger::getLogs() 메서드 없음</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ 오류: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
</style>