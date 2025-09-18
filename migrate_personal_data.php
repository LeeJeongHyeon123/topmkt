<?php
/**
 * 개인정보 암호화 마이그레이션 스크립트
 *
 * 주의: 이 스크립트는 기존 개인정보를 암호화합니다.
 * 반드시 데이터베이스 백업 후 실행하세요!
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/helpers/SecurityHelper.php';

echo "=== 개인정보 암호화 마이그레이션 시작 ===\n\n";

// 데이터베이스 연결
require_once SRC_PATH . '/config/database.php';
$db = Database::getInstance();

// 백업 확인
echo "⚠️  중요: 데이터베이스 백업을 완료하셨습니까? (y/N): ";
$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmation) !== 'y') {
    echo "❌ 마이그레이션이 취소되었습니다. 먼저 데이터베이스를 백업해주세요.\n";
    exit(1);
}

try {
    echo "🔄 기존 사용자 데이터 조회 중...\n";

    // 아직 암호화되지 않은 사용자 데이터 조회
    $sql = "SELECT id, phone, email, birth_date FROM users WHERE phone_search_hash IS NULL";
    $users = $db->fetchAll($sql);

    $totalUsers = count($users);
    echo "📊 암호화할 사용자 수: {$totalUsers}명\n\n";

    if ($totalUsers === 0) {
        echo "✅ 모든 사용자 데이터가 이미 암호화되어 있습니다.\n";
        exit(0);
    }

    $db->beginTransaction();

    $successCount = 0;
    $errorCount = 0;

    foreach ($users as $index => $user) {
        $progress = $index + 1;
        echo "🔐 [{$progress}/{$totalUsers}] 사용자 ID {$user['id']} 암호화 중... ";

        try {
            $updates = [];
            $params = [];

            // 휴대폰 번호 암호화
            if (!empty($user['phone'])) {
                $encryptedPhone = SecurityHelper::encrypt($user['phone']);
                $phoneHash = SecurityHelper::encryptSearchable($user['phone']);

                if ($encryptedPhone !== false && $phoneHash !== false) {
                    $updates[] = "phone = ?";
                    $updates[] = "phone_search_hash = ?";
                    $params[] = $encryptedPhone;
                    $params[] = $phoneHash;
                }
            }

            // 이메일 암호화
            if (!empty($user['email'])) {
                $encryptedEmail = SecurityHelper::encrypt($user['email']);
                $emailHash = SecurityHelper::encryptSearchable($user['email']);

                if ($encryptedEmail !== false && $emailHash !== false) {
                    $updates[] = "email = ?";
                    $updates[] = "email_search_hash = ?";
                    $params[] = $encryptedEmail;
                    $params[] = $emailHash;
                }
            }

            // 생년월일 암호화 및 연령대 변환
            if (!empty($user['birth_date'])) {
                $encryptedBirthDate = SecurityHelper::encrypt($user['birth_date']);
                $ageGroup = SecurityHelper::convertToAgeGroup($user['birth_date']);

                if ($encryptedBirthDate !== false) {
                    $updates[] = "birth_date = ?";
                    $updates[] = "birth_date_age_group = ?";
                    $params[] = $encryptedBirthDate;
                    $params[] = $ageGroup;
                }
            }

            if (!empty($updates)) {
                $updateSql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
                $params[] = $user['id'];

                $db->execute($updateSql, $params);
            }

            echo "✅ 완료\n";
            $successCount++;

        } catch (Exception $e) {
            echo "❌ 실패: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }

    if ($errorCount === 0) {
        $db->commit();
        echo "\n🎉 마이그레이션 성공!\n";
        echo "✅ 성공: {$successCount}명\n";
        echo "❌ 실패: {$errorCount}명\n";
    } else {
        $db->rollback();
        echo "\n⚠️  오류가 발생하여 변경사항이 롤백되었습니다.\n";
        echo "✅ 성공: {$successCount}명\n";
        echo "❌ 실패: {$errorCount}명\n";
        exit(1);
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }

    echo "\n💥 마이그레이션 실패: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== 마이그레이션 완료 ===\n";
echo "💡 참고: 마이그레이션이 완료되면 기존 평문 데이터는 암호화되어 더 이상 읽을 수 없습니다.\n";
echo "💡 복호화는 애플리케이션을 통해서만 가능합니다.\n\n";

// 마이그레이션 검증
echo "🔍 마이그레이션 검증 중...\n";

try {
    $sql = "SELECT COUNT(*) as total,
                   COUNT(CASE WHEN phone_search_hash IS NOT NULL THEN 1 END) as encrypted_count
            FROM users";
    $result = $db->fetch($sql);

    echo "📊 전체 사용자: {$result['total']}명\n";
    echo "🔐 암호화 완료: {$result['encrypted_count']}명\n";

    if ($result['total'] === $result['encrypted_count']) {
        echo "✅ 모든 사용자 데이터가 성공적으로 암호화되었습니다!\n";
    } else {
        $remaining = $result['total'] - $result['encrypted_count'];
        echo "⚠️  {$remaining}명의 사용자 데이터가 아직 암호화되지 않았습니다.\n";
    }

} catch (Exception $e) {
    echo "❌ 검증 실패: " . $e->getMessage() . "\n";
}

echo "\n=== 작업 완료 ===\n";
?>