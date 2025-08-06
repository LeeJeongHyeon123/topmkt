<?php
/**
 * 간단한 컨트롤러 테스트
 */

define('SRC_PATH', __DIR__ . '/src');

echo "🧪 간단한 컨트롤러 테스트\n";
echo str_repeat("=", 40) . "\n";

try {
    // 1. NoticeController 로딩
    require_once SRC_PATH . '/controllers/NoticeController.php';
    $noticeController = new NoticeController();
    echo "✅ NoticeController 로딩 성공\n";
    
    // 2. NoticeCommentController 로딩
    require_once SRC_PATH . '/controllers/NoticeCommentController.php';
    $commentController = new NoticeCommentController();
    echo "✅ NoticeCommentController 로딩 성공\n";
    
    // 3. 헬퍼 클래스 확인
    $helpers = ['ResponseHelper', 'AuthMiddleware', 'WebLogger', 'CacheHelper'];
    $loadedHelpers = 0;
    
    foreach ($helpers as $helper) {
        if (class_exists($helper)) {
            $loadedHelpers++;
            echo "✅ {$helper} 로딩됨\n";
        } else {
            echo "⚠️ {$helper} 미로딩\n";
        }
    }
    
    echo "\n로딩된 헬퍼: {$loadedHelpers}/" . count($helpers) . "\n";
    
    // 4. 데이터베이스 연결 확인
    $noticeModel = new Notice();
    $commentModel = new NoticeComment();
    echo "✅ 모델 인스턴스 생성 성공\n";
    
    // 5. 권한 검증 테스트
    if (class_exists('AuthMiddleware')) {
        $isLoggedIn = AuthMiddleware::isLoggedIn();
        echo "✅ 권한 검증 시스템 작동 (로그인 상태: " . ($isLoggedIn ? "예" : "아니요") . ")\n";
    }
    
    echo "\n🎉 기본 컨트롤러 테스트 완료!\n";
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}
?>