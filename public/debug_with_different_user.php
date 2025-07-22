<?php
/**
 * 다른 사용자 ID로 강의 신청 테스트
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>🔧 다른 사용자로 강의 신청 테스트</h1>";

// 모든 PHP 오류 표시
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 세션 시작
session_start();

// POST 요청 시뮬레이션
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/lectures/167/registration';
$_SERVER['HTTP_ACCEPT'] = 'application/json';
$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

// 가상 POST 데이터
$_POST = [
    'participant_name' => '테스트사용자',
    'participant_email' => 'test@example.com', 
    'participant_phone' => '010-1234-5678',
    'special_requests' => '테스트 신청입니다.',
    'csrf_token' => 'test_token'
];

// 다른 사용자 ID로 테스트 (강의 등록자가 아닌 사용자)
$_SESSION['user_id'] = 1;  // 사용자 ID를 1로 변경
$_SESSION['csrf_token'] = 'test_token';

echo "<h2>📋 테스트 정보</h2>";
echo "테스트 사용자 ID: " . $_SESSION['user_id'] . "<br>";
echo "CSRF 토큰: " . $_SESSION['csrf_token'] . "<br>";

try {
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    
    // 글로벌 에러 핸들러 등록
    GlobalErrorHandler::register();
    
    echo "<h2>✅ 시스템 로드 완료</h2>";
    
    // 데이터베이스 연결
    $db = Database::getInstance();
    echo "✅ 데이터베이스 연결 성공<br>";
    
    // 강의 정보 확인
    $lecture = $db->fetch('SELECT id, title, user_id as organizer_id FROM lectures WHERE id = 167');
    if ($lecture) {
        echo "<h2>📚 강의 정보</h2>";
        echo "강의 ID: " . $lecture['id'] . "<br>";
        echo "강의명: " . htmlspecialchars($lecture['title']) . "<br>";
        echo "등록자 ID: " . $lecture['organizer_id'] . "<br>";
        
        if ($lecture['organizer_id'] == $_SESSION['user_id']) {
            echo "<div style='background: #ffebee; padding: 10px; margin: 10px 0; border: 1px solid #f44336;'>";
            echo "❌ 테스트 사용자와 강의 등록자가 동일합니다. 다른 사용자 ID로 변경합니다.";
            echo "</div>";
            
            // 자동으로 다른 사용자 ID 찾기
            $users = $db->fetchAll('SELECT id FROM users WHERE id != ? LIMIT 5', [$lecture['organizer_id']]);
            if ($users) {
                $_SESSION['user_id'] = $users[0]['id'];
                echo "🔄 사용자 ID를 " . $_SESSION['user_id'] . "로 변경했습니다.<br>";
            }
        } else {
            echo "<div style='background: #e8f5e8; padding: 10px; margin: 10px 0; border: 1px solid #4caf50;'>";
            echo "✅ 테스트 사용자와 강의 등록자가 다릅니다. 테스트 진행 가능합니다.";
            echo "</div>";
        }
    }
    
    // RegistrationController 로드
    require_once SRC_PATH . '/controllers/BaseController.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/helpers/ValidationHelper.php';
    require_once SRC_PATH . '/services/EmailService.php';
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    
    echo "<h2>🎮 컨트롤러 테스트</h2>";
    
    // 출력 버퍼 시작
    ob_start();
    
    try {
        $controller = new RegistrationController();
        echo "✅ 컨트롤러 인스턴스 생성<br>";
        
        echo "📞 createRegistration(167) 호출 중...<br>";
        flush();
        
        $result = $controller->createRegistration(167);
        
        echo "✅ 메소드 실행 완료<br>";
        
    } catch (Exception $e) {
        echo "<h2>❌ 예외 발생</h2>";
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0;'>";
        echo "<strong>예외 타입:</strong> " . get_class($e) . "<br>";
        echo "<strong>메시지:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        echo "<strong>스택 트레이스:</strong><br>";
        echo "<pre style='font-size: 12px; max-height: 400px; overflow-y: scroll;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    } catch (Error $e) {
        echo "<h2>❌ PHP Fatal Error</h2>";
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0;'>";
        echo "<strong>오류 타입:</strong> " . get_class($e) . "<br>";
        echo "<strong>메시지:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        echo "<strong>스택 트레이스:</strong><br>";
        echo "<pre style='font-size: 12px; max-height: 400px; overflow-y: scroll;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
    
    $jsonOutput = ob_get_clean();
    
    echo "<h2>📄 JSON 응답</h2>";
    if (!empty($jsonOutput)) {
        echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px 0;'>";
        echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars($jsonOutput);
        echo "</pre>";
        
        $decoded = json_decode($jsonOutput, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<strong>파싱된 응답:</strong><br>";
            echo "<pre>" . print_r($decoded, true) . "</pre>";
        }
        echo "</div>";
    } else {
        echo "⚠️ JSON 응답이 없습니다.<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 시스템 오류</h2>";
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0;'>";
    echo "<strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>