<?php
/**
 * 강의 신청 제출 500 오류 디버깅
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>🔧 강의 신청 제출 500 오류 디버깅</h1>";

// 모든 PHP 오류 표시
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 세션 시작
session_start();

try {
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    
    echo "<h2>✅ 시스템 로드 완료</h2>";
    
    // 데이터베이스 연결
    $db = Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ 데이터베이스 연결 성공<br>";
    
    // POST 데이터 시뮬레이션
    echo "<h2>📤 POST 요청 시뮬레이션</h2>";
    
    // 실제 사용자 로그인 상태 확인
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "로그인 상태: " . ($isLoggedIn ? "✅ 로그인됨" : "❌ 로그인 안됨") . "<br>";
    
    if ($isLoggedIn) {
        $userId = AuthMiddleware::getCurrentUserId();
        echo "사용자 ID: $userId<br>";
        
        // CSRF 토큰 생성
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo "CSRF 토큰: " . $_SESSION['csrf_token'] . "<br>";
        
        // RegistrationController 로드
        require_once SRC_PATH . '/controllers/RegistrationController.php';
        
        echo "<h2>🧪 RegistrationController 테스트</h2>";
        
        // 테스트 데이터 준비
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
        echo "<pre>" . print_r($_POST, true) . "</pre>";
        
        // 컨트롤러 인스턴스 생성
        try {
            $controller = new RegistrationController();
            echo "✅ RegistrationController 인스턴스 생성 성공<br>";
            
            // createRegistration 메소드 호출
            echo "<h3>🚀 createRegistration(167) 호출</h3>";
            
            // 출력 버퍼링 시작
            ob_start();
            $result = $controller->createRegistration(167);
            $output = ob_get_clean();
            
            echo "메소드 실행 완료<br>";
            echo "출력 내용:<br>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
            
        } catch (Exception $e) {
            echo "❌ 컨트롤러 실행 오류: " . $e->getMessage() . "<br>";
            echo "파일: " . $e->getFile() . "<br>";
            echo "라인: " . $e->getLine() . "<br>";
            echo "스택 추적:<br>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
    } else {
        echo "❌ 로그인이 필요합니다.<br>";
    }
    
    // 에러 로그 확인
    echo "<h2>📋 에러 로그 확인</h2>";
    $errorLogPath = '/var/www/html/topmkt/logs/topmkt_errors.log';
    if (file_exists($errorLogPath)) {
        $logContent = file_get_contents($errorLogPath);
        $recentLogs = array_slice(explode("\n", $logContent), -50);
        echo "<pre>" . htmlspecialchars(implode("\n", $recentLogs)) . "</pre>";
    } else {
        echo "에러 로그 파일이 없습니다.<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 전체 오류 발생</h2>";
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<strong>오류:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>파일:</strong> " . $e->getFile() . "<br>";
    echo "<strong>라인:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>