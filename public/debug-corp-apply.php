<?php
/**
 * 기업 신청 디버그 도구
 */

// 최근 PHP 오류 로그 확인
echo "<h3>🔍 PHP 오류 로그 (최근 20줄)</h3>";
echo "<pre>";
$logFiles = [
    '/var/log/apache2/error.log',
    '/var/log/php_errors.log',
    '/var/log/php/error.log',
    '/tmp/php_errors.log'
];

$found = false;
foreach ($logFiles as $logFile) {
    if (file_exists($logFile) && is_readable($logFile)) {
        echo "=== $logFile ===\n";
        $lines = file($logFile);
        $recentLines = array_slice($lines, -20);
        foreach ($recentLines as $line) {
            if (strpos($line, 'CORP_APPLY') !== false || strpos($line, 'Corporate') !== false) {
                echo htmlspecialchars($line);
            }
        }
        $found = true;
        break;
    }
}

if (!$found) {
    echo "로그 파일을 찾을 수 없습니다.\n";
    echo "확인된 로그 위치:\n";
    foreach ($logFiles as $logFile) {
        echo "- $logFile: " . (file_exists($logFile) ? "존재" : "없음") . "\n";
    }
}
echo "</pre>";

// PHP 설정 확인
echo "<h3>📋 PHP 오류 로깅 설정</h3>";
echo "<pre>";
echo "log_errors: " . ini_get('log_errors') . "\n";
echo "error_log: " . ini_get('error_log') . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . error_reporting() . "\n";
echo "</pre>";

// 수동 테스트
echo "<h3>🧪 수동 테스트</h3>";
echo "<pre>";
try {
    // SRC_PATH 상수 정의
    if (!defined('SRC_PATH')) {
        define('SRC_PATH', '/var/www/html/topmkt/src');
    }
    
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/models/Corporate.php';
    
    $corporate = new Corporate();
    echo "✅ Corporate 모델 생성 성공\n";
    
    // 테스트 데이터로 중복 체크
    $result = $corporate->checkBusinessNumberExists(null, 4);
    echo "✅ null 사업자번호 중복 체크 결과: " . ($result ? 'true' : 'false') . "\n";
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
echo "</pre>";

// 실시간 로그 확인
echo "<h3>📊 실시간 로그 확인</h3>";
echo "<p>기업 신청을 시도한 후 이 페이지를 새로고침하면 최신 로그를 확인할 수 있습니다.</p>";
echo "<button onclick='location.reload()'>🔄 새로고침</button>";
?>