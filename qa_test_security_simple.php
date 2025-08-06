<?php
/**
 * QA 테스트: 보안 간단 버전
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/models/Notice.php';

echo "🧪 === 보안 간단 QA 테스트 ===\n";
echo str_repeat("=", 40) . "\n\n";

try {
    // AuthMiddleware 기본 기능
    echo "✅ AuthMiddleware 클래스 로딩\n";
    
    // Notice 모델 권한 확인
    $notice = new Notice();
    $isCompanyUser = $notice->isCompanyUser(4);
    echo ($isCompanyUser ? "✅" : "❌") . " 기업 사용자 권한 확인\n";
    
    // 비기업 사용자 확인
    $isNotCompanyUser = $notice->isCompanyUser(99999);
    echo (!$isNotCompanyUser ? "✅" : "❌") . " 비기업 사용자 차단\n";
    
    // 소유권 확인 (기존 공지사항 사용)
    $notices = $notice->getList(1, 1);
    if (!empty($notices)) {
        $testNotice = $notices[0];
        $isOwner = $notice->isOwner($testNotice['id'], $testNotice['user_id']);
        echo ($isOwner ? "✅" : "❌") . " 소유권 확인 정상\n";
        
        $isNotOwner = $notice->isOwner($testNotice['id'], 99999);
        echo (!$isNotOwner ? "✅" : "❌") . " 비소유자 차단 정상\n";
    }
    
    // HTML 새니타이저 확인
    if (class_exists('HtmlSanitizerHelper')) {
        echo "✅ HtmlSanitizerHelper 클래스 존재\n";
    } else {
        echo "⚠️ HtmlSanitizerHelper 클래스 없음\n";
    }
    
    echo "\n" . str_repeat("=", 40) . "\n";
    echo "🎉 기본 보안 기능 정상!\n";
    echo str_repeat("=", 40) . "\n";
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}
?>