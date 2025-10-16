<?php
/**
 * 데이터베이스 연결 테스트
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 설정 파일 로드
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>데이터베이스 연결 테스트</h1>";

try {
    echo "<h2>1. Database 클래스 로드</h2>";
    echo "<p style='color: green;'>✅ Database 클래스 로드 성공</p>";
    
    echo "<h2>2. Database 인스턴스 생성</h2>";
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Database 인스턴스 생성 성공</p>";
    
    echo "<h2>3. 간단한 쿼리 테스트</h2>";
    $result = $db->fetch("SELECT 1 as test");
    echo "<p style='color: green;'>✅ 쿼리 실행 성공: " . json_encode($result) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>