<?php
/**
 * 실시간 에러 확인
 */

echo "<h1>⚡ 실시간 에러 확인</h1>";

$logFile = '/var/www/html/topmkt/logs/topmkt_errors.log';

if (file_exists($logFile)) {
    echo "<h2>📋 최근 5분간 로그</h2>";
    
    // 현재 시간
    $now = new DateTime();
    $fiveMinutesAgo = $now->modify('-5 minutes')->format('Y-m-d H:i');
    
    echo "검색 시간대: {$fiveMinutesAgo} ~ " . date('Y-m-d H:i') . "<br><br>";
    
    // tail로 최근 50줄 가져와서 시간 필터링
    $recentLines = shell_exec("tail -50 '$logFile' 2>/dev/null");
    
    if ($recentLines) {
        $lines = explode("\n", $recentLines);
        $relevantLines = [];
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            // 시간 기반 필터링 또는 에러 키워드 필터링
            if (strpos($line, date('Y-m-d')) !== false || 
                stripos($line, 'error') !== false || 
                stripos($line, 'fatal') !== false || 
                stripos($line, 'exception') !== false ||
                stripos($line, 'registration') !== false ||
                stripos($line, '강의') !== false ||
                stripos($line, '신청') !== false) {
                $relevantLines[] = $line;
            }
        }
        
        if (!empty($relevantLines)) {
            echo "<pre style='background: #f8f8f8; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 12px;'>";
            foreach (array_slice($relevantLines, -20) as $line) { // 최근 20줄만
                echo htmlspecialchars($line) . "\n";
            }
            echo "</pre>";
        } else {
            echo "❌ 최근 5분간 관련 로그가 없습니다.<br>";
        }
    } else {
        echo "❌ 로그 파일을 읽을 수 없습니다.<br>";
    }
    
    // 파일 크기 정보
    $fileSize = filesize($logFile);
    $fileSizeMB = round($fileSize / 1024 / 1024, 2);
    echo "<br>📊 로그 파일 크기: {$fileSizeMB} MB<br>";
    
} else {
    echo "❌ 로그 파일을 찾을 수 없습니다: $logFile<br>";
}

echo "<h2>💡 디버깅 안내</h2>";
echo "1. 강의 재신청을 시도하신 후<br>";
echo "2. 이 페이지를 새로고침하여<br>";
echo "3. 실시간 에러 로그를 확인하세요<br>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { border-radius: 5px; }
</style>