<?php
/**
 * 강의 신청 취소 디버깅 도구
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>🔧 강의 신청 취소 디버깅</h1>";

// 모든 PHP 오류 표시
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 세션 시작
session_start();

// DELETE 요청 시뮬레이션
$_SERVER['REQUEST_METHOD'] = 'DELETE';
$_SERVER['REQUEST_URI'] = '/api/lectures/167/registration';
$_SERVER['HTTP_ACCEPT'] = 'application/json';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// CSRF 토큰을 쿼리 파라미터로 전달 (DELETE 요청에서 일반적)
$_GET['csrf_token'] = 'test_token';

// 가상 사용자 로그인 상태 (신청한 사용자)
$_SESSION['user_id'] = 5;  // 신청한 사용자 ID
$_SESSION['csrf_token'] = 'test_token';

echo "<h2>📋 디버깅 정보</h2>";
echo "요청 메소드: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Content-Type: " . $_SERVER['CONTENT_TYPE'] . "<br>";
echo "세션 사용자 ID: " . ($_SESSION['user_id'] ?? 'null') . "<br>";
echo "CSRF 토큰: " . ($_SESSION['csrf_token'] ?? 'null') . "<br>";
echo "GET 파라미터: " . print_r($_GET, true) . "<br>";

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
    
    // 현재 신청 상태 확인
    echo "<h2>📋 현재 신청 상태 확인</h2>";
    $registration = $db->fetch(
        "SELECT * FROM lecture_registrations WHERE lecture_id = 167 AND user_id = 5 ORDER BY created_at DESC LIMIT 1"
    );
    
    if ($registration) {
        echo "✅ 신청 정보 발견<br>";
        echo "신청 ID: " . $registration['id'] . "<br>";
        echo "상태: " . $registration['status'] . "<br>";
        echo "생성일: " . $registration['created_at'] . "<br>";
    } else {
        echo "❌ 신청 정보를 찾을 수 없습니다.<br>";
        
        // 모든 신청 내역 확인
        $allRegistrations = $db->fetchAll("SELECT * FROM lecture_registrations WHERE lecture_id = 167");
        echo "<h3>강의 167의 모든 신청 내역:</h3>";
        echo "<pre>" . print_r($allRegistrations, true) . "</pre>";
    }
    
    // RegistrationController 로드
    require_once SRC_PATH . '/controllers/BaseController.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/helpers/ValidationHelper.php';
    require_once SRC_PATH . '/services/EmailService.php';
    require_once SRC_PATH . '/controllers/RegistrationController.php';
    
    echo "<h2>🎮 취소 요청 테스트</h2>";
    
    // 출력 버퍼 시작
    ob_start();
    
    try {
        $controller = new RegistrationController();
        echo "✅ 컨트롤러 인스턴스 생성<br>";
        
        echo "📞 cancelRegistration(167) 호출 중...<br>";
        flush();
        
        $result = $controller->cancelRegistration(167);
        
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
    
    // 취소 후 상태 확인
    echo "<h2>🔍 취소 후 상태 확인</h2>";
    $afterRegistration = $db->fetch(
        "SELECT * FROM lecture_registrations WHERE lecture_id = 167 AND user_id = 5 ORDER BY created_at DESC LIMIT 1"
    );
    
    if ($afterRegistration) {
        echo "신청 ID: " . $afterRegistration['id'] . "<br>";
        echo "상태: " . $afterRegistration['status'] . "<br>";
        echo "처리일: " . ($afterRegistration['processed_at'] ?? 'null') . "<br>";
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