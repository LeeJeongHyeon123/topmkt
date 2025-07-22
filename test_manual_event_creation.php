<?php
// 수동 행사 생성 테스트 (문제 시나리오 재현)
session_start();

// 테스트용 POST 데이터 시뮬레이션
$_POST = [
    'title' => 'QA 테스트 행사',
    'description' => '<p>이것은 QA 테스트를 위한 행사입니다.</p>',
    'start_date' => '2025-07-15',
    'start_time' => '14:00',
    'end_date' => '', // 빈 값 테스트
    'end_time' => '', // 빈 값 테스트
    'location_type' => 'offline',
    'venue_name' => 'QA 테스트 장소',
    'venue_address' => '서울 송파구 올림픽로 300',
    'category' => 'networking', // 무효한 카테고리 테스트
    'instructor_name' => '', // 빈 값 테스트
    'instructor_info' => '',
    'max_participants' => '50',
    'registration_fee' => '0',
    'csrf_token' => 'test_token',
    'event_images' => json_encode([
        ['url' => '/assets/uploads/events/test1.jpg', 'alt' => 'QA 테스트 이미지']
    ])
];

$_SESSION['csrf_token'] = 'test_token';
$_SESSION['user_id'] = 4;
$_SERVER['REQUEST_METHOD'] = 'POST';

define('ROOT_PATH', '/workspace/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

try {
    // EventController 직접 호출
    require_once SRC_PATH . '/controllers/EventController.php';
    
    echo "<h1>🧪 수동 행사 생성 테스트</h1>";
    echo "<p><strong>테스트 시나리오:</strong></p>";
    echo "<ul>";
    echo "<li>빈 instructor_name (기본값 적용 테스트)</li>";
    echo "<li>빈 end_date, end_time (기본값 적용 테스트)</li>";
    echo "<li>무효한 카테고리 'networking' (매핑 테스트)</li>";
    echo "<li>이미지 처리 테스트</li>";
    echo "</ul>";
    
    $controller = new EventController();
    
    // 출력 버퍼링으로 리다이렉트 캐치
    ob_start();
    $controller->store();
    $output = ob_get_clean();
    
    echo "<p><strong>결과:</strong> " . ($output ? "출력 있음" : "리다이렉트 성공") . "</p>";
    
    if ($output) {
        echo "<pre>$output</pre>";
    }
    
    echo "<p>✅ 수동 테스트 완료 - 오류 없이 실행됨</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ 테스트 실패</h2>";
    echo "<p><strong>오류:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>