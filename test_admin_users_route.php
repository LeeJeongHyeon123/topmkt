<?php
/**
 * 관리자 사용자 라우트 직접 테스트
 */

// 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// 라우터 로드
require_once CONFIG_PATH . '/routes.php';

echo "🔍 관리자 사용자 라우트 테스트\n";
echo "================================\n\n";

// 테스트할 URI들
$testUris = [
    'GET:/admin/users',
    'GET:/admin/users/data',
    'POST:/admin/users/1/status'
];

$router = new Router();

// 리플렉션을 사용하여 private routes 속성에 접근
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

echo "📋 등록된 라우트 확인:\n";
foreach ($routes as $key => $route) {
    if (strpos($key, 'admin/users') !== false) {
        echo "✅ {$key} => {$route[0]}::{$route[1]}\n";
    }
}

echo "\n🔧 AdminController 파일 존재 확인:\n";
$adminControllerPath = SRC_PATH . '/controllers/AdminController.php';
if (file_exists($adminControllerPath)) {
    echo "✅ AdminController.php 파일 존재: {$adminControllerPath}\n";
    
    // AdminController 로드 테스트
    require_once $adminControllerPath;
    
    if (class_exists('AdminController')) {
        echo "✅ AdminController 클래스 로드 성공\n";
        
        $adminController = new AdminController();
        if (method_exists($adminController, 'userList')) {
            echo "✅ userList 메서드 존재\n";
        } else {
            echo "❌ userList 메서드 없음\n";
        }
    } else {
        echo "❌ AdminController 클래스 로드 실패\n";
    }
} else {
    echo "❌ AdminController.php 파일 없음: {$adminControllerPath}\n";
}

echo "\n🔧 사용자 목록 뷰 파일 존재 확인:\n";
$viewPath = SRC_PATH . '/views/admin/users/list.php';
if (file_exists($viewPath)) {
    echo "✅ 뷰 파일 존재: {$viewPath}\n";
} else {
    echo "❌ 뷰 파일 없음: {$viewPath}\n";
}

echo "\n🔧 실제 라우팅 시뮬레이션:\n";
// $_SERVER 변수 설정
$_SERVER['REQUEST_URI'] = '/admin/users';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "요청 URI: {$_SERVER['REQUEST_URI']}\n";
echo "요청 메서드: {$_SERVER['REQUEST_METHOD']}\n";

// 라우트 키 생성 (라우터와 동일한 로직)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);
$uri = rtrim($uri, '/');
if (empty($uri)) {
    $uri = '/';
}
$method = $_SERVER['REQUEST_METHOD'];
$routeKey = $method . ':' . $uri;

echo "생성된 라우트 키: {$routeKey}\n";

if (isset($routes[$routeKey])) {
    echo "✅ 라우트 매치 성공!\n";
    echo "실행할 컨트롤러: {$routes[$routeKey][0]}::{$routes[$routeKey][1]}\n";
} else {
    echo "❌ 라우트 매치 실패\n";
    echo "등록된 라우트들:\n";
    foreach ($routes as $key => $route) {
        echo "  - {$key}\n";
    }
}

echo "\n🎯 테스트 완료!\n";
?>