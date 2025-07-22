<?php
/**
 * 최근 PHP 에러 로그 확인 스크립트
 */

// 관리자 권한 확인 (실제 환경에서는 적절한 인증 필요)
session_start();
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['ADMIN', 'SUPER_ADMIN'])) {
    die('관리자 권한이 필요합니다.');
}

echo "<h2>최근 PHP 에러 로그</h2>";

// 가능한 로그 파일 위치들
$logPaths = [
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log',
    '/var/log/php_errors.log',
    '/var/log/php/error.log',
    '/tmp/php_errors.log',
    ini_get('error_log')
];

foreach ($logPaths as $logPath) {
    if (file_exists($logPath) && is_readable($logPath)) {
        echo "<h3>로그 파일: {$logPath}</h3>";
        
        // 최근 100줄만 읽기
        $lines = file($logPath);
        $recentLines = array_slice($lines, -100);
        
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 400px; overflow-y: scroll;'>";
        
        // LectureController 관련 로그만 필터링
        foreach ($recentLines as $line) {
            if (strpos($line, 'registration_deadline') !== false || 
                strpos($line, 'youtube_video') !== false ||
                strpos($line, 'createLecture') !== false ||
                strpos($line, 'validateLectureData') !== false ||
                strpos($line, 'MySQLi') !== false) {
                echo htmlspecialchars($line);
            }
        }
        
        echo "</pre>";
        break; // 첫 번째 찾은 로그 파일만 표시
    }
}

if (!file_exists($logPath)) {
    echo "<p>접근 가능한 로그 파일을 찾을 수 없습니다.</p>";
    echo "<p>PHP 에러 로그 설정: " . ini_get('error_log') . "</p>";
    echo "<p>로그 기록 여부: " . (ini_get('log_errors') ? 'ON' : 'OFF') . "</p>";
}
?>