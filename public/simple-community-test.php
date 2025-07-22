<?php
/**
 * 간단한 커뮤니티 테스트
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

echo "<h1>단계별 커뮤니티 테스트</h1>";

try {
    echo "<p>1. 기본 경로 설정 완료</p>";
    
    require_once SRC_PATH . '/config/config.php';
    echo "<p>2. config.php 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/WebLogger.php';
    echo "<p>3. WebLogger 로드 완료</p>";
    
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    echo "<p>4. ResponseHelper 로드 완료</p>";
    
    require_once SRC_PATH . '/config/database.php';
    echo "<p>5. database.php 로드 완료</p>";
    
    require_once SRC_PATH . '/models/Post.php';
    echo "<p>6. Post 모델 로드 완료</p>";
    
    require_once SRC_PATH . '/models/User.php';
    echo "<p>7. User 모델 로드 완료</p>";
    
    // 여기서 에러가 발생하는지 확인
    require_once SRC_PATH . '/controllers/CommunityController.php';
    echo "<p>8. CommunityController 로드 완료</p>";
    
    // 인스턴스 생성
    $controller = new CommunityController();
    echo "<p>9. CommunityController 인스턴스 생성 완료</p>";
    
    echo "<p>✅ 모든 단계 성공 - 커뮤니티 시스템 정상</p>";
    
} catch (ParseError $e) {
    echo "<p>❌ 파싱 에러 발생</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<p>❌ Fatal Error 발생</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ 예외 발생</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<p>메시지: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
</style>