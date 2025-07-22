<?php
/**
 * POST 강의 신청 요청 직접 시뮬레이션 및 디버깅
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>🔧 POST 강의 신청 디버깅</h1>";

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

echo "<h2>📡 요청 정보</h2>";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Accept: " . $_SERVER['HTTP_ACCEPT'] . "<br>";
echo "Content-Type: " . $_SERVER['CONTENT_TYPE'] . "<br>";

echo "<h2>📦 POST 데이터</h2>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

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
    
    echo "<h2>✅ 기본 시스템 로드 완료</h2>";
    
    // 데이터베이스 연결 확인
    echo "<h2>🗄️ 데이터베이스 연결 확인</h2>";
    $db = Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ 데이터베이스 연결: 성공<br>";
    
    // RegistrationController 로드
    echo "<h2>🎮 RegistrationController 로드</h2>";
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    echo "✅ RegistrationController 로드 완료<br>";
    
    // 실제 POST 요청 시뮬레이션
    echo "<h2>🚀 POST 요청 실행</h2>";
    echo "강의 ID: 167<br>";
    
    ob_start(); // 출력 버퍼링 시작
    
    try {
        $controller = new RegistrationController();
        
        // createRegistration 메소드 직접 호출
        echo "📞 createRegistration 메소드 호출...<br>";
        $result = $controller->createRegistration(167);
        
        echo "✅ register 메소드 실행 완료<br>";
        echo "결과: " . ($result ? 'true' : 'false') . "<br>";
        
    } catch (Exception $e) {
        echo "❌ register 메소드 실행 중 예외 발생:<br>";
        echo "<strong>오류:</strong> " . $e->getMessage() . "<br>";
        echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
        echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
        echo "<strong>스택 트레이스:</strong><br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    $output = ob_get_clean(); // 출력 버퍼 내용 가져오기
    
    echo "<h2>📄 실행 출력</h2>";
    echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;'>";
    echo $output;
    echo "</div>";
    
    // SMS Helper 확인
    echo "<h2>📱 SMS Helper 상세 확인</h2>";
    try {
        require_once SRC_PATH . '/helpers/SmsHelper.php';
        echo "✅ SmsHelper 로드 성공<br>";
        
        // Aligo 설정 확인
        if (defined('ALIGO_API_KEY')) {
            echo "✅ ALIGO_API_KEY 정의됨<br>";
        } else {
            echo "❌ ALIGO_API_KEY 정의되지 않음<br>";
        }
        
        if (defined('ALIGO_USER_ID')) {
            echo "✅ ALIGO_USER_ID 정의됨<br>";
        } else {
            echo "❌ ALIGO_USER_ID 정의되지 않음<br>";
        }
        
        if (defined('ALIGO_SENDER')) {
            echo "✅ ALIGO_SENDER 정의됨<br>";
        } else {
            echo "❌ ALIGO_SENDER 정의되지 않음<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ SMS Helper 오류: " . $e->getMessage() . "<br>";
    }
    
    // 로그 파일 확인
    echo "<h2>📋 로그 파일 확인</h2>";
    $logPath = '/var/www/html/topmkt/logs/topmkt_errors.log';
    if (file_exists($logPath)) {
        echo "✅ 로그 파일 존재: {$logPath}<br>";
        $recentLogs = shell_exec("tail -10 {$logPath} 2>&1");
        echo "<h3>최근 10개 로그:</h3>";
        echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow-y: scroll;'>";
        echo htmlspecialchars($recentLogs);
        echo "</pre>";
    } else {
        echo "❌ 로그 파일 없음: {$logPath}<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 시스템 오류</h2>";
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<strong>오류:</strong> " . $e->getMessage() . "<br>";
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
h1 { border-bottom: 2px solid #dc3545; padding-bottom: 10px; }
h2 { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 30px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>