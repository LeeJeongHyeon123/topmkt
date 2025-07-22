<?php
/**
 * 임시 디버깅 로그 확인
 * URL: /debug_log.php
 */

// 보안: 관리자만 접근 가능하도록 설정
session_start();
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['ADMIN', 'SUPER_ADMIN'])) {
    die('Access denied');
}

echo "<h2>최근 PHP 에러 로그 (관련 키워드 필터링)</h2>";

// 키워드 목록
$keywords = [
    'registration_deadline',
    'youtube_video', 
    'createLecture',
    'updateLecture',
    'MySQLi',
    '파라미터 바인딩',
    '실제 저장된 데이터'
];

// 가능한 로그 위치들
$possibleLogs = [
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log', 
    '/var/log/php_errors.log',
    '/tmp/php_errors.log',
    ini_get('error_log'),
    '/var/log/httpd/error_log'
];

$found = false;

foreach ($possibleLogs as $logFile) {
    if (file_exists($logFile) && is_readable($logFile)) {
        echo "<h3>로그 파일: $logFile</h3>";
        
        // 최근 1000줄 읽기
        $handle = fopen($logFile, 'r');
        if ($handle) {
            fseek($handle, -50000, SEEK_END); // 파일 끝에서 50KB 전부터 읽기
            $content = fread($handle, 50000);
            fclose($handle);
            
            $lines = explode("\n", $content);
            $recentLines = array_slice($lines, -200); // 최근 200줄
            
            echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 500px; overflow-y: scroll; font-size: 12px;'>";
            
            foreach ($recentLines as $line) {
                // 키워드 중 하나라도 포함되어 있으면 출력
                foreach ($keywords as $keyword) {
                    if (stripos($line, $keyword) !== false) {
                        echo htmlspecialchars($line) . "\n";
                        break;
                    }
                }
            }
            
            echo "</pre>";
            $found = true;
            break;
        }
    }
}

if (!$found) {
    echo "<p>접근 가능한 로그 파일을 찾을 수 없습니다.</p>";
    echo "<p>PHP 설정:</p>";
    echo "<ul>";
    echo "<li>error_log: " . ini_get('error_log') . "</li>";
    echo "<li>log_errors: " . (ini_get('log_errors') ? 'ON' : 'OFF') . "</li>";
    echo "<li>display_errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</li>";
    echo "</ul>";
}
?>