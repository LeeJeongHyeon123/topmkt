<?php
/**
 * 탑마케팅 애플리케이션 진입점
 * 
 * 모든 요청은 이 파일을 통해 처리됩니다.
 */

// 상수 정의 (paths.php 로드 전이므로 여기서 정의)
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

// paths.php 로드
require_once CONFIG_PATH . '/paths.php';

// 강의 신청 API 디버깅을 위한 로그
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/lectures/') !== false && strpos($_SERVER['REQUEST_URI'], '/registration') !== false) {
    error_log("=== API 강의 신청 요청 디버깅 ===");
    error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
    error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'NOT_SET'));
    error_log("POST 데이터 존재: " . (empty($_POST) ? 'NO' : 'YES'));
    error_log("INPUT 스트림: " . file_get_contents('php://input'));
}

// 강의 등록 디버깅을 위한 로그
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/lectures/store') !== false) {
    file_put_contents(DEBUG_STORE_FLOW_LOG, "=== INDEX.PHP에서 캐치 ===\n", FILE_APPEND);
    file_put_contents(DEBUG_STORE_FLOW_LOG, "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
    file_put_contents(DEBUG_STORE_FLOW_LOG, "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
    file_put_contents(DEBUG_STORE_FLOW_LOG, "POST 데이터 존재: " . (empty($_POST) ? 'NO' : 'YES') . "\n", FILE_APPEND);
    file_put_contents(DEBUG_STORE_FLOW_LOG, "FILES 데이터 존재: " . (empty($_FILES) ? 'NO' : 'YES') . "\n", FILE_APPEND);
}

// 오류 표시 (프로덕션에서는 비활성화)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

try {
    // 설정 파일 로드
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once CONFIG_PATH . '/routes.php';
    
    // 헬퍼 클래스 로드
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    require_once SRC_PATH . '/helpers/LogAnalyzer.php';
    
    // 글로벌 에러 핸들러 등록
    GlobalErrorHandler::register();
    
    // 로깅 시스템 초기화 (설정 적용)
    WebLogger::init([
        'useUnifiedLog' => defined('LOG_USE_UNIFIED') ? LOG_USE_UNIFIED : true,
        'unifiedLogFile' => defined('LOG_UNIFIED_FILE') ? LOG_UNIFIED_FILE : 'topmkt_errors.log',
        'maxFileSize' => defined('LOG_MAX_FILE_SIZE') ? LOG_MAX_FILE_SIZE : 20 * 1024 * 1024
    ]);

    // 기본 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        // 세션 설정을 먼저 적용
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 0); // HTTPS 환경에서만 1
        ini_set('session.gc_maxlifetime', 2592000); // 30일
        ini_set('session.cookie_lifetime', 0); // 브라우저 종료시
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 1000);
        
        // PHP 버전에 따른 세션 시작
        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => false, // HTTPS 환경에서만 true
                'cookie_samesite' => 'Strict',
                'gc_maxlifetime' => 2592000, // 30일
                'cookie_lifetime' => 0 // 브라우저 종료시
            ]);
        } else {
            // PHP 7.3 미만에서는 cookie_samesite 지원 안함
            session_start();
        }
    }
    
    // 기존 Remember Token 쿠키가 있는 경우 정리 (마이그레이션 호환성)
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // JWT 토큰을 통한 자동 인증은 AuthMiddleware에서 처리

    // 요청 시작 로그
    $startTime = microtime(true);
    WebLogger::info('Request started', [
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'referer' => $_SERVER['HTTP_REFERER'] ?? ''
    ]);
    
    
    // 라우팅 처리
    
// 🔥 Ultra Think Mode: API 요청 디버깅
if (strpos($_SERVER['REQUEST_URI'], '/api/events/') !== false && strpos($_SERVER['REQUEST_URI'], 'previous-registration') !== false) {
    error_log("=== API 요청 추적 ===");
    error_log("URI: " . $_SERVER['REQUEST_URI']);
    error_log("METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("HOST: " . ($_SERVER['HTTP_HOST'] ?? 'UNKNOWN'));
    error_log("USER_AGENT: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'));
    error_log("AUTHORIZATION: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'NOT_SET'));
    error_log("Time: " . date('Y-m-d H:i:s'));
}

$router = new Router();
    $router->dispatch();
    
    // 요청 완료 로그
    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    WebLogger::performance('Request completed', $duration, [
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'uri' => $_SERVER['REQUEST_URI'] ?? ''
    ]);
    
} catch (Exception $e) {
    // 예외는 GlobalErrorHandler에서 처리됨
    // 하지만 여기에 도달한 경우 최종 안전망
    if (class_exists('WebLogger')) {
        WebLogger::critical('Unhandled Exception in index.php', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    // 개발 환경에서만 상세 에러 표시
    if ((getenv('APP_ENV') === 'development') || (defined('APP_DEBUG') && APP_DEBUG)) {
        echo "<h1>오류 발생</h1>";
        echo "<p>오류 메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>파일: " . $e->getFile() . "</p>";
        echo "<p>라인: " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>서비스 오류</h1><p>일시적인 오류가 발생했습니다. 잠시 후 다시 시도해주세요.</p>";
    }
} catch (Error $e) {
    if (class_exists('WebLogger')) {
        WebLogger::emergency('Fatal Error in index.php', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    if ((getenv('APP_ENV') === 'development') || (defined('APP_DEBUG') && APP_DEBUG)) {
        echo "<h1>치명적 오류 발생</h1>";
        echo "<p>오류 메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>파일: " . $e->getFile() . "</p>";
        echo "<p>라인: " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>시스템 오류</h1><p>시스템에 심각한 문제가 발생했습니다.</p>";
    }
}
?>