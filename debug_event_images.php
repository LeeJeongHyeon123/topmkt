<?php
/**
 * 이벤트 이미지 디버깅 스크립트
 */

// 경로 설정
define('ROOT_PATH', '/workspace/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';

echo "<h1>🔍 이벤트 이미지 디버깅 (이벤트 ID: 182)</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

try {
    $db = Database::getInstance();
    $eventId = 182;
    
    echo "<div class='debug-section'>";
    echo "<h2>📊 이벤트 이미지 테이블 상태</h2>";
    
    // event_images 테이블 존재 확인
    $tableExists = $db->query("SHOW TABLES LIKE 'event_images'");
    
    if ($tableExists) {
        echo "<span class='success'>✅ event_images 테이블이 존재합니다.</span><br><br>";
        
        // 테이블 구조 확인
        echo "<h3>테이블 구조</h3>";
        $columns = $db->fetchAll("DESCRIBE event_images");
        echo "<table>";
        echo "<tr><th>컬럼명</th><th>타입</th><th>NULL 허용</th><th>키</th><th>기본값</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 이벤트 182 이미지 확인
        echo "<h3>이벤트 ID 182 이미지 데이터</h3>";
        $images = $db->fetchAll("SELECT * FROM event_images WHERE event_id = ?", [$eventId]);
        
        if (!empty($images)) {
            echo "<span class='success'>✅ " . count($images) . "개의 이미지 발견</span><br><br>";
            echo "<table>";
            echo "<tr><th>ID</th><th>이미지 경로</th><th>Alt Text</th><th>Sort Order</th><th>생성일</th></tr>";
            foreach ($images as $image) {
                echo "<tr>";
                echo "<td>{$image['id']}</td>";
                echo "<td>{$image['image_path']}</td>";
                echo "<td>{$image['alt_text']}</td>";
                echo "<td>{$image['sort_order']}</td>";
                echo "<td>{$image['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<span class='warning'>⚠️ 이벤트 ID 182에 저장된 이미지가 없습니다.</span><br>";
            
            // 전체 이미지 확인
            echo "<h3>전체 이벤트 이미지</h3>";
            $allImages = $db->fetchAll("SELECT * FROM event_images ORDER BY event_id DESC LIMIT 10");
            
            if (!empty($allImages)) {
                echo "<table>";
                echo "<tr><th>Event ID</th><th>이미지 경로</th><th>생성일</th></tr>";
                foreach ($allImages as $image) {
                    echo "<tr>";
                    echo "<td>{$image['event_id']}</td>";
                    echo "<td>{$image['image_path']}</td>";
                    echo "<td>{$image['created_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<span class='error'>❌ 전체 이벤트 이미지가 없습니다.</span>";
            }
        }
        
    } else {
        echo "<span class='error'>❌ event_images 테이블이 존재하지 않습니다.</span>";
        
        // 테이블 생성 제안
        echo "<h3>🔧 테이블 생성 SQL</h3>";
        echo "<pre>";
        echo "CREATE TABLE event_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES lectures(id) ON DELETE CASCADE
);";
        echo "</pre>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='debug-section'>";
    echo "<span class='error'>❌ 오류: " . $e->getMessage() . "</span>";
    echo "</div>";
}

echo "<div class='debug-section'>";
echo "<h2>📝 해결 방안</h2>";
echo "<ol>";
echo "<li>이벤트 등록 시 이미지 업로드가 제대로 처리되지 않는 경우</li>";
echo "<li>event_images 테이블에 데이터가 저장되지 않는 경우</li>";
echo "<li>getEventImages 메서드에서 데이터를 불러오지 못하는 경우</li>";
echo "</ol>";
echo "</div>";
?>