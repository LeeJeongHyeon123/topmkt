<?php
/**
 * 최종 권한 시스템 검증 - 핵심만
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/Notice.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/config/database.php';

echo "🧪 최종 권한 시스템 검증\n";
echo str_repeat("=", 40) . "\n";

try {
    $noticeModel = new Notice();
    $db = Database::getInstance();
    
    // 1. 기업 사용자 확인
    echo "\n1. 기업 사용자 확인:\n";
    $isCompanyUser = $noticeModel->isCompanyUser(4);
    echo ($isCompanyUser ? "✅" : "❌") . " 사용자 ID 4 기업 권한\n";
    
    // 2. AuthMiddleware 확인
    echo "\n2. AuthMiddleware 확인:\n";
    $_SESSION['user_id'] = 4;
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    $userId = AuthMiddleware::getCurrentUserId();
    echo ($isLoggedIn ? "✅" : "❌") . " 로그인 상태 감지\n";
    echo ($userId == 4 ? "✅" : "❌") . " 사용자 ID 반환: $userId\n";
    
    unset($_SESSION['user_id']);
    $isLoggedOut = AuthMiddleware::isLoggedIn();
    echo (!$isLoggedOut ? "✅" : "❌") . " 로그아웃 상태 감지\n";
    
    // 3. 데이터베이스 권한 체크
    echo "\n3. 데이터베이스 권한 체크:\n";
    $adminUsers = $db->fetchAll("
        SELECT u.id, u.nickname, u.role, cp.company_name 
        FROM users u 
        JOIN company_profiles cp ON u.id = cp.user_id 
        WHERE u.role = 'ROLE_ADMIN' AND u.corp_status = 'approved' AND cp.status = 'approved'
    ");
    
    echo "승인된 관리자 사용자: " . count($adminUsers) . "명\n";
    foreach ($adminUsers as $user) {
        echo "- [{$user['id']}] {$user['nickname']} - {$user['company_name']}\n";
    }
    
    // 4. 공지사항 작성 권한 최종 확인
    echo "\n4. 공지사항 작성 권한 최종 확인:\n";
    if (count($adminUsers) > 0) {
        $testUser = $adminUsers[0];
        $canWrite = $noticeModel->isCompanyUser($testUser['id']);
        $companyId = $noticeModel->getUserCompanyId($testUser['id']);
        
        echo ($canWrite ? "✅" : "❌") . " 사용자 {$testUser['id']}의 작성 권한\n";
        echo ($companyId ? "✅" : "❌") . " 기업 ID 조회: $companyId\n";
    } else {
        echo "⚠️ 승인된 관리자 사용자 없음\n";
    }
    
    echo "\n🎉 권한 시스템 검증 완료!\n";
    
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n";
}
?>