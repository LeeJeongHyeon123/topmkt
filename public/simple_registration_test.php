<?php
/**
 * 간단한 신청관리 테스트
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/controllers/RegistrationDashboardController.php';

echo "<h1>간단한 신청관리 테스트</h1>";

// 데이터베이스 연결 테스트
try {
    $db = Database::getInstance();
    $result = $db->fetch("SELECT COUNT(*) as count FROM lecture_registrations");
    echo "<p style='color: green;'>✅ 데이터베이스 연결 성공 - 신청 개수: {$result['count']}</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 데이터베이스 연결 실패: " . $e->getMessage() . "</p>";
    exit;
}

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

echo "<h2>로그인 상태 설정</h2>";
echo "<p>사용자 ID: 4</p>";
echo "<p>사용자 역할: ROLE_USER</p>";

// RegistrationDashboardController 테스트
try {
    $controller = new RegistrationDashboardController();
    echo "<p style='color: green;'>✅ RegistrationDashboardController 생성 성공</p>";
    
    // index 메서드 테스트
    if (method_exists($controller, 'index')) {
        echo "<p style='color: green;'>✅ index 메서드 존재</p>";
        
        // 출력 캡처하여 실행 테스트
        ob_start();
        try {
            $controller->index();
            $output = ob_get_contents();
            echo "<p style='color: green;'>✅ index 메서드 실행 성공</p>";
            echo "<details><summary>출력 결과 (처음 2000자)</summary>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 2000)) . "</pre>";
            echo "</details>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ index 메서드 실행 실패: " . $e->getMessage() . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        ob_end_clean();
    } else {
        echo "<p style='color: red;'>❌ index 메서드 없음</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ RegistrationDashboardController 생성 실패: " . $e->getMessage() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// 사용자 4의 강의 개수 확인
echo "<h2>사용자 4의 강의 개수 확인</h2>";
try {
    $lectureCount = $db->fetch("SELECT COUNT(*) as count FROM lectures WHERE user_id = 4 AND status = 'published'");
    echo "<p>사용자 4의 강의 개수: {$lectureCount['count']}</p>";
    
    $eventCount = $db->fetch("SELECT COUNT(*) as count FROM lectures WHERE user_id = 4 AND status = 'published' AND content_type = 'event'");
    echo "<p>사용자 4의 행사 개수: {$eventCount['count']}</p>";
    
    // 최근 강의 몇 개 조회
    $recentLectures = $db->fetchAll("SELECT id, title, content_type, start_date FROM lectures WHERE user_id = 4 AND status = 'published' ORDER BY created_at DESC LIMIT 5");
    echo "<h3>최근 강의/행사 5개</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>제목</th><th>시작일</th></tr>";
    foreach ($recentLectures as $lecture) {
        echo "<tr>";
        echo "<td>{$lecture['id']}</td>";
        echo "<td>" . htmlspecialchars($lecture['title']) . "</td>";
        echo "<td>{$lecture['start_date']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 강의 조회 실패: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/registrations'>실제 신청관리 페이지로 이동</a></p>";
echo "<p><a href='/'>홈으로 이동</a></p>";
?>