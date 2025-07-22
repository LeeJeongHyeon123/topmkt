<?php
require_once __DIR__ . '/../src/config/database.php';

try {
    $db = Database::getInstance();
    
    // lecture_instructors 테이블 존재 확인
    $result = $db->fetchAll("SHOW TABLES LIKE 'lecture_instructors'");
    
    if (empty($result)) {
        echo "❌ lecture_instructors 테이블이 존재하지 않습니다!\n";
        echo "테이블을 생성해야 합니다.\n";
    } else {
        echo "✅ lecture_instructors 테이블 존재\n";
        
        // 테이블 구조 확인
        $structure = $db->fetchAll("DESCRIBE lecture_instructors");
        echo "테이블 구조:\n";
        foreach ($structure as $col) {
            echo "- {$col['Field']}: {$col['Type']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage() . "\n";
}
?>