<?php
/**
 * 관리자 사용자 데이터 API 테스트
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
    echo "<h1>🧪 /admin/users/data API 테스트</h1>";
    echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";
    
    // 세션 시작 및 관리자 설정
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = 4; // 우리집탄이 ID
    $_SESSION['user_role'] = 'ROLE_ADMIN';
    $_SESSION['user_name'] = '우리집탄이';
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    echo "<p>✅ 관리자 세션 설정 완료 (우리집탄이)</p>";
    
    // 필요한 파일들 로드
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/database.php';
    require_once CONFIG_PATH . '/routes.php';
    require_once SRC_PATH . '/helpers/WebLogger.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/helpers/GlobalErrorHandler.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    echo "<p>✅ 모든 파일 로드 성공</p>";
    
    // $_SERVER 변수 설정 (라우터 테스트용)
    $_SERVER['REQUEST_URI'] = '/admin/users/data?status=all&role=all&search=&page=1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $_GET['status'] = 'all';
    $_GET['role'] = 'all';
    $_GET['search'] = '';
    $_GET['page'] = '1';
    
    echo "<h3>사용자 데이터 API 테스트:</h3>";
    echo "<p><strong>URI:</strong> {$_SERVER['REQUEST_URI']}</p>";
    echo "<p><strong>Method:</strong> {$_SERVER['REQUEST_METHOD']}</p>";
    echo "<p><strong>Parameters:</strong></p>";
    echo "<ul>";
    foreach ($_GET as $key => $value) {
        echo "<li><strong>{$key}:</strong> " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul>";
    
    // 라우터 인스턴스 생성 및 디스패치
    $router = new Router();
    
    // 출력 버퍼링으로 라우터 응답 캐치
    ob_start();
    $router->dispatch();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<p>✅ 라우터 디스패치 완료</p>";
    echo "<p><strong>응답 크기:</strong> " . strlen($output) . " bytes</p>";
    
    if (strlen($output) > 0) {
        echo "<h4>API 응답:</h4>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . (strlen($output) > 1000 ? '...(truncated)' : '') . "</pre>";
        
        // JSON 파싱 시도
        $data = json_decode($output, true);
        if ($data) {
            echo "<h4>JSON 파싱 성공:</h4>";
            echo "<ul>";
            if (isset($data['success'])) {
                echo "<li><strong>성공 상태:</strong> " . ($data['success'] ? 'true' : 'false') . "</li>";
            }
            if (isset($data['users'])) {
                echo "<li><strong>사용자 수:</strong> " . count($data['users']) . "명</li>";
                if (count($data['users']) > 0) {
                    $firstUser = $data['users'][0];
                    echo "<li><strong>첫 번째 사용자:</strong></li>";
                    echo "<ul>";
                    foreach (['id', 'nickname', 'email', 'role', 'status'] as $field) {
                        if (isset($firstUser[$field])) {
                            echo "<li><strong>{$field}:</strong> " . htmlspecialchars($firstUser[$field]) . "</li>";
                        }
                    }
                    echo "</ul>";
                }
            }
            if (isset($data['total'])) {
                echo "<li><strong>총 사용자 수:</strong> " . $data['total'] . "</li>";
            }
            if (isset($data['page'])) {
                echo "<li><strong>현재 페이지:</strong> " . $data['page'] . "</li>";
            }
            if (isset($data['error'])) {
                echo "<li><strong>오류:</strong> " . htmlspecialchars($data['error']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>❌ JSON 파싱 실패 - 일반 텍스트 응답</p>";
            $jsonError = json_last_error_msg();
            echo "<p><strong>JSON 오류:</strong> " . $jsonError . "</p>";
        }
    } else {
        echo "<p>❌ API에서 응답 없음</p>";
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