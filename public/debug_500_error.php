<?php
/**
 * 500 오류 실시간 디버깅
 */

echo "<h1>🚨 500 오류 실시간 디버깅</h1>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";

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

echo "<h3>1. 관리자 페이지 직접 접근 시뮬레이션</h3>";

try {
    // 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // 관리자 세션 설정
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'ROLE_SUPER_ADMIN';
    $_SESSION['user_name'] = 'Admin';
    
    echo "<p>✅ 관리자 세션 설정 완료</p>";
    
    // 필요한 파일들 로드
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once CONFIG_PATH . '/routes.php';
    
    echo "<p>✅ 설정 파일 로드 성공</p>";
    
    // 헬퍼 클래스 로드
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    
    echo "<p>✅ 헬퍼 클래스 로드 성공</p>";
    
    // AdminController 직접 로드 및 실행
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/controllers/AdminController.php';
    
    echo "<p>✅ AdminController 로드 성공</p>";
    
    $controller = new AdminController();
    echo "<p>✅ AdminController 인스턴스 생성 성공</p>";
    
    echo "<h3>2. userList 메서드 직접 실행 테스트</h3>";
    
    // 출력 버퍼링
    ob_start();
    $controller->userList();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<p>✅ userList 메서드 실행 성공</p>";
    echo "<p><strong>출력 길이:</strong> " . strlen($output) . " bytes</p>";
    
    if (strlen($output) > 100) {
        echo "<details><summary>🔍 출력 미리보기 (처음 300자)</summary>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 300)) . "...</pre>";
        echo "</details>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ 오류 발생!</h3>";
    echo "<p><strong>오류 메시지:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>오류 파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<details><summary>스택 트레이스</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
} catch (Error $e) {
    echo "<h3>❌ Fatal Error 발생!</h3>";
    echo "<p><strong>오류 메시지:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>오류 파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<details><summary>스택 트레이스</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
}

echo "<hr>";
echo "<p>🎯 디버깅 완료: " . date('Y-m-d H:i:s') . "</p>";
?>