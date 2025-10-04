<?php
/**
 * 회원탈퇴 API 직접 테스트
 */

// 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

// 세션 시작
session_start();

// 필요한 파일들 포함
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "=== 회원탈퇴 API 직접 테스트 ===\n\n";

try {
    // 로그인한 사용자 ID (우리집탄이 = 4)
    $userId = 4;
    $password = 'Dnlszkem1!';

    echo "1. 사용자 ID: $userId\n";
    echo "2. 입력 비밀번호: $password\n\n";

    // User 모델 인스턴스 생성
    $userModel = new User();

    // 사용자 정보 먼저 확인
    $user = $userModel->findById($userId);
    if (!$user) {
        echo "❌ 사용자를 찾을 수 없습니다.\n";
        exit;
    }

    echo "3. 사용자 정보 확인:\n";
    echo "   - 닉네임: {$user['nickname']}\n";
    echo "   - 상태: {$user['status']}\n";
    echo "   - 비밀번호 해시: {$user['password_hash']}\n\n";

    // 비밀번호 검증 테스트
    $passwordCheck = password_verify($password, $user['password_hash']);
    echo "4. 비밀번호 검증: " . ($passwordCheck ? '✅ 성공' : '❌ 실패') . "\n\n";

    if (!$passwordCheck) {
        echo "비밀번호가 일치하지 않습니다. 종료합니다.\n";
        exit;
    }

    // 실제 deleteAccount 메서드 호출
    echo "5. deleteAccount 메서드 호출 중...\n";

    // 에러 로깅 활성화
    ini_set('log_errors', 1);
    ini_set('error_log', '/tmp/delete_account_debug.log');

    $result = $userModel->deleteAccount($userId, $password, '테스트 탈퇴');

    echo "6. 결과:\n";
    echo "   - Success: " . ($result['success'] ? 'TRUE' : 'FALSE') . "\n";
    echo "   - Message: " . $result['message'] . "\n";

    if ($result['success']) {
        echo "\n✅ 회원탈퇴 테스트 성공!\n";
    } else {
        echo "\n❌ 회원탈퇴 테스트 실패: " . $result['message'] . "\n";

        // 디버그 로그 확인
        if (file_exists('/tmp/delete_account_debug.log')) {
            echo "\n=== 디버그 로그 ===\n";
            echo file_get_contents('/tmp/delete_account_debug.log');
        }
    }

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
}
?>