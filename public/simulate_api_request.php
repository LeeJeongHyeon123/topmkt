<?php
/**
 * 실제 API 요청 시뮬레이션
 */

// 오류 표시 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🚀 실제 API 요청 시뮬레이션</h1>";

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

try {
    // 세션 시작
    session_start();
    
    // 실제 API 요청 환경 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/api/lectures/167/registration';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    
    // POST 데이터 시뮬레이션
    $_POST = [
        'csrf_token' => 'test_token',
        'participant_name' => '이정현',
        'participant_email' => '2jeonghyeon@naver.com',
        'participant_phone' => '010-26591346',
        'company_name' => '윈카드',
        'position' => '대표이사',
        'motivation' => '네트워킹 마케팅 학습',
        'special_requests' => '',
        'how_did_you_know' => 'website'
    ];
    
    echo "<h2>📋 환경 설정 완료</h2>";
    echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "<br>";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
    echo "CONTENT_TYPE: " . $_SERVER['CONTENT_TYPE'] . "<br>";
    echo "POST 데이터 설정 완료<br>";
    
    // 기본 설정 로드
    echo "<h2>📂 시스템 로드</h2>";
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    echo "✅ 기본 설정 로드 완료<br>";
    
    // 컨트롤러 로드
    require_once SRC_PATH . '/controllers/BaseController.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/ValidationHelper.php';
    require_once SRC_PATH . '/services/EmailService.php';
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    echo "✅ 컨트롤러 및 의존성 로드 완료<br>";
    
    echo "<h2>🔐 인증 상태 확인</h2>";
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "로그인 상태: " . ($isLoggedIn ? "✅ 로그인됨" : "❌ 로그인 안됨") . "<br>";
    
    if ($isLoggedIn) {
        $userId = AuthMiddleware::getCurrentUserId();
        echo "사용자 ID: $userId<br>";
    } else {
        // 테스트용 가짜 인증 설정
        echo "⚠️ 로그인 안된 상태이므로 테스트용 세션 설정<br>";
        $_SESSION['user_id'] = 5;
        $_SESSION['user_role'] = 'ROLE_USER';
        $_SESSION['csrf_token'] = 'test_token';
        echo "테스트 사용자 ID: 5 설정 완료<br>";
    }
    
    echo "<h2>🧪 RegistrationController::createRegistration 직접 호출</h2>";
    
    $controller = new RegistrationController();
    echo "✅ 컨트롤러 인스턴스 생성 완료<br>";
    
    echo "📞 createRegistration(167) 메소드 호출 중...<br>";
    
    // 출력 버퍼링으로 결과 캡처
    ob_start();
    
    try {
        $controller->createRegistration(167);
        $output = ob_get_clean();
        
        echo "<h3>📤 메소드 실행 결과:</h3>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars($output);
        echo "</pre>";
        
    } catch (Exception $e) {
        ob_end_clean();
        
        echo "<h3>❌ 메소드 실행 중 예외 발생:</h3>";
        echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
        echo "<strong>예외:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        echo "<h4>스택 추적:</h4>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    } catch (Error $e) {
        ob_end_clean();
        
        echo "<h3>❌ Fatal Error 발생:</h3>";
        echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
        echo "<strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        echo "<h4>스택 추적:</h4>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 전체 시뮬레이션 실패</h2>";
    echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
    echo "<strong>예외:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "<h4>스택 추적:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error in Simulation</h2>";
    echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "<h4>스택 추적:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { border-radius: 5px; overflow-x: auto; }
</style>