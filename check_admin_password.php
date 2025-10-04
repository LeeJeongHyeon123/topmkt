<?php
try {
    // PDO 직접 연결
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=TOPMKT;charset=utf8mb4',
        'root',
        'Dnlszkem1!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== 총괄관리자 계정 비밀번호 정보 ===\n";

    // 총괄관리자 계정 비밀번호 해시 조회
    $stmt = $pdo->prepare("SELECT id, nickname, password_hash, created_at, last_login FROM users WHERE id = 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "ID: " . $admin['id'] . "\n";
        echo "닉네임: " . $admin['nickname'] . "\n";
        echo "계정 생성일: " . $admin['created_at'] . "\n";
        echo "마지막 로그인: " . ($admin['last_login'] ?: '없음') . "\n";
        echo "비밀번호 해시: " . $admin['password_hash'] . "\n";
        echo "해시 길이: " . strlen($admin['password_hash']) . " characters\n";

        // 해시 방식 추정
        $hashLength = strlen($admin['password_hash']);
        if ($hashLength == 60 && substr($admin['password_hash'], 0, 4) == '$2y$') {
            echo "해시 방식: bcrypt (PHP password_hash)\n";
        } elseif ($hashLength == 64) {
            echo "해시 방식: SHA-256 또는 유사한 64자 해시\n";
        } elseif ($hashLength == 32) {
            echo "해시 방식: MD5\n";
        } else {
            echo "해시 방식: 불명 (길이: {$hashLength})\n";
        }

        // 일반적인 관리자 비밀번호들 테스트 (보안상 몇 개만)
        echo "\n=== 일반적인 비밀번호 테스트 ===\n";
        $commonPasswords = [
            'admin',
            'password',
            '123456',
            'admin123',
            'topmkt',
            'Dnlszkem1!', // DB 비밀번호와 같은지 확인
            '010-0000-0000' // 전화번호와 같은지 확인
        ];

        foreach ($commonPasswords as $testPassword) {
            if (password_verify($testPassword, $admin['password_hash'])) {
                echo "✅ 비밀번호 발견: {$testPassword}\n";
                break;
            }
        }

    } else {
        echo "❌ ID 1번 계정을 찾을 수 없습니다.\n";
    }

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>