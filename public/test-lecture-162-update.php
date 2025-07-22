<?php
/**
 * 강의 162번 업데이트 500 에러 테스트
 */

// 에러 출력 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 세션 시작
session_start();

echo "<h1>강의 162번 업데이트 500 에러 테스트</h1>";

try {
    // 1. 필수 파일 로드
    echo "<h2>1. 필수 파일 로드 테스트</h2>";
    
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    
    require_once SRC_PATH . '/config/config.php';
    echo "<p>✅ config.php 로드 완료</p>";
    
    require_once SRC_PATH . '/config/database.php';
    echo "<p>✅ database.php 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/WebLogger.php';
    echo "<p>✅ WebLogger 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    echo "<p>✅ ResponseHelper 로드 완료</p>";
    
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    echo "<p>✅ AuthMiddleware 로드 완료</p>";
    
    require_once SRC_PATH . '/middleware/CorporateMiddleware.php';
    echo "<p>✅ CorporateMiddleware 로드 완료</p>";
    
    require_once SRC_PATH . '/controllers/LectureController.php';
    echo "<p>✅ LectureController 로드 완료</p>";
    
    // 2. 로깅 시스템 초기화
    echo "<h2>2. 로깅 시스템 초기화</h2>";
    WebLogger::init();
    echo "<p>✅ 로깅 시스템 초기화 완료</p>";
    
    // 3. 데이터베이스 연결 테스트
    echo "<h2>3. 데이터베이스 연결 테스트</h2>";
    $db = Database::getInstance();
    $testQuery = $db->fetchOne("SELECT 1 as test");
    if ($testQuery && $testQuery['test'] == 1) {
        echo "<p>✅ 데이터베이스 연결 성공</p>";
    } else {
        echo "<p>❌ 데이터베이스 연결 실패</p>";
    }
    
    // 4. 강의 162번 존재 확인
    echo "<h2>4. 강의 162번 존재 확인</h2>";
    $lecture = $db->fetch("SELECT * FROM lectures WHERE id = 162");
    if ($lecture) {
        echo "<p>✅ 강의 162번 존재함</p>";
        echo "<p>제목: " . htmlspecialchars($lecture['title']) . "</p>";
        echo "<p>소유자 ID: " . $lecture['user_id'] . "</p>";
    } else {
        echo "<p>❌ 강의 162번 없음</p>";
    }
    
    // 5. LectureController 인스턴스 생성 테스트
    echo "<h2>5. LectureController 인스턴스 생성 테스트</h2>";
    $controller = new LectureController();
    echo "<p>✅ LectureController 인스턴스 생성 성공</p>";
    
    // 6. 실제 update 메서드 호출 시뮬레이션
    echo "<h2>6. Update 메서드 시뮬레이션</h2>";
    
    // 가짜 로그인 상태 설정 (테스트용)
    $_SESSION['user_id'] = $lecture['user_id']; // 강의 소유자로 설정
    $_SESSION['user_role'] = 'user';
    $_SESSION['is_authenticated'] = true;
    
    // CSRF 토큰 생성
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    echo "<p>✅ 테스트용 로그인 상태 설정</p>";
    echo "<p>현재 사용자 ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>CSRF 토큰: " . substr($_SESSION['csrf_token'], 0, 20) . "...</p>";
    
    // 7. 권한 검증 테스트
    echo "<h2>7. 권한 검증 테스트</h2>";
    
    // AuthMiddleware 테스트
    if (AuthMiddleware::isLoggedIn()) {
        echo "<p>✅ 로그인 상태 확인됨</p>";
    } else {
        echo "<p>❌ 로그인 상태 확인 실패</p>";
    }
    
    // 8. CorporateMiddleware 테스트
    echo "<h2>8. CorporateMiddleware 테스트</h2>";
    $permission = CorporateMiddleware::checkLectureEventPermission();
    if ($permission['hasPermission']) {
        echo "<p>✅ 기업회원 권한 있음</p>";
    } else {
        echo "<p>❌ 기업회원 권한 없음: " . htmlspecialchars($permission['message']) . "</p>";
    }
    
    // 9. 실제 500 에러 원인 추적
    echo "<h2>9. 실제 Update 메서드 호출 테스트</h2>";
    
    // 가짜 POST 데이터 설정
    $_POST = [
        'csrf_token' => $_SESSION['csrf_token'],
        'title' => 'Test Update',
        'description' => 'Test Description',
        'start_date' => '2025-07-15',
        'end_date' => '2025-07-15',
        'start_time' => '14:00',
        'end_time' => '16:00',
        'location_type' => 'offline',
        'venue_name' => 'Test Venue',
        'instructor_name' => 'Test Instructor',
        'max_participants' => 20,
        'registration_fee' => 0,
        'category' => 'marketing'
    ];
    
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    
    echo "<p>✅ 테스트 데이터 설정 완료</p>";
    
    // 실제 update 메서드 호출
    echo "<p>🔄 update(162) 메서드 호출 중...</p>";
    
    ob_start();
    try {
        $controller->update(162);
        $output = ob_get_clean();
        echo "<p>✅ update 메서드 실행 완료</p>";
        echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 4px;'>";
        echo "<strong>출력 결과:</strong><br>";
        echo htmlspecialchars($output);
        echo "</div>";
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p>❌ update 메서드 실행 중 오류 발생</p>";
        echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 치명적 오류 발생</h2>";
    echo "<p>오류: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>파일: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ddd; }
</style>