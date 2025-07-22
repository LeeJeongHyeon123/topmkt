<?php
/**
 * 최종 신청관리 대시보드 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/controllers/RegistrationDashboardController.php';

echo "<h1>최종 신청관리 대시보드 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

echo "<p>테스트 사용자 설정: ID=4, 역할=ROLE_USER</p>";

try {
    // 컨트롤러 직접 실행
    $controller = new RegistrationDashboardController();
    
    echo "<h2>컨트롤러 실행 결과</h2>";
    
    // 출력 캡처
    ob_start();
    $controller->index();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<p style='color: green;'>✅ 컨트롤러 실행 성공</p>";
    echo "<p>출력 길이: " . number_format(strlen($output)) . " 바이트</p>";
    
    // 출력 내용 분석
    if (strpos($output, 'error') !== false) {
        echo "<p style='color: red;'>⚠️ 출력에 'error' 문자열 포함</p>";
    }
    
    if (strpos($output, 'Exception') !== false) {
        echo "<p style='color: red;'>⚠️ 출력에 'Exception' 문자열 포함</p>";
    }
    
    if (strpos($output, 'dashboard-container') !== false) {
        echo "<p style='color: green;'>✅ 대시보드 컨테이너 확인</p>";
    }
    
    if (strpos($output, 'stat-card') !== false) {
        echo "<p style='color: green;'>✅ 통계 카드 확인</p>";
    }
    
    if (strpos($output, '탑마케팅') !== false) {
        echo "<p style='color: green;'>✅ 제목 확인</p>";
    }
    
    // 중요 에러 체크
    if (strpos($output, 'Fatal error') !== false) {
        echo "<p style='color: red;'>❌ Fatal error 발생</p>";
    }
    
    if (strpos($output, 'Warning') !== false) {
        echo "<p style='color: orange;'>⚠️ Warning 발생</p>";
    }
    
    echo "<h3>실제 대시보드 출력</h3>";
    echo $output;
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h2>데이터베이스 상태 확인</h2>";

try {
    $db = Database::getInstance();
    
    // 사용자 4의 강의 수
    $lectureCount = $db->fetch("SELECT COUNT(*) as count FROM lectures WHERE user_id = 4 AND status = 'published'");
    echo "<p>사용자 4의 강의 수: {$lectureCount['count']}</p>";
    
    // 사용자 4의 행사 수
    $eventCount = $db->fetch("SELECT COUNT(*) as count FROM lectures WHERE user_id = 4 AND status = 'published' AND content_type = 'event'");
    echo "<p>사용자 4의 행사 수: {$eventCount['count']}</p>";
    
    // 전체 신청 수
    $registrationCount = $db->fetch("SELECT COUNT(*) as count FROM lecture_registrations lr JOIN lectures l ON lr.lecture_id = l.id WHERE l.user_id = 4");
    echo "<p>사용자 4의 전체 신청 수: {$registrationCount['count']}</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>데이터베이스 확인 실패: " . $e->getMessage() . "</p>";
}
?>