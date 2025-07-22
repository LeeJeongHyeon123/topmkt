<?php
/**
 * 상세한 강의 신청 디버깅 도구
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>🔍 상세 강의 신청 디버깅</h1>";

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

// 가상 사용자 로그인 상태
$_SESSION['user_id'] = 5;
$_SESSION['csrf_token'] = 'test_token';

echo "<h2>📋 디버깅 정보</h2>";
echo "세션 사용자 ID: " . ($_SESSION['user_id'] ?? 'null') . "<br>";
echo "CSRF 토큰: " . ($_SESSION['csrf_token'] ?? 'null') . "<br>";

try {
    // 기본 설정 로드
    echo "<h2>🔧 시스템 로드</h2>";
    require_once CONFIG_PATH . '/paths.php';
    echo "✅ paths.php 로드<br>";
    
    require_once CONFIG_PATH . '/config.php';
    echo "✅ config.php 로드<br>";
    
    require_once CONFIG_PATH . '/database.php';
    echo "✅ database.php 로드<br>";
    
    require_once SRC_PATH . '/helpers/WebLogger.php';
    echo "✅ WebLogger 로드<br>";
    
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    echo "✅ ResponseHelper 로드<br>";
    
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    echo "✅ GlobalErrorHandler 로드<br>";
    
    // 글로벌 에러 핸들러 등록
    GlobalErrorHandler::register();
    echo "✅ 에러 핸들러 등록<br>";
    
    // 데이터베이스 연결 테스트
    echo "<h2>🗄️ 데이터베이스 테스트</h2>";
    $db = Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ 데이터베이스 인스턴스 생성<br>";
    echo "✅ 연결 객체: " . get_class($connection) . "<br>";
    
    // RegistrationController 로드
    echo "<h2>🎮 컨트롤러 로드</h2>";
    require_once SRC_PATH . '/controllers/BaseController.php';
    echo "✅ BaseController 로드<br>";
    
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    echo "✅ AuthMiddleware 로드<br>";
    
    require_once SRC_PATH . '/helpers/ValidationHelper.php';
    echo "✅ ValidationHelper 로드<br>";
    
    require_once SRC_PATH . '/services/EmailService.php';
    echo "✅ EmailService 로드<br>";
    
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    echo "✅ RegistrationController 로드<br>";
    
    // AuthMiddleware 테스트
    echo "<h2>🔐 인증 테스트</h2>";
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "로그인 상태: " . ($isLoggedIn ? '✅ 로그인됨' : '❌ 미로그인') . "<br>";
    
    if ($isLoggedIn) {
        $userId = AuthMiddleware::getCurrentUserId();
        echo "사용자 ID: " . $userId . "<br>";
    }
    
    // 컨트롤러 인스턴스 생성
    echo "<h2>🚀 컨트롤러 실행</h2>";
    
    // 출력 버퍼링 시작하여 JSON 응답 캡처
    ob_start();
    
    try {
        $controller = new RegistrationController();
        echo "✅ 컨트롤러 인스턴스 생성<br>";
        
        // createRegistration 메소드 호출
        echo "📞 createRegistration(167) 메소드 호출...<br>";
        flush(); // 즉시 출력
        
        $result = $controller->createRegistration(167);
        
        echo "✅ 메소드 실행 완료<br>";
        echo "반환값: " . var_export($result, true) . "<br>";
        
    } catch (Exception $e) {
        echo "❌ 컨트롤러 실행 중 예외:<br>";
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0;'>";
        echo "<strong>예외 타입:</strong> " . get_class($e) . "<br>";
        echo "<strong>메시지:</strong> " . $e->getMessage() . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        echo "<strong>스택 트레이스:</strong><br>";
        echo "<pre style='font-size: 12px; background: #f5f5f5; padding: 10px; overflow-x: auto;'>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    } catch (Error $e) {
        echo "❌ PHP Fatal Error:<br>";
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0;'>";
        echo "<strong>오류 타입:</strong> " . get_class($e) . "<br>";
        echo "<strong>메시지:</strong> " . $e->getMessage() . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        echo "<strong>스택 트레이스:</strong><br>";
        echo "<pre style='font-size: 12px; background: #f5f5f5; padding: 10px; overflow-x: auto;'>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }
    
    // 출력 버퍼 내용 가져오기
    $jsonOutput = ob_get_clean();
    
    echo "<h2>📄 JSON 응답</h2>";
    if (!empty($jsonOutput)) {
        echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px 0;'>";
        echo "<strong>JSON 응답:</strong><br>";
        echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
        echo htmlspecialchars($jsonOutput);
        echo "</pre>";
        
        // JSON 유효성 검사
        $decoded = json_decode($jsonOutput, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ 유효한 JSON<br>";
            echo "<strong>파싱된 데이터:</strong><br>";
            echo "<pre>" . print_r($decoded, true) . "</pre>";
        } else {
            echo "❌ JSON 파싱 오류: " . json_last_error_msg() . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 10px 0;'>";
        echo "⚠️ JSON 응답이 없습니다.";
        echo "</div>";
    }
    
    // 로그 파일 확인
    echo "<h2>📋 최근 로그</h2>";
    $logPath = '/var/www/html/topmkt/logs/topmkt_errors.log';
    if (file_exists($logPath)) {
        $recentLogs = shell_exec("tail -20 {$logPath} 2>&1");
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0;'>";
        echo "<strong>최근 20개 로그:</strong><br>";
        echo "<pre style='max-height: 300px; overflow-y: scroll; font-size: 12px;'>";
        echo htmlspecialchars($recentLogs);
        echo "</pre>";
        echo "</div>";
    } else {
        echo "❌ 로그 파일 없음<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 시스템 오류</h2>";
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<strong>시스템 오류:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "<strong>스택 트레이스:</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; }
h1 { border-bottom: 2px solid #007bff; padding-bottom: 10px; }
h2 { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 30px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>