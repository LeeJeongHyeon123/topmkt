<?php
header('Content-Type: text/plain');

echo "웹 환경 테스트\n";

// PHP 확장 확인
echo "MySQLi 확장: " . (extension_loaded('mysqli') ? 'OK' : 'NOT FOUND') . "\n";
echo "PDO 확장: " . (extension_loaded('pdo') ? 'OK' : 'NOT FOUND') . "\n";

if (!extension_loaded('mysqli')) {
    echo "❌ MySQLi 확장 없음 - 여기서 중단\n";
    exit;
}

// 실제 데이터베이스 설정 파일 읽기
try {
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    
    require_once SRC_PATH . '/config/paths.php';
    require_once SRC_PATH . '/config/config.php';
    
    echo "✅ 설정 파일 로드 완료\n";
    
    // Database 클래스 로드 및 연결 테스트
    require_once SRC_PATH . '/config/database.php';
    
    echo "데이터베이스 클래스 로드 시도...\n";
    
    $db = Database::getInstance();
    echo "✅ 데이터베이스 연결 성공!\n";
    
    // 간단한 쿼리 테스트
    $result = $db->query("SELECT 1 as test");
    if ($result) {
        echo "✅ 쿼리 실행 성공\n";
    }
    
    // 강의 테이블 확인
    $result = $db->query("SELECT COUNT(*) as count FROM lectures WHERE id = 167");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "강의 167 존재 여부: " . ($row['count'] > 0 ? 'YES' : 'NO') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>