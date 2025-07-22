<?php
// 즉시 에러 확인
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== 즉시 디버깅 ===\n";

// 1. 라우팅 확인
echo "1. Request Info:\n";
echo "URI: " . ($_SERVER['REQUEST_URI'] ?? 'undefined') . "\n";
echo "Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'undefined') . "\n";

// 2. 직접 RegistrationController 테스트
echo "\n2. Direct Controller Test:\n";

try {
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    
    require_once SRC_PATH . '/config/paths.php';
    require_once SRC_PATH . '/config/config.php';
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/controllers/BaseController.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    
    echo "✅ All files loaded\n";
    
    $controller = new RegistrationController();
    echo "✅ Controller created\n";
    
    // 세션 설정
    session_start();
    $_SESSION['user_id'] = 5;
    $_SESSION['csrf_token'] = 'test123';
    
    // POST 데이터 설정
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $_POST = [];
    
    $testData = json_encode([
        'csrf_token' => 'test123',
        'participant_name' => '이정현',
        'participant_email' => '2jeonghyeon@naver.com',
        'participant_phone' => '010-26591346',
        'company_name' => '윈카드',
        'position' => '대표이사',
        'motivation' => '테스트',
        'special_requests' => '',
        'how_did_you_know' => 'website'
    ]);
    
    // php://input 시뮬레이션
    file_put_contents('php://input', $testData);
    
    echo "\n3. 직접 메소드 호출:\n";
    ob_start();
    $controller->createRegistration(167);
    $output = ob_get_clean();
    
    echo "출력: " . $output . "\n";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>