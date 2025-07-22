<?php
/**
 * 🔍 기업 대시보드 500 오류 디버깅
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>🔍 대시보드 디버깅</title>";
echo "<style>body{font-family:monospace;background:#000;color:#0f0;padding:20px;} .error{color:#f00;} .success{color:#0f0;} .warning{color:#fa0;} pre{background:#111;padding:15px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🔍 기업 대시보드 500 오류 디버깅</h1>";

// 경로 설정
define('ROOT_PATH', realpath(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

try {
    // 데이터베이스 연결
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();
    
    echo "<h2>1️⃣ 데이터베이스 연결 테스트</h2>";
    echo "<pre>";
    echo "<span class='success'>✅ 데이터베이스 연결 성공</span>\n";
    echo "</pre>";
    
    // 테이블 존재 확인
    echo "<h2>2️⃣ 필요한 테이블 존재 확인</h2>";
    echo "<pre>";
    
    $tables = ['lectures', 'lecture_registrations', 'registration_statistics', 'users'];
    
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<span class='success'>✅ $table 테이블 존재</span>\n";
        } else {
            echo "<span class='error'>❌ $table 테이블 누락</span>\n";
        }
    }
    
    echo "</pre>";
    
    // 테이블 구조 확인
    echo "<h2>3️⃣ lecture_registrations 테이블 구조 확인</h2>";
    echo "<pre>";
    
    $result = $db->query("DESCRIBE lecture_registrations");
    if ($result) {
        echo "<span class='success'>✅ lecture_registrations 테이블 구조:</span>\n";
        while ($row = $result->fetch_assoc()) {
            echo "  {$row['Field']} - {$row['Type']}\n";
        }
    } else {
        echo "<span class='error'>❌ lecture_registrations 테이블 조회 실패</span>\n";
    }
    
    echo "</pre>";
    
    // 사용자 권한 확인
    echo "<h2>4️⃣ 사용자 세션 및 권한 확인</h2>";
    echo "<pre>";
    
    session_start();
    
    if (isset($_SESSION['user_id'])) {
        echo "<span class='success'>✅ 로그인된 사용자 ID: {$_SESSION['user_id']}</span>\n";
        
        if (isset($_SESSION['user_role'])) {
            echo "<span class='success'>✅ 사용자 권한: {$_SESSION['user_role']}</span>\n";
        } else {
            echo "<span class='warning'>⚠️ 사용자 권한 정보 없음</span>\n";
        }
    } else {
        echo "<span class='error'>❌ 로그인 되지 않음</span>\n";
    }
    
    echo "</pre>";
    
    // 강의 데이터 확인
    echo "<h2>5️⃣ 강의 데이터 확인</h2>";
    echo "<pre>";
    
    $lectureResult = $db->query("SELECT COUNT(*) as count FROM lectures WHERE status = 'published'");
    if ($lectureResult) {
        $lectureCount = $lectureResult->fetch_assoc()['count'];
        echo "<span class='success'>✅ 게시된 강의 수: $lectureCount</span>\n";
    }
    
    $regResult = $db->query("SELECT COUNT(*) as count FROM lecture_registrations");
    if ($regResult) {
        $regCount = $regResult->fetch_assoc()['count'];
        echo "<span class='success'>✅ 총 신청 수: $regCount</span>\n";
    }
    
    echo "</pre>";
    
    // 수정된 쿼리 테스트
    echo "<h2>6️⃣ 수정된 쿼리 테스트</h2>";
    echo "<pre>";
    
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        // registration_statistics 테이블 없이 직접 집계하는 쿼리
        $testQuery = "
            SELECT 
                l.id, l.title, l.start_date, l.start_time, l.end_date, l.end_time,
                l.max_participants, l.current_participants, l.auto_approval,
                l.registration_end_date,
                COUNT(DISTINCT lr.id) as total_applications,
                COUNT(DISTINCT CASE WHEN lr.status = 'pending' THEN lr.id END) as pending_count,
                COUNT(DISTINCT CASE WHEN lr.status = 'approved' THEN lr.id END) as approved_count,
                COUNT(DISTINCT CASE WHEN lr.status = 'rejected' THEN lr.id END) as rejected_count,
                COUNT(DISTINCT CASE WHEN lr.status = 'waiting' THEN lr.id END) as waiting_count
            FROM lectures l
            LEFT JOIN lecture_registrations lr ON l.id = lr.lecture_id
            WHERE l.user_id = ? AND l.status = 'published'
            GROUP BY l.id
            ORDER BY l.start_date DESC, l.created_at DESC
            LIMIT 10
        ";
        
        $stmt = $db->prepare($testQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "<span class='success'>✅ 수정된 쿼리 실행 성공</span>\n";
        echo "결과 수: " . $result->num_rows . "\n";
        
        while ($row = $result->fetch_assoc()) {
            echo "강의: {$row['title']} (신청: {$row['total_applications']})\n";
        }
    }
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>💥 오류 발생</h2>";
    echo "<pre><span class='error'>";
    echo "오류: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . "\n";
    echo "라인: " . $e->getLine() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString();
    echo "</span></pre>";
}

echo "<h2>7️⃣ 해결 방안</h2>";
echo "<div style='color:#fff;padding:15px;background:#222;border-radius:5px;'>";
echo "<h3>🔧 문제 해결 단계:</h3>";
echo "<ol>";
echo "<li><strong>registration_statistics 테이블 생성</strong> 또는 쿼리 수정</li>";
echo "<li><strong>컨트롤러 오류 처리</strong> 개선</li>";
echo "<li><strong>권한 확인 로직</strong> 검증</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>