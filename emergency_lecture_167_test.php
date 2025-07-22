<?php
/**
 * 긴급 강의 167 페이지 테스트
 */

// 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

echo "=== 긴급 강의 167 페이지 디버깅 ===\n\n";

// 기본 경로 확인
echo "1. 파일 시스템 확인:\n";
echo "- index.php 존재: " . (file_exists('public/index.php') ? 'YES' : 'NO') . "\n";
echo "- LectureController 존재: " . (file_exists('src/controllers/LectureController.php') ? 'YES' : 'NO') . "\n";
echo "- routes.php 존재: " . (file_exists('src/config/routes.php') ? 'YES' : 'NO') . "\n\n";

// 직접 컨트롤러 테스트
try {
    require_once 'src/config/config.php';
    require_once 'src/config/database.php';
    require_once 'src/controllers/LectureController.php';
    
    echo "2. 컨트롤러 직접 테스트:\n";
    
    $controller = new LectureController();
    
    // 출력 버퍼링
    ob_start();
    
    // show 메소드 직접 호출
    $controller->show(167);
    
    $output = ob_get_clean();
    
    if (empty($output)) {
        echo "- 컨트롤러 출력: 비어있음 (오류 발생 가능)\n";
    } else {
        echo "- 컨트롤러 출력: " . strlen($output) . " bytes\n";
        echo "- HTML 포함 여부: " . (strpos($output, '<html') !== false ? 'YES' : 'NO') . "\n";
    }
    
} catch (Exception $e) {
    echo "- 컨트롤러 오류: " . $e->getMessage() . "\n";
    echo "- 파일: " . $e->getFile() . "\n";
    echo "- 라인: " . $e->getLine() . "\n";
}

echo "\n3. 라우터 시뮬레이션:\n";

// 환경 변수 설정
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/lectures/167';
$_SERVER['SERVER_NAME'] = 'www.topmktx.com';
$_SERVER['HTTPS'] = 'on';

try {
    require_once 'src/config/routes.php';
    
    $router = new Router();
    
    echo "- Router 클래스 로드: SUCCESS\n";
    echo "- REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "- REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
    
    // route 메소드가 있는지 확인
    if (method_exists($router, 'route')) {
        echo "- route 메소드 존재: YES\n";
    } else {
        echo "- route 메소드 존재: NO\n";
    }
    
} catch (Exception $e) {
    echo "- 라우터 오류: " . $e->getMessage() . "\n";
}

// Apache 설정 확인
echo "\n4. Apache 설정 확인:\n";
echo "- DocumentRoot: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'UNKNOWN') . "\n";
echo "- 현재 디렉토리: " . __DIR__ . "\n";

// .htaccess 파일 확인
if (file_exists('public/.htaccess')) {
    echo "- public/.htaccess 존재: YES\n";
    $htaccess = file_get_contents('public/.htaccess');
    if (strpos($htaccess, 'RewriteRule') !== false) {
        echo "- RewriteRule 포함: YES\n";
    } else {
        echo "- RewriteRule 포함: NO\n";
    }
} else {
    echo "- public/.htaccess 존재: NO\n";
}

if (file_exists('.htaccess')) {
    echo "- 루트 .htaccess 존재: YES\n";
} else {
    echo "- 루트 .htaccess 존재: NO\n";
}
?>