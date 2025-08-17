<?php
/**
 * 긴급 데이터베이스 연결 테스트
 */

echo "<h1>🚨 긴급 데이터베이스 연결 테스트</h1>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";

// 상수 정의
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('SRC_PATH')) {
    define('SRC_PATH', ROOT_PATH . '/src');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', SRC_PATH . '/config');
}

try {
    echo "<h3>1. Database 클래스 로드 테스트</h3>";
    require_once CONFIG_PATH . '/database.php';
    echo "<p>✅ database.php 로드 성공</p>";
    
    echo "<h3>2. Database 인스턴스 생성 테스트</h3>";
    $db = Database::getInstance();
    echo "<p>✅ Database 인스턴스 생성 성공</p>";
    
    echo "<h3>3. 간단한 쿼리 테스트</h3>";
    $result = $db->fetch("SELECT 'Database Connection OK' as status");
    
    if ($result) {
        echo "<p>✅ 쿼리 실행 성공</p>";
        echo "<ul>";
        echo "<li><strong>상태:</strong> " . htmlspecialchars($result['status']) . "</li>";
        echo "</ul>";
    } else {
        echo "<p>❌ 쿼리 결과 없음</p>";
    }
    
    echo "<h3>4. 사용자 테이블 접근 테스트</h3>";
    $userCount = $db->fetch("SELECT COUNT(*) as user_count FROM users");
    
    if ($userCount) {
        echo "<p>✅ 사용자 테이블 접근 성공</p>";
        echo "<p><strong>총 사용자 수:</strong> " . $userCount['user_count'] . "</p>";
    } else {
        echo "<p>❌ 사용자 테이블 접근 실패</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ 오류 발생</h3>";
    echo "<p><strong>오류 메시지:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>오류 파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<details><summary>스택 트레이스</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
}

// 직접 PDO 연결 테스트
echo "<h3>5. 직접 PDO 연결 테스트</h3>";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=TOPMKT;charset=utf8mb4', 'root', 'Dnlszkem1!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT 'Direct PDO Connection OK' as status");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>✅ 직접 PDO 연결 성공</p>";
    echo "<p><strong>결과:</strong> " . htmlspecialchars($result['status']) . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ 직접 PDO 연결 실패</p>";
    echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p>🎯 테스트 완료: " . date('Y-m-d H:i:s') . "</p>";
?>