<?php
/**
 * 최신 PHP 에러 확인
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 최신 PHP 에러 확인</h1>";

// 가능한 에러 로그 파일들
$errorLogs = [
    '/var/log/php_errors.log',
    '/var/log/apache2/error.log',
    '/var/log/httpd/error_log',
    '/var/www/html/topmkt/logs/topmkt_errors.log',
    error_log() // PHP 기본 에러 로그
];

echo "<h2>📋 에러 로그 파일 확인</h2>";

foreach ($errorLogs as $logFile) {
    if (empty($logFile)) continue;
    
    if (file_exists($logFile) && is_readable($logFile)) {
        $size = filesize($logFile);
        $sizeMB = round($size / 1024 / 1024, 2);
        echo "✅ $logFile ({$sizeMB} MB)<br>";
        
        // 최근 30줄만 확인하고 강의 관련 에러 필터링
        echo "<h3>📄 최근 에러 ($logFile)</h3>";
        $recentErrors = shell_exec("tail -30 '$logFile' 2>/dev/null | grep -i -E '(fatal|error|exception|registration|lecture)' 2>/dev/null");
        
        if ($recentErrors) {
            echo "<pre style='background: #ffe6e6; padding: 10px; max-height: 200px; overflow-y: auto; font-size: 11px;'>";
            echo htmlspecialchars($recentErrors);
            echo "</pre>";
        } else {
            echo "❌ 관련 에러 없음<br>";
        }
    } else {
        echo "❌ $logFile (접근 불가)<br>";
    }
}

// 현재 시점 기준으로 실시간 에러 로그 확인
echo "<h2>⏰ 실시간 에러 확인</h2>";
echo "현재 시간: " . date('Y-m-d H:i:s') . "<br>";

// PHP 에러 로그 강제 생성 테스트
echo "<h3>🧪 에러 로깅 테스트</h3>";
error_log("=== 강의 재신청 디버깅 시작 - " . date('Y-m-d H:i:s') . " ===");
echo "✅ 테스트 로그 기록 완료<br>";

// 메모리 및 시스템 상태
echo "<h2>💻 시스템 상태</h2>";
echo "PHP 메모리 한계: " . ini_get('memory_limit') . "<br>";
echo "현재 메모리 사용: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB<br>";
echo "최대 실행 시간: " . ini_get('max_execution_time') . " 초<br>";

// 강의 등록 관련 특정 시간대 로그 확인 (최근 10분)
$mainLog = '/var/www/html/topmkt/logs/topmkt_errors.log';
if (file_exists($mainLog)) {
    echo "<h3>🔍 최근 10분 강의 관련 로그</h3>";
    $currentTime = time();
    $tenMinutesAgo = $currentTime - 600; // 10분 전
    
    $recentLogs = shell_exec("tail -100 '$mainLog' | grep '" . date('Y-m-d H:') . "' | grep -i -E '(registration|강의|신청|error|fatal|exception)' 2>/dev/null");
    
    if ($recentLogs) {
        echo "<pre style='background: #f0f8ff; padding: 10px; max-height: 300px; overflow-y: auto; font-size: 11px;'>";
        echo htmlspecialchars($recentLogs);
        echo "</pre>";
    } else {
        echo "❌ 최근 10분간 관련 로그 없음<br>";
    }
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { border-radius: 5px; overflow-x: auto; }
</style>