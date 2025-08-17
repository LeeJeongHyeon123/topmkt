<?php
/**
 * 관리자 사용자 페이지 직접 테스트
 */

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
    // 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // 관리자 세션 설정
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'ROLE_SUPER_ADMIN';
    $_SESSION['user_name'] = 'Admin';
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    // 필요한 파일들 로드
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once CONFIG_PATH . '/routes.php';
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/controllers/AdminController.php';
    
    // AdminController 실행
    $controller = new AdminController();
    $controller->userList();
    
} catch (Exception $e) {
    echo "<h1>오류 발생</h1>";
    echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<details><summary>스택 트레이스</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
} catch (Error $e) {
    echo "<h1>Fatal Error 발생</h1>";
    echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<details><summary>스택 트레이스</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
}
?>