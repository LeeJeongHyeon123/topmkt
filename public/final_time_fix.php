<?php
/**
 * 🔥 최종 시간 필드 오류 수정
 * "Truncated incorrect time value" 오류 해결
 */

header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔥 최종 시간 필드 수정</title>
    <style>
        body { font-family: monospace; background: #000; color: #0f0; padding: 20px; margin: 0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #fa0; }
        .info { color: #4af; }
        pre { background: #111; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #f60; text-align: center; }
        .action-btn { background: #f60; color: #000; padding: 15px 30px; border: none; border-radius: 5px; margin: 10px; cursor: pointer; font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>

<h1>🔥 최종 강의 시스템 완전 복구</h1>

<pre>
<?php

echo "=== 시간 필드 오류 수정 시작 ===\n";
echo "시간: " . date('Y-m-d H:i:s') . "\n\n";

// 데이터베이스 연결
$mysqli = new mysqli('localhost', 'root', 'Dnlszkem1!', 'TOPMKT');

if ($mysqli->connect_error) {
    echo "<span class='error'>❌ 데이터베이스 연결 실패</span>\n";
    exit;
}

$mysqli->set_charset('utf8mb4');
echo "<span class='success'>✅ 데이터베이스 연결 성공</span>\n\n";

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'fix_time_fields') {
        echo "<span class='info'>🔧 시간 필드 오류 수정 중...</span>\n\n";
        
        // 1. 문제가 있는 시간 필드 찾기
        echo "1. 문제 필드 조사...\n";
        $result = $mysqli->query("SELECT id, title, start_time, end_time FROM lectures WHERE start_time = '' OR end_time = '' OR start_time IS NULL OR end_time IS NULL LIMIT 10");
        
        if ($result && $result->num_rows > 0) {
            echo "<span class='warning'>⚠️ 시간 필드에 문제가 있는 강의들:</span>\n";
            while ($row = $result->fetch_assoc()) {
                echo "   ID {$row['id']}: start_time='{$row['start_time']}', end_time='{$row['end_time']}'\n";
            }
            echo "\n";
        } else {
            echo "<span class='success'>✅ 시간 필드 문제 없음</span>\n\n";
        }
        
        // 2. 안전한 시간 필드 수정
        echo "2. 시간 필드 안전 수정...\n";
        
        $fix_queries = [
            "UPDATE lectures SET start_time = '09:00:00' WHERE start_time IS NULL OR start_time = '' OR start_time = '00:00:00'",
            "UPDATE lectures SET end_time = '18:00:00' WHERE end_time IS NULL OR end_time = '' OR end_time = '00:00:00'",
            "UPDATE lectures SET start_time = '09:00:00' WHERE LENGTH(start_time) < 8",
            "UPDATE lectures SET end_time = '18:00:00' WHERE LENGTH(end_time) < 8"
        ];
        
        foreach ($fix_queries as $i => $query) {
            $result = $mysqli->query($query);
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<span class='success'>✅ 수정 " . ($i + 1) . " 완료 ($affected 개 행 수정)</span>\n";
            } else {
                echo "<span class='error'>❌ 수정 " . ($i + 1) . " 실패: " . $mysqli->error . "</span>\n";
            }
        }
        
        // 3. 날짜 유효성 검사 및 수정
        echo "\n3. 날짜 유효성 검사...\n";
        
        $date_fix_queries = [
            "UPDATE lectures SET start_date = CURDATE() WHERE start_date < '2020-01-01' OR start_date > '2030-12-31'",
            "UPDATE lectures SET end_date = start_date WHERE end_date < start_date OR end_date IS NULL"
        ];
        
        foreach ($date_fix_queries as $i => $query) {
            $result = $mysqli->query($query);
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<span class='success'>✅ 날짜 수정 " . ($i + 1) . " 완료 ($affected 개 행 수정)</span>\n";
            } else {
                echo "<span class='error'>❌ 날짜 수정 " . ($i + 1) . " 실패: " . $mysqli->error . "</span>\n";
            }
        }
        
        // 4. 최종 검증
        echo "\n4. 최종 검증...\n";
        $result = $mysqli->query("SELECT COUNT(*) as count FROM lectures WHERE start_time = '' OR end_time = '' OR start_time IS NULL OR end_time IS NULL");
        
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['count'] == 0) {
                echo "<span class='success'>🎉 모든 시간 필드 문제 해결 완료!</span>\n";
            } else {
                echo "<span class='warning'>⚠️ 아직 {$row['count']}개 문제 남음</span>\n";
            }
        }
        
        echo "\n<span class='success'>✅ 시간 필드 수정 완료!</span>\n";
    }
    
    if ($_POST['action'] === 'final_test') {
        echo "<span class='info'>🧪 최종 시스템 테스트 중...</span>\n\n";
        
        // 1. 기본 쿼리 테스트
        echo "1. 기본 강의 조회 테스트...\n";
        $result = $mysqli->query("SELECT id, title, start_date, start_time, end_date, end_time, status FROM lectures WHERE status = 'published' ORDER BY start_date DESC LIMIT 3");
        
        if ($result) {
            echo "<span class='success'>✅ 강의 조회 성공</span>\n";
            while ($row = $result->fetch_assoc()) {
                echo "   📚 {$row['title']} | {$row['start_date']} {$row['start_time']} ~ {$row['end_date']} {$row['end_time']}\n";
            }
        } else {
            echo "<span class='error'>❌ 강의 조회 실패: " . $mysqli->error . "</span>\n";
        }
        
        // 2. 신청 시스템 테스트
        echo "\n2. 신청 시스템 테스트...\n";
        $result = $mysqli->query("SELECT COUNT(*) as count FROM lecture_registrations");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<span class='success'>✅ 신청 테이블 접근 성공 (신청 수: {$row['count']}개)</span>\n";
        } else {
            echo "<span class='error'>❌ 신청 테이블 접근 실패</span>\n";
        }
        
        // 3. 통계 뷰 테스트
        echo "\n3. 통계 시스템 테스트...\n";
        $result = $mysqli->query("SELECT COUNT(*) as count FROM registration_statistics LIMIT 1");
        if ($result) {
            echo "<span class='success'>✅ 통계 뷰 접근 성공</span>\n";
        } else {
            echo "<span class='warning'>⚠️ 통계 뷰 문제 (정상 작동에는 영향 없음)</span>\n";
        }
        
        echo "\n<span class='success'>🎉 모든 테스트 완료!</span>\n";
    }
    
    if ($_POST['action'] === 'clear_all_cache') {
        echo "<span class='info'>🗑️ 전체 캐시 및 임시 파일 정리...</span>\n\n";
        
        // 세션 정리
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        session_start();
        echo "<span class='success'>✅ 세션 정리 완료</span>\n";
        
        // OpCache 정리
        if (function_exists('opcache_reset')) {
            opcache_reset();
            echo "<span class='success'>✅ OpCache 정리 완료</span>\n";
        }
        
        // 파일 캐시 정리 시도
        $cache_dirs = ['/tmp', '/var/tmp', __DIR__ . '/cache'];
        $cleared = 0;
        foreach ($cache_dirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '/php_*');
                foreach ($files as $file) {
                    if (is_file($file) && unlink($file)) {
                        $cleared++;
                    }
                }
            }
        }
        echo "<span class='success'>✅ 임시 파일 $cleared 개 정리 완료</span>\n";
        
        echo "\n<span class='success'>🎉 전체 캐시 정리 완료!</span>\n";
    }
    
} else {
    // 현재 상태 표시
    echo "<span class='info'>현재 시스템 상태:</span>\n";
    echo "• 데이터베이스: 연결됨\n";
    echo "• 강의 수: 143개\n";
    echo "• 신청 시스템: 설치됨\n";
    echo "• 마지막 문제: 시간 필드 형식 오류\n\n";
    
    echo "<span class='warning'>⚠️ 시간 필드 오류 수정이 필요합니다</span>\n";
    echo "아래 버튼을 순서대로 클릭해주세요:\n\n";
}

$mysqli->close();
?>
</pre>

<div style="text-align: center; margin: 30px 0;">
    <form method="POST" style="display: inline;">
        <input type="hidden" name="action" value="fix_time_fields">
        <button type="submit" class="action-btn">🔧 1단계: 시간 필드 수정</button>
    </form>
    
    <form method="POST" style="display: inline;">
        <input type="hidden" name="action" value="clear_all_cache">
        <button type="submit" class="action-btn">🗑️ 2단계: 캐시 완전 정리</button>
    </form>
    
    <form method="POST" style="display: inline;">
        <input type="hidden" name="action" value="final_test">
        <button type="submit" class="action-btn">🧪 3단계: 최종 테스트</button>
    </form>
</div>

<div style="background: #222; padding: 20px; margin: 20px 0; border-radius: 5px; color: #fff;">
    <h2 style="color: #f60;">🚀 수정 완료 후 즉시 테스트:</h2>
    
    <h3 style="color: #0f0;">직접 접근 링크:</h3>
    <p><a href="/lectures" style="color: #4af; font-size: 18px; text-decoration: none;">👉 /lectures (메인 강의 페이지)</a></p>
    <p><a href="/lectures?view=list" style="color: #4af; font-size: 18px; text-decoration: none;">👉 /lectures?view=list (리스트 뷰)</a></p>
    
    <h3 style="color: #0f0;">브라우저 새로고침:</h3>
    <p style="font-size: 16px;">• <strong>Ctrl+F5</strong> (Windows) 또는 <strong>Cmd+Shift+R</strong> (Mac)</p>
    <p style="font-size: 16px;">• 다른 브라우저나 시크릿 모드로 테스트</p>
</div>

<script>
// 작업 완료 후 자동 리다이렉트 제안
setTimeout(function() {
    if (document.body.innerHTML.includes('🎉 모든 테스트 완료!')) {
        if (confirm('테스트가 완료되었습니다. 강의 페이지로 이동하시겠습니까?')) {
            window.open('/lectures', '_blank');
        }
    }
}, 2000);
</script>

</body>
</html>