<?php
/**
 * 이벤트 188 데이터 직접 확인 스크립트
 */

// 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

// 필요한 파일 로드
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/controllers/EventController.php';

try {
    echo "<h1>이벤트 188 데이터 디버깅</h1>";
    
    // 데이터베이스에서 직접 조회
    $db = Database::getInstance();
    
    echo "<h2>1. 데이터베이스 직접 조회</h2>";
    $sql = "SELECT id, title, youtube_video, instructor_name, instructor_info FROM lectures WHERE id = 188";
    $lecture = $db->fetch($sql, []);
    echo "<pre>";
    var_dump($lecture);
    echo "</pre>";
    
    echo "<h2>2. lecture_instructors 테이블 조회</h2>";
    $sql = "SELECT * FROM lecture_instructors WHERE lecture_id = 188";
    $instructors = $db->fetchAll($sql, []);
    echo "<pre>";
    var_dump($instructors);
    echo "</pre>";
    
    echo "<h2>3. EventController getEventById() 메서드 결과</h2>";
    $controller = new EventController();
    
    // getEventById는 private이므로 reflection을 사용
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getEventById');
    $method->setAccessible(true);
    
    $event = $method->invoke($controller, 188);
    echo "<pre>";
    var_dump($event);
    echo "</pre>";
    
    echo "<h2>4. YouTube 비디오 값 확인</h2>";
    echo "YouTube Video: " . ($event['youtube_video'] ?? 'NULL') . "<br>";
    echo "Empty Check: " . (empty($event['youtube_video']) ? 'TRUE (empty)' : 'FALSE (not empty)') . "<br>";
    
    echo "<h2>5. 강사 정보 확인</h2>";
    if (isset($event['instructors'])) {
        echo "강사 수: " . count($event['instructors']) . "<br>";
        foreach ($event['instructors'] as $idx => $instructor) {
            echo "강사 " . ($idx + 1) . ": " . ($instructor['name'] ?? 'NULL') . "<br>";
            echo "이미지: " . ($instructor['image'] ?? 'NULL') . "<br>";
        }
    } else {
        echo "강사 정보 없음<br>";
    }
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage();
    echo "<br>스택 추적:<br><pre>";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>