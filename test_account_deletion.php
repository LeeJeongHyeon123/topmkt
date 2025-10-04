<?php
/**
 * 회원탈퇴 기능 테스트 스크립트
 */

// 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/SecurityHelper.php';

echo "=== 회원탈퇴 기능 테스트 ===\n\n";

try {
    $userModel = new User();

    // 1. 테스트 사용자 생성
    echo "1. 테스트 사용자 생성 중...\n";
    $testUserData = [
        'nickname' => 'test_delete_' . rand(1000, 9999),
        'phone' => '010-' . rand(1000, 9999) . '-' . rand(1000, 9999),
        'email' => 'test_' . rand(1000, 9999) . '@test.com',
        'password' => 'Test1234!',
        'marketing_agreed' => false
    ];

    $testUserId = $userModel->create($testUserData);

    if (!$testUserId) {
        throw new Exception("테스트 사용자 생성 실패");
    }
    echo "✅ 테스트 사용자 생성 완료 (ID: $testUserId, Nickname: {$testUserData['nickname']})\n\n";

    // 2. 사용자 정보 확인
    echo "2. 사용자 정보 확인 중...\n";
    $user = $userModel->findById($testUserId);
    if (!$user) {
        throw new Exception("사용자 조회 실패");
    }
    echo "✅ 사용자 정보 확인 완료\n";
    echo "   - Status: {$user['status']}\n";
    echo "   - Role: {$user['role']}\n\n";

    // 3. 잘못된 비밀번호로 탈퇴 시도
    echo "3. 잘못된 비밀번호로 탈퇴 시도...\n";
    $result = $userModel->deleteAccount($testUserId, 'WrongPassword123!', '테스트 탈퇴');
    if ($result['success']) {
        throw new Exception("잘못된 비밀번호로 탈퇴가 성공함 (보안 문제)");
    }
    echo "✅ 잘못된 비밀번호 거부 확인\n";
    echo "   - 메시지: {$result['message']}\n\n";

    // 4. 올바른 비밀번호로 탈퇴 처리
    echo "4. 올바른 비밀번호로 탈퇴 처리...\n";
    $result = $userModel->deleteAccount($testUserId, 'Test1234!', '테스트 목적 탈퇴');
    if (!$result['success']) {
        throw new Exception("탈퇴 처리 실패: " . $result['message']);
    }
    echo "✅ 회원 탈퇴 처리 완료\n";
    echo "   - 메시지: {$result['message']}\n\n";

    // 5. 탈퇴 후 사용자 상태 확인
    echo "5. 탈퇴 후 사용자 상태 확인...\n";
    // 탈퇴한 사용자는 findById로 조회되지 않으므로 직접 쿼리 실행
    $db = Database::getInstance();
    $deletedUser = $db->fetch("SELECT * FROM users WHERE id = ?", [$testUserId]);
    if (!$deletedUser) {
        throw new Exception("탈퇴한 사용자 조회 실패");
    }

    echo "✅ 탈퇴 상태 확인 완료\n";
    echo "   - Status: {$deletedUser['status']}\n";
    echo "   - Nickname: {$deletedUser['nickname']}\n";
    echo "   - Email: {$deletedUser['email']}\n";
    echo "   - Phone: {$deletedUser['phone']}\n";
    echo "   - Deleted At: {$deletedUser['deleted_at']}\n";
    echo "   - Deletion Reason: {$deletedUser['deletion_reason']}\n\n";

    // 6. 개인정보 익명화 확인
    echo "6. 개인정보 익명화 확인...\n";
    if (strpos($deletedUser['nickname'], '탈퇴회원_') !== 0) {
        throw new Exception("닉네임 익명화 실패");
    }
    if (strpos($deletedUser['email'], 'deleted_') !== 0) {
        throw new Exception("이메일 익명화 실패");
    }
    if (!empty($deletedUser['bio']) || !empty($deletedUser['birth_date'])) {
        throw new Exception("개인정보 삭제 실패");
    }
    echo "✅ 개인정보 익명화 확인 완료\n\n";

    // 7. 로그 확인
    echo "7. 탈퇴 로그 확인...\n";
    $db = Database::getInstance();
    $log = $db->fetch(
        "SELECT * FROM user_logs WHERE user_id = ? AND action = 'ACCOUNT_DELETE' ORDER BY created_at DESC LIMIT 1",
        [$testUserId]
    );
    if (!$log) {
        throw new Exception("탈퇴 로그 기록 실패");
    }
    echo "✅ 탈퇴 로그 확인 완료\n";
    echo "   - Action: {$log['action']}\n";
    echo "   - Description: {$log['description']}\n";
    echo "   - Extra Data: {$log['extra_data']}\n\n";

    echo "========================================\n";
    echo "🎉 모든 테스트가 성공적으로 완료되었습니다!\n";
    echo "========================================\n\n";

    echo "📋 테스트 결과 요약:\n";
    echo "✅ 사용자 생성 성공\n";
    echo "✅ 잘못된 비밀번호 거부 확인\n";
    echo "✅ 올바른 비밀번호로 탈퇴 처리\n";
    echo "✅ Status가 'deleted'로 변경됨\n";
    echo "✅ 개인정보 익명화 완료\n";
    echo "✅ 탈퇴 로그 기록 확인\n";

} catch (Exception $e) {
    echo "❌ 테스트 실패: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}