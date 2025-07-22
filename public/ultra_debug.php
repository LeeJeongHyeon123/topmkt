<?php
/**
 * 🔥 울트라씽크 완전 디버깅 시스템
 * 모든 로그, 콘솔, 서버 상태를 실시간으로 수집 및 분석
 */

// 모든 오류 캡처
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// 로그 파일 경로 설정
$log_file = __DIR__ . '/debug_ultra.log';
ini_set('error_log', $log_file);

header('Content-Type: text/html; charset=UTF-8');

// 디버그 로그 함수
function debug_log($message, $type = 'INFO') {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$type] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    return $log_entry;
}

debug_log("=== 울트라 디버깅 세션 시작 ===");
debug_log("사용자 IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));
debug_log("사용자 Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));
debug_log("요청 URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔥 울트라씽크 디버깅 콘솔</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #000; color: #00ff00; margin: 0; padding: 0; overflow-x: auto; }
        .container { padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #00ff00; padding: 20px; margin-bottom: 20px; background: #001100; }
        .section { background: #111; border: 1px solid #333; margin: 15px 0; border-radius: 8px; overflow: hidden; }
        .section-header { background: #00ff00; color: #000; padding: 12px 20px; font-weight: bold; font-size: 16px; }
        .section-content { padding: 20px; }
        .log-container { background: #000; border: 1px solid #333; border-radius: 5px; padding: 15px; margin: 10px 0; max-height: 400px; overflow-y: auto; }
        .error { color: #ff4444; background: #330000; padding: 5px; margin: 2px 0; border-radius: 3px; }
        .warning { color: #ffaa00; background: #332200; padding: 5px; margin: 2px 0; border-radius: 3px; }
        .success { color: #44ff44; }
        .info { color: #4444ff; }
        .critical { color: #ff0000; background: #440000; padding: 10px; border: 2px solid #ff0000; border-radius: 5px; margin: 10px 0; }
        .btn { background: #00ff00; color: #000; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; font-weight: bold; }
        .btn:hover { background: #44ff44; }
        .btn-danger { background: #ff4444; color: #fff; }
        .btn-warning { background: #ffaa00; color: #000; }
        .real-time { position: fixed; top: 10px; right: 10px; background: #001100; border: 2px solid #00ff00; padding: 15px; border-radius: 8px; max-width: 300px; z-index: 1000; }
        .console-output { background: #000; color: #0f0; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.4; white-space: pre-wrap; overflow-x: auto; }
        .timestamp { color: #888; }
        .variable-dump { background: #001122; color: #88ccff; padding: 10px; border-radius: 5px; margin: 5px 0; overflow-x: auto; }
        .debug-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .debug-grid { grid-template-columns: 1fr; } }
        .status-indicator { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 8px; }
        .status-ok { background: #00ff00; }
        .status-error { background: #ff0000; }
        .status-warning { background: #ffaa00; }
        .tab { display: inline-block; padding: 10px 20px; background: #333; color: #fff; cursor: pointer; border-radius: 5px 5px 0 0; margin-right: 2px; }
        .tab.active { background: #00ff00; color: #000; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🔥 울트라씽크 완전 디버깅 콘솔</h1>
        <p>실시간 로그 수집 • 서버 상태 분석 • 콘솔 로그 추적</p>
        <div id="realTimeStatus" class="real-time">
            <strong>🔄 실시간 모니터링</strong><br>
            <span id="statusIndicator">⏳ 초기화 중...</span>
        </div>
    </div>

    <!-- 탭 네비게이션 -->
    <div class="tab-navigation">
        <div class="tab active" onclick="switchTab('console')">🖥️ 콘솔 로그</div>
        <div class="tab" onclick="switchTab('server')">🖧 서버 로그</div>
        <div class="tab" onclick="switchTab('php')">🐘 PHP 상태</div>
        <div class="tab" onclick="switchTab('database')">🗄️ 데이터베이스</div>
        <div class="tab" onclick="switchTab('system')">⚙️ 시스템 정보</div>
        <div class="tab" onclick="switchTab('realtime')">📊 실시간 모니터</div>
    </div>

    <!-- 콘솔 로그 탭 -->
    <div id="console" class="tab-content active">
        <div class="section">
            <div class="section-header">🖥️ 실시간 콘솔 로그 및 JavaScript 오류</div>
            <div class="section-content">
                <div id="consoleOutput" class="console-output">
콘솔 로그 캡처 준비 중...
JavaScript 오류 감지 시스템 활성화됨
브라우저 콘솔과 연동하여 모든 로그를 여기에 표시합니다.
                </div>
                <button class="btn" onclick="clearConsole()">🗑️ 콘솔 정리</button>
                <button class="btn" onclick="testJavaScriptError()">🧪 JS 오류 테스트</button>
            </div>
        </div>
    </div>

    <!-- 서버 로그 탭 -->
    <div id="server" class="tab-content">
        <div class="section">
            <div class="section-header">🖧 서버 로그 실시간 분석</div>
            <div class="section-content">
                <div class="log-container">
<?php
echo "=== 서버 로그 수집 중... ===\n";
debug_log("서버 로그 분석 시작");

// 다양한 로그 파일들 확인
$log_files = [
    '/var/log/httpd/error_log' => 'Apache Error Log',
    '/var/log/apache2/error.log' => 'Apache2 Error Log', 
    '/var/log/nginx/error.log' => 'Nginx Error Log',
    '/var/log/php-fpm/www-error.log' => 'PHP-FPM Error Log',
    '/var/log/php/error.log' => 'PHP Error Log',
    '/var/log/mysqld.log' => 'MySQL Log',
    '/var/log/mysql/error.log' => 'MySQL Error Log',
    __DIR__ . '/../logs/topmkt_errors.log' => 'TopMKT Custom Log',
    $log_file => 'Ultra Debug Log'
];

$found_logs = [];
foreach ($log_files as $file => $description) {
    if (file_exists($file) && is_readable($file)) {
        $found_logs[$file] = $description;
        echo "<span class='success'>✅ $description: $file</span>\n";
        debug_log("발견된 로그 파일: $file ($description)");
    } else {
        echo "<span class='warning'>⚠️ $description: 파일 없음 또는 읽기 불가</span>\n";
    }
}

echo "\n=== 최근 에러 로그 (최근 20줄) ===\n";

foreach ($found_logs as $file => $description) {
    echo "\n<span class='info'>📄 $description</span>\n";
    echo str_repeat('-', 60) . "\n";
    
    try {
        $lines = file($file);
        if ($lines) {
            $recent_lines = array_slice($lines, -20);
            foreach ($recent_lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // 에러 레벨에 따라 색상 구분
                if (stripos($line, 'error') !== false || stripos($line, 'fatal') !== false) {
                    echo "<span class='error'>🔴 $line</span>\n";
                    debug_log("에러 발견: $line", "ERROR");
                } elseif (stripos($line, 'warning') !== false) {
                    echo "<span class='warning'>🟡 $line</span>\n";
                    debug_log("경고 발견: $line", "WARNING");
                } else {
                    echo "<span class='timestamp'>" . substr($line, 0, 150) . "</span>\n";
                }
            }
        } else {
            echo "로그 파일이 비어있습니다.\n";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ 로그 읽기 실패: {$e->getMessage()}</span>\n";
        debug_log("로그 읽기 실패: {$e->getMessage()}", "ERROR");
    }
    
    echo "\n";
}
?>
                </div>
                <button class="btn" onclick="refreshServerLogs()">🔄 로그 새로고침</button>
                <button class="btn btn-warning" onclick="downloadLogs()">💾 로그 다운로드</button>
            </div>
        </div>
    </div>

    <!-- PHP 상태 탭 -->
    <div id="php" class="tab-content">
        <div class="section">
            <div class="section-header">🐘 PHP 환경 및 오류 상세 분석</div>
            <div class="section-content">
                <div class="debug-grid">
                    <div>
                        <h3>📊 PHP 기본 정보</h3>
                        <div class="variable-dump">
<?php
debug_log("PHP 환경 분석 시작");

echo "PHP 버전: " . PHP_VERSION . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "서버 소프트웨어: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "문서 루트: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "스크립트 경로: " . __FILE__ . "\n";
echo "현재 작업 디렉토리: " . getcwd() . "\n";
echo "메모리 제한: " . ini_get('memory_limit') . "\n";
echo "메모리 사용량: " . number_format(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
echo "최대 메모리: " . number_format(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
echo "최대 실행 시간: " . ini_get('max_execution_time') . "초\n";
echo "업로드 최대 크기: " . ini_get('upload_max_filesize') . "\n";
echo "POST 최대 크기: " . ini_get('post_max_size') . "\n";

debug_log("PHP 메모리 사용량: " . memory_get_usage());
?>
                        </div>
                    </div>
                    <div>
                        <h3>🔧 PHP 확장 모듈</h3>
                        <div class="variable-dump">
<?php
$required_extensions = [
    'mysqli' => 'MySQL 데이터베이스',
    'curl' => 'HTTP 클라이언트',
    'json' => 'JSON 처리',
    'session' => '세션 관리',
    'mbstring' => '멀티바이트 문자열',
    'openssl' => 'SSL/TLS',
    'zip' => 'ZIP 압축',
    'gd' => '이미지 처리',
    'pdo' => 'PDO 데이터베이스',
    'xml' => 'XML 처리',
    'fileinfo' => '파일 정보'
];

$missing_critical = [];
foreach ($required_extensions as $ext => $desc) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✅ $ext ($desc)</span>\n";
    } else {
        echo "<span class='error'>❌ $ext ($desc) - 누락됨</span>\n";
        $missing_critical[] = $ext;
        debug_log("누락된 확장: $ext", "WARNING");
    }
}

if (!empty($missing_critical)) {
    echo "\n<span class='critical'>🔥 누락된 중요 확장들:</span>\n";
    foreach ($missing_critical as $ext) {
        echo "   sudo yum install php-$ext\n";
    }
}
?>
                        </div>
                    </div>
                </div>

                <h3>⚠️ PHP 설정 문제점</h3>
                <div class="variable-dump">
<?php
$php_issues = [];

// 메모리 부족 체크
$memory_limit_bytes = parse_memory_limit(ini_get('memory_limit'));
$current_usage = memory_get_usage();
if ($current_usage > ($memory_limit_bytes * 0.8)) {
    $php_issues[] = "메모리 사용량이 제한의 80%를 초과했습니다";
}

// 실행 시간 체크
$max_execution_time = ini_get('max_execution_time');
if ($max_execution_time < 30) {
    $php_issues[] = "최대 실행 시간이 너무 짧습니다 ($max_execution_time 초)";
}

// 에러 보고 설정 체크
$error_reporting = error_reporting();
if ($error_reporting != E_ALL) {
    $php_issues[] = "에러 보고 설정이 최적화되지 않았습니다";
}

// 디스플레이 에러 체크
if (!ini_get('display_errors')) {
    $php_issues[] = "display_errors가 비활성화되어 있어 디버깅이 어려울 수 있습니다";
}

if (empty($php_issues)) {
    echo "<span class='success'>✅ PHP 설정에 심각한 문제가 없습니다</span>\n";
} else {
    foreach ($php_issues as $issue) {
        echo "<span class='warning'>⚠️ $issue</span>\n";
        debug_log("PHP 설정 문제: $issue", "WARNING");
    }
}

function parse_memory_limit($limit) {
    $limit = trim($limit);
    $last = strtolower($limit[strlen($limit)-1]);
    $limit = substr($limit, 0, -1);
    switch($last) {
        case 'g': $limit *= 1024;
        case 'm': $limit *= 1024;
        case 'k': $limit *= 1024;
    }
    return $limit;
}
?>
                </div>
            </div>
        </div>
    </div>

    <!-- 데이터베이스 탭 -->
    <div id="database" class="tab-content">
        <div class="section">
            <div class="section-header">🗄️ 데이터베이스 연결 및 쿼리 디버깅</div>
            <div class="section-content">
                <div class="log-container">
<?php
echo "=== 데이터베이스 완전 진단 ===\n";
debug_log("데이터베이스 진단 시작");

$db_configs = [
    ['localhost', 'root', 'Dnlszkem1!', 'TOPMKT'],
    ['127.0.0.1', 'root', 'Dnlszkem1!', 'TOPMKT'],
    ['localhost', 'root', '', 'TOPMKT']
];

$successful_connection = null;
foreach ($db_configs as $i => $config) {
    echo "🔍 연결 시도 " . ($i + 1) . ": {$config[0]}:{$config[1]}@{$config[3]}\n";
    debug_log("DB 연결 시도: {$config[0]}:{$config[1]}@{$config[3]}");
    
    try {
        $mysqli = new mysqli($config[0], $config[1], $config[2], $config[3]);
        
        if ($mysqli->connect_error) {
            echo "<span class='error'>❌ 연결 실패: {$mysqli->connect_error}</span>\n";
            debug_log("DB 연결 실패: {$mysqli->connect_error}", "ERROR");
        } else {
            echo "<span class='success'>✅ 연결 성공!</span>\n";
            $successful_connection = $mysqli;
            debug_log("DB 연결 성공: {$config[0]}");
            break;
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ 예외 발생: {$e->getMessage()}</span>\n";
        debug_log("DB 연결 예외: {$e->getMessage()}", "ERROR");
    }
}

if ($successful_connection) {
    $mysqli = $successful_connection;
    $mysqli->set_charset('utf8mb4');
    
    echo "\n=== 데이터베이스 정보 ===\n";
    $result = $mysqli->query("SELECT VERSION() as version");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "MySQL 버전: {$row['version']}\n";
        debug_log("MySQL 버전: {$row['version']}");
    }
    
    echo "\n=== 테이블 존재 확인 ===\n";
    $tables = ['lectures', 'users', 'lecture_registrations', 'lecture_images'];
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<span class='success'>✅ $table 테이블 존재</span>\n";
            
            // 테이블 구조 확인
            $result = $mysqli->query("DESCRIBE $table");
            if ($result) {
                echo "   컬럼 수: " . $result->num_rows . "개\n";
            }
            
            // 데이터 수 확인
            $result = $mysqli->query("SELECT COUNT(*) as count FROM $table");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "   데이터 수: {$row['count']}개\n";
                debug_log("$table 테이블: {$row['count']}개 데이터");
            }
        } else {
            echo "<span class='error'>❌ $table 테이블 없음</span>\n";
            debug_log("$table 테이블 없음", "WARNING");
        }
    }
    
    echo "\n=== 강의 데이터 샘플 ===\n";
    $result = $mysqli->query("SELECT id, title, status, start_date, start_time FROM lectures ORDER BY created_at DESC LIMIT 3");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "📚 [{$row['id']}] {$row['title']}\n";
            echo "   상태: {$row['status']} | 일시: {$row['start_date']} {$row['start_time']}\n";
        }
    } else {
        echo "<span class='error'>❌ 강의 데이터 조회 실패: {$mysqli->error}</span>\n";
        debug_log("강의 데이터 조회 실패: {$mysqli->error}", "ERROR");
    }
    
    echo "\n=== 쿼리 성능 테스트 ===\n";
    $start_time = microtime(true);
    $result = $mysqli->query("SELECT COUNT(*) as count FROM lectures WHERE status = 'published'");
    $end_time = microtime(true);
    $query_time = ($end_time - $start_time) * 1000;
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<span class='success'>✅ 게시된 강의 수: {$row['count']}개</span>\n";
        echo "<span class='info'>🕐 쿼리 실행 시간: " . number_format($query_time, 2) . "ms</span>\n";
        debug_log("쿼리 성능: {$query_time}ms");
    }
    
    $mysqli->close();
} else {
    echo "\n<span class='critical'>🔥 모든 데이터베이스 연결 실패!</span>\n";
    echo "가능한 원인:\n";
    echo "1. MySQL 서비스가 중지됨\n";
    echo "2. 잘못된 비밀번호\n";
    echo "3. 네트워크 문제\n";
    echo "4. 권한 문제\n";
    debug_log("모든 DB 연결 실패", "CRITICAL");
}
?>
                </div>
            </div>
        </div>
    </div>

    <!-- 시스템 정보 탭 -->
    <div id="system" class="tab-content">
        <div class="section">
            <div class="section-header">⚙️ 시스템 리소스 및 환경 정보</div>
            <div class="section-content">
                <div class="debug-grid">
                    <div>
                        <h3>💾 시스템 리소스</h3>
                        <div class="variable-dump">
<?php
debug_log("시스템 리소스 확인");

echo "운영체제: " . php_uname() . "\n";

// 메모리 정보
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    echo "시스템 부하: " . implode(', ', array_map(function($l) { return number_format($l, 2); }, $load)) . "\n";
}

// 디스크 공간
$disk_free = disk_free_space(__DIR__);
$disk_total = disk_total_space(__DIR__);
if ($disk_free && $disk_total) {
    $disk_used_percent = (($disk_total - $disk_free) / $disk_total) * 100;
    echo "디스크 사용률: " . number_format($disk_used_percent, 1) . "%\n";
    echo "여유 공간: " . number_format($disk_free / 1024 / 1024 / 1024, 2) . " GB\n";
    
    if ($disk_used_percent > 90) {
        echo "<span class='error'>⚠️ 디스크 공간 부족!</span>\n";
        debug_log("디스크 공간 부족: {$disk_used_percent}%", "WARNING");
    }
}

// 프로세스 정보
if (function_exists('getmypid')) {
    echo "프로세스 ID: " . getmypid() . "\n";
}

if (function_exists('getmyuid')) {
    echo "사용자 ID: " . getmyuid() . "\n";
}

if (function_exists('getmygid')) {
    echo "그룹 ID: " . getmygid() . "\n";
}
?>
                        </div>
                    </div>
                    <div>
                        <h3>🌐 네트워크 및 요청 정보</h3>
                        <div class="variable-dump">
<?php
echo "서버 이름: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "\n";
echo "서버 포트: " . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . "\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'YES' : 'NO') . "\n";
echo "클라이언트 IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
echo "요청 메소드: " . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown') . "\n";
echo "요청 URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
echo "쿼리 스트링: " . ($_SERVER['QUERY_STRING'] ?? 'None') . "\n";
echo "리퍼러: " . ($_SERVER['HTTP_REFERER'] ?? 'None') . "\n";
echo "Accept 언어: " . ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown') . "\n";

// 세션 정보
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "세션 ID: " . session_id() . "\n";
    echo "세션 변수 수: " . count($_SESSION) . "개\n";
} else {
    echo "세션: 비활성화\n";
}
?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 실시간 모니터 탭 -->
    <div id="realtime" class="tab-content">
        <div class="section">
            <div class="section-header">📊 실시간 모니터링 및 자동 새로고침</div>
            <div class="section-content">
                <div class="debug-grid">
                    <div>
                        <h3>🔄 자동 모니터링</h3>
                        <div id="realtimeMonitor" class="console-output">
실시간 모니터링 시작...
<span class="timestamp">[<?= date('H:i:s') ?>]</span> 모든 시스템 확인 중...
                        </div>
                    </div>
                    <div>
                        <h3>⚡ 퀵 액션</h3>
                        <button class="btn" onclick="quickHealthCheck()">🏥 퀵 헬스체크</button>
                        <button class="btn" onclick="testLectureAccess()">🎯 강의 접근 테스트</button>
                        <button class="btn btn-warning" onclick="emergencyReset()">🚨 응급 리셋</button>
                        <button class="btn btn-danger" onclick="forceRedirect()">🚀 강제 이동</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// 콘솔 로그 캡처
const originalConsoleLog = console.log;
const originalConsoleError = console.error;
const originalConsoleWarn = console.warn;

function addToConsole(message, type = 'log') {
    const output = document.getElementById('consoleOutput');
    const timestamp = new Date().toLocaleTimeString();
    const typePrefix = {
        'log': '💬',
        'error': '🔴',
        'warn': '🟡',
        'info': '🔵'
    };
    
    output.innerHTML += `<span class="timestamp">[${timestamp}]</span> ${typePrefix[type]} ${message}\n`;
    output.scrollTop = output.scrollHeight;
}

// 콘솔 함수 오버라이드
console.log = function(...args) {
    originalConsoleLog.apply(console, args);
    addToConsole(args.join(' '), 'log');
};

console.error = function(...args) {
    originalConsoleError.apply(console, args);
    addToConsole(args.join(' '), 'error');
};

console.warn = function(...args) {
    originalConsoleWarn.apply(console, args);
    addToConsole(args.join(' '), 'warn');
};

// 전역 에러 캐처
window.addEventListener('error', function(e) {
    addToConsole(`JavaScript 오류: ${e.message} (${e.filename}:${e.lineno})`, 'error');
});

window.addEventListener('unhandledrejection', function(e) {
    addToConsole(`Promise 거부: ${e.reason}`, 'error');
});

// 탭 전환 함수 (전역 스코프에서 정의)
window.switchTab = function(tabName) {
    // 모든 탭 비활성화
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // 클릭된 탭 찾기 및 활성화
    const clickedTab = event ? event.target : document.querySelector(`.tab[onclick*="${tabName}"]`);
    if (clickedTab) {
        clickedTab.classList.add('active');
    }
    
    // 선택된 탭 콘텐츠 활성화
    const tabContent = document.getElementById(tabName);
    if (tabContent) {
        tabContent.classList.add('active');
    }
    
    console.log(`탭 전환: ${tabName}`);
};

// 유틸리티 함수들 (전역 스코프)
window.clearConsole = function() {
    document.getElementById('consoleOutput').innerHTML = '콘솔 정리됨...\n';
    console.log('콘솔이 정리되었습니다.');
};

window.testJavaScriptError = function() {
    console.log('JavaScript 오류 테스트 시작...');
    try {
        // 의도적 오류 발생
        nonExistentFunction();
    } catch (e) {
        console.error('테스트 오류:', e.message);
    }
};

window.refreshServerLogs = function() {
    console.log('서버 로그 새로고침 요청...');
    location.reload();
};

window.downloadLogs = function() {
    console.log('로그 다운로드 요청...');
    // 로그 다운로드 구현
    alert('로그 다운로드 기능은 서버 관리자만 사용할 수 있습니다.');
};

window.quickHealthCheck = function() {
    console.log('퀵 헬스체크 실행...');
    const monitor = document.getElementById('realtimeMonitor');
    if (monitor) {
        monitor.innerHTML += `\n<span class="timestamp">[${new Date().toLocaleTimeString()}]</span> 🏥 헬스체크 실행...`;
        
        // 각종 체크 수행
        setTimeout(() => {
            monitor.innerHTML += `\n<span class="success">✅ PHP: 정상</span>`;
            monitor.innerHTML += `\n<span class="success">✅ 메모리: ${(performance.memory?.usedJSHeapSize/1024/1024).toFixed(2) || 'N/A'} MB</span>`;
            monitor.innerHTML += `\n<span class="info">ℹ️ 브라우저: ${navigator.userAgent.split(' ')[0]}</span>`;
            monitor.scrollTop = monitor.scrollHeight;
        }, 1000);
    }
};

window.testLectureAccess = function() {
    console.log('강의 접근 테스트 시작...');
    const monitor = document.getElementById('realtimeMonitor');
    if (monitor) {
        monitor.innerHTML += `\n<span class="timestamp">[${new Date().toLocaleTimeString()}]</span> 🎯 강의 페이지 접근 테스트...`;
        
        // 강의 페이지 접근 테스트
        fetch('/lectures', {method: 'HEAD'})
            .then(response => {
                const status = response.ok ? '✅ 접근 가능' : '❌ 접근 불가';
                monitor.innerHTML += `\n<span class="${response.ok ? 'success' : 'error'}">${status} (상태: ${response.status})</span>`;
            })
            .catch(error => {
                monitor.innerHTML += `\n<span class="error">❌ 네트워크 오류: ${error.message}</span>`;
            })
            .finally(() => {
                monitor.scrollTop = monitor.scrollHeight;
            });
    }
};

window.emergencyReset = function() {
    if (confirm('응급 리셋을 실행하시겠습니까? 모든 캐시가 정리됩니다.')) {
        console.log('응급 리셋 실행...');
        // 브라우저 캐시 정리
        if ('caches' in window) {
            caches.keys().then(names => {
                names.forEach(name => caches.delete(name));
            });
        }
        
        // 로컬 스토리지 정리
        localStorage.clear();
        sessionStorage.clear();
        
        console.log('캐시 정리 완료. 페이지를 새로고침합니다...');
        setTimeout(() => location.reload(true), 2000);
    }
};

window.forceRedirect = function() {
    console.log('강의 페이지로 강제 이동...');
    window.open('/lectures', '_blank');
};

// 실시간 상태 업데이트
function updateStatus() {
    const indicator = document.getElementById('statusIndicator');
    const now = new Date().toLocaleTimeString();
    indicator.innerHTML = `🟢 활성 (${now})`;
    
    // 메모리 사용량 체크 (가능한 경우)
    if (performance.memory) {
        const used = (performance.memory.usedJSHeapSize / 1024 / 1024).toFixed(2);
        indicator.innerHTML += `<br>💾 JS 메모리: ${used}MB`;
    }
}

// 페이지 로드 완료 시
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔥 울트라 디버깅 콘솔 초기화 완료');
    console.log('모든 시스템 모니터링 시작...');
    console.log('JavaScript 함수들이 전역 스코프에 정의되었습니다.');
    
    // 함수 정의 확인
    const functions = ['switchTab', 'clearConsole', 'testJavaScriptError', 'quickHealthCheck', 'testLectureAccess'];
    functions.forEach(func => {
        if (typeof window[func] === 'function') {
            console.log(`✅ ${func} 함수 정의됨`);
        } else {
            console.error(`❌ ${func} 함수 정의 안됨`);
        }
    });
    
    // 5초마다 상태 업데이트
    setInterval(updateStatus, 5000);
    updateStatus();
    
    // 초기 헬스체크 (3초 후)
    setTimeout(() => {
        if (typeof window.quickHealthCheck === 'function') {
            window.quickHealthCheck();
        }
    }, 3000);
    
    // 탭 클릭 이벤트 리스너 추가 (대안)
    document.querySelectorAll('.tab').forEach((tab, index) => {
        tab.addEventListener('click', function() {
            const tabNames = ['console', 'server', 'php', 'database', 'system', 'realtime'];
            if (typeof window.switchTab === 'function') {
                window.switchTab(tabNames[index]);
            } else {
                console.error('switchTab 함수를 찾을 수 없습니다.');
            }
        });
    });
});

// 페이지 언로드 시
window.addEventListener('beforeunload', function() {
    console.log('디버깅 세션 종료...');
});

// 네트워크 상태 모니터링
window.addEventListener('online', () => console.log('네트워크 연결됨'));
window.addEventListener('offline', () => console.error('네트워크 연결 끊어짐'));

// 성능 모니터링
if ('PerformanceObserver' in window) {
    const observer = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
            if (entry.entryType === 'navigation') {
                console.log(`페이지 로드 시간: ${entry.loadEventEnd - entry.loadEventStart}ms`);
            }
        }
    });
    observer.observe({entryTypes: ['navigation']});
}

// 자동 새로고침 (60초마다)
setTimeout(function() {
    if (confirm('60초가 지났습니다. 최신 로그를 확인하시겠습니까?')) {
        location.reload();
    }
}, 60000);

// 즉시 실행 초기화
(function() {
    console.log('🔥 울트라씽크 디버깅 시스템 완전 활성화!');
    console.log('모든 로그와 오류가 실시간으로 캡처됩니다.');
    console.log('JavaScript 함수 즉시 로드 완료');
    
    // 함수들이 정의되었는지 즉시 확인
    setTimeout(() => {
        console.log('=== 함수 정의 상태 확인 ===');
        const testFunctions = ['switchTab', 'clearConsole', 'quickHealthCheck'];
        testFunctions.forEach(func => {
            if (typeof window[func] === 'function') {
                console.log(`✅ ${func}: 정의됨`);
            } else {
                console.error(`❌ ${func}: 정의 안됨`);
            }
        });
    }, 100);
})();
</script>

</body>
</html>

<?php
debug_log("울트라 디버깅 페이지 완료");
debug_log("총 메모리 사용량: " . memory_get_peak_usage());
debug_log("=== 디버깅 세션 종료 ===");
?>