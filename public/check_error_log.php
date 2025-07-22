<?php
/**
 * PHP 에러 로그 확인
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 PHP 에러 로그 확인</h1>";

// 가능한 로그 파일 경로들
$logPaths = [
    '/var/log/php_errors.log',
    '/var/log/apache2/error.log',
    '/var/log/httpd/error_log',
    '/var/www/html/topmkt/logs/topmkt_errors.log',
    '/tmp/php_errors.log',
    error_get_last()['message'] ?? ''
];

echo "<h2>📂 로그 파일 확인</h2>";

foreach ($logPaths as $path) {
    if (empty($path)) continue;
    
    if (file_exists($path)) {
        $size = filesize($path);
        $sizeMB = round($size / 1024 / 1024, 2);
        echo "✅ 발견: $path ({$sizeMB} MB)<br>";
        
        // 최근 50줄만 확인
        if ($size > 0) {
            echo "<h3>📋 최근 로그 ($path)</h3>";
            $lastLines = shell_exec("tail -50 '$path' 2>/dev/null");
            if ($lastLines) {
                echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow-y: auto; font-size: 11px;'>";
                echo htmlspecialchars($lastLines);
                echo "</pre>";
            }
        }
    } else {
        echo "❌ 없음: $path<br>";
    }
}

echo "<h2>🔍 강의 등록 관련 로그 검색</h2>";

// 가장 가능성 높은 로그 파일에서 강의 관련 에러만 필터링
$mainLogPath = '/var/www/html/topmkt/logs/topmkt_errors.log';
if (file_exists($mainLogPath)) {
    echo "<h3>📋 강의 등록 관련 최근 로그</h3>";
    
    // 'registration', '강의', 'error', 'Exception' 포함 로그만 필터링
    $filtered = shell_exec("tail -200 '$mainLogPath' | grep -i -E '(registration|강의|error|exception|fatal)' | tail -20 2>/dev/null");
    
    if ($filtered) {
        echo "<pre style='background: #fff0f0; padding: 10px; max-height: 400px; overflow-y: auto; font-size: 11px;'>";
        echo htmlspecialchars($filtered);
        echo "</pre>";
    } else {
        echo "❌ 관련 로그를 찾을 수 없습니다.<br>";
    }
}

echo "<h2>🔧 PHP 설정 확인</h2>";

echo "<h3>📋 에러 설정</h3>";
echo "display_errors: " . ini_get('display_errors') . "<br>";
echo "log_errors: " . ini_get('log_errors') . "<br>";
echo "error_log: " . ini_get('error_log') . "<br>";
echo "error_reporting: " . error_reporting() . "<br>";

echo "<h3>📋 PHP 확장 확인</h3>";
$extensions = ['mysqli', 'pdo', 'json', 'mbstring', 'curl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext<br>";
    } else {
        echo "❌ $ext<br>";
    }
}

echo "<h3>📋 메모리 설정</h3>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
echo "max_input_time: " . ini_get('max_input_time') . "<br>";

// 실시간 에러 테스트
echo "<h2>🧪 실시간 에러 테스트</h2>";

try {
    // 간단한 에러 테스트
    trigger_error("테스트 에러 메시지", E_USER_WARNING);
    echo "✅ 에러 테스트 완료<br>";
} catch (Exception $e) {
    echo "❌ 에러 테스트 실패: " . $e->getMessage() . "<br>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { border-radius: 5px; overflow-x: auto; }
</style>