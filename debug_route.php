<?php
/**
 * 라우팅 디버깅 스크립트
 */

// POST 요청 시뮬레이션
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/user/delete-account';

// 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

// 라우팅 파일 로드
require_once SRC_PATH . '/config/routes.php';

echo "=== 라우팅 디버깅 ===\n\n";

echo "1. REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "2. REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n\n";

echo "3. 라우팅 규칙 확인:\n";
foreach ($routes as $route => $handler) {
    if (strpos($route, 'delete-account') !== false) {
        echo "   - $route => " . print_r($handler, true) . "\n";
    }
}

echo "\n4. 매칭 테스트:\n";

// 라우팅 매칭 로직 시뮬레이션
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routeKey = $method . ':' . $uri;

echo "   - 생성된 라우트 키: $routeKey\n";

if (isset($routes[$routeKey])) {
    echo "   - ✅ 매칭 성공: " . print_r($routes[$routeKey], true) . "\n";
} else {
    echo "   - ❌ 매칭 실패\n";
    echo "   - 사용 가능한 라우트들:\n";
    foreach ($routes as $route => $handler) {
        if (strpos($route, 'POST:') === 0) {
            echo "     * $route\n";
        }
    }
}
?>