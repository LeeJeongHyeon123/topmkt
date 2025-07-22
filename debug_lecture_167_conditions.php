<?php
/**
 * 강의 167 페이지 조건 디버깅
 */

// 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

require_once 'src/config/config.php';
require_once 'src/config/database.php';
require_once 'src/middlewares/AuthMiddleware.php';

// 세션 시작
session_start();

echo "=== 강의 167 페이지 조건 디버깅 ===\n\n";

// 현재 세션 확인
echo "📋 현재 세션 정보:\n";
echo "- 세션 ID: " . session_id() . "\n";
echo "- user_id: " . ($_SESSION['user_id'] ?? 'null') . "\n";
echo "- user_role: " . ($_SESSION['user_role'] ?? 'null') . "\n";
echo "- nickname: " . ($_SESSION['nickname'] ?? 'null') . "\n\n";

// 안계현으로 로그인 설정
$_SESSION['user_id'] = 5;
$_SESSION['user_role'] = 'ROLE_USER';
$_SESSION['nickname'] = '안계현';

echo "🔧 안계현 사용자로 설정 완료\n\n";

// AuthMiddleware 확인
$isLoggedIn = AuthMiddleware::isLoggedIn();
$currentUserId = AuthMiddleware::getCurrentUserId();
$userRole = AuthMiddleware::getUserRole();

echo "📊 AuthMiddleware 결과:\n";
echo "- isLoggedIn: " . ($isLoggedIn ? 'true' : 'false') . "\n";
echo "- currentUserId: " . ($currentUserId ?? 'null') . "\n";
echo "- userRole: " . ($userRole ?? 'null') . "\n\n";

// 강의 정보 가져오기
$db = Database::getInstance();
$lecture = $db->fetch("SELECT * FROM lectures WHERE id = 167");

echo "📚 강의 167 정보:\n";
echo "- title: " . ($lecture['title'] ?? 'null') . "\n";
echo "- user_id (작성자): " . ($lecture['user_id'] ?? 'null') . "\n";
echo "- status: " . ($lecture['status'] ?? 'null') . "\n\n";

// 편집 권한 확인
$canEdit = false;
if ($isLoggedIn && isset($lecture)) {
    $canEdit = ($userRole === 'ROLE_ADMIN') || ($lecture['user_id'] == $currentUserId);
}

echo "✏️ 편집 권한 계산:\n";
echo "- canEdit: " . ($canEdit ? 'true' : 'false') . "\n";
echo "- 조건1 (관리자): " . (($userRole === 'ROLE_ADMIN') ? 'true' : 'false') . "\n";
echo "- 조건2 (작성자): " . (($lecture['user_id'] == $currentUserId) ? 'true' : 'false') . "\n\n";

// 최종 조건 확인
$showStatusMessage = $isLoggedIn && !$canEdit;

echo "🎯 최종 조건 확인:\n";
echo "- \$isLoggedIn: " . ($isLoggedIn ? 'true' : 'false') . "\n";
echo "- !\$canEdit: " . (!$canEdit ? 'true' : 'false') . "\n";
echo "- \$isLoggedIn && !\$canEdit: " . ($showStatusMessage ? 'true' : 'false') . "\n\n";

if ($showStatusMessage) {
    echo "✅ 조건 만족! 거절 메시지 HTML이 출력되어야 합니다.\n";
} else {
    echo "❌ 조건 불만족! 거절 메시지 HTML이 출력되지 않습니다.\n";
}

// 사용자 신청 정보 확인
$userRegistration = $db->fetch("
    SELECT * FROM lecture_registrations
    WHERE lecture_id = 167 AND user_id = 5
");

echo "\n📝 사용자 신청 정보:\n";
if ($userRegistration) {
    echo "- status: " . $userRegistration['status'] . "\n";
    echo "- admin_notes: " . $userRegistration['admin_notes'] . "\n";
    echo "- created_at: " . $userRegistration['created_at'] . "\n";
} else {
    echo "- 신청 정보 없음\n";
}
?>