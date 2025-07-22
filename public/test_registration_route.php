<?php
/**
 * 🧪 기업 대시보드 라우트 직접 테스트
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>🧪 라우트 테스트</title>";
echo "<style>body{font-family:monospace;background:#000;color:#0f0;padding:20px;} .error{color:#f00;} .success{color:#0f0;} .warning{color:#fa0;} pre{background:#111;padding:15px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🧪 기업 대시보드 라우트 직접 테스트</h1>";

// 경로 설정
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('SRC_PATH', ROOT_PATH . '/src');

echo "<h2>1️⃣ 라우터 시뮬레이션</h2>";
echo "<pre>";

try {
    // 세션 시작
    session_start();
    
    // 모든 필요한 파일 로드
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/controllers/BaseController.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    require_once SRC_PATH . '/helpers/ResponseHelper.php';
    require_once SRC_PATH . '/services/EmailService.php';
    require_once SRC_PATH . '/controllers/RegistrationDashboardController.php';
    
    echo "<span class='success'>✅ 모든 필요한 파일 로드 성공</span>\n";
    
    // 컨트롤러 인스턴스 생성
    $controller = new RegistrationDashboardController();
    echo "<span class='success'>✅ RegistrationDashboardController 인스턴스 생성 성공</span>\n";
    
    echo "</pre>";
    
    echo "<h2>2️⃣ 인증 상태 확인</h2>";
    echo "<pre>";
    
    // 세션 정보 출력
    if (empty($_SESSION)) {
        echo "<span class='warning'>⚠️ 세션이 비어있음 - 로그인 필요</span>\n";
        echo "로그인 페이지로 리다이렉트 될 예정\n";
    } else {
        echo "<span class='success'>✅ 세션 데이터 존재:</span>\n";
        foreach ($_SESSION as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                echo "  $key: $value\n";
            } else {
                echo "  $key: " . gettype($value) . "\n";
            }
        }
    }
    
    echo "</pre>";
    
    echo "<h2>3️⃣ 실제 index() 메소드 실행</h2>";
    echo "<pre>";
    
    // 실제 메소드 실행 (출력 버퍼링으로 캡처)
    ob_start();
    
    try {
        $controller->index();
        $output = ob_get_contents();
        ob_end_clean();
        
        echo "<span class='success'>✅ index() 메소드 실행 성공</span>\n";
        echo "출력 길이: " . strlen($output) . " 바이트\n";
        
        if (strlen($output) > 0) {
            echo "<span class='success'>✅ HTML 출력 생성됨</span>\n";
            echo "출력 시작 부분 (첫 200자):\n";
            echo htmlspecialchars(substr($output, 0, 200)) . "...\n";
        } else {
            echo "<span class='warning'>⚠️ 출력 없음 (리다이렉트 또는 오류)</span>\n";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "<span class='error'>❌ index() 메소드 실행 실패</span>\n";
        echo "오류: " . $e->getMessage() . "\n";
        echo "파일: " . $e->getFile() . "\n";
        echo "라인: " . $e->getLine() . "\n";
        echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
    }
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ 초기화 실패: " . $e->getMessage() . "</span>\n";
    echo "파일: " . $e->getFile() . "\n";
    echo "라인: " . $e->getLine() . "\n";
}

echo "<h2>4️⃣ 해결 방안</h2>";
echo "<div style='color:#fff;padding:15px;background:#222;border-radius:5px;'>";
echo "<h3>🔧 확인된 문제와 해결:</h3>";
echo "<ul>";
echo "<li><strong>BaseController.php 누락</strong> → ✅ 생성 완료</li>";
echo "<li><strong>로그인 상태 확인</strong> → 로그인 후 재테스트 필요</li>";
echo "<li><strong>권한 검증</strong> → ROLE_USER도 허용하도록 수정 완료</li>";
echo "</ul>";
echo "<h3>🚀 다음 단계:</h3>";
echo "<p>1. 로그인 후 <a href='/registrations' style='color:#0f0;'>👉 /registrations</a> 재접속</p>";
echo "<p>2. 또는 <a href='/emergency_registration_debug.php' style='color:#0f0;'>👉 추가 진단 도구</a> 사용</p>";
echo "</div>";

echo "</body></html>";
?>