<?php
/**
 * 긴급 라우팅 테스트
 */

echo "Content-Type: text/html; charset=UTF-8\n\n";

// 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

echo "<!DOCTYPE html><html><body>";
echo "<h1>긴급 라우팅 테스트</h1>";

echo "<h2>1. 기본 정보</h2>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'UNKNOWN') . "<br>";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'UNKNOWN') . "<br>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'UNKNOWN') . "<br>";
echo "현재 파일: " . __FILE__ . "<br>";

echo "<h2>2. 강의 167 직접 테스트</h2>";

try {
    require_once 'src/config/config.php';
    require_once 'src/config/database.php';
    require_once 'src/controllers/LectureController.php';
    require_once 'src/middlewares/AuthMiddleware.php';
    
    // 세션 시작
    session_start();
    
    // 안계현으로 로그인 설정
    $_SESSION['user_id'] = 5;
    $_SESSION['user_role'] = 'ROLE_USER';
    $_SESSION['nickname'] = '안계현';
    
    echo "✅ 세션 설정 완료<br>";
    
    $controller = new LectureController();
    echo "✅ 컨트롤러 생성 완료<br>";
    
    // 출력 버퍼링 시작
    ob_start();
    
    // show 메소드 호출
    $controller->show(167);
    
    $output = ob_get_clean();
    
    if (empty($output)) {
        echo "❌ 컨트롤러 출력 없음<br>";
    } else {
        echo "✅ 컨트롤러 출력: " . strlen($output) . " bytes<br>";
        
        // HTML이 포함되어 있는지 확인
        if (strpos($output, '<!DOCTYPE html') !== false) {
            echo "✅ HTML 문서 포함<br>";
        } else {
            echo "❌ HTML 문서 없음<br>";
        }
        
        // 거절 메시지 포함 여부 확인
        if (strpos($output, 'lecture-status-message') !== false) {
            echo "✅ 거절 메시지 영역 포함<br>";
        } else {
            echo "❌ 거절 메시지 영역 없음<br>";
        }
        
        // 첫 1000자 출력
        echo "<h3>출력 샘플 (첫 1000자):</h3>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "...</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
}

echo "</body></html>";
?>