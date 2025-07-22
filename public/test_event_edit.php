<?php
/**
 * 행사 수정 모드 테스트
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/controllers/EventController.php';

echo "<h1>행사 수정 모드 테스트</h1>";

// 테스트용 로그인 상태 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['is_logged_in'] = true;

echo "<p>테스트 사용자 설정: ID=4, 역할=ROLE_USER</p>";

// GET 파라미터 설정
$_GET['id'] = '190';

try {
    // 컨트롤러 직접 실행
    $controller = new EventController();
    
    echo "<h2>컨트롤러 실행 결과</h2>";
    
    // 출력 캡처
    ob_start();
    $controller->create();
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
    
    if (strpos($output, 'event-form') !== false) {
        echo "<p style='color: green;'>✅ 행사 폼 확인</p>";
    }
    
    if (strpos($output, '행사 수정') !== false) {
        echo "<p style='color: green;'>✅ 수정 모드 제목 확인</p>";
    }
    
    if (strpos($output, 'value=') !== false) {
        echo "<p style='color: green;'>✅ 폼 필드 값 확인</p>";
    }
    
    // 중요 에러 체크
    if (strpos($output, 'Fatal error') !== false) {
        echo "<p style='color: red;'>❌ Fatal error 발생</p>";
    }
    
    if (strpos($output, 'Warning') !== false) {
        echo "<p style='color: orange;'>⚠️ Warning 발생</p>";
    }
    
    echo "<h3>실제 출력 (처음 1000자)</h3>";
    echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "...</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>