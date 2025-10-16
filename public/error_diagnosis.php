<?php
/**
 * 🚨 실시간 에러 진단 및 대체 강의 페이지
 * 시스템 오류 발생 시 즉시 원인 파악 및 우회
 */

// 모든 에러 출력
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🚨 시스템 오류 진단 및 해결</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #0a0a0a; color: #00ff41; margin: 0; padding: 20px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #00ff41; padding-bottom: 20px; margin-bottom: 30px; }
        .error { color: #ff0040; background: #220010; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .success { color: #00ff41; }
        .warning { color: #ffaa00; }
        .info { color: #40aaff; }
        .critical { color: #ff4040; background: #440000; padding: 15px; border-radius: 8px; margin: 15px 0; border: 2px solid #ff4040; }
        .section { background: #1a1a1a; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #333; }
        .code { background: #000; padding: 10px; border-radius: 5px; font-family: monospace; overflow-x: auto; }
        .btn { background: #00ff41; color: #000; padding: 12px 24px; border: none; border-radius: 5px; margin: 10px 5px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn:hover { background: #40ff80; }
        .emergency { background: #ff4040; color: #fff; }
        .emergency:hover { background: #ff6060; }
        pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        h1, h2, h3 { color: #ffff40; }
        .lecture-list { background: #002200; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .lecture-item { padding: 10px; margin: 5px 0; background: #003300; border-radius: 4px; }
    </style>
</head>
<body>

<div class="header">
    <h1>🚨 탑마케팅 시스템 응급실</h1>
    <p>실시간 오류 진단 및 즉시 해결</p>
</div>

<div class="section">
    <h2>🔍 즉시 에러 원인 분석</h2>
    <pre>
<?php
echo "=== 긴급 에러 진단 시작 ===\n";
echo "시간: " . date('Y-m-d H:i:s') . "\n";
echo "사용자 IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
echo "사용자 Agent: " . substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 100) . "\n\n";

// 1. 치명적 오류 원인 체크
$critical_issues = [];

// MySQL 확장 체크
if (!extension_loaded('mysqli')) {
    $critical_issues[] = "PHP MySQLi 확장이 설치되지 않음";
}

// 메모리 부족 체크
$memory_limit = ini_get('memory_limit');
$memory_usage = memory_get_usage();
$memory_usage_mb = round($memory_usage / 1024 / 1024, 2);

if ($memory_usage_mb > 100) {
    $critical_issues[] = "메모리 사용량 과다: {$memory_usage_mb}MB (제한: $memory_limit)";
}

// 필수 파일 체크
$critical_files = [
    '../src/controllers/LectureController.php' => 'LectureController',
    '../src/config/database.php' => 'Database Config',
    '../src/views/lectures/index.php' => 'Lecture View',
    '../index.php' => 'Main Index'
];

foreach ($critical_files as $file => $desc) {
    if (!file_exists($file)) {
        $critical_issues[] = "$desc 파일 누락: $file";
    }
}

if (!empty($critical_issues)) {
    echo "<span class='error'>🔥 치명적 문제 발견:</span>\n";
    foreach ($critical_issues as $issue) {
        echo "<span class='error'>❌ $issue</span>\n";
    }
    echo "\n";
} else {
    echo "<span class='success'>✅ 치명적 문제 없음</span>\n\n";
}

// 2. 실시간 데이터베이스 연결 테스트
echo "<span class='info'>=== 데이터베이스 실시간 테스트 ===</span>\n";

try {
    $mysqli = new mysqli('localhost', 'root', 'Dnlszkem1!', 'TOPMKT');
    
    if ($mysqli->connect_error) {
        echo "<span class='error'>❌ DB 연결 실패: {$mysqli->connect_error}</span>\n";
        
        // 대체 연결 시도
        $mysqli = new mysqli('127.0.0.1', 'root', 'Dnlszkem1!', 'TOPMKT', 3306);
        if ($mysqli->connect_error) {
            echo "<span class='error'>❌ 대체 연결도 실패</span>\n";
            throw new Exception("데이터베이스 연결 불가능");
        } else {
            echo "<span class='warning'>⚠️ 대체 연결 성공 (127.0.0.1)</span>\n";
        }
    } else {
        echo "<span class='success'>✅ 데이터베이스 연결 성공</span>\n";
    }
    
    $mysqli->set_charset('utf8mb4');
    
    // 빠른 강의 조회 테스트
    $result = $mysqli->query("SELECT id, title, start_date, start_time, status FROM lectures WHERE status = 'published' ORDER BY start_date DESC LIMIT 5");
    
    if ($result) {
        echo "<span class='success'>✅ 강의 데이터 조회 성공</span>\n";
        $lectures = [];
        while ($row = $result->fetch_assoc()) {
            $lectures[] = $row;
        }
        echo "<span class='info'>📊 조회된 강의 수: " . count($lectures) . "개</span>\n\n";
    } else {
        echo "<span class='error'>❌ 강의 데이터 조회 실패: {$mysqli->error}</span>\n\n";
        throw new Exception("강의 데이터 조회 불가능");
    }
    
} catch (Exception $e) {
    echo "<span class='error'>💥 데이터베이스 오류: {$e->getMessage()}</span>\n\n";
    $lectures = [];
}

// 3. PHP 환경 상세 진단
echo "<span class='info'>=== PHP 환경 진단 ===</span>\n";
echo "PHP 버전: " . PHP_VERSION . "\n";
echo "메모리 사용량: {$memory_usage_mb}MB / $memory_limit\n";
echo "최대 실행 시간: " . ini_get('max_execution_time') . "초\n";

$required_extensions = ['mysqli', 'curl', 'json', 'session', 'mbstring'];
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✅ $ext</span>\n";
    } else {
        echo "<span class='error'>❌ $ext (누락)</span>\n";
        $missing_extensions[] = $ext;
    }
}

// 4. 실제 강의 페이지 시뮬레이션
echo "\n<span class='info'>=== 강의 페이지 시뮬레이션 ===</span>\n";

if (isset($lectures) && !empty($lectures)) {
    echo "<span class='success'>✅ 강의 데이터 로드 성공</span>\n";
    echo "<span class='info'>📚 최신 강의 목록:</span>\n";
    
    foreach ($lectures as $lecture) {
        $date_time = $lecture['start_date'] . ' ' . $lecture['start_time'];
        echo "  🎯 [{$lecture['id']}] {$lecture['title']}\n";
        echo "     📅 {$date_time} | 상태: {$lecture['status']}\n";
    }
} else {
    echo "<span class='error'>❌ 강의 데이터 로드 실패</span>\n";
}

echo "\n";

// 5. 에러 로그 확인
echo "<span class='info'>=== 최근 에러 로그 분석 ===</span>\n";

$log_files = [
    '/var/log/httpd/error_log',
    '/var/log/apache2/error.log',
    '/var/log/php-fpm/www-error.log',
    __DIR__ . '/../logs/topmkt_errors.log'
];

$recent_errors = [];
foreach ($log_files as $log_file) {
    if (file_exists($log_file) && is_readable($log_file)) {
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -10);
        
        foreach ($recent_lines as $line) {
            if (strpos($line, 'ERROR') !== false || strpos($line, 'FATAL') !== false || strpos($line, 'Warning') !== false) {
                $recent_errors[] = trim($line);
            }
        }
        
        if (!empty($recent_errors)) {
            echo "<span class='warning'>⚠️ 최근 에러 발견 ($log_file):</span>\n";
            foreach (array_slice($recent_errors, -3) as $error) {
                echo "<span class='error'>🔴 " . substr($error, 0, 100) . "...</span>\n";
            }
            break;
        }
    }
}

if (empty($recent_errors)) {
    echo "<span class='success'>✅ 최근 에러 로그 없음</span>\n";
}

echo "\n=== 진단 완료 ===\n";

?>
    </pre>
</div>

<?php if (!empty($missing_extensions)): ?>
<div class="critical">
    <h2>🔥 긴급 수정 필요</h2>
    <p><strong>누락된 PHP 확장:</strong> <?= implode(', ', $missing_extensions) ?></p>
    <div class="code">
# CentOS/RHEL 서버에서 실행:
sudo yum install <?= implode(' ', array_map(function($ext) { return "php-$ext"; }, $missing_extensions)) ?>

# 설치 후 웹서버 재시작:
sudo systemctl restart httpd
sudo systemctl restart php-fpm
    </div>
</div>
<?php endif; ?>

<div class="section">
    <h2>🚀 즉시 해결 방법</h2>
    
    <h3>1️⃣ 브라우저 강제 새로고침</h3>
    <p><strong>Ctrl+F5</strong> (Windows) 또는 <strong>Cmd+Shift+R</strong> (Mac)</p>
    
    <h3>2️⃣ 다른 방법으로 접근</h3>
    <a href="/lectures?view=list" class="btn">📋 리스트 뷰로 접근</a>
    <a href="/lectures?year=2025&month=7" class="btn">📅 7월 강의 보기</a>
    <a href="/" class="btn">🏠 홈페이지로 이동</a>
    
    <h3>3️⃣ 캐시 완전 정리</h3>
    <form method="POST" style="display: inline;">
        <input type="hidden" name="action" value="emergency_cache_clear">
        <button type="submit" class="btn emergency">🗑️ 응급 캐시 정리</button>
    </form>
</div>

<?php if (isset($lectures) && !empty($lectures)): ?>
<div class="lecture-list">
    <h2>📚 응급 강의 목록 (데이터베이스 직접 조회)</h2>
    <p>메인 시스템에 문제가 있으니 여기서 강의를 확인하세요:</p>
    
    <?php foreach ($lectures as $lecture): ?>
    <div class="lecture-item">
        <h3>🎯 <?= htmlspecialchars($lecture['title']) ?></h3>
        <p>📅 일시: <?= $lecture['start_date'] ?> <?= $lecture['start_time'] ?></p>
        <p>📋 상태: <?= $lecture['status'] ?></p>
        <a href="/lectures/<?= $lecture['id'] ?>" class="btn">상세보기</a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="section">
    <h2>🛠️ 서버 관리자용 명령어</h2>
    <div class="code">
# 웹서버 재시작
sudo systemctl restart httpd

# PHP-FPM 재시작  
sudo systemctl restart php-fpm

# MySQL 재시작
sudo systemctl restart mysqld

# 실시간 에러 로그 확인
tail -f /var/log/httpd/error_log

# PHP 에러 로그 확인
tail -f /var/log/php-fpm/www-error.log
    </div>
</div>

<?php
// POST 요청 처리 (응급 캐시 정리)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'emergency_cache_clear') {
        echo '<div class="section"><h2>🔄 응급 캐시 정리 실행</h2><pre>';
        
        // 세션 정리
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        echo "<span class='success'>✅ 세션 정리 완료</span>\n";
        
        // OpCache 정리
        if (function_exists('opcache_reset')) {
            opcache_reset();
            echo "<span class='success'>✅ OpCache 정리 완료</span>\n";
        }
        
        // 브라우저 캐시 무력화 헤더
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo "<span class='success'>✅ 브라우저 캐시 무력화 완료</span>\n";
        echo "<span class='info'>🔄 5초 후 강의 페이지로 자동 이동합니다...</span>\n";
        
        echo '</pre>';
        echo '<script>setTimeout(function(){ window.location.href="/lectures"; }, 5000);</script>';
        echo '</div>';
    }
}
?>

<div style="text-align: center; margin: 40px 0; padding: 20px; background: #001100; border-radius: 8px;">
    <h2 style="color: #40ff80;">💡 문제가 지속되면</h2>
    <p>이 페이지가 정상 작동한다면 PHP와 데이터베이스는 문제없습니다.</p>
    <p>문제는 <strong>라우팅 시스템</strong> 또는 <strong>특정 컨트롤러</strong>에 있을 가능성이 높습니다.</p>
    
    <a href="/lectures" class="btn" style="font-size: 18px; padding: 15px 30px;">🚀 다시 강의 페이지 접근</a>
</div>

<script>
// 자동 새로고침 (30초마다)
setTimeout(function() {
    var refresh = confirm('30초가 지났습니다. 페이지를 새로고침하여 상태를 다시 확인하시겠습니까?');
    if (refresh) {
        location.reload();
    }
}, 30000);

// 에러 발생 시 자동 대안 제시
window.addEventListener('error', function(e) {
    document.body.insertAdjacentHTML('beforeend', 
        '<div style="position: fixed; top: 10px; right: 10px; background: #ff4040; color: #fff; padding: 15px; border-radius: 8px; z-index: 9999;">' +
        '🚨 JavaScript 오류 감지<br>' +
        '<a href="/lectures?view=list" style="color: #fff;">리스트 뷰로 접근</a>' +
        '</div>'
    );
});
</script>

</body>
</html>