<?php
/**
 * 간단한 이벤트 테스트
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/controllers/EventController.php';

echo "<h1>간단한 이벤트 테스트</h1>";

// 데이터베이스 연결 테스트
try {
    $db = Database::getInstance();
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM lectures WHERE content_type = 'event'");
    echo "<p style='color: green;'>✅ 데이터베이스 연결 성공 - 이벤트 개수: {$result['count']}</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 데이터베이스 연결 실패: " . $e->getMessage() . "</p>";
    exit;
}

// EventController 테스트
try {
    $controller = new EventController();
    echo "<p style='color: green;'>✅ EventController 생성 성공</p>";
    
    // index 메서드 테스트
    if (method_exists($controller, 'index')) {
        echo "<p style='color: green;'>✅ index 메서드 존재</p>";
        
        // 출력 캡처하여 실행 테스트
        ob_start();
        try {
            $controller->index();
            $output = ob_get_contents();
            echo "<p style='color: green;'>✅ index 메서드 실행 성공</p>";
            echo "<details><summary>출력 결과 (처음 1000자)</summary>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "</pre>";
            echo "</details>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ index 메서드 실행 실패: " . $e->getMessage() . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        ob_end_clean();
    } else {
        echo "<p style='color: red;'>❌ index 메서드 없음</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ EventController 생성 실패: " . $e->getMessage() . "</p>";
}

// 현재 날짜 기준 이벤트 조회 테스트
echo "<h2>현재 날짜 기준 이벤트 조회</h2>";
try {
    $db = Database::getInstance();
    $currentYear = date('Y');
    $currentMonth = date('m');
    
    $sql = "SELECT COUNT(*) as count FROM lectures WHERE content_type = 'event' AND YEAR(start_date) = ? AND MONTH(start_date) = ?";
    $result = $db->fetchOne($sql, [$currentYear, $currentMonth]);
    echo "<p>현재 월 ({$currentYear}-{$currentMonth}) 이벤트 개수: {$result['count']}</p>";
    
    // 전체 이벤트 목록
    $sql = "SELECT id, title, start_date, start_time, status FROM lectures WHERE content_type = 'event' ORDER BY start_date DESC LIMIT 10";
    $events = $db->fetchAll($sql);
    
    echo "<h3>최근 이벤트 10개</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>제목</th><th>시작일</th><th>시작시간</th><th>상태</th></tr>";
    foreach ($events as $event) {
        echo "<tr>";
        echo "<td>{$event['id']}</td>";
        echo "<td>" . htmlspecialchars($event['title']) . "</td>";
        echo "<td>{$event['start_date']}</td>";
        echo "<td>{$event['start_time']}</td>";
        echo "<td>{$event['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 이벤트 조회 실패: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/events'>실제 이벤트 페이지로 이동</a></p>";
echo "<p><a href='/'>홈으로 이동</a></p>";
?>