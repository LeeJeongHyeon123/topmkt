<?php
require_once __DIR__ . '/src/helpers/SecurityHelper.php';

try {
    // PDO 직접 연결
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=TOPMKT;charset=utf8mb4',
        'root',
        'Dnlszkem1!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== 총괄관리자 계정 전화번호 조회 ===\n";

    // 총괄관리자 계정 정보 조회
    $stmt = $pdo->prepare("SELECT id, nickname, phone, role FROM users WHERE id = 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "ID: " . $admin['id'] . "\n";
        echo "닉네임: " . $admin['nickname'] . "\n";
        echo "권한: " . $admin['role'] . "\n";

        if (!empty($admin['phone'])) {
            try {
                // 암호화된 전화번호 복호화 시도
                $decryptedPhone = SecurityHelper::decrypt($admin['phone']);
                echo "전화번호: " . $decryptedPhone . "\n";
            } catch (Exception $e) {
                echo "전화번호 복호화 실패: " . $e->getMessage() . "\n";
                echo "암호화된 전화번호 데이터 길이: " . strlen($admin['phone']) . " bytes\n";
            }
        } else {
            echo "전화번호: 등록되지 않음\n";
        }
    } else {
        echo "❌ ID 1번 계정을 찾을 수 없습니다.\n";
    }

    // 모든 관리자 계정 조회
    echo "\n=== 모든 관리자 계정 ===\n";
    $stmt = $pdo->query("SELECT id, nickname, role FROM users WHERE role IN ('ROLE_ADMIN', 'ROLE_CORPORATE') ORDER BY role, id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID {$row['id']}: {$row['nickname']} ({$row['role']})\n";
    }

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>