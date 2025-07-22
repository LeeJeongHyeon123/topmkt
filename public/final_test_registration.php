<?php
/**
 * 강의 등록 시스템 최종 테스트
 */

// 오류 표시 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🎯 강의 등록 시스템 최종 테스트</h1>";

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

try {
    // 세션 시작
    session_start();
    
    echo "<h2>📂 시스템 로드</h2>";
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once SRC_PATH . '/controllers/BaseController.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/ValidationHelper.php';
    require_once SRC_PATH . '/services/EmailService.php';
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    echo "✅ 모든 파일 로드 완료<br>";
    
    echo "<h2>🔐 인증 설정</h2>";
    $_SESSION['user_id'] = 5;
    $_SESSION['user_role'] = 'ROLE_USER';
    $_SESSION['csrf_token'] = 'test_token_' . uniqid();
    echo "✅ 테스트 사용자 인증 설정 완료<br>";
    
    echo "<h2>📝 POST 데이터 설정</h2>";
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
    $_SERVER['REQUEST_URI'] = '/api/lectures/167/registration';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    echo "✅ POST 데이터 및 환경 설정 완료<br>";
    
    echo "<h2>🧪 RegistrationController 테스트</h2>";
    $controller = new RegistrationController();
    echo "✅ 컨트롤러 인스턴스 생성 완료<br>";
    
    // ResponseHelper 테스트
    echo "<h3>📋 ResponseHelper 테스트</h3>";
    if (class_exists('ResponseHelper')) {
        echo "✅ ResponseHelper 클래스 존재<br>";
        
        // WebLogger 클래스 존재 여부 확인
        if (class_exists('WebLogger')) {
            echo "⚠️ WebLogger 클래스가 존재함 - 로깅 기능 활성화됨<br>";
        } else {
            echo "✅ WebLogger 클래스 없음 - 의존성 문제 해결됨<br>";
        }
    }
    
    echo "<h3>🔥 강의 등록 실행</h3>";
    echo "createRegistration(167) 메소드 호출 중...<br>";
    
    // 출력 버퍼링
    ob_start();
    
    try {
        $controller->createRegistration(167);
        $output = ob_get_clean();
        
        echo "<h4>✅ 메소드 실행 성공</h4>";
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        echo "<strong>출력 결과:</strong><br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        echo "</div>";
        
    } catch (Exception $e) {
        ob_end_clean();
        
        echo "<h4>❌ 예외 발생</h4>";
        echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
        echo "<strong>예외:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        echo "</div>";
        
    } catch (Error $e) {
        ob_end_clean();
        
        echo "<h4>❌ Fatal Error</h4>";
        echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
        echo "<strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        echo "</div>";
    }
    
    echo "<h2>🔍 추가 테스트</h2>";
    
    // 이전 신청 데이터 조회 테스트
    echo "<h3>📄 이전 신청 데이터 조회 테스트</h3>";
    try {
        $previousData = $controller->getPreviousRegistration(167);
        echo "✅ 이전 신청 데이터 조회 성공<br>";
    } catch (Exception $e) {
        echo "❌ 이전 신청 데이터 조회 실패: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
    
    // 등록 상태 확인 테스트
    echo "<h3>🔍 등록 상태 확인 테스트</h3>";
    try {
        $status = $controller->getRegistrationStatus(167);
        echo "✅ 등록 상태 확인 성공<br>";
    } catch (Exception $e) {
        echo "❌ 등록 상태 확인 실패: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 전체 테스트 실패</h2>";
    echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
    echo "<strong>예외:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error in Test</h2>";
    echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3, h4 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { border-radius: 5px; overflow-x: auto; font-size: 12px; }
</style>