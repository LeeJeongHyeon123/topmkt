<?php
/**
 * AdminController 의존성 테스트
 */

echo "🔍 AdminController 의존성 테스트\n";
echo "==============================\n\n";

// 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

try {
    echo "1. Database 클래스 로드 테스트:\n";
    require_once SRC_PATH . '/config/database.php';
    echo "✅ database.php 파일 로드 성공\n";
    
    if (class_exists('Database')) {
        echo "✅ Database 클래스 존재\n";
        
        $db = Database::getInstance();
        echo "✅ Database 인스턴스 생성 성공\n";
    } else {
        echo "❌ Database 클래스 없음\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database 로드 오류: " . $e->getMessage() . "\n";
}

try {
    echo "\n2. AuthMiddleware 클래스 로드 테스트:\n";
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    echo "✅ AuthMiddleware.php 파일 로드 성공\n";
    
    if (class_exists('AuthMiddleware')) {
        echo "✅ AuthMiddleware 클래스 존재\n";
    } else {
        echo "❌ AuthMiddleware 클래스 없음\n";
    }
    
} catch (Exception $e) {
    echo "❌ AuthMiddleware 로드 오류: " . $e->getMessage() . "\n";
}

try {
    echo "\n3. AdminController 로드 테스트:\n";
    
    // 필요한 파일들이 모두 로드되었는지 확인
    if (class_exists('Database') && class_exists('AuthMiddleware')) {
        echo "✅ 모든 의존성 로드 완료\n";
        
        // 관리자 인증을 우회하기 위해 세션 설정
        session_start();
        $_SESSION['user_id'] = 1;  // 테스트용 사용자 ID
        $_SESSION['user_role'] = 'ROLE_SUPER_ADMIN';  // 관리자 권한
        
        require_once SRC_PATH . '/controllers/AdminController.php';
        echo "✅ AdminController.php 파일 로드 성공\n";
        
        $controller = new AdminController();
        echo "✅ AdminController 인스턴스 생성 성공\n";
        
    } else {
        echo "❌ 의존성 로드 실패\n";
    }
    
} catch (Exception $e) {
    echo "❌ AdminController 로드 오류: " . $e->getMessage() . "\n";
    echo "오류 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🎯 테스트 완료!\n";
?>