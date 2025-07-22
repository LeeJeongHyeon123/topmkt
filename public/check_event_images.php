<?php
/**
 * 행사 이미지 확인
 */

session_start();

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>행사 이미지 확인</h1>";

try {
    $db = Database::getInstance();
    
    // 1. 이미지가 있는 행사 찾기
    $eventsWithImages = $db->fetchAll("
        SELECT event_id, COUNT(*) as image_count 
        FROM event_images 
        GROUP BY event_id 
        ORDER BY image_count DESC 
        LIMIT 10
    ");
    
    echo "<h2>📊 이미지가 있는 행사 목록</h2>";
    if (count($eventsWithImages) > 0) {
        echo "<ul>";
        foreach ($eventsWithImages as $event) {
            echo "<li>행사 ID: " . $event['event_id'] . " (이미지 " . $event['image_count'] . "개)</li>";
        }
        echo "</ul>";
        
        // 가장 많은 이미지를 가진 행사 선택
        $targetEventId = $eventsWithImages[0]['event_id'];
        echo "<h2>🎯 테스트 대상: 행사 ID " . $targetEventId . "</h2>";
        
        // 해당 행사의 이미지 상세 정보
        $images = $db->fetchAll("SELECT * FROM event_images WHERE event_id = ? ORDER BY order_index", [$targetEventId]);
        
        echo "<h3>📸 이미지 상세 정보</h3>";
        echo "<ul>";
        foreach ($images as $image) {
            echo "<li>";
            echo "ID: " . $image['id'] . " | ";
            echo "경로: " . htmlspecialchars($image['image_path']) . " | ";
            echo "순서: " . $image['order_index'] . " | ";
            echo "상태: " . $image['status'];
            echo "</li>";
        }
        echo "</ul>";
        
        // 행사 기본 정보
        $event = $db->fetch("SELECT * FROM lectures WHERE id = ? AND content_type = 'event'", [$targetEventId]);
        if ($event) {
            echo "<h3>📋 행사 기본 정보</h3>";
            echo "<p><strong>제목:</strong> " . htmlspecialchars($event['title']) . "</p>";
            echo "<p><strong>시작일:</strong> " . $event['start_date'] . "</p>";
            echo "<p><strong>상태:</strong> " . $event['status'] . "</p>";
        }
        
    } else {
        echo "<p>이미지가 있는 행사가 없습니다.</p>";
    }
    
    // 2. 행사 190 확인
    echo "<h2>🔍 행사 190 이미지 확인</h2>";
    $images190 = $db->fetchAll("SELECT * FROM event_images WHERE event_id = 190");
    if (count($images190) > 0) {
        echo "<p>행사 190에 " . count($images190) . "개의 이미지가 있습니다.</p>";
        foreach ($images190 as $image) {
            echo "<p>- " . htmlspecialchars($image['image_path']) . "</p>";
        }
    } else {
        echo "<p>행사 190에는 이미지가 없습니다.</p>";
    }
    
    // 3. 전체 이미지 테이블 상태
    $totalImages = $db->fetch("SELECT COUNT(*) as total FROM event_images");
    echo "<h2>📈 전체 이미지 통계</h2>";
    echo "<p>전체 이미지 수: " . $totalImages['total'] . "개</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>