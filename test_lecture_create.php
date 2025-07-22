<?php
// 강의 생성 페이지 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 기본 설정
define('ROOT_PATH', '/workspace/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

// 세션 시작
session_start();

// 테스트용 사용자 설정 (실제로는 로그인 세션에서 가져옴)
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'admin';

try {
    // LectureController 로드 테스트
    require_once SRC_PATH . '/controllers/LectureController.php';
    
    echo "✅ LectureController 로드 성공<br>";
    
    // 컨트롤러 인스턴스 생성 테스트
    $controller = new LectureController();
    echo "✅ LectureController 인스턴스 생성 성공<br>";
    
    // create 메소드 존재 확인
    if (method_exists($controller, 'create')) {
        echo "✅ create 메소드 존재<br>";
        
        // create 메소드 호출 테스트 (실제 출력은 캐치)
        ob_start();
        $controller->create();
        $output = ob_get_clean();
        
        if (strlen($output) > 0) {
            echo "✅ create 메소드 실행 성공 (출력 길이: " . strlen($output) . " bytes)<br>";
            echo "📝 출력 시작 부분: " . htmlspecialchars(substr($output, 0, 200)) . "...<br>";
        } else {
            echo "⚠️ create 메소드 실행했지만 출력 없음<br>";
        }
        
    } else {
        echo "❌ create 메소드 없음<br>";
    }
    
    // deleteDraftLectures 메소드 확인
    $reflection = new ReflectionClass($controller);
    $methods = $reflection->getMethods();
    $deleteDraftMethods = array_filter($methods, function($method) {
        return $method->getName() === 'deleteDraftLectures';
    });
    
    echo "🔍 deleteDraftLectures 메소드 개수: " . count($deleteDraftMethods) . "<br>";
    
    if (count($deleteDraftMethods) > 1) {
        echo "❌ deleteDraftLectures 메소드 중복 발견!<br>";
    } else {
        echo "✅ deleteDraftLectures 메소드 정상<br>";
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "<br>";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "🔍 스택 추적:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>