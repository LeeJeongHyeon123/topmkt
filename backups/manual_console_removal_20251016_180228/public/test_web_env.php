<?php
/**
 * 웹 환경 PHP 모듈 테스트
 */

echo "<h1>웹 환경 PHP 모듈 테스트</h1>";

echo "<h2>1. PHP 버전</h2>";
echo "<p>" . phpversion() . "</p>";

echo "<h2>2. MySQLi 확장 상태</h2>";
if (extension_loaded('mysqli')) {
    echo "<p style='color: green;'>✅ MySQLi 확장 로드됨</p>";
} else {
    echo "<p style='color: red;'>❌ MySQLi 확장 로드되지 않음</p>";
}

echo "<h2>3. MySQLi 클래스 존재 여부</h2>";
if (class_exists('mysqli')) {
    echo "<p style='color: green;'>✅ MySQLi 클래스 사용 가능</p>";
} else {
    echo "<p style='color: red;'>❌ MySQLi 클래스 사용 불가</p>";
}

echo "<h2>4. 직접 데이터베이스 연결 테스트</h2>";
try {
    $connection = new mysqli('127.0.0.1', 'root', 'Dnlszkem1!', 'TOPMKT', 3306);
    
    if ($connection->connect_error) {
        echo "<p style='color: red;'>❌ 연결 실패: " . $connection->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ 웹에서 MySQLi 연결 성공!</p>";
        $connection->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 예외 발생: " . $e->getMessage() . "</p>";
}

echo "<h2>5. 로드된 확장 목록</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<pre>" . implode(", ", $extensions) . "</pre>";
?>