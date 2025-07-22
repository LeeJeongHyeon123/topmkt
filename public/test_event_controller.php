<?php
/**
 * EventController 실제 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>EventController 실제 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

echo "<p>테스트 사용자: ID=4, 역할=ROLE_USER</p>";

// GET 파라미터 설정
$_GET['id'] = '190';

try {
    echo "<h2>1. 기본 설정 파일 로드</h2>";
    require_once CONFIG_PATH . '/config.php';
    echo "<p style='color: green;'>✅ config.php 로드 성공</p>";
    
    echo "<h2>2. 데이터베이스 설정 로드</h2>";
    require_once CONFIG_PATH . '/database.php';
    echo "<p style='color: green;'>✅ database.php 로드 성공</p>";
    
    echo "<h2>3. 데이터베이스 연결 테스트</h2>";
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Database 인스턴스 생성 성공</p>";
    
    echo "<h2>4. EventController 로드</h2>";
    require_once SRC_PATH . '/controllers/EventController.php';
    echo "<p style='color: green;'>✅ EventController 로드 성공</p>";
    
    echo "<h2>5. EventController 인스턴스 생성</h2>";
    $controller = new EventController();
    echo "<p style='color: green;'>✅ EventController 인스턴스 생성 성공</p>";
    
    echo "<h2>6. create() 메서드 실행</h2>";
    ob_start();
    $controller->create();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<p style='color: green;'>✅ create() 메서드 실행 성공</p>";
    echo "<p>출력 길이: " . strlen($output) . " 바이트</p>";
    
    if (strpos($output, '행사 수정') !== false) {
        echo "<p style='color: green;'>✅ 수정 모드 제목 확인됨</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Fatal Error: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>7. 실제 웹 접근 시뮬레이션</h2>";
echo "<p>실제 URL: <a href='/events/create?id=190' target='_blank'>/events/create?id=190</a></p>";
?>