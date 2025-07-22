<?php
// 🔥 Ultra Think Mode: API 디버깅 엔드포인트
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

header('Content-Type: application/json');

// 요청 정보 수집
$requestInfo = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
    'query_string' => $_SERVER['QUERY_STRING'] ?? '',
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'UNKNOWN',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
    'authorization' => $_SERVER['HTTP_AUTHORIZATION'] ?? 'NOT_SET',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'UNKNOWN',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'UNKNOWN',
    'current_file' => __FILE__,
    'working_directory' => getcwd()
];

try {
    // 설정 파일들 로드
    require_once SRC_PATH . '/config/config.php';
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/config/routes.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    $configStatus = 'OK';
    
    // 라우터 테스트
    $router = new Router();
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    $routerInfo = [
        'total_routes' => count($routes),
        'target_route_exists' => isset($routes['GET:/api/events/{id}/previous-registration']),
        'target_handler' => $routes['GET:/api/events/{id}/previous-registration'] ?? null
    ];
    
    // EventController 테스트
    require_once SRC_PATH . '/controllers/EventController.php';
    
    $controllerInfo = [
        'class_exists' => class_exists('EventController'),
        'method_exists' => method_exists('EventController', 'getPreviousRegistration')
    ];
    
    // 인증 상태 확인
    $authInfo = [
        'is_logged_in' => AuthMiddleware::isLoggedIn(),
        'current_user_id' => AuthMiddleware::getCurrentUserId(),
        'session_exists' => session_status() === PHP_SESSION_ACTIVE
    ];
    
} catch (Exception $e) {
    $configStatus = 'ERROR: ' . $e->getMessage();
    $routerInfo = null;
    $controllerInfo = null;
    $authInfo = null;
}

// 시뮬레이션된 API 호출
$simulatedCall = null;
if (isset($_GET['test_api']) && $_GET['test_api'] === '1') {
    try {
        $controller = new EventController();
        
        // 출력 버퍼링
        ob_start();
        $controller->getPreviousRegistration(198);
        $apiOutput = ob_get_contents();
        ob_end_clean();
        
        $simulatedCall = [
            'success' => true,
            'output' => $apiOutput,
            'output_length' => strlen($apiOutput)
        ];
    } catch (Exception $e) {
        $simulatedCall = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

$response = [
    'debug_endpoint' => 'API 디버깅 정보',
    'request_info' => $requestInfo,
    'config_status' => $configStatus,
    'router_info' => $routerInfo,
    'controller_info' => $controllerInfo,
    'auth_info' => $authInfo,
    'simulated_call' => $simulatedCall,
    'instructions' => [
        'basic_test' => '?test=1',
        'api_test' => '?test_api=1',
        'with_auth' => 'Authorization 헤더 포함하여 호출'
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>