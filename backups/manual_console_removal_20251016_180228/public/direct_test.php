<?php
// 직접 테스트 - 매우 간단하게
echo "직접 테스트 시작\n";

// 1단계: 기본 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

try {
    // 2단계: 필수 파일만 로드
    require_once SRC_PATH . '/config/paths.php';
    echo "✅ paths.php\n";
    
    require_once SRC_PATH . '/config/config.php';
    echo "✅ config.php\n";
    
    require_once SRC_PATH . '/config/database.php';
    echo "✅ database.php\n";
    
    require_once SRC_PATH . '/controllers/BaseController.php';
    echo "✅ BaseController.php\n";
    
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    echo "✅ AuthMiddleware.php\n";
    
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    echo "✅ ResponseHelper.php\n";
    
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    echo "✅ RegistrationController.php\n";
    
    // 3단계: 세션 및 환경 설정
    session_start();
    $_SESSION['user_id'] = 5;
    $_SESSION['csrf_token'] = 'test_token';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    echo "✅ 환경 설정 완료\n";
    
    // 4단계: 컨트롤러 생성
    $controller = new RegistrationController();
    echo "✅ 컨트롤러 생성\n";
    
    // 5단계: POST 데이터 직접 설정
    $_POST = [
        'csrf_token' => 'test_token',
        'participant_name' => '이정현',
        'participant_email' => '2jeonghyeon@naver.com',
        'participant_phone' => '010-26591346',
        'company_name' => '윈카드',
        'position' => '대표이사',
        'motivation' => '테스트',
        'special_requests' => '',
        'how_did_you_know' => 'website'
    ];
    
    echo "✅ POST 데이터 설정\n";
    echo "POST 데이터: " . json_encode($_POST) . "\n";
    
    // 6단계: 메소드 직접 호출
    echo "\n=== 메소드 호출 시작 ===\n";
    
    ob_start();
    $result = $controller->createRegistration(167);
    $output = ob_get_clean();
    
    echo "결과: " . $output . "\n";
    echo "=== 완료 ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Exception: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "스택:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "스택:\n" . $e->getTraceAsString() . "\n";
}
?>