<?php
/**
 * 이벤트 상세 페이지 디버깅
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/controllers/EventController.php';

echo "<h1>이벤트 상세 페이지 디버깅</h1>";

try {
    $eventId = $_GET['id'] ?? 194;
    
    echo "<h2>🔍 1. 이벤트 ID: {$eventId}</h2>";
    
    // 이벤트 조회
    $db = Database::getInstance();
    $sql = "SELECT * FROM lectures WHERE id = ? AND content_type = 'event'";
    $event = $db->fetch($sql, [$eventId]);
    
    if ($event) {
        echo "<div style='background: #f0fdf4; padding: 15px; border-radius: 8px;'>";
        echo "<h3>✅ 이벤트 존재 확인</h3>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $event['id'] . "</li>";
        echo "<li><strong>제목:</strong> " . htmlspecialchars($event['title']) . "</li>";
        echo "<li><strong>상태:</strong> " . $event['status'] . "</li>";
        echo "<li><strong>생성자:</strong> " . $event['user_id'] . "</li>";
        echo "<li><strong>유형:</strong> " . $event['content_type'] . "</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px;'>";
        echo "<h3>❌ 이벤트를 찾을 수 없습니다</h3>";
        echo "</div>";
        exit;
    }
    
    // 2. EventController 인스턴스 생성 테스트
    echo "<h2>🔧 2. EventController 테스트</h2>";
    
    try {
        $controller = new EventController();
        echo "<div style='background: #f0fdf4; padding: 15px; border-radius: 8px;'>";
        echo "<h3>✅ EventController 생성 성공</h3>";
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px;'>";
        echo "<h3>❌ EventController 생성 실패</h3>";
        echo "<p>오류: " . $e->getMessage() . "</p>";
        echo "</div>";
        exit;
    }
    
    // 3. 메소드 존재 확인
    echo "<h2>📋 3. 메소드 존재 확인</h2>";
    
    $methods = [
        'detail' => method_exists($controller, 'detail'),
        'registrationStatus' => method_exists($controller, 'registrationStatus'),
        'registerEvent' => method_exists($controller, 'registerEvent'),
        'cancelEventRegistration' => method_exists($controller, 'cancelEventRegistration')
    ];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
    echo "<h3>메소드 존재 여부:</h3>";
    echo "<ul>";
    foreach ($methods as $method => $exists) {
        $icon = $exists ? '✅' : '❌';
        echo "<li>{$icon} {$method}</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // 4. 실제 detail 메소드 호출 시뮬레이션
    echo "<h2>🚀 4. detail 메소드 호출 시뮬레이션</h2>";
    
    try {
        $_GET['id'] = $eventId;
        
        // 출력 버퍼링 시작
        ob_start();
        
        // detail 메소드 직접 호출
        $controller->detail();
        
        // 출력 내용 가져오기
        $output = ob_get_clean();
        
        if (strlen($output) > 0) {
            echo "<div style='background: #f0fdf4; padding: 15px; border-radius: 8px;'>";
            echo "<h3>✅ detail 메소드 실행 성공</h3>";
            echo "<p>출력 길이: " . strlen($output) . " 바이트</p>";
            echo "<p>처음 200자: " . htmlspecialchars(substr($output, 0, 200)) . "...</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px;'>";
            echo "<h3>⚠️ detail 메소드 실행됨 (출력 없음)</h3>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px;'>";
        echo "<h3>❌ detail 메소드 실행 실패</h3>";
        echo "<p>오류: " . $e->getMessage() . "</p>";
        echo "<p>스택 트레이스:</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }
    
    // 5. 라우팅 시스템 확인
    echo "<h2>🛣️ 5. 라우팅 시스템 확인</h2>";
    
    $routeFile = SRC_PATH . '/config/routes.php';
    if (file_exists($routeFile)) {
        $routeContent = file_get_contents($routeFile);
        
        // 이벤트 관련 라우트 확인
        $hasEventDetail = strpos($routeContent, 'events/detail') !== false;
        $hasEventRegistration = strpos($routeContent, 'events/{id}/registration') !== false;
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
        echo "<h3>라우트 확인:</h3>";
        echo "<ul>";
        echo "<li>" . ($hasEventDetail ? '✅' : '❌') . " events/detail 라우트</li>";
        echo "<li>" . ($hasEventRegistration ? '✅' : '❌') . " events/{id}/registration 라우트</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    // 6. 직접 URL 테스트
    echo "<h2>🌐 6. 직접 URL 테스트</h2>";
    
    $testUrl = "https://www.topmktx.com/events/detail?id={$eventId}";
    
    echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px;'>";
    echo "<h3>테스트 URL:</h3>";
    echo "<p><a href='{$testUrl}' target='_blank'>{$testUrl}</a></p>";
    echo "<p>이 링크를 새 탭에서 열어 오류를 확인해보세요.</p>";
    echo "</div>";
    
    // 7. 오류 로그 최근 항목
    echo "<h2>📝 7. 최근 오류 로그</h2>";
    
    $logFile = '/workspace/var/log/php-fpm/www-error.log';
    if (file_exists($logFile)) {
        $logs = file($logFile);
        $recentLogs = array_slice($logs, -20);
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 300px; overflow-y: auto;'>";
        echo "<h3>최근 20개 로그:</h3>";
        echo "<pre style='font-size: 12px;'>";
        foreach ($recentLogs as $log) {
            echo htmlspecialchars($log);
        }
        echo "</pre>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px;'>";
    echo "<h3>❌ 디버깅 중 오류 발생</h3>";
    echo "<p>오류: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>