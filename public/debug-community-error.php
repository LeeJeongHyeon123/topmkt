<?php
/**
 * 커뮤니티 페이지 시스템 오류 디버깅
 */

// 에러 출력 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>커뮤니티 페이지 시스템 오류 디버깅</h1>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";

try {
    // 1. 기본 설정
    echo "<h2>1. 기본 설정</h2>";
    define('ROOT_PATH', dirname(__DIR__));
    define('SRC_PATH', ROOT_PATH . '/src');
    
    echo "<p>✅ ROOT_PATH: " . ROOT_PATH . "</p>";
    echo "<p>✅ SRC_PATH: " . SRC_PATH . "</p>";
    
    // 2. 필수 파일 로드 테스트
    echo "<h2>2. 필수 파일 로드 테스트</h2>";
    
    require_once SRC_PATH . '/config/config.php';
    echo "<p>✅ config.php 로드 완료</p>";
    
    require_once SRC_PATH . '/config/database.php';
    echo "<p>✅ database.php 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/WebLogger.php';
    echo "<p>✅ WebLogger 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    echo "<p>✅ ResponseHelper 로드 완료</p>";
    
    require_once SRC_PATH . '/models/Post.php';
    echo "<p>✅ Post 모델 로드 완료</p>";
    
    require_once SRC_PATH . '/models/User.php';
    echo "<p>✅ User 모델 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/ValidationHelper.php';
    echo "<p>✅ ValidationHelper 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/SearchHelper.php';
    echo "<p>✅ SearchHelper 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/PerformanceDebugger.php';
    echo "<p>✅ PerformanceDebugger 로드 완료</p>";
    
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    echo "<p>✅ AuthMiddleware 로드 완료</p>";
    
    require_once SRC_PATH . '/controllers/CommunityController.php';
    echo "<p>✅ CommunityController 로드 완료</p>";
    
    // 3. 세션 시작
    echo "<h2>3. 세션 시작</h2>";
    session_start();
    echo "<p>✅ 세션 시작 완료</p>";
    
    // 4. 로깅 시스템 초기화
    echo "<h2>4. 로깅 시스템 초기화</h2>";
    WebLogger::init();
    echo "<p>✅ 로깅 시스템 초기화 완료</p>";
    
    // 5. 데이터베이스 연결 테스트
    echo "<h2>5. 데이터베이스 연결 테스트</h2>";
    try {
        $db = Database::getInstance();
        echo "<p>✅ Database 인스턴스 생성 성공</p>";
        
        // 커뮤니티 관련 테이블 확인
        $tables = ['posts', 'users', 'comments', 'categories'];
        foreach ($tables as $table) {
            $result = $db->fetch("SHOW TABLES LIKE '{$table}'");
            if ($result) {
                echo "<p>✅ {$table} 테이블 존재</p>";
            } else {
                echo "<p>❌ {$table} 테이블 없음</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ 데이터베이스 연결 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // 6. CommunityController 인스턴스 생성 테스트
    echo "<h2>6. CommunityController 인스턴스 생성 테스트</h2>";
    try {
        $controller = new CommunityController();
        echo "<p>✅ CommunityController 인스턴스 생성 성공</p>";
    } catch (Exception $e) {
        echo "<p>❌ CommunityController 인스턴스 생성 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>파일: " . $e->getFile() . ":" . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    
    // 7. 실제 index 메서드 호출 테스트
    echo "<h2>7. CommunityController::index() 메서드 호출 테스트</h2>";
    
    if (isset($controller)) {
        ob_start();
        try {
            $controller->index();
            $output = ob_get_clean();
            echo "<p>✅ index 메서드 실행 완료</p>";
            echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;'>";
            echo "<strong>출력 결과 (첫 500자):</strong><br>";
            echo htmlspecialchars(substr($output, 0, 500));
            if (strlen($output) > 500) {
                echo "...";
            }
            echo "</div>";
        } catch (Exception $e) {
            ob_end_clean();
            echo "<p>❌ index 메서드 실행 중 오류 발생</p>";
            echo "<p><strong>오류:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>파일:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    }
    
    echo "<h2>✅ 디버깅 완료</h2>";
    
} catch (ParseError $e) {
    echo "<h2>❌ 파싱 에러</h2>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error</h2>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<h2>❌ 예외 발생</h2>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ddd; }
p { margin: 5px 0; }
</style>