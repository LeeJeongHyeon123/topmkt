<?php
// 133번 강의 description 디버깅 스크립트
try {
    $mysqli = new mysqli('localhost', 'root', 'Dnlszkem1!', 'TOPMKT', 3306, '/var/lib/mysql/mysql.sock');
    
    if ($mysqli->connect_error) {
        throw new Exception('연결 실패: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    $result = $mysqli->query("SELECT id, title, description FROM lectures WHERE id = 133");
    $lecture = $result->fetch_assoc();
    
    echo "<h2>133번 강의 데이터 디버깅</h2>";
    echo "<h3>원본 데이터:</h3>";
    echo "<pre>";
    echo "ID: " . $lecture['id'] . "\n";
    echo "Title: " . $lecture['title'] . "\n";
    echo "Description length: " . strlen($lecture['description']) . "\n";
    echo "Description: " . $lecture['description'] . "\n";
    echo "</pre>";
    
    echo "<h3>PHP 검증:</h3>";
    echo "<pre>";
    echo "isset(description): " . (isset($lecture['description']) ? 'true' : 'false') . "\n";
    echo "!empty(description): " . (!empty($lecture['description']) ? 'true' : 'false') . "\n";
    echo "trim(description) !== '': " . (trim($lecture['description']) !== '' ? 'true' : 'false') . "\n";
    echo "</pre>";
    
    echo "<h3>처리된 값:</h3>";
    echo "<pre>";
    $processedDescription = (isset($lecture['description']) && trim($lecture['description']) !== '') 
        ? substr(strip_tags($lecture['description']), 0, 100) . '...' 
        : (isset($lecture['title']) ? $lecture['title'] . ' 강의에 참여해보세요!' : '탑마케팅 강의에 참여해보세요!');
    
    echo "Processed: " . $processedDescription . "\n";
    echo "JSON encoded: " . json_encode($processedDescription) . "\n";
    echo "</pre>";
    
    echo "<h3>JavaScript 변수로 출력:</h3>";
    echo "<script>";
    echo "const lectureDescription = " . json_encode($processedDescription) . ";";
    echo "console.log('lectureDescription:', lectureDescription);";
    echo "</script>";
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage();
}
?>