<?php
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');

require_once SRC_PATH . '/config/database.php';

try {
    $db = Database::getInstance();

    // UPDATE 모드: corp_status를 pending으로 변경
    if (isset($_GET['update']) && $_GET['update'] === 'pending') {
        $updateSql = "UPDATE users SET corp_status = 'pending' WHERE id = 5";
        $db->query($updateSql);
        echo "✅ corp_status를 pending으로 변경했습니다!\n\n";
    }

    // 안계현 계정 조회
    $sql = "SELECT id, username, nickname, email, role, corp_status, corporate_id, created_at
            FROM users
            WHERE nickname = '안계현'
            LIMIT 1";

    $user = $db->fetch($sql);

    header('Content-Type: text/plain; charset=utf-8');

    if ($user) {
        echo "=== 안계현 계정 정보 ===\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Nickname: " . $user['nickname'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Corp Status: " . $user['corp_status'] . "\n";
        echo "Corporate ID: " . ($user['corporate_id'] ?? 'NULL') . "\n";
        echo "Created: " . $user['created_at'] . "\n";

        // 기업 회원 여부 확인
        echo "\n=== 권한 분석 ===\n";
        if ($user['corp_status'] === 'approved') {
            echo "🟢 corp_status = 'approved' → 신청 관리 메뉴 표시됨!\n";
        } else {
            echo "🔴 corp_status = '" . $user['corp_status'] . "' → 신청 관리 메뉴 숨김\n";
        }

        // Corporate 정보 확인
        if ($user['corporate_id']) {
            $corpSql = "SELECT id, company_name, business_number, approval_status
                       FROM corporates
                       WHERE id = ?";
            $corp = $db->fetch($corpSql, [$user['corporate_id']]);

            if ($corp) {
                echo "\n=== 연결된 기업 정보 ===\n";
                echo "기업 ID: " . $corp['id'] . "\n";
                echo "회사명: " . $corp['company_name'] . "\n";
                echo "사업자번호: " . $corp['business_number'] . "\n";
                echo "승인 상태: " . $corp['approval_status'] . "\n";
            }
        }

    } else {
        echo "❌ 안계현 계정을 찾을 수 없습니다.\n";
    }

} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Error: " . $e->getMessage() . "\n";
}
?>
