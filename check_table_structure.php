<?php
/**
 * lecture_instructors 테이블 구조 확인 스크립트
 */

// 데이터베이스 연결 클래스 로드
require_once '/var/www/html/topmkt/src/config/database.php';

try {
    // 데이터베이스 연결
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>lecture_instructors 테이블 구조 확인</h2>";
    
    // 1. 테이블 존재 여부 확인
    echo "<h3>1. 테이블 존재 여부 확인</h3>";
    $showTablesQuery = "SHOW TABLES LIKE 'lecture_instructors'";
    $result = $connection->query($showTablesQuery);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ lecture_instructors 테이블이 존재합니다.</p>";
        
        // 2. 테이블 구조 확인 (DESCRIBE)
        echo "<h3>2. 테이블 구조 (DESCRIBE)</h3>";
        $describeQuery = "DESCRIBE lecture_instructors";
        $describeResult = $connection->query($describeQuery);
        
        if ($describeResult) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            while ($row = $describeResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>DESCRIBE 쿼리 실행 실패: " . $connection->error . "</p>";
        }
        
        // 3. 테이블 생성 구문 확인 (SHOW CREATE TABLE)
        echo "<h3>3. 테이블 생성 구문 (SHOW CREATE TABLE)</h3>";
        $createTableQuery = "SHOW CREATE TABLE lecture_instructors";
        $createResult = $connection->query($createTableQuery);
        
        if ($createResult) {
            $createRow = $createResult->fetch_assoc();
            echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>";
            echo htmlspecialchars($createRow['Create Table']);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>SHOW CREATE TABLE 쿼리 실행 실패: " . $connection->error . "</p>";
        }
        
        // 4. 테이블 데이터 샘플 확인
        echo "<h3>4. 테이블 데이터 샘플 (최근 5개 행)</h3>";
        $sampleQuery = "SELECT * FROM lecture_instructors ORDER BY created_at DESC LIMIT 5";
        $sampleResult = $connection->query($sampleQuery);
        
        if ($sampleResult) {
            if ($sampleResult->num_rows > 0) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                
                // 헤더 출력
                $firstRow = $sampleResult->fetch_assoc();
                echo "<tr>";
                foreach ($firstRow as $key => $value) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                
                // 첫 번째 행 출력
                echo "<tr>";
                foreach ($firstRow as $key => $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
                
                // 나머지 행 출력
                while ($row = $sampleResult->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $key => $value) {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p style='color: orange;'>테이블에 데이터가 없습니다.</p>";
            }
        } else {
            echo "<p style='color: red;'>샘플 데이터 조회 실패: " . $connection->error . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ lecture_instructors 테이블이 존재하지 않습니다.</p>";
        
        // 테이블이 없는 경우 생성할 CREATE TABLE 문 제공
        echo "<h3>권장 CREATE TABLE 문</h3>";
        echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>";
        echo "CREATE TABLE lecture_instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT NOT NULL,
    instructor_name VARCHAR(255) NOT NULL,
    instructor_info TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    INDEX idx_lecture_id (lecture_id),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        echo "</pre>";
    }
    
    // 5. 모든 테이블 목록 확인
    echo "<h3>5. 현재 데이터베이스의 모든 테이블</h3>";
    $allTablesQuery = "SHOW TABLES";
    $allTablesResult = $connection->query($allTablesQuery);
    
    if ($allTablesResult) {
        echo "<ul>";
        while ($tableRow = $allTablesResult->fetch_row()) {
            echo "<li>" . htmlspecialchars($tableRow[0]) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>테이블 목록 조회 실패: " . $connection->error . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>오류 발생: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>