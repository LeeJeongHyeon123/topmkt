<?php
/**
 * 간단한 데이터베이스 연결 테스트
 */

try {
    $mysqli = new mysqli('127.0.0.1', 'root', 'Dnlszkem1!', 'TOPMKT');
    
    if ($mysqli->connect_error) {
        die('Connection failed: ' . $mysqli->connect_error);
    }
    
    echo "<h1>✅ 데이터베이스 연결 성공!</h1>";
    
    // 테이블 목록 조회
    $result = $mysqli->query("SHOW TABLES");
    echo "<h2>📊 테이블 목록</h2>";
    echo "<ul>";
    while ($row = $result->fetch_row()) {
        echo "<li>{$row[0]}</li>";
    }
    echo "</ul>";
    
    // event_images 테이블 확인
    $result = $mysqli->query("SHOW TABLES LIKE 'event_images'");
    if ($result->num_rows > 0) {
        echo "<h2>✅ event_images 테이블 존재</h2>";
        
        // 이벤트 182 이미지 확인
        $result = $mysqli->query("SELECT * FROM event_images WHERE event_id = 182");
        echo "<p>이벤트 182 이미지 개수: " . $result->num_rows . "</p>";
        
        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Event ID</th><th>Image Path</th><th>Alt Text</th><th>Sort Order</th><th>Created At</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['event_id']}</td>";
                echo "<td>{$row['image_path']}</td>";
                echo "<td>{$row['alt_text']}</td>";
                echo "<td>{$row['sort_order']}</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<h2>❌ event_images 테이블이 없습니다</h2>";
        echo "<p>테이블을 생성해야 합니다.</p>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<h1>❌ 오류 발생</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>