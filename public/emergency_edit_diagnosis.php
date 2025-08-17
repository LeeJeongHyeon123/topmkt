<?php
/**
 * 긴급 편집 기능 완전 진단
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚨 긴급 공지사항 편집 기능 완전 진단</h1>";

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

// 기본 파일 시스템 체크
echo "<h2>📁 1. 파일 시스템 체크</h2>";

$files = [
    'edit.php' => SRC_PATH . '/views/notices/edit.php',
    'NoticeController.php' => SRC_PATH . '/controllers/NoticeController.php',
    'routes.php' => SRC_PATH . '/config/routes.php'
];

foreach ($files as $name => $path) {
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    echo "<p>- $name: " . ($exists ? "✅ 존재 ($size bytes)" : "❌ 없음") . "</p>";
}

// 라우팅 시스템 체크
echo "<h2>🛣️ 2. 라우팅 시스템 체크</h2>";
require_once SRC_PATH . '/config/routes.php';

$router = new Router();

// 라우트 정보를 확인하기 위해 내부 routes 속성에 접근
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

$editRoutes = [];
foreach ($routes as $pattern => $route) {
    if (strpos($pattern, 'edit') !== false) {
        $editRoutes[$pattern] = $route;
    }
}

echo "<p>편집 관련 라우트:</p>";
echo "<ul>";
foreach ($editRoutes as $pattern => $route) {
    echo "<li><strong>$pattern</strong> → {$route[0]}::{$route[1]}</li>";
}
echo "</ul>";

// NoticeController 메서드 체크
echo "<h2>🎮 3. NoticeController 메서드 체크</h2>";
require_once SRC_PATH . '/controllers/NoticeController.php';

$controller = new NoticeController();
$methods = get_class_methods($controller);
$noticeMethods = array_filter($methods, function($method) {
    return strpos($method, 'show') === 0 || strpos($method, 'update') === 0;
});

echo "<p>공지사항 관련 메서드:</p>";
echo "<ul>";
foreach ($noticeMethods as $method) {
    echo "<li>✅ $method</li>";
}
echo "</ul>";

// 실제 라우팅 테스트
echo "<h2>🧪 4. 라우팅 테스트</h2>";

// $_SERVER 변수 시뮬레이션
$_SERVER['REQUEST_URI'] = '/notices/10/edit';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "<p>테스트 URI: {$_SERVER['REQUEST_URI']}</p>";
echo "<p>테스트 METHOD: {$_SERVER['REQUEST_METHOD']}</p>";

// 라우팅 매칭 테스트
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$routeKey = $method . ':' . rtrim($uri, '/');

echo "<p>생성된 라우트 키: <strong>$routeKey</strong></p>";

// 정적 라우트 체크
$staticMatch = isset($routes[$routeKey]);
echo "<p>정적 라우트 매치: " . ($staticMatch ? "✅ 매치됨" : "❌ 안됨") . "</p>";

if (!$staticMatch) {
    // 동적 라우트 체크
    echo "<p>동적 라우트 검색 중...</p>";
    
    foreach ($routes as $pattern => $route) {
        if (strpos($pattern, '{id}') !== false) {
            $regexPattern = preg_replace('/\{[^}]+\}/', '(\d+)', $pattern);
            $regexPattern = '#^' . str_replace('/', '\/', $regexPattern) . '$#';
            
            if (preg_match($regexPattern, $routeKey)) {
                echo "<p>✅ 동적 라우트 매치: <strong>$pattern</strong> → {$route[0]}::{$route[1]}</p>";
                break;
            }
        }
    }
}

// 권한 및 데이터 체크
echo "<h2>🔐 5. 권한 및 데이터 체크</h2>";

try {
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/models/Notice.php';
    
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    echo "<p>로그인 상태: " . ($isLoggedIn ? "✅ 로그인됨" : "❌ 로그아웃됨") . "</p>";
    
    $noticeModel = new Notice();
    $notice = $noticeModel->getById(10);
    echo "<p>공지사항 10번: " . ($notice ? "✅ 존재" : "❌ 없음") . "</p>";
    
    if ($notice) {
        echo "<p>제목: " . htmlspecialchars($notice['title']) . "</p>";
        echo "<p>작성자 ID: " . ($notice['company_id'] ?? 'N/A') . "</p>";
        
        if ($isLoggedIn) {
            $currentUserId = AuthMiddleware::getCurrentUserId();
            $isOwner = $noticeModel->isOwner(10, $currentUserId);
            echo "<p>편집 권한: " . ($isOwner ? "✅ 있음" : "❌ 없음") . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ 오류: " . $e->getMessage() . "</p>";
}

// 실제 컨트롤러 호출 시뮬레이션
echo "<h2>🔧 6. 컨트롤러 호출 시뮬레이션</h2>";

try {
    if ($isLoggedIn && $notice) {
        echo "<p>컨트롤러 showEdit(10) 시뮬레이션...</p>";
        
        // 출력 버퍼 시작
        ob_start();
        
        // showEdit 호출 시뮬레이션 (실제로는 호출하지 않음)
        echo "<p>⏳ 실제 호출은 권한 문제로 스키핑...</p>";
        
        $output = ob_get_clean();
        echo $output;
        
    } else {
        echo "<p>❌ 로그인하지 않았거나 데이터가 없어 컨트롤러 호출 불가</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ 컨트롤러 호출 오류: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>🎯 진단 결론</h2>";
echo "<p><strong>이 진단 결과를 통해 편집 기능 문제의 정확한 원인을 파악할 수 있습니다.</strong></p>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";
?>