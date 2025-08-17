<?php
/**
 * 사용자 통계 API 직접 테스트
 */

echo "<h1>📊 사용자 통계 API 테스트</h1>";
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

try {
    // 세션 시작 및 관리자 설정
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'ROLE_SUPER_ADMIN';
    $_SESSION['user_name'] = 'Admin';
    
    echo "<p>✅ 관리자 세션 설정 완료</p>";
    
    // 필요한 파일들 로드
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once CONFIG_PATH . '/routes.php';
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/controllers/AdminController.php';
    
    echo "<p>✅ 모든 파일 로드 성공</p>";
    
    $controller = new AdminController();
    echo "<p>✅ AdminController 인스턴스 생성 성공</p>";
    
    echo "<h3>getUserStats 메서드 직접 실행:</h3>";
    
    // 출력 버퍼링으로 JSON 응답 캐치
    ob_start();
    $controller->getUserStats();
    $jsonOutput = ob_get_contents();
    ob_end_clean();
    
    echo "<p>✅ getUserStats 실행 완료</p>";
    echo "<p><strong>JSON 출력:</strong></p>";
    echo "<pre>" . htmlspecialchars($jsonOutput) . "</pre>";
    
    // JSON 파싱 테스트
    $data = json_decode($jsonOutput, true);
    
    if ($data) {
        echo "<p>✅ JSON 파싱 성공</p>";
        echo "<h4>파싱된 데이터:</h4>";
        echo "<ul>";
        if (isset($data['success'])) {
            echo "<li><strong>성공 상태:</strong> " . ($data['success'] ? 'true' : 'false') . "</li>";
        }
        if (isset($data['stats'])) {
            echo "<li><strong>통계 데이터:</strong></li>";
            echo "<ul>";
            foreach ($data['stats'] as $key => $value) {
                echo "<li><strong>{$key}:</strong> {$value}</li>";
            }
            echo "</ul>";
        }
        if (isset($data['error'])) {
            echo "<li><strong>오류:</strong> " . htmlspecialchars($data['error']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ JSON 파싱 실패</p>";
        $jsonError = json_last_error_msg();
        echo "<p><strong>JSON 오류:</strong> " . $jsonError . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ 오류 발생</h3>";
    echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<details><summary>스택 트레이스</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
}

echo "<hr>";
echo "<p>🎯 테스트 완료: " . date('Y-m-d H:i:s') . "</p>";
?>