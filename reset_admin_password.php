<?php
// 관리자 비밀번호를 'admin123'으로 재설정하는 스크립트
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=TOPMKT;charset=utf8mb4',
        'root',
        'Dnlszkem1!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $newPassword = 'admin123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    echo "=== 관리자 비밀번호 재설정 ===\n";
    echo "새 비밀번호: {$newPassword}\n";
    echo "실행하려면 스크립트를 수정하고 다시 실행하세요.\n";

    // 주석 해제하면 실제로 변경됩니다
    /*
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = 1");
    $result = $stmt->execute([$hashedPassword]);

    if ($result) {
        echo "✅ 비밀번호가 성공적으로 변경되었습니다!\n";
        echo "📱 전화번호: 010-0000-0000\n";
        echo "🔑 새 비밀번호: {$newPassword}\n";
    } else {
        echo "❌ 비밀번호 변경에 실패했습니다.\n";
    }
    */

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>