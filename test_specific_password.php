<?php
try {
    // PDO 직접 연결
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=TOPMKT;charset=utf8mb4',
        'root',
        'Dnlszkem1!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 총괄관리자 계정 비밀번호 해시 조회
    $stmt = $pdo->prepare("SELECT id, nickname, password_hash FROM users WHERE id = 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "=== 특정 비밀번호 테스트 ===\n";
        echo "계정: {$admin['nickname']}\n";

        $testPassword = 'Dnlszkem1!';
        echo "테스트 비밀번호: {$testPassword}\n";
        echo "저장된 해시: {$admin['password_hash']}\n\n";

        if (password_verify($testPassword, $admin['password_hash'])) {
            echo "🎉 성공! 비밀번호가 맞습니다!\n";
            echo "✅ 총괄관리자 로그인 정보:\n";
            echo "   📱 전화번호: 010-0000-0000\n";
            echo "   🔑 비밀번호: Dnlszkem1!\n";
        } else {
            echo "❌ 비밀번호가 틀렸습니다.\n";

            // 추가로 다른 변형도 테스트
            $variations = [
                'dnlszkem1!',
                'DNLSZKEM1!',
                'Dnlszkem1',
                'dnlszkem1',
                'Dnlszkem1!!',
            ];

            echo "\n=== 변형 패턴 테스트 ===\n";
            foreach ($variations as $variation) {
                if (password_verify($variation, $admin['password_hash'])) {
                    echo "🎉 변형 패턴 발견: {$variation}\n";
                    break;
                }
            }
        }

    } else {
        echo "❌ 관리자 계정을 찾을 수 없습니다.\n";
    }

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>