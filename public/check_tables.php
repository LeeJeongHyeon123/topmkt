<?php
/**
 * 현재 테이블 상태 확인 스크립트
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';

echo "<h1>🔍 데이터베이스 테이블 상태 확인</h1>\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 1. 모든 테이블 목록
    echo "<h2>1. 현재 테이블 목록</h2>\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "📋 {$table}<br>\n";
    }
    
    // 2. 강의 관련 테이블 확인
    echo "<h2>2. 강의 관련 테이블 확인</h2>\n";
    $lectureRelatedTables = ['lectures', 'lecture_registrations', 'lecture_categories'];
    
    foreach ($lectureRelatedTables as $tableName) {
        $exists = $pdo->query("SHOW TABLES LIKE '{$tableName}'")->fetch();
        if ($exists) {
            echo "✅ {$tableName} 테이블 존재<br>\n";
            
            // 데이터 수 확인
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM {$tableName}")->fetchColumn();
                echo "   📊 데이터 수: {$count}개<br>\n";
            } catch (Exception $e) {
                echo "   ❌ 데이터 조회 오류: " . $e->getMessage() . "<br>\n";
            }
        } else {
            echo "❌ {$tableName} 테이블 없음<br>\n";
        }
    }
    
    // 3. 외래키 제약 조건 확인
    echo "<h2>3. 외래키 제약 조건 확인</h2>\n";
    try {
        $constraints = $pdo->query("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_SCHEMA = 'TOPMKT'
            AND TABLE_NAME LIKE '%lecture%'
        ")->fetchAll();
        
        if (empty($constraints)) {
            echo "ℹ️ 강의 관련 외래키 제약 조건 없음<br>\n";
        } else {
            foreach ($constraints as $constraint) {
                echo "🔗 {$constraint['TABLE_NAME']}.{$constraint['COLUMN_NAME']} → {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']}<br>\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ 외래키 조회 오류: " . $e->getMessage() . "<br>\n";
    }
    
    // 4. lectures 테이블 구조 확인 (있는 경우)
    if (in_array('lectures', $tables)) {
        echo "<h2>4. lectures 테이블 구조</h2>\n";
        $columns = $pdo->query("DESCRIBE lectures")->fetchAll();
        echo "<table border='1' style='border-collapse: collapse;'>\n";
        echo "<tr><th>필드명</th><th>타입</th><th>NULL</th><th>키</th><th>기본값</th></tr>\n";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ 오류 발생</h2>\n";
    echo "<p style='color: red;'>{$e->getMessage()}</p>\n";
}
?>