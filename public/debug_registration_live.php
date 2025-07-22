<?php
/**
 * 실시간 강의 신청 오류 디버깅
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 메모리 한계 늘리기
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

// 모든 PHP 오류 표시
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 세션 시작
session_start();

// 에러 핸들러 설정
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "PHP 오류: [$errno] $errstr in $errfile on line $errline<br>";
});

try {
    echo "<h1>🔧 실시간 강의 신청 오류 디버깅</h1>";
    
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    
    echo "✅ 시스템 로드 완료<br>";
    
    // 데이터베이스 연결
    $db = Database::getInstance();
    echo "✅ 데이터베이스 연결 성공<br>";
    
    // 로그인 상태 확인
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "로그인 상태: " . ($isLoggedIn ? "✅ 로그인됨" : "❌ 로그인 안됨") . "<br>";
    
    if (!$isLoggedIn) {
        echo "❌ 로그인이 필요합니다.<br>";
        exit;
    }
    
    $userId = AuthMiddleware::getCurrentUserId();
    echo "사용자 ID: $userId<br>";
    
    // CSRF 토큰 생성
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    echo "CSRF 토큰: 생성됨<br>";
    
    echo "<h2>🧪 직접 강의 신청 테스트</h2>";
    
    // POST 데이터 설정
    $_POST = [
        'csrf_token' => $_SESSION['csrf_token'],
        'participant_name' => '이정현',
        'participant_email' => '2jeonghyeon@naver.com',
        'participant_phone' => '010-26591346',
        'company_name' => '윈카드',
        'position' => '대표이사',
        'motivation' => '네트워킹 마케팅 학습',
        'special_requests' => '',
        'how_did_you_know' => 'website'
    ];
    
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    
    echo "POST 데이터 준비 완료<br>";
    
    // RegistrationController 직접 테스트
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    
    echo "<h3>🚀 RegistrationController 직접 호출</h3>";
    
    try {
        $controller = new RegistrationController();
        echo "✅ 컨트롤러 인스턴스 생성 성공<br>";
        
        // 출력 버퍼링으로 JSON 응답 캡처
        ob_start();
        $controller->createRegistration(167);
        $jsonResponse = ob_get_clean();
        
        echo "<h3>📤 JSON 응답:</h3>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars($jsonResponse);
        echo "</pre>";
        
        // JSON 파싱 시도
        $responseData = json_decode($jsonResponse, true);
        if ($responseData) {
            echo "<h3>📋 파싱된 응답:</h3>";
            echo "<pre>" . print_r($responseData, true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "❌ 컨트롤러 실행 오류:<br>";
        echo "메시지: " . $e->getMessage() . "<br>";
        echo "파일: " . $e->getFile() . "<br>";
        echo "라인: " . $e->getLine() . "<br>";
        echo "<h4>스택 추적:</h4>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } catch (Error $e) {
        echo "❌ Fatal Error:<br>";
        echo "메시지: " . $e->getMessage() . "<br>";
        echo "파일: " . $e->getFile() . "<br>";
        echo "라인: " . $e->getLine() . "<br>";
        echo "<h4>스택 추적:</h4>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 전체 오류 발생</h2>";
    echo "메시지: " . $e->getMessage() . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error 발생</h2>";
    echo "메시지: " . $e->getMessage() . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>