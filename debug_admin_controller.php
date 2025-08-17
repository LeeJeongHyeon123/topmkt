<?php
/**
 * AdminController 직접 테스트
 */

echo "🔍 AdminController 직접 테스트\n";
echo "============================\n\n";

// 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

try {
    // 필요한 파일들 로드
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/controllers/AdminController.php';
    
    echo "✅ 파일 로드 성공\n";
    
    // AdminController 인스턴스 생성
    $controller = new AdminController();
    echo "✅ AdminController 인스턴스 생성 성공\n";
    
    // userList 메서드 존재 확인
    if (method_exists($controller, 'userList')) {
        echo "✅ userList 메서드 존재\n";
        
        echo "\n🔧 userList 메서드 직접 호출 테스트:\n";
        
        // 출력 버퍼링 시작
        ob_start();
        
        try {
            $controller->userList();
            $output = ob_get_contents();
            ob_end_clean();
            
            echo "✅ userList 메서드 실행 성공\n";
            echo "출력 길이: " . strlen($output) . " bytes\n";
            
            if (strlen($output) > 0) {
                echo "✅ 정상적인 출력 생성됨\n";
            } else {
                echo "⚠️ 출력이 비어있음\n";
            }
            
        } catch (Exception $e) {
            ob_end_clean();
            echo "❌ userList 메서드 실행 중 오류: " . $e->getMessage() . "\n";
            echo "오류 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
        
    } else {
        echo "❌ userList 메서드 없음\n";
    }
    
} catch (Exception $e) {
    echo "❌ 치명적 오류: " . $e->getMessage() . "\n";
    echo "오류 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🎯 테스트 완료!\n";
?>