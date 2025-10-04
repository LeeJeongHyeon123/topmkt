<?php
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/helpers/SecurityHelper.php';

$phone = '01026591346';

try {
    $pdo = Database::getConnection();

    echo "=== 전화번호로 사용자 검색 ===\n";
    echo "검색 대상 전화번호: {$phone}\n\n";

    // SecurityHelper를 사용해서 해시 생성
    $searchHash = SecurityHelper::generateSearchHash($phone, 'phone');
    echo "생성된 검색 해시: {$searchHash}\n\n";

    // 해시로 사용자 검색
    $stmt = $pdo->prepare("
        SELECT id, nickname, role, status, corp_status, created_at
        FROM users
        WHERE phone_search_hash = ?
    ");
    $stmt->execute([$searchHash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "=== 사용자 발견! ===\n";
        echo "ID: " . $user['id'] . "\n";
        echo "닉네임: " . $user['nickname'] . "\n";
        echo "권한(role): " . $user['role'] . "\n";
        echo "상태(status): " . $user['status'] . "\n";
        echo "기업 상태(corp_status): " . $user['corp_status'] . "\n";
        echo "생성일: " . $user['created_at'] . "\n\n";

        echo "=== 권한 분석 ===\n";
        if ($user['role'] === 'ROLE_ADMIN') {
            echo "✅ 총괄관리자(admin) 권한 보유\n";
        }
        if ($user['role'] === 'ROLE_CORPORATE' || $user['role'] === 'ROLE_CORP') {
            echo "✅ 기업 관리자 권한 보유\n";
        }
        if ($user['corp_status'] === 'approved') {
            echo "✅ 기업 승인 완료\n";
        }

        echo "\n=== 결론 ===\n";
        if ($user['role'] === 'ROLE_ADMIN') {
            echo "🎯 이 계정은 총괄관리자입니다.\n";
            echo "   - admin 페이지 접근 가능\n";
            echo "   - 모든 시스템 관리 권한 보유\n";
        } elseif ($user['role'] === 'ROLE_CORPORATE' || $user['role'] === 'ROLE_CORP') {
            echo "🏢 이 계정은 기업 관리자입니다.\n";
            echo "   - 기업 관리 기능 접근 가능\n";
            echo "   - admin 페이지는 접근 불가\n";
        } else {
            echo "👤 이 계정은 일반 사용자입니다.\n";
        }

    } else {
        echo "❌ 해당 전화번호로 등록된 사용자를 찾을 수 없습니다.\n";

        // 모든 사용자 목록 확인
        echo "\n=== 등록된 모든 사용자 ===\n";
        $stmt = $pdo->query("SELECT id, nickname, role, status, corp_status FROM users ORDER BY id");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID {$row['id']}: {$row['nickname']} ({$row['role']}, {$row['status']}, corp:{$row['corp_status']})\n";
        }
    }

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}
?>