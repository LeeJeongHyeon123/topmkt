<?php
/**
 * forgot-password 직접 테스트
 */

// 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

// 요청 시뮬레이션
$_SERVER['REQUEST_URI'] = '/auth/forgot-password';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "<h1>🔍 forgot-password 직접 테스트</h1>";

try {
    // 필요한 파일들 로드
    require_once SRC_PATH . '/config/config.php';
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/config/routes.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    
    echo "<h2>✅ 파일 로드 성공</h2>";
    
    // 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // CSRF 토큰 생성
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    echo "<h2>✅ 세션 시작 성공</h2>";
    
    // 라우터 생성 및 실행
    $router = new Router();
    echo "<h2>✅ 라우터 생성 성공</h2>";
    
    echo "<h2>🚀 디스패치 실행</h2>";
    
    // 출력 버퍼링 시작
    ob_start();
    $router->dispatch();
    $output = ob_get_clean();
    
    echo "<div style='background: lightgreen; padding: 15px; margin: 10px 0;'>";
    echo "<h3>✅ 디스패치 성공!</h3>";
    echo "<p><strong>출력 길이:</strong> " . strlen($output) . " 바이트</p>";
    
    if (strlen($output) > 0) {
        echo "<details><summary>출력 내용 미리보기 (처음 1000자)</summary>";
        echo "<pre style='background: white; padding: 10px; max-height: 300px; overflow: auto;'>";
        echo htmlspecialchars(substr($output, 0, 1000));
        echo "</pre></details>";
        
        // HTML 출력인지 확인
        if (strpos($output, '<!DOCTYPE html>') !== false) {
            echo "<p>✅ <strong>HTML 페이지가 성공적으로 생성되었습니다!</strong></p>";
            echo "<p><a href='https://www.topmktx.com/auth/forgot-password' target='_blank'>실제 페이지 테스트하기</a></p>";
        }
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: lightcoral; padding: 15px;'>";
    echo "<h3>❌ 오류 발생</h3>";
    echo "<p><strong>메시지:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>파일:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>라인:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>