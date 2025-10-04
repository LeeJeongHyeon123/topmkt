<?php
/**
 * 우리집탄이 비밀번호 재설정
 */

// 상수 정의
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

// 필요한 파일들 포함
require_once SRC_PATH . '/config/database.php';

try {
    // 데이터베이스 연결 (Database 클래스 사용)
    $db = Database::getInstance();
    $pdo = $db->getPDO();

    // 새 비밀번호 해시 생성
    $newPassword = 'Dnlszkem1!';
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    echo "=== 비밀번호 재설정 ===\n\n";
    echo "새 비밀번호: $newPassword\n";
    echo "새 해시: $newPasswordHash\n\n";

    // 기존 사용자 정보 확인
    $stmt = $pdo->prepare("SELECT id, nickname, password_hash FROM users WHERE nickname = ?");
    $stmt->execute(['우리집탄이']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "❌ 사용자를 찾을 수 없습니다.\n";
        exit;
    }

    echo "현재 사용자 정보:\n";
    echo "  - ID: {$user['id']}\n";
    echo "  - 닉네임: {$user['nickname']}\n";
    echo "  - 기존 해시: {$user['password_hash']}\n\n";

    // 비밀번호 업데이트
    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $result = $updateStmt->execute([$newPasswordHash, $user['id']]);

    if ($result) {
        echo "✅ 비밀번호 재설정 성공!\n\n";

        // 업데이트된 정보 확인
        $stmt->execute(['우리집탄이']);
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "업데이트된 해시: {$updatedUser['password_hash']}\n\n";

        // 비밀번호 검증 테스트
        $verifyResult = password_verify($newPassword, $updatedUser['password_hash']);
        echo "비밀번호 검증 테스트: " . ($verifyResult ? '✅ 성공' : '❌ 실패') . "\n";

    } else {
        echo "❌ 비밀번호 재설정 실패\n";
    }

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
}
?>