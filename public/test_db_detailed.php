<?php
/**
 * 데이터베이스 연결 상세 테스트
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';

echo "<h1>데이터베이스 연결 상세 테스트</h1>";

echo "<h2>1. 직접 MySQLi 연결 테스트</h2>";
echo "<p>호스트: 127.0.0.1</p>";
echo "<p>사용자: root</p>";
echo "<p>비밀번호: Dnlszkem1!</p>";
echo "<p>데이터베이스: topmkt</p>";

try {
    $connection = new mysqli('127.0.0.1', 'root', 'Dnlszkem1!', 'TOPMKT', 3306);
    
    if ($connection->connect_error) {
        echo "<p style='color: red;'>❌ 연결 실패: " . $connection->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ 직접 연결 성공!</p>";
        
        // 간단한 쿼리 테스트
        $result = $connection->query("SELECT 1 as test");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p style='color: green;'>✅ 쿼리 테스트 성공: " . json_encode($row) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ 쿼리 테스트 실패</p>";
        }
        
        $connection->close();
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 예외 발생: " . $e->getMessage() . "</p>";
}

echo "<h2>2. Database 클래스 테스트</h2>";
try {
    require_once CONFIG_PATH . '/database.php';
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Database 클래스 연결 성공!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database 클래스 연결 실패: " . $e->getMessage() . "</p>";
}
?>