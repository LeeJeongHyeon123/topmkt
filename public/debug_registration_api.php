<?php
/**
 * 강의 등록 API 디버깅 도구
 */

// 오류 표시 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 강의 등록 API 디버깅</h1>";

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

try {
    // 세션 시작
    session_start();
    
    echo "<h2>📂 시스템 초기화</h2>";
    
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    echo "✅ 기본 설정 로드 완료<br>";
    
    // 필수 클래스들 로드
    require_once SRC_PATH . '/controllers/BaseController.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/ValidationHelper.php';
    require_once SRC_PATH . '/services/EmailService.php';
    
    // SMS 관련 헬퍼 로드 (존재하는 경우)
    if (file_exists(SRC_PATH . '/helpers/SmsHelper.php')) {
        require_once SRC_PATH . '/helpers/SmsHelper.php';
        echo "✅ SmsHelper 로드 완료<br>";
    }
    
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    echo "✅ 모든 필수 클래스 로드 완료<br>";
    
    echo "<h2>🔐 인증 시뮬레이션</h2>";
    
    // 실제 사용자 세션 시뮬레이션 (로그에서 확인된 user_id: 5)
    $_SESSION['user_id'] = 5;
    $_SESSION['user_role'] = 'ROLE_USER';
    $_SESSION['csrf_token'] = 'test_token_' . time();
    echo "✅ 사용자 ID 5로 인증 설정 완료<br>";
    echo "🔑 CSRF Token: " . $_SESSION['csrf_token'] . "<br>";
    
    echo "<h2>📋 환경 시뮬레이션</h2>";
    
    // 실제 AJAX 요청 환경 시뮬레이션
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/api/lectures/167/registration';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    echo "✅ AJAX 환경 시뮬레이션 완료<br>";
    
    // 실제 등록 데이터 설정 (콘솔 로그에서 확인된 값들)
    $registrationData = [
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
    
    echo "<h3>📝 등록 데이터:</h3>";
    echo "<pre>" . json_encode($registrationData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
    // JSON 입력 스트림 시뮬레이션
    $jsonInput = json_encode($registrationData);
    file_put_contents('php://input', $jsonInput);
    $_POST = []; // POST 배열 비우기 (JSON 데이터이므로)
    
    echo "<h2>🧪 RegistrationController 테스트</h2>";
    
    $controller = new RegistrationController();
    echo "✅ 컨트롤러 인스턴스 생성 완료<br>";
    
    echo "<h3>🔍 createRegistration 메소드 호출</h3>";
    echo "강의 ID: 167<br>";
    echo "메소드 호출 시작...<br>";
    
    // 출력 버퍼링
    ob_start();
    
    try {
        $controller->createRegistration(167);
        $output = ob_get_clean();
        
        echo "<h4>✅ 메소드 실행 완료</h4>";
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        echo "<strong>출력 결과:</strong><br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        echo "</div>";
        
    } catch (Exception $e) {
        ob_end_clean();
        
        echo "<h4>❌ Exception 발생</h4>";
        echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
        echo "<strong>예외 타입:</strong> " . get_class($e) . "<br>";
        echo "<strong>메시지:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        
        echo "<h5>스택 트레이스:</h5>";
        echo "<pre style='font-size: 11px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
        echo "</div>";
        
    } catch (Error $e) {
        ob_end_clean();
        
        echo "<h4>❌ Fatal Error 발생</h4>";
        echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
        echo "<strong>Error 타입:</strong> " . get_class($e) . "<br>";
        echo "<strong>메시지:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        
        echo "<h5>스택 트레이스:</h5>";
        echo "<pre style='font-size: 11px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
        echo "</div>";
    }
    
    echo "<h2>🔧 추가 디버깅 정보</h2>";
    
    // PHP 메모리 사용량
    echo "<h3>💾 메모리 사용량</h3>";
    echo "현재 메모리: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB<br>";
    echo "최대 메모리: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB<br>";
    echo "메모리 한계: " . ini_get('memory_limit') . "<br>";
    
    // 로드된 클래스 확인
    echo "<h3>📚 로드된 주요 클래스</h3>";
    $importantClasses = ['RegistrationController', 'ResponseHelper', 'AuthMiddleware', 'ValidationHelper', 'Database'];
    foreach ($importantClasses as $className) {
        if (class_exists($className)) {
            echo "✅ $className<br>";
        } else {
            echo "❌ $className<br>";
        }
    }
    
    // 함수 존재 여부 확인
    echo "<h3>🔧 주요 함수</h3>";
    $importantFunctions = ['sendSms', 'sendLectureApplicationSms'];
    foreach ($importantFunctions as $functionName) {
        if (function_exists($functionName)) {
            echo "✅ $functionName()<br>";
        } else {
            echo "❌ $functionName()<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 전체 디버깅 실패</h2>";
    echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
    echo "<strong>예외:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error in Debug</h2>";
    echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3, h4, h5 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { border-radius: 5px; overflow-x: auto; font-size: 12px; }
</style>