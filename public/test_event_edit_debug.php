<?php
/**
 * 행사 수정 모드 디버깅 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>행사 수정 모드 디버깅 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

echo "<p>테스트 사용자 설정: ID=4, 역할=ROLE_USER</p>";

// GET 파라미터 설정
$_GET['id'] = '190';

try {
    echo "<h2>1. EventController 로드 시도</h2>";
    require_once SRC_PATH . '/controllers/EventController.php';
    echo "<p style='color: green;'>✅ EventController 로드 성공</p>";
    
    echo "<h2>2. EventController 인스턴스 생성</h2>";
    $controller = new EventController();
    echo "<p style='color: green;'>✅ EventController 인스턴스 생성 성공</p>";
    
    echo "<h2>3. create() 메서드 실행</h2>";
    echo "<p>현재 GET id: " . ($_GET['id'] ?? 'null') . "</p>";
    
    // 메서드 실행
    $controller->create();
    
    echo "<p style='color: green;'>✅ create() 메서드 실행 완료</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ 치명적 오류 발생: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>