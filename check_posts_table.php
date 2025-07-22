<?php
try {
    require_once '/var/www/html/topmkt/src/config/database.php';
    
    $db = Database::getInstance();
    
    echo "<h1>Posts 테이블 구조 확인</h1>";
    
    // 테이블 구조 확인
    $stmt = $db->query("DESCRIBE posts");
    $columns = $stmt->fetchAll();
    
    echo "<h2>현재 컬럼들:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 샘플 데이터 확인
    echo "<h2>샘플 데이터 (최신 5개):</h2>";
    $stmt = $db->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 5");
    $posts = $stmt->fetchAll();
    
    if (empty($posts)) {
        echo "<p>데이터가 없습니다.</p>";
    } else {
        echo "<table border='1'>";
        $keys = array_keys($posts[0]);
        echo "<tr>";
        foreach ($keys as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        foreach ($posts as $post) {
            echo "<tr>";
            foreach ($post as $value) {
                echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage();
}
?>