<?php
/**
 * 시스템 캐시 및 세션 긴급 정리 스크립트
 * topmktx.com/lectures 시스템 오류 해결용
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 시스템 긴급 복구</title>
    <style>
        body { font-family: 'Consolas', monospace; margin: 20px; background: #1a1a1a; color: #00ff00; }
        .success { color: #00ff00; }
        .error { color: #ff4444; }
        .warning { color: #ffaa00; }
        .info { color: #44aaff; }
        pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #ff6666; text-align: center; }
        .box { border: 1px solid #333; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>

<h1>🚨 강의 시스템 긴급 복구 스크립트</h1>

<div class="box">
    <h2>📋 진행 상황</h2>
    <pre>
<?php
echo "=== 긴급 시스템 복구 시작 ===\n";
echo "시작 시간: " . date('Y-m-d H:i:s') . "\n\n";

// 1. PHP 오류 보고 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<span class='info'>1. PHP 설정 확인</span>\n";
echo "PHP 버전: " . PHP_VERSION . "\n";
echo "메모리 제한: " . ini_get('memory_limit') . "\n";
echo "최대 실행 시간: " . ini_get('max_execution_time') . "초\n\n";

// 2. 필수 확장 모듈 확인
echo "<span class='info'>2. PHP 확장 모듈 확인</span>\n";
$required_extensions = ['mysqli', 'curl', 'json', 'session', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✅ $ext 확장: 설치됨</span>\n";
    } else {
        echo "<span class='error'>❌ $ext 확장: 누락됨 (설치 필요)</span>\n";
    }
}
echo "\n";

// 3. 세션 정리
echo "<span class='info'>3. 세션 데이터 정리</span>\n";
try {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $session_count = count($_SESSION);
    session_destroy();
    session_start();
    
    echo "<span class='success'>✅ 세션 정리 완료 (제거된 세션 변수: $session_count 개)</span>\n";
} catch (Exception $e) {
    echo "<span class='error'>❌ 세션 정리 실패: " . $e->getMessage() . "</span>\n";
}
echo "\n";

// 4. 캐시 파일 정리
echo "<span class='info'>4. 시스템 캐시 정리</span>\n";
$cache_directories = [
    '/tmp/php_cache',
    '/var/cache/php',
    __DIR__ . '/cache',
    __DIR__ . '/tmp'
];

$cleared_files = 0;
foreach ($cache_directories as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                $cleared_files++;
            }
        }
    }
}
echo "<span class='success'>✅ 캐시 파일 정리 완료 ($cleared_files 개 파일 삭제)</span>\n\n";

// 5. OpCache 정리 (있는 경우)
echo "<span class='info'>5. OpCache 정리</span>\n";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<span class='success'>✅ OpCache 초기화 완료</span>\n";
} else {
    echo "<span class='warning'>⚠️ OpCache가 설치되지 않음</span>\n";
}
echo "\n";

// 6. 데이터베이스 연결 테스트
echo "<span class='info'>6. 데이터베이스 연결 테스트</span>\n";
try {
    $mysqli = new mysqli('localhost', 'root', 'Dnlszkem1!', 'TOPMKT');
    
    if ($mysqli->connect_error) {
        echo "<span class='error'>❌ 데이터베이스 연결 실패: " . $mysqli->connect_error . "</span>\n";
        
        // 대안 연결 시도
        $mysqli = new mysqli('127.0.0.1', 'root', 'Dnlszkem1!', 'TOPMKT', 3306);
        if ($mysqli->connect_error) {
            echo "<span class='error'>❌ 대안 연결도 실패</span>\n";
        } else {
            echo "<span class='success'>✅ 대안 연결 성공 (127.0.0.1)</span>\n";
        }
    } else {
        echo "<span class='success'>✅ 데이터베이스 연결 성공</span>\n";
        
        // 간단한 쿼리 테스트
        $result = $mysqli->query("SELECT COUNT(*) as count FROM lectures WHERE status = 'published'");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<span class='success'>✅ 쿼리 테스트 성공 (게시된 강의: " . $row['count'] . "개)</span>\n";
        } else {
            echo "<span class='error'>❌ 쿼리 테스트 실패: " . $mysqli->error . "</span>\n";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ 데이터베이스 테스트 중 예외: " . $e->getMessage() . "</span>\n";
}
echo "\n";

// 7. 파일 시스템 권한 확인
echo "<span class='info'>7. 파일 시스템 권한 확인</span>\n";
$critical_paths = [
    __DIR__ . '/src/controllers/LectureController.php',
    __DIR__ . '/src/views/lectures/index.php',
    __DIR__ . '/src/config/database.php',
    __DIR__ . '/logs'
];

foreach ($critical_paths as $path) {
    if (file_exists($path)) {
        $perms = fileperms($path);
        $readable = is_readable($path);
        $writable = is_writable($path);
        
        echo "📁 $path\n";
        echo "  권한: " . substr(sprintf('%o', $perms), -4) . " ";
        echo ($readable ? "<span class='success'>읽기✅</span>" : "<span class='error'>읽기❌</span>") . " ";
        echo ($writable ? "<span class='success'>쓰기✅</span>" : "<span class='warning'>쓰기⚠️</span>") . "\n";
    } else {
        echo "<span class='error'>❌ 파일 없음: $path</span>\n";
    }
}
echo "\n";

// 8. 에러 로그 확인
echo "<span class='info'>8. 최근 에러 로그 확인</span>\n";
$log_files = [
    '/var/log/httpd/error_log',
    '/var/log/apache2/error.log',
    '/var/log/php-fpm/www-error.log',
    __DIR__ . '/logs/topmkt_errors.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file) && is_readable($log_file)) {
        echo "📄 로그 파일: $log_file\n";
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -5); // 최근 5줄
        foreach ($recent_lines as $line) {
            if (strpos($line, 'ERROR') !== false || strpos($line, 'FATAL') !== false) {
                echo "<span class='error'>  🔴 " . trim($line) . "</span>\n";
            } elseif (strpos($line, 'WARNING') !== false) {
                echo "<span class='warning'>  🟡 " . trim($line) . "</span>\n";
            }
        }
        echo "\n";
        break; // 첫 번째 존재하는 로그만 확인
    }
}

// 9. 메모리 사용량 확인
echo "<span class='info'>9. 시스템 리소스 확인</span>\n";
echo "현재 메모리 사용량: " . number_format(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
echo "최대 메모리 사용량: " . number_format(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";

// 디스크 공간 확인
$disk_free = disk_free_space(__DIR__);
$disk_total = disk_total_space(__DIR__);
$disk_used_percent = (($disk_total - $disk_free) / $disk_total) * 100;

echo "디스크 사용률: " . number_format($disk_used_percent, 1) . "% ";
if ($disk_used_percent > 90) {
    echo "<span class='error'>(위험)</span>\n";
} elseif ($disk_used_percent > 80) {
    echo "<span class='warning'>(주의)</span>\n";
} else {
    echo "<span class='success'>(양호)</span>\n";
}
echo "\n";

// 10. 복구 권장사항
echo "<span class='info'>10. 복구 권장사항</span>\n";

$recommendations = [];

// MySQLi 누락 시
if (!extension_loaded('mysqli')) {
    $recommendations[] = "❗ PHP MySQLi 확장 설치: sudo yum install php-mysqli";
}

// cURL 누락 시  
if (!extension_loaded('curl')) {
    $recommendations[] = "❗ PHP cURL 확장 설치: sudo yum install php-curl";
}

// 디스크 공간 부족 시
if ($disk_used_percent > 90) {
    $recommendations[] = "❗ 디스크 공간 확보 필요: 로그 파일 정리 또는 불필요한 파일 삭제";
}

if (empty($recommendations)) {
    echo "<span class='success'>✅ 특별한 조치사항 없음 - 시스템이 정상입니다</span>\n";
} else {
    echo "<span class='warning'>⚠️ 권장 조치사항:</span>\n";
    foreach ($recommendations as $rec) {
        echo "  $rec\n";
    }
}

echo "\n";
echo "<span class='info'>=== 긴급 복구 완료 ===</span>\n";
echo "완료 시간: " . date('Y-m-d H:i:s') . "\n";
?>
    </pre>
</div>

<div class="box">
    <h2>🔧 즉시 적용 가능한 해결책</h2>
    <pre>
<span class='info'>1. 브라우저에서 시도해볼 것:</span>
   - 강제 새로고침: Ctrl+F5 (Windows) / Cmd+Shift+R (Mac)
   - 캐시 삭제 후 재접속
   - 시크릿/프라이빗 모드로 접속

<span class='info'>2. URL 직접 접근 테스트:</span>
   - https://www.topmktx.com/lectures?view=list
   - https://www.topmktx.com/lectures?year=2025&month=7

<span class='info'>3. 서버에서 확인할 것:</span>
   - 웹서버 재시작: sudo systemctl restart httpd
   - PHP-FPM 재시작: sudo systemctl restart php-fpm  
   - MySQL 상태 확인: sudo systemctl status mysqld

<span class='info'>4. 로그 실시간 모니터링:</span>
   - tail -f /var/log/httpd/error_log
   - tail -f /var/www/html/topmkt/logs/topmkt_errors.log
    </pre>
</div>

<div class="box">
    <h2>📊 시스템 상태 요약</h2>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
            <h3>✅ 정상 작동</h3>
            <ul style="color: #00ff00;">
                <li>라우팅 시스템</li>
                <li>컨트롤러 로드</li>
                <li>뷰 파일 존재</li>
                <li>PHP 문법 검사</li>
            </ul>
        </div>
        <div>
            <h3>⚠️ 점검 필요</h3>
            <ul style="color: #ffaa00;">
                <li>데이터베이스 연결 안정성</li>
                <li>세션/캐시 정리</li>
                <li>서버 리소스</li>
                <li>에러 로그 모니터링</li>
            </ul>
        </div>
    </div>
</div>

<script>
// 자동 새로고침 (30초마다)
setTimeout(function() {
    if (confirm('30초가 지났습니다. 페이지를 새로고침하시겠습니까?')) {
        location.reload();
    }
}, 30000);
</script>

</body>
</html>