<?php
/**
 * 라우터 dispatch 직접 테스트
 */

echo "<h1>🚀 라우터 Dispatch 직접 테스트</h1>";
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
    // 필요한 파일들 로드
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once CONFIG_PATH . '/routes.php';
    
    echo "<p>✅ 모든 설정 파일 로드 성공</p>";
    
    // 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // 테스트용 $_SERVER 설정 (홈 페이지)
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    echo "<p><strong>테스트 URI:</strong> {$_SERVER['REQUEST_URI']}</p>";
    echo "<p><strong>테스트 메서드:</strong> {$_SERVER['REQUEST_METHOD']}</p>";
    
    $router = new Router();
    echo "<p>✅ Router 인스턴스 생성 성공</p>";
    
    echo "<h3>라우터 Dispatch 실행:</h3>";
    
    // 출력 버퍼링으로 dispatch 결과 캐치
    ob_start();
    $router->dispatch();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<p>✅ Dispatch 실행 완료</p>";
    echo "<p><strong>출력 길이:</strong> " . strlen($output) . " bytes</p>";
    
    if (strlen($output) > 0) {
        echo "<p>✅ 정상적인 출력 생성됨</p>";
        
        if (strlen($output) < 1000) {
            echo "<details><summary>🔍 전체 출력 내용</summary>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
            echo "</details>";
        } else {
            echo "<details><summary>🔍 출력 내용 미리보기 (처음 500자)</summary>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
            echo "</details>";
        }
    } else {
        echo "<p>⚠️ 출력이 비어있음</p>";
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