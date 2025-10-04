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
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "=== 확장된 비밀번호 테스트 ===\n";

        // 더 많은 패턴 테스트
        $testPasswords = [
            // 기본 패턴
            'admin',
            'password',
            '123456',
            'admin123',
            'password123',

            // 프로젝트 관련
            'topmkt',
            'topmkt123',
            'TOPMKT',
            'topmarketing',
            'marketing',

            // 한글/특수문자 포함
            '관리자',
            '탑마케팅',
            'admin!',
            'admin@123',
            'Admin123!',

            // 날짜 패턴 (계정 생성일 기준: 2025-06-02)
            '20250602',
            '2025',
            '0602',

            // 시스템 관련
            'root',
            'mysql',
            'php',

            // DB 비밀번호
            'Dnlszkem1!',

            // 전화번호 관련
            '010-0000-0000',
            '01000000000',
            '0000',

            // 기타 패턴
            'test',
            'test123',
            'admin2025',
            '1234',
            '12345',
            'qwerty',
            'asdf',
            'zxcv'
        ];

        $found = false;
        foreach ($testPasswords as $testPassword) {
            if (password_verify($testPassword, $admin['password_hash'])) {
                echo "🎯 비밀번호 발견: {$testPassword}\n";
                $found = true;
                break;
            }
        }

        if (!$found) {
            echo "❌ 테스트한 " . count($testPasswords) . "개 비밀번호 중 일치하는 것이 없습니다.\n";
            echo "\n비밀번호 해시: {$admin['password_hash']}\n";
            echo "\n💡 가능한 해결책:\n";
            echo "1. 비밀번호 재설정 기능 사용\n";
            echo "2. 데이터베이스에서 직접 새 비밀번호 해시로 변경\n";
            echo "3. 새로운 관리자 계정 생성\n";
        }

    } else {
        echo "❌ 관리자 계정을 찾을 수 없습니다.\n";
    }

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>