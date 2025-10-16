<?php
/**
 * 로깅 시스템 테스트
 */

require_once '../src/helpers/WebLogger.php';
require_once '../src/helpers/ResponseHelper.php';
require_once '../src/helpers/GlobalErrorHandler.php';
require_once '../src/helpers/LogAnalyzer.php';

// 글로벌 에러 핸들러 등록
GlobalErrorHandler::register();

echo "<h1>로깅 시스템 테스트</h1>";

// 1. 기본 로그 테스트
echo "<h2>1. 기본 로그 레벨 테스트</h2>";
WebLogger::debug('디버그 메시지 테스트', ['test_data' => '디버그용']);
WebLogger::info('정보 메시지 테스트', ['user_id' => 123, 'action' => 'test']);
WebLogger::warning('경고 메시지 테스트', ['warning_type' => 'performance']);
WebLogger::error('에러 메시지 테스트', ['error_code' => 'TEST_001']);
echo "✅ 기본 로그 레벨 테스트 완료<br>";

// 2. 특수 로그 테스트
echo "<h2>2. 특수 로그 테스트</h2>";
WebLogger::performance('테스트 작업', 1.5, ['operation' => 'database_query']);
WebLogger::security('의심스러운 로그인 시도', ['ip' => '192.168.1.100']);
WebLogger::activity('사용자 프로필 수정', ['user_id' => 456]);
WebLogger::api('/api/test', 'POST', 0.8, 200);
WebLogger::slowQuery('SELECT * FROM users WHERE id = ?', 2.5);
echo "✅ 특수 로그 테스트 완료<br>";

// 3. 민감한 정보 마스킹 테스트
echo "<h2>3. 민감한 정보 마스킹 테스트</h2>";
WebLogger::info('로그인 시도', [
    'email' => 'user@example.com',
    'password' => 'secret123',  // 마스킹 되어야 함
    'api_key' => 'abc123',      // 마스킹 되어야 함
    'phone' => '010-1234-5678'  // 마스킹 되어야 함
]);
echo "✅ 민감한 정보 마스킹 테스트 완료<br>";

// 4. ResponseHelper 테스트
echo "<h2>4. ResponseHelper 테스트</h2>";
try {
    // ValidationException 테스트
    throw new ValidationException([
        'email' => '이메일 형식이 올바르지 않습니다.',
        'password' => '비밀번호는 8자 이상이어야 합니다.'
    ]);
} catch (ValidationException $e) {
    echo "ValidationException 처리됨: " . $e->getUserMessage() . "<br>";
}

try {
    // DatabaseException 테스트
    throw new DatabaseException('연결 실패', 'SELECT * FROM test');
} catch (DatabaseException $e) {
    echo "DatabaseException 처리됨: " . $e->getUserMessage() . "<br>";
}
echo "✅ ResponseHelper 테스트 완료<br>";

// 5. 로그 파일 확인
echo "<h2>5. 생성된 로그 파일 확인</h2>";
$logDir = '/workspace/logs/';
$today = date('Y-m-d');

$logFiles = [
    "debug-{$today}.log",
    "info-{$today}.log", 
    "warning-{$today}.log",
    "error-{$today}.log"
];

foreach ($logFiles as $logFile) {
    $filePath = $logDir . $logFile;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        $lines = count(file($filePath));
        echo "📁 {$logFile}: {$size} bytes, {$lines} lines<br>";
    } else {
        echo "❌ {$logFile}: 파일 없음<br>";
    }
}

// 6. LogAnalyzer 테스트
echo "<h2>6. LogAnalyzer 테스트</h2>";
$errorStats = LogAnalyzer::getErrorStats();
echo "오늘 에러 수: " . $errorStats['total'] . "<br>";

$performanceStats = LogAnalyzer::getPerformanceStats();
echo "오늘 요청 수: " . $performanceStats['total_requests'] . "<br>";

$anomalies = LogAnalyzer::detectAnomalies();
echo "이상 징후 감지: " . ($anomalies['anomaly_detected'] ? 'YES' : 'NO') . "<br>";

echo "✅ LogAnalyzer 테스트 완료<br>";

// 7. 최근 로그 내용 확인
echo "<h2>7. 최근 로그 내용 확인</h2>";
$infoLogFile = $logDir . "info-{$today}.log";
if (file_exists($infoLogFile)) {
    $lines = file($infoLogFile);
    $recentLines = array_slice($lines, -3); // 최근 3줄
    
    foreach ($recentLines as $line) {
        $data = json_decode($line, true);
        if ($data) {
            echo "<div style='background: #f5f5f5; padding: 5px; margin: 5px 0; font-family: monospace; font-size: 12px;'>";
            echo "[{$data['timestamp']}] {$data['level']}: {$data['message']}";
            echo "</div>";
        }
    }
}

echo "<h2>🎉 로깅 시스템 테스트 완료!</h2>";
echo "<p>모든 로그는 <code>/workspace/logs/</code> 디렉토리에 저장됩니다.</p>";
echo "<p>문제 발생 시 해당 로그 파일을 확인하여 빠르게 원인을 파악할 수 있습니다.</p>";
?>