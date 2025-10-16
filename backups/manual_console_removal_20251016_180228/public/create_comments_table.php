<?php
/**
 * 댓글 테이블 생성 스크립트
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // SQL 파일 읽기
    $sql = file_get_contents(dirname(__DIR__) . '/database/create_comments_table.sql');
    
    // SQL 실행
    $db->exec($sql);
    
    echo "✅ 댓글 테이블이 성공적으로 생성되었습니다.\n";
    
    // 테이블 확인
    $stmt = $db->query("SHOW TABLES LIKE 'comments'");
    if ($stmt->fetch()) {
        echo "✅ comments 테이블이 존재합니다.\n";
        
        // 컬럼 정보 출력
        echo "\n📋 테이블 구조:\n";
        $stmt = $db->query("DESCRIBE comments");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo sprintf("  - %s: %s %s\n", 
                $column['Field'], 
                $column['Type'],
                $column['Null'] === 'NO' ? 'NOT NULL' : 'NULL'
            );
        }
    }
    
} catch (PDOException $e) {
    echo "❌ 오류가 발생했습니다: " . $e->getMessage() . "\n";
}