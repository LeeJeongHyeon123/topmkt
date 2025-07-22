<?php
require_once __DIR__ . '/../src/config/database.php';

try {
    $db = Database::getInstance();
    
    echo "=== lecture_instructors 테이블 구조 확인 ===\n\n";
    
    // 1. 테이블 존재 여부 확인
    $tables = $db->fetchAll("SHOW TABLES LIKE 'lecture_instructors'");
    if (empty($tables)) {
        echo "❌ lecture_instructors 테이블이 존재하지 않습니다\!\n\n";
        
        // 모든 테이블 목록 보기
        echo "📋 현재 존재하는 테이블들:\n";
        $allTables = $db->fetchAll("SHOW TABLES");
        foreach ($allTables as $table) {
            $tableName = array_values($table)[0];
            echo "- $tableName\n";
        }
        
        echo "\n🔧 lecture_instructors 테이블을 생성해야 합니다\!\n";
        
        // 테이블 생성 SQL
        echo "\n📝 필요한 CREATE TABLE 문:\n";
        echo "CREATE TABLE lecture_instructors (\n";
        echo "  id INT AUTO_INCREMENT PRIMARY KEY,\n";
        echo "  lecture_id INT NOT NULL,\n";
        echo "  instructor_name VARCHAR(255) NOT NULL,\n";
        echo "  instructor_info TEXT,\n";
        echo "  sort_order INT DEFAULT 1,\n";
        echo "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        echo "  INDEX idx_lecture_id (lecture_id)\n";
        echo ");\n";
        
        exit;
    }
    
    echo "✅ lecture_instructors 테이블이 존재합니다\!\n\n";
    
    // 2. 테이블 구조 확인
    echo "📋 테이블 구조:\n";
    $columns = $db->fetchAll("DESCRIBE lecture_instructors");
    
    foreach ($columns as $column) {
        printf("%-20s %-15s %-10s %-10s %-15s %s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Key'], 
            $column['Default'], 
            $column['Extra']
        );
    }
    
    echo "\n✅ 테이블 구조 확인 완료\!\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
}
EOF < /dev/null
