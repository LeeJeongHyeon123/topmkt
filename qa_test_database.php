<?php
/**
 * QA 테스트: 데이터베이스 스키마 및 연결
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/config/database.php';

echo "🧪 === 데이터베이스 QA 테스트 ===\n";
echo str_repeat("=", 60) . "\n\n";

$testResults = [];
$totalTests = 0;
$passedTests = 0;

function runTest($testName, $testFunction) {
    global $testResults, $totalTests, $passedTests;
    $totalTests++;
    
    try {
        $result = $testFunction();
        if ($result === true) {
            $passedTests++;
            $testResults[] = "✅ PASS: $testName";
            echo "✅ PASS: $testName\n";
        } else {
            $testResults[] = "❌ FAIL: $testName - $result";
            echo "❌ FAIL: $testName - $result\n";
        }
    } catch (Exception $e) {
        $testResults[] = "❌ ERROR: $testName - " . $e->getMessage();
        echo "❌ ERROR: $testName - " . $e->getMessage() . "\n";
    }
}

// 테스트 1: 데이터베이스 연결
runTest("데이터베이스 연결", function() {
    $db = Database::getInstance();
    return $db->getConnection() ? true : "연결 실패";
});

// 테스트 2: notices 테이블 존재 확인
runTest("notices 테이블 존재", function() {
    $db = Database::getInstance();
    $result = $db->fetch("SHOW TABLES LIKE 'notices'");
    return $result ? true : "notices 테이블이 존재하지 않음";
});

// 테스트 3: notice_comments 테이블 존재 확인
runTest("notice_comments 테이블 존재", function() {
    $db = Database::getInstance();
    $result = $db->fetch("SHOW TABLES LIKE 'notice_comments'");
    return $result ? true : "notice_comments 테이블이 존재하지 않음";
});

// 테스트 4: notices 테이블 스키마 검증
runTest("notices 테이블 스키마", function() {
    $db = Database::getInstance();
    $columns = $db->fetchAll("DESCRIBE notices");
    $requiredColumns = ['id', 'user_id', 'company_id', 'title', 'content', 'is_featured', 'view_count', 'comment_count', 'created_at', 'updated_at'];
    
    $existingColumns = array_column($columns, 'Field');
    foreach ($requiredColumns as $column) {
        if (!in_array($column, $existingColumns)) {
            return "필수 컬럼 '$column'이 존재하지 않음";
        }
    }
    return true;
});

// 테스트 5: notice_comments 테이블 스키마 검증
runTest("notice_comments 테이블 스키마", function() {
    $db = Database::getInstance();
    $columns = $db->fetchAll("DESCRIBE notice_comments");
    $requiredColumns = ['id', 'notice_id', 'user_id', 'parent_id', 'content', 'created_at', 'updated_at'];
    
    $existingColumns = array_column($columns, 'Field');
    foreach ($requiredColumns as $column) {
        if (!in_array($column, $existingColumns)) {
            return "필수 컬럼 '$column'이 존재하지 않음";
        }
    }
    return true;
});

// 테스트 6: 외래키 제약조건 확인
runTest("외래키 제약조건", function() {
    $db = Database::getInstance();
    
    // notices 테이블의 user_id 외래키
    $fks = $db->fetchAll("
        SELECT CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'TOPMKT' 
        AND TABLE_NAME = 'notices' 
        AND REFERENCED_TABLE_NAME = 'users'
    ");
    
    if (empty($fks)) {
        return "notices.user_id 외래키가 설정되지 않음";
    }
    
    // notice_comments 테이블의 notice_id 외래키
    $fks = $db->fetchAll("
        SELECT CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'TOPMKT' 
        AND TABLE_NAME = 'notice_comments' 
        AND REFERENCED_TABLE_NAME = 'notices'
    ");
    
    if (empty($fks)) {
        return "notice_comments.notice_id 외래키가 설정되지 않음";
    }
    
    return true;
});

// 테스트 7: 인덱스 확인
runTest("데이터베이스 인덱스", function() {
    $db = Database::getInstance();
    
    // notices 테이블 인덱스
    $indexes = $db->fetchAll("SHOW INDEX FROM notices");
    $indexNames = array_column($indexes, 'Key_name');
    
    $requiredIndexes = ['PRIMARY', 'idx_user_id', 'idx_company_id'];
    foreach ($requiredIndexes as $index) {
        if (!in_array($index, $indexNames)) {
            return "notices 테이블에 '$index' 인덱스가 없음";
        }
    }
    
    // notice_comments 테이블 인덱스
    $indexes = $db->fetchAll("SHOW INDEX FROM notice_comments");
    $indexNames = array_column($indexes, 'Key_name');
    
    $requiredIndexes = ['PRIMARY', 'idx_notice_id', 'idx_user_id', 'idx_parent_id'];
    foreach ($requiredIndexes as $index) {
        if (!in_array($index, $indexNames)) {
            return "notice_comments 테이블에 '$index' 인덱스가 없음";
        }
    }
    
    return true;
});

// 테스트 8: 데이터 타입 검증
runTest("데이터 타입 검증", function() {
    $db = Database::getInstance();
    
    // notices 테이블 데이터 타입 확인
    $columns = $db->fetchAll("SHOW COLUMNS FROM notices");
    $columnTypes = [];
    foreach ($columns as $column) {
        $columnTypes[$column['Field']] = $column['Type'];
    }
    
    $expectedTypes = [
        'id' => 'int',
        'title' => 'varchar',
        'content' => 'longtext',
        'is_featured' => 'tinyint',
        'view_count' => 'int',
        'comment_count' => 'int'
    ];
    
    foreach ($expectedTypes as $column => $expectedType) {
        if (!isset($columnTypes[$column]) || strpos($columnTypes[$column], $expectedType) === false) {
            return "notices.$column의 데이터 타입이 올바르지 않음: " . ($columnTypes[$column] ?? 'NULL');
        }
    }
    
    return true;
});

// 테스트 9: 기본값 확인
runTest("기본값 확인", function() {
    $db = Database::getInstance();
    
    $columns = $db->fetchAll("SHOW COLUMNS FROM notices");
    $defaults = [];
    foreach ($columns as $column) {
        $defaults[$column['Field']] = $column['Default'];
    }
    
    // 기본값 검증
    if ($defaults['is_featured'] !== '0') {
        return "is_featured의 기본값이 0이 아님: " . $defaults['is_featured'];
    }
    
    if ($defaults['view_count'] !== '0') {
        return "view_count의 기본값이 0이 아님: " . $defaults['view_count'];
    }
    
    if ($defaults['comment_count'] !== '0') {
        return "comment_count의 기본값이 0이 아님: " . $defaults['comment_count'];
    }
    
    return true;
});

// 테스트 10: 관련 테이블 존재 확인
runTest("관련 테이블 존재 확인", function() {
    $db = Database::getInstance();
    
    $requiredTables = ['users', 'company_profiles'];
    foreach ($requiredTables as $table) {
        $result = $db->fetch("SHOW TABLES LIKE '$table'");
        if (!$result) {
            return "$table 테이블이 존재하지 않음";
        }
    }
    
    return true;
});

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 데이터베이스 QA 테스트 완료\n";
echo "📊 결과: $passedTests/$totalTests 통과 (" . round(($passedTests/$totalTests)*100, 1) . "%)\n";

if ($passedTests === $totalTests) {
    echo "✅ 모든 데이터베이스 테스트 통과!\n";
} else {
    echo "❌ " . ($totalTests - $passedTests) . "개 테스트 실패\n";
    echo "\n실패한 테스트들:\n";
    foreach ($testResults as $result) {
        if (strpos($result, '❌') === 0) {
            echo $result . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
?>