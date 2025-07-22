<?php
/**
 * 500 오류 디버깅 스크립트
 */

echo "<h1>🔍 500 오류 디버깅</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .error-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .success { color: green; font-weight: bold; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>";

// PHP 에러 로그 확인
echo "<div class='error-section'>";
echo "<h2>📋 PHP 에러 로그 (최근 50줄)</h2>";

$logFiles = [
    '/workspace/var/www/html/topmkt/logs/php_errors.log',
    '/var/log/php_errors.log',
    '/var/log/httpd/error_log',
    '/var/log/apache2/error.log'
];

$foundLog = false;
foreach ($logFiles as $logFile) {
    if (file_exists($logFile) && is_readable($logFile)) {
        echo "<h3>로그 파일: {$logFile}</h3>";
        $lines = file($logFile);
        $recentLines = array_slice($lines, -50); // 최근 50줄
        
        echo "<pre>";
        foreach ($recentLines as $line) {
            // 500 오류나 events와 관련된 라인 강조
            if (strpos($line, 'events') !== false || strpos($line, 'EventController') !== false || strpos($line, 'Fatal') !== false || strpos($line, 'Error') !== false) {
                echo "<span class='error'>" . htmlspecialchars($line) . "</span>";
            } else {
                echo htmlspecialchars($line);
            }
        }
        echo "</pre>";
        $foundLog = true;
        break;
    }
}

if (!$foundLog) {
    echo "<span class='warning'>⚠️ PHP 에러 로그를 찾을 수 없습니다.</span>";
}

echo "</div>";

// 웹서버 에러 로그 확인
echo "<div class='error-section'>";
echo "<h2>🌐 웹서버 에러 로그</h2>";

// 현재 시간 기준으로 최근 로그만 확인
$currentTime = time();
$fiveMinutesAgo = $currentTime - 300; // 5분 전

$webLogFiles = [
    '/var/log/httpd/error_log',
    '/var/log/apache2/error.log',
    '/workspace/var/www/html/topmkt/logs/topmkt_errors.log'
];

$foundWebLog = false;
foreach ($webLogFiles as $logFile) {
    if (file_exists($logFile) && is_readable($logFile)) {
        echo "<h3>웹서버 로그: {$logFile}</h3>";
        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);
        $recentLines = array_slice($lines, -30); // 최근 30줄
        
        echo "<pre>";
        foreach ($recentLines as $line) {
            if (!empty(trim($line))) {
                if (strpos($line, 'events') !== false || strpos($line, '[error]') !== false) {
                    echo "<span class='error'>" . htmlspecialchars($line) . "</span>\n";
                } else {
                    echo htmlspecialchars($line) . "\n";
                }
            }
        }
        echo "</pre>";
        $foundWebLog = true;
        break;
    }
}

if (!$foundWebLog) {
    echo "<span class='warning'>⚠️ 웹서버 에러 로그를 찾을 수 없습니다.</span>";
}

echo "</div>";

// 디스크 공간 및 권한 확인
echo "<div class='error-section'>";
echo "<h2>💾 시스템 상태 확인</h2>";

// 디스크 공간
$freeBytes = disk_free_space('/workspace');
$totalBytes = disk_total_space('/workspace');
$usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;

echo "<p><strong>디스크 사용률:</strong> " . number_format($usedPercent, 1) . "%</p>";

// 업로드 디렉토리 권한 확인
$uploadDir = '/workspace/var/www/html/topmkt/public/assets/uploads/events';
if (is_dir($uploadDir)) {
    $perms = fileperms($uploadDir);
    echo "<p><strong>업로드 디렉토리 권한:</strong> " . substr(sprintf('%o', $perms), -4) . "</p>";
    echo "<p><strong>업로드 디렉토리 쓰기 가능:</strong> " . (is_writable($uploadDir) ? '✅ 예' : '❌ 아니오') . "</p>";
} else {
    echo "<p><span class='error'>❌ 업로드 디렉토리가 존재하지 않습니다: {$uploadDir}</span></p>";
}

// PHP 메모리 및 업로드 제한 확인
echo "<p><strong>PHP 메모리 제한:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>파일 업로드 제한:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>POST 데이터 제한:</strong> " . ini_get('post_max_size') . "</p>";

echo "</div>";

// 현재 요청 정보
echo "<div class='error-section'>";
echo "<h2>📡 현재 요청 정보</h2>";
echo "<p><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
echo "<p><strong>HTTP_USER_AGENT:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</p>";
echo "<p><strong>현재 시간:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

?>