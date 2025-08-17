<?php
/**
 * 관리자 사용자 페이지 디버깅 스크립트
 */

// 에러 출력 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 관리자 사용자 페이지 디버깅</h1>";
echo "<hr>";

try {
    echo "<h2>1. 경로 설정 확인</h2>";
    
    // ROOT_PATH 정의 전에 체크
    if (defined('ROOT_PATH')) {
        echo "⚠️ ROOT_PATH가 이미 정의되어 있습니다: " . ROOT_PATH . "<br>";
    } else {
        define('ROOT_PATH', dirname(__DIR__));
        echo "✅ ROOT_PATH 정의: " . ROOT_PATH . "<br>";
    }
    
    if (defined('SRC_PATH')) {
        echo "⚠️ SRC_PATH가 이미 정의되어 있습니다: " . SRC_PATH . "<br>";
    } else {
        define('SRC_PATH', ROOT_PATH . '/src');
        echo "✅ SRC_PATH 정의: " . SRC_PATH . "<br>";
    }
    
    echo "<h2>2. 필수 파일 존재 확인</h2>";
    $requiredFiles = [
        SRC_PATH . '/config/database.php',
        SRC_PATH . '/middlewares/AuthMiddleware.php',
        SRC_PATH . '/controllers/AdminController.php',
        SRC_PATH . '/views/admin/users/list_simple.php'
    ];
    
    foreach ($requiredFiles as $file) {
        if (file_exists($file)) {
            echo "✅ " . basename($file) . " 존재<br>";
        } else {
            echo "❌ " . basename($file) . " 누락: " . $file . "<br>";
        }
    }
    
    echo "<h2>3. 관리자 컨트롤러 로드 테스트</h2>";
    require_once SRC_PATH . '/controllers/AdminController.php';
    echo "✅ AdminController 로드 성공<br>";
    
    echo "<h2>4. 사용자 메서드 테스트</h2>";
    $admin = new AdminController();
    echo "✅ AdminController 인스턴스 생성 성공<br>";
    
    echo "<h2>5. 사용자 뷰 파일 읽기 테스트</h2>";
    $viewFile = SRC_PATH . '/views/admin/users/list_simple.php';
    if (file_exists($viewFile)) {
        $content = file_get_contents($viewFile);
        $lines = substr_count($content, "\n");
        echo "✅ 뷰 파일 읽기 성공 - " . $lines . " 라인<br>";
        
        // PHP 구문 검사
        $tempFile = tempnam(sys_get_temp_dir(), 'php_syntax_check');
        file_put_contents($tempFile, $content);
        $output = shell_exec("php -l $tempFile 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✅ PHP 구문 검사 통과<br>";
        } else {
            echo "❌ PHP 구문 오류: " . htmlspecialchars($output) . "<br>";
        }
        unlink($tempFile);
    }
    
    echo "<h2>6. 직접 뷰 렌더링 테스트</h2>";
    
    // 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // 가짜 관리자 세션 설정 (테스트용)
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    
    echo "✅ 테스트 세션 설정 완료<br>";
    
    // 뷰 파일 직접 include
    ob_start();
    try {
        include $viewFile;
        $output = ob_get_clean();
        echo "✅ 뷰 파일 렌더링 성공 - " . strlen($output) . " 바이트<br>";
        
        // 첫 200자 출력
        echo "<h3>출력 미리보기:</h3>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 200)) . "...</pre>";
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ 뷰 렌더링 오류: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "스택 추적: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } catch (Error $e) {
        ob_end_clean();
        echo "❌ 뷰 렌더링 치명적 오류: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "스택 추적: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }

} catch (Exception $e) {
    echo "❌ 디버깅 중 예외 발생: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "스택 추적: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "❌ 디버깅 중 치명적 오류: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "스택 추적: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p>디버깅 완료: " . date('Y-m-d H:i:s') . "</p>";
?>