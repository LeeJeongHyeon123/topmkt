<?php
/**
 * lecture_registrations 테이블 구조 확인
 */

// 상수 정의
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

echo "<h1>📋 lecture_registrations 테이블 구조 확인</h1>";

// 세션 시작
session_start();

try {
    // 기본 설정 로드
    require_once CONFIG_PATH . '/paths.php';
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    
    echo "✅ 시스템 로드 완료<br>";
    
    // 데이터베이스 연결
    $db = Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ 데이터베이스 연결 성공<br>";
    
    // 테이블 구조 확인
    echo "<h2>🔍 lecture_registrations 테이블 구조</h2>";
    $result = $connection->query("DESCRIBE lecture_registrations");
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
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
    }
    
    // 현재 데이터 확인
    echo "<h2>📊 현재 등록 데이터 (강의 167)</h2>";
    $currentData = $connection->query("SELECT * FROM lecture_registrations WHERE lecture_id = 167 ORDER BY created_at DESC LIMIT 5");
    
    if ($currentData && $currentData->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
        echo "<tr style='background: #f0f0f0;'>";
        
        // 헤더 출력
        $firstRow = $currentData->fetch_assoc();
        foreach (array_keys($firstRow) as $column) {
            echo "<th>" . htmlspecialchars($column) . "</th>";
        }
        echo "</tr>";
        
        // 첫 번째 행 출력
        echo "<tr>";
        foreach ($firstRow as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
        
        // 나머지 행 출력
        while ($row = $currentData->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "등록된 데이터가 없습니다.<br>";
    }
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
table { margin-top: 10px; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>