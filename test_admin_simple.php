<?php
/**
 * 간단한 관리자 페이지 테스트
 */

// 에러 출력 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 간단한 관리자 페이지 테스트</h1>";

try {
    // 1. 경로 설정
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(__FILE__)); // 현재 파일의 디렉토리
        echo "✅ ROOT_PATH 정의: " . ROOT_PATH . "<br>";
    }
    
    if (!defined('SRC_PATH')) {
        define('SRC_PATH', ROOT_PATH . '/src');
        echo "✅ SRC_PATH 정의: " . SRC_PATH . "<br>";
    }
    
    // 2. 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "✅ 세션 시작<br>";
    }
    
    // 3. 관리자 권한으로 가짜 세션 설정
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN',
        'email' => '2jeonghyeon2@naver.com'
    ];
    echo "✅ 관리자 세션 설정<br>";
    
    // 4. 데이터베이스 클래스 로드 테스트
    echo "<h2>4. 데이터베이스 연결 테스트</h2>";
    require_once SRC_PATH . '/config/database.php';
    echo "✅ Database 클래스 로드<br>";
    
    $db = Database::getInstance();
    echo "✅ Database 인스턴스 생성<br>";
    
    // 5. AuthMiddleware 로드 테스트
    echo "<h2>5. AuthMiddleware 로드 테스트</h2>";
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    echo "✅ AuthMiddleware 로드<br>";
    
    // 6. AdminController 로드 테스트
    echo "<h2>6. AdminController 로드 테스트</h2>";
    require_once SRC_PATH . '/controllers/AdminController.php';
    echo "✅ AdminController 로드<br>";
    
    // 7. AdminController 인스턴스 생성 테스트
    echo "<h2>7. AdminController 인스턴스 생성 테스트</h2>";
    $admin = new AdminController();
    echo "✅ AdminController 인스턴스 생성 성공<br>";
    
    // 8. userList 메서드 호출 테스트
    echo "<h2>8. userList 메서드 호출 테스트</h2>";
    ob_start();
    $admin->userList();
    $output = ob_get_contents();
    ob_end_clean();
    
    if (strlen($output) > 0) {
        echo "✅ userList 메서드 실행 성공 - " . strlen($output) . " 바이트 출력<br>";
        echo "<h3>출력 미리보기 (처음 500자):</h3>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
    } else {
        echo "❌ userList 메서드 출력 없음<br>";
    }
    
} catch (Exception $e) {
    echo "❌ 예외 발생: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "스택 추적:<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "❌ 치명적 오류: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "스택 추적:<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p>테스트 완료: " . date('Y-m-d H:i:s') . "</p>";
?>