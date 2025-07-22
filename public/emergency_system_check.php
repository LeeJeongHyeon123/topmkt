<?php
/**
 * 🚨 긴급 시스템 체크 - 단순 버전
 * 복잡한 의존성 없이 기본 상태만 확인
 */

// 에러 출력 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTML 헤더
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🚨 긴급 시스템 체크</title>
    <style>
        body { font-family: monospace; background: #000; color: #0f0; padding: 20px; margin: 0; }
        .ok { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #fa0; }
        pre { background: #111; padding: 10px; border-radius: 5px; }
        h1 { color: #f60; text-align: center; }
    </style>
</head>
<body>

<h1>🚨 탑마케팅 긴급 시스템 체크</h1>

<pre>
<?php
echo "=== 긴급 진단 시작 ===\n";
echo "시간: " . date('Y-m-d H:i:s') . "\n\n";

// 1. PHP 기본 정보
echo "<span class='ok'>✅ PHP 버전: " . PHP_VERSION . "</span>\n";
echo "<span class='ok'>✅ 서버: " . $_SERVER['SERVER_SOFTWARE'] . "</span>\n";
echo "<span class='ok'>✅ 현재 경로: " . __DIR__ . "</span>\n\n";

// 2. 필수 확장 확인
echo "=== PHP 확장 모듈 확인 ===\n";
$extensions = ['mysqli', 'curl', 'json', 'session'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='ok'>✅ $ext</span>\n";
    } else {
        echo "<span class='error'>❌ $ext (누락)</span>\n";
    }
}
echo "\n";

// 3. 파일 시스템 확인
echo "=== 파일 시스템 확인 ===\n";
$files = [
    '../src/controllers/LectureController.php',
    '../src/config/database.php', 
    '../src/views/lectures/index.php',
    '../index.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<span class='ok'>✅ $file</span>\n";
    } else {
        echo "<span class='error'>❌ $file (누락)</span>\n";
    }
}
echo "\n";

// 4. 데이터베이스 연결 테스트
echo "=== 데이터베이스 연결 테스트 ===\n";
try {
    // 여러 연결 방법 시도
    $connections = [
        ['localhost', 'root', 'Dnlszkem1!', 'TOPMKT'],
        ['127.0.0.1', 'root', 'Dnlszkem1!', 'TOPMKT'],
        ['localhost', 'root', '', 'TOPMKT']
    ];
    
    $connected = false;
    foreach ($connections as $i => $conn) {
        try {
            $mysqli = new mysqli($conn[0], $conn[1], $conn[2], $conn[3]);
            if (!$mysqli->connect_error) {
                echo "<span class='ok'>✅ 연결 성공 (방법 " . ($i + 1) . "): {$conn[0]}</span>\n";
                
                // 테이블 확인
                $result = $mysqli->query("SHOW TABLES LIKE 'lectures'");
                if ($result && $result->num_rows > 0) {
                    echo "<span class='ok'>✅ lectures 테이블 존재</span>\n";
                    
                    $result = $mysqli->query("SELECT COUNT(*) as count FROM lectures");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        echo "<span class='ok'>✅ 강의 데이터: {$row['count']}개</span>\n";
                    }
                } else {
                    echo "<span class='error'>❌ lectures 테이블 없음</span>\n";
                }
                
                $mysqli->close();
                $connected = true;
                break;
            }
        } catch (Exception $e) {
            // 다음 연결 시도
        }
    }
    
    if (!$connected) {
        echo "<span class='error'>❌ 모든 데이터베이스 연결 실패</span>\n";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ 데이터베이스 오류: " . $e->getMessage() . "</span>\n";
}
echo "\n";

// 5. 웹 경로 테스트
echo "=== 웹 경로 테스트 ===\n";
$base_url = 'https://' . $_SERVER['HTTP_HOST'];
echo "기본 URL: $base_url\n";

$test_urls = [
    '/' => '홈페이지',
    '/lectures' => '강의 목록',
    '/community' => '커뮤니티'
];

foreach ($test_urls as $path => $name) {
    $url = $base_url . $path;
    echo "📋 $name: $url\n";
}
echo "\n";

// 6. 서버 리소스
echo "=== 서버 리소스 ===\n";
echo "메모리 사용량: " . number_format(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
echo "메모리 제한: " . ini_get('memory_limit') . "\n";
echo "최대 실행 시간: " . ini_get('max_execution_time') . "초\n";

$load = sys_getloadavg();
if ($load) {
    echo "시스템 부하: " . number_format($load[0], 2) . "\n";
}
echo "\n";

// 7. 응급 처치 방법
echo "=== 🛠️ 응급 처치 방법 ===\n";

if (!extension_loaded('mysqli')) {
    echo "<span class='error'>🔥 긴급: PHP MySQLi 확장 설치 필요</span>\n";
    echo "   명령어: sudo yum install php-mysqli && sudo systemctl restart httpd\n";
}

echo "<span class='ok'>💡 브라우저 해결 방법:</span>\n";
echo "   1. 강제 새로고침: Ctrl+F5\n";
echo "   2. 캐시 삭제 후 재접속\n";
echo "   3. 다른 브라우저나 시크릿 모드 시도\n\n";

echo "<span class='ok'>🔧 서버 재시작:</span>\n";
echo "   sudo systemctl restart httpd\n";
echo "   sudo systemctl restart mysqld\n\n";

echo "<span class='ok'>🔍 강의 페이지 직접 접근:</span>\n";
echo "   $base_url/lectures?view=list\n";
echo "   $base_url/lectures?year=2025&month=7\n\n";

// 8. 실시간 상태
echo "=== 🔴 실시간 상태 ===\n";
echo "이 페이지가 보인다면 PHP는 정상 작동 중\n";
echo "문제는 라우팅 또는 특정 컨트롤러에 있을 가능성 높음\n\n";

echo "=== 진단 완료 ===\n";
echo "완료 시간: " . date('Y-m-d H:i:s') . "\n";
?>
</pre>

<div style="background: #222; padding: 20px; margin: 20px 0; border-radius: 5px;">
    <h2 style="color: #f60;">🎯 즉시 시도할 방법</h2>
    <div style="color: #fff;">
        <h3 style="color: #0f0;">1. 브라우저에서 직접 접근:</h3>
        <p><a href="/lectures" style="color: #4af;">https://www.topmktx.com/lectures</a></p>
        <p><a href="/lectures?view=list" style="color: #4af;">https://www.topmktx.com/lectures?view=list</a></p>
        
        <h3 style="color: #0f0;">2. 강제 새로고침:</h3>
        <p>Ctrl+F5 (Windows) 또는 Cmd+Shift+R (Mac)</p>
        
        <h3 style="color: #0f0;">3. 다른 기기/브라우저:</h3>
        <p>스마트폰, 다른 컴퓨터, 시크릿 모드로 테스트</p>
    </div>
</div>

<script>
// 10초마다 자동 새로고침 옵션
setTimeout(function() {
    var refresh = confirm('10초가 지났습니다. 상태를 다시 확인하시겠습니까?');
    if (refresh) {
        location.reload();
    }
}, 10000);
</script>

</body>
</html>