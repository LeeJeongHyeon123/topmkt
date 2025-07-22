<?php
// 행사 페이지 수정 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 기본 설정
define('ROOT_PATH', '/workspace/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

// 세션 시작
session_start();

// 테스트용 사용자 설정
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'admin';

try {
    echo "<h2>EventController 수정 테스트</h2>";
    
    // EventController 로드 테스트
    require_once SRC_PATH . '/controllers/EventController.php';
    echo "✅ EventController 로드 성공<br>";
    
    // 컨트롤러 인스턴스 생성 테스트
    $controller = new EventController();
    echo "✅ EventController 인스턴스 생성 성공<br>";
    
    // getEventsByMonth 메소드 직접 테스트
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getEventsByMonth');
    $method->setAccessible(true);
    
    echo "<h3>getEventsByMonth 테스트</h3>";
    $events = $method->invokeArgs($controller, [2025, 7]);
    echo "✅ getEventsByMonth 실행 성공<br>";
    echo "📊 조회된 행사 수: " . count($events) . "개<br>";
    
    if (!empty($events)) {
        echo "<h4>첫 번째 행사 정보:</h4>";
        echo "<pre>" . print_r($events[0], true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "<br>";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "🔍 스택 추적:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>