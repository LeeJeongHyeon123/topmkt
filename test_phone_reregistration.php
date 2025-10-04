<?php
/**
 * 탈퇴한 전화번호 재가입 가능성 테스트
 */

// 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

// 필요한 파일들 포함
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';

echo "=== 탈퇴한 전화번호 재가입 가능성 테스트 ===\n\n";

try {
    // User 모델 인스턴스 생성
    $userModel = new User();

    // 탈퇴한 사용자의 원래 전화번호 확인 (직접 SQL로)
    $db = Database::getInstance();
    $deletedUser = $db->fetch("SELECT * FROM users WHERE id = 4", []);

    if ($deletedUser && $deletedUser['status'] === 'deleted') {
        echo "1. 탈퇴한 사용자 정보:\n";
        echo "   - ID: {$deletedUser['id']}\n";
        echo "   - 닉네임: {$deletedUser['nickname']}\n";
        echo "   - 전화번호: {$deletedUser['phone']}\n";
        echo "   - 상태: {$deletedUser['status']}\n\n";

        // 실제 원래 전화번호 (우리집탄이)
        $originalPhone = '010-2659-1346';
        echo "2. 실제 원래 전화번호: $originalPhone\n\n";

        // findByPhone 테스트
        echo "3. findByPhone 테스트:\n";
        $foundUser = $userModel->findByPhone($originalPhone);
        echo "   - 결과: " . ($foundUser ? "사용자 발견 (ID: {$foundUser['id']})" : "사용자 없음") . "\n\n";

        // isPhoneExists 테스트
        echo "4. isPhoneExists 테스트:\n";
        $phoneExists = $userModel->isPhoneExists($originalPhone);
        echo "   - 결과: " . ($phoneExists ? "전화번호 이미 존재" : "전화번호 사용 가능") . "\n\n";

        // 결론
        if (!$phoneExists) {
            echo "✅ 결론: 탈퇴한 전화번호로 재가입 가능!\n";
            echo "   - findByPhone: status != 'deleted' 조건으로 탈퇴회원 제외\n";
            echo "   - isPhoneExists: 중복 검사에서 탈퇴회원 제외\n";
        } else {
            echo "❌ 결론: 재가입 불가능\n";
        }

    } else {
        echo "❌ 탈퇴한 사용자가 아니거나 찾을 수 없습니다.\n";
    }

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
}
?>