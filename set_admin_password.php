<?php
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=TOPMKT;charset=utf8mb4',
        'root',
        'Dnlszkem1!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $newPassword = 'Dnlszkem1!';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    echo "=== 관리자 비밀번호 변경 ===\n";
    echo "새 비밀번호: {$newPassword}\n";
    echo "해시: {$hashedPassword}\n\n";

    // 비밀번호 변경 실행
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = 1");
    $result = $stmt->execute([$hashedPassword]);

    if ($result) {
        echo "✅ 비밀번호가 성공적으로 변경되었습니다!\n\n";

        // 변경 확인
        $stmt = $pdo->prepare("SELECT id, nickname, password_hash FROM users WHERE id = 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // 비밀번호 검증 테스트
        if (password_verify($newPassword, $admin['password_hash'])) {
            echo "🎉 검증 성공! 새 비밀번호가 정상적으로 설정되었습니다.\n\n";
            echo "=== 총괄관리자 로그인 정보 ===\n";
            echo "📱 전화번호: 010-0000-0000\n";
            echo "🔑 비밀번호: Dnlszkem1!\n";
            echo "👤 계정명: {$admin['nickname']}\n";
            echo "🔐 권한: ROLE_ADMIN (총괄관리자)\n";
        } else {
            echo "❌ 검증 실패! 비밀번호 설정에 문제가 있습니다.\n";
        }
    } else {
        echo "❌ 비밀번호 변경에 실패했습니다.\n";
    }

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>