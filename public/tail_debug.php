<?php
/**
 * 메모리 효율적인 로그 확인 도구
 */

// 메모리 한계 늘리기
ini_set('memory_limit', '512M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 메모리 효율적인 디버깅</h1>";

try {
    // tail 명령어로 최근 로그만 확인
    echo "<h2>📋 최근 에러 로그 (tail -50)</h2>";
    
    $logPath = '/var/www/html/topmkt/logs/topmkt_errors.log';
    if (file_exists($logPath)) {
        echo "✅ 로그 파일 존재: $logPath<br>";
        
        // tail 명령어로 마지막 50줄만 가져오기
        $output = shell_exec("tail -50 '$logPath' 2>&1");
        
        if ($output) {
            echo "<h3>최근 50줄:</h3>";
            echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto; max-height: 400px; overflow-y: auto;'>";
            echo htmlspecialchars($output);
            echo "</pre>";
        } else {
            echo "❌ tail 명령어 실행 실패<br>";
        }
    } else {
        echo "❌ 로그 파일이 없습니다: $logPath<br>";
    }
    
    // 로그 파일 크기 확인
    echo "<h2>📊 로그 파일 정보</h2>";
    if (file_exists($logPath)) {
        $fileSize = filesize($logPath);
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);
        echo "파일 크기: {$fileSizeMB} MB ({$fileSize} bytes)<br>";
        
        if ($fileSizeMB > 100) {
            echo "⚠️ <strong>로그 파일이 너무 큽니다! ({$fileSizeMB} MB)</strong><br>";
            echo "로그 파일을 정리하는 것을 권장합니다.<br>";
        }
    }
    
    // 강의 신청 관련 최근 로그만 필터링
    echo "<h2>🎯 강의 신청 관련 로그</h2>";
    $registrationLogs = shell_exec("tail -100 '$logPath' | grep -i '강의\\|registration\\|lecture' 2>&1");
    
    if ($registrationLogs) {
        echo "<pre style='background: #f0fff0; padding: 10px; overflow-x: auto; max-height: 300px; overflow-y: auto;'>";
        echo htmlspecialchars($registrationLogs);
        echo "</pre>";
    } else {
        echo "❌ 강의 신청 관련 로그가 없습니다.<br>";
    }
    
    // PHP 에러 로그 확인
    echo "<h2>📋 PHP 오류 로그 (tail -20)</h2>";
    $phpLogPath = '/var/log/php_errors.log';
    if (file_exists($phpLogPath)) {
        $phpOutput = shell_exec("tail -20 '$phpLogPath' 2>&1");
        if ($phpOutput) {
            echo "<pre style='background: #fff0f0; padding: 10px; overflow-x: auto; max-height: 200px; overflow-y: auto;'>";
            echo htmlspecialchars($phpOutput);
            echo "</pre>";
        }
    } else {
        echo "❌ PHP 로그 파일이 없습니다<br>";
    }
    
    // Apache 에러 로그 확인
    echo "<h2>📋 Apache 오류 로그 (tail -20)</h2>";
    $apacheLogPath = '/var/log/apache2/error.log';
    if (file_exists($apacheLogPath)) {
        $apacheOutput = shell_exec("tail -20 '$apacheLogPath' 2>&1");
        if ($apacheOutput) {
            echo "<pre style='background: #f0f0ff; padding: 10px; overflow-x: auto; max-height: 200px; overflow-y: auto;'>";
            echo htmlspecialchars($apacheOutput);
            echo "</pre>";
        }
    } else {
        echo "❌ Apache 로그 파일이 없습니다<br>";
    }
    
    // 시스템 정보
    echo "<h2>💻 시스템 정보</h2>";
    echo "PHP 메모리 한계: " . ini_get('memory_limit') . "<br>";
    echo "현재 메모리 사용량: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB<br>";
    echo "최대 메모리 사용량: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB<br>";
    
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