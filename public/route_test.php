<?php
header('Content-Type: text/plain');

echo "라우팅 시스템 테스트\n";

// 환경 설정
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/lectures/167/registration';

try {
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    
    require_once SRC_PATH . '/config/paths.php';
    require_once SRC_PATH . '/config/config.php';
    require_once SRC_PATH . '/config/routes.php';
    
    echo "라우팅 파일 로드 완료\n";
    echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    
    $router = new Router();
    echo "라우터 인스턴스 생성 완료\n";
    
    echo "\n=== 라우팅 디스패치 시작 ===\n";
    $router->dispatch();
    echo "=== 라우팅 디스패치 완료 ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Exception: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
} catch (Error $e) {
    echo "\n❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>