<?php
/**
 * 실시간 로그 모니터링 도구
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 헤더 설정
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache');

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>실시간 강의 신청 로그 모니터링</title>
    <style>
        body { font-family: "Courier New", monospace; background: #1a1a1a; color: #00ff00; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #ffffff; text-align: center; border-bottom: 2px solid #00ff00; padding-bottom: 10px; }
        .log-container { background: #000; border: 1px solid #333; border-radius: 5px; padding: 15px; height: 500px; overflow-y: auto; }
        .log-line { margin: 2px 0; padding: 3px; border-radius: 3px; }
        .error { background: #330000; color: #ff6666; }
        .success { background: #003300; color: #66ff66; }
        .warning { background: #333300; color: #ffff66; }
        .info { background: #000033; color: #6666ff; }
        .timestamp { color: #666; font-size: 0.9em; }
        .refresh-btn { background: #00aa00; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 10px 0; }
        .refresh-btn:hover { background: #00ff00; color: black; }
        .auto-refresh { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 실시간 강의 신청 로그 모니터링</h1>
        
        <div class="auto-refresh">
            <button class="refresh-btn" onclick="refreshLogs()">🔄 새로고침</button>
            <label>
                <input type="checkbox" id="autoRefresh" onchange="toggleAutoRefresh()">
                자동 새로고침 (3초마다)
            </label>
        </div>
        
        <div class="log-container" id="logContainer">
            로그를 불러오는 중...
        </div>
        
        <p style="color: #888; font-size: 0.9em; text-align: center;">
            ⚠️ 강의 신청을 시도한 후 이 페이지에서 실시간 로그를 확인하세요
        </p>
    </div>

    <script>
        let autoRefreshInterval;
        
        function refreshLogs() {
            fetch("' . $_SERVER['PHP_SELF'] . '?action=getLogs")
                .then(response => response.text())
                .then(data => {
                    document.getElementById("logContainer").innerHTML = data;
                    // 자동으로 맨 아래로 스크롤
                    const container = document.getElementById("logContainer");
                    container.scrollTop = container.scrollHeight;
                })
                .catch(error => {
                    document.getElementById("logContainer").innerHTML = 
                        "<div class=\"log-line error\">로그 로드 오류: " + error.message + "</div>";
                });
        }
        
        function toggleAutoRefresh() {
            const autoRefresh = document.getElementById("autoRefresh");
            if (autoRefresh.checked) {
                autoRefreshInterval = setInterval(refreshLogs, 3000);
                refreshLogs(); // 즉시 한 번 실행
            } else {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
            }
        }
        
        // 페이지 로드 시 즉시 로그 로드
        document.addEventListener("DOMContentLoaded", function() {
            refreshLogs();
        });
    </script>
</body>
</html>';

// AJAX 요청 처리
if (isset($_GET['action']) && $_GET['action'] === 'getLogs') {
    exit(getRecentLogs());
}

function getRecentLogs() {
    $logPaths = [
        '/var/www/html/topmkt/logs/topmkt_errors.log',
        '/var/log/php_errors.log',
        '/var/log/apache2/error.log'
    ];
    
    $allLogs = [];
    
    foreach ($logPaths as $logPath) {
        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            $lines = explode("\n", $content);
            
            // 최근 50줄만 가져오기
            $recentLines = array_slice($lines, -50);
            
            foreach ($recentLines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // 시간 추출 (로그에 따라 다를 수 있음)
                $timestamp = date('H:i:s');
                if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
                    $timestamp = date('H:i:s', strtotime($matches[1]));
                }
                
                $allLogs[] = [
                    'timestamp' => $timestamp,
                    'content' => $line,
                    'source' => basename($logPath)
                ];
            }
        }
    }
    
    // 시간순 정렬
    usort($allLogs, function($a, $b) {
        return strcmp($a['timestamp'], $b['timestamp']);
    });
    
    $output = '';
    foreach (array_slice($allLogs, -30) as $log) {
        $cssClass = 'info';
        
        if (strpos($log['content'], '❌') !== false || strpos($log['content'], 'ERROR') !== false || strpos($log['content'], 'Fatal') !== false) {
            $cssClass = 'error';
        } elseif (strpos($log['content'], '✅') !== false || strpos($log['content'], 'SUCCESS') !== false) {
            $cssClass = 'success';
        } elseif (strpos($log['content'], '⚠️') !== false || strpos($log['content'], 'WARNING') !== false) {
            $cssClass = 'warning';
        }
        
        $output .= '<div class="log-line ' . $cssClass . '">';
        $output .= '<span class="timestamp">[' . htmlspecialchars($log['timestamp']) . ']</span> ';
        $output .= '<small>(' . htmlspecialchars($log['source']) . ')</small> ';
        $output .= htmlspecialchars($log['content']);
        $output .= '</div>';
    }
    
    if (empty($output)) {
        $output = '<div class="log-line warning">로그가 없거나 읽을 수 없습니다.</div>';
    }
    
    return $output;
}
?>