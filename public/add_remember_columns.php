<?php
/**
 * Remember Token 컬럼 추가 스크립트
 */

// 설정 파일 로드
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

// 보안 토큰 검증
$token = $_GET['token'] ?? '';
if ($token !== 'add_remember_columns_2025') {
    http_response_code(403);
    die('접근이 거부되었습니다. 올바른 토큰이 필요합니다.');
}

try {
    $db = Database::getInstance();
    
    echo "<h1>Remember Token 컬럼 추가</h1>";
    
    // 현재 테이블 구조 확인
    echo "<h2>현재 users 테이블 구조:</h2>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM users");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // remember_token 컬럼이 이미 있는지 확인
    $hasRememberToken = false;
    $hasRememberExpires = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'remember_token') {
            $hasRememberToken = true;
        }
        if ($column['Field'] === 'remember_expires') {
            $hasRememberExpires = true;
        }
    }
    
    // 컬럼 추가
    if (!$hasRememberToken) {
        $db->execute("ALTER TABLE users ADD COLUMN remember_token VARCHAR(64) DEFAULT NULL AFTER last_login");
        echo "✅ remember_token 컬럼이 추가되었습니다.<br>";
    } else {
        echo "ℹ️ remember_token 컬럼이 이미 존재합니다.<br>";
    }
    
    if (!$hasRememberExpires) {
        $db->execute("ALTER TABLE users ADD COLUMN remember_expires DATETIME DEFAULT NULL AFTER remember_token");
        echo "✅ remember_expires 컬럼이 추가되었습니다.<br>";
    } else {
        echo "ℹ️ remember_expires 컬럼이 이미 존재합니다.<br>";
    }
    
    // 인덱스 추가
    try {
        $db->execute("ALTER TABLE users ADD INDEX idx_remember_token (remember_token)");
        echo "✅ remember_token 인덱스가 추가되었습니다.<br>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "ℹ️ remember_token 인덱스가 이미 존재합니다.<br>";
        } else {
            echo "⚠️ 인덱스 추가 실패: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br><h2>수정된 users 테이블 구조:</h2>";
    $newColumns = $db->fetchAll("SHOW COLUMNS FROM users");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($newColumns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h2>✅ 모든 작업이 완료되었습니다!</h2>";
    echo "<p>이제 Remember Token 기능이 정상적으로 작동할 것입니다.</p>";
    echo "<p><a href='/'>메인 페이지로 이동</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ 오류 발생</h2>";
    echo "<p>오류 메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
}
?>