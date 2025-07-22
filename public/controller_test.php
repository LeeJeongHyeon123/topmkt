<?php
header('Content-Type: text/plain');

echo "RegistrationController 직접 테스트\n";

try {
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    
    // 세션 시작
    session_start();
    $_SESSION['user_id'] = 5;
    $_SESSION['csrf_token'] = 'test123';
    
    // 환경 설정
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    
    // POST 데이터 설정
    $_POST = [
        'csrf_token' => 'test123',
        'participant_name' => '이정현',
        'participant_email' => '2jeonghyeon@naver.com',
        'participant_phone' => '010-26591346',
        'company_name' => '윈카드',
        'position' => '대표이사',
        'motivation' => '테스트 재신청',
        'special_requests' => '',
        'how_did_you_know' => 'website'
    ];
    
    echo "환경 설정 완료\n";
    echo "POST 데이터: " . json_encode($_POST) . "\n\n";
    
    // 필수 파일 로드
    require_once SRC_PATH . '/config/paths.php';
    require_once SRC_PATH . '/config/config.php';
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/controllers/BaseController.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/ValidationHelper.php';
    require_once SRC_PATH . '/services/EmailService.php';
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    
    echo "모든 파일 로드 완료\n";
    
    // 컨트롤러 생성 및 호출
    $controller = new RegistrationController();
    echo "컨트롤러 생성 완료\n";
    
    echo "\n=== createRegistration(167) 호출 ===\n";
    
    // 출력 캡처
    ob_start();
    $controller->createRegistration(167);
    $output = ob_get_clean();
    
    echo "응답: " . $output . "\n";
    echo "=== 완료 ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Exception: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>