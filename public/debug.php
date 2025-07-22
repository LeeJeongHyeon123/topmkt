<?php
echo "<h1>디버그 페이지</h1>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";

// 경로 확인
echo "<h2>경로 정보</h2>";
echo "<p>ROOT_PATH: " . dirname(__DIR__) . "</p>";
echo "<p>SRC_PATH: " . dirname(__DIR__) . '/src' . "</p>";

// 파일 존재 확인
$src_path = dirname(__DIR__) . '/src';
echo "<h2>파일 존재 확인</h2>";
echo "<p>config.php: " . (file_exists($src_path . '/config/config.php') ? '존재' : '없음') . "</p>";
echo "<p>database.php: " . (file_exists($src_path . '/config/database.php') ? '존재' : '없음') . "</p>";
echo "<p>routes.php: " . (file_exists($src_path . '/config/routes.php') ? '존재' : '없음') . "</p>";
echo "<p>HomeController.php: " . (file_exists($src_path . '/controllers/HomeController.php') ? '존재' : '없음') . "</p>";

try {
    echo "<h2>설정 파일 로드 테스트</h2>";
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    
    require_once SRC_PATH . '/config/config.php';
    echo "<p>config.php 로드 성공</p>";
    
    require_once SRC_PATH . '/config/database.php';
    echo "<p>database.php 로드 성공</p>";
    
    require_once SRC_PATH . '/config/routes.php';
    echo "<p>routes.php 로드 성공</p>";
    
    echo "<h2>라우터 테스트</h2>";
    $router = new Router();
    echo "<p>라우터 생성 성공</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>오류: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
}
?>