<?php
/**
 * 기업정보 암호화 마이그레이션 스크립트
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/helpers/SecurityHelper.php';

echo "=== 기업정보 암호화 마이그레이션 시작 ===\n\n";

// 데이터베이스 연결
require_once SRC_PATH . '/config/database.php';
$db = Database::getInstance();

try {
    echo "🔄 기존 기업 데이터 조회 중...\n";

    // 기업정보 조회
    $sql = "SELECT id, business_number, representative_phone FROM company_profiles";
    $companies = $db->fetchAll($sql);

    $totalCompanies = count($companies);
    echo "📊 암호화할 기업 수: {$totalCompanies}개\n\n";

    if ($totalCompanies === 0) {
        echo "✅ 암호화할 기업 데이터가 없습니다.\n";
        exit(0);
    }

    $db->beginTransaction();

    $successCount = 0;
    $errorCount = 0;

    foreach ($companies as $index => $company) {
        $progress = $index + 1;
        echo "🔐 [{$progress}/{$totalCompanies}] 기업 ID {$company['id']} 암호화 중... ";

        try {
            $updates = [];
            $params = [];

            // 사업자번호 암호화
            if (!empty($company['business_number']) && !SecurityHelper::isEncrypted($company['business_number'])) {
                $encrypted = SecurityHelper::encrypt($company['business_number']);
                if ($encrypted !== false) {
                    $updates[] = "business_number = ?";
                    $params[] = $encrypted;
                }
            }

            // 대표자 휴대폰 번호 암호화
            if (!empty($company['representative_phone']) && !SecurityHelper::isEncrypted($company['representative_phone'])) {
                $encrypted = SecurityHelper::encrypt($company['representative_phone']);
                if ($encrypted !== false) {
                    $updates[] = "representative_phone = ?";
                    $params[] = $encrypted;
                }
            }

            if (!empty($updates)) {
                $updateSql = "UPDATE company_profiles SET " . implode(", ", $updates) . " WHERE id = ?";
                $params[] = $company['id'];

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
        echo "✅ 성공: {$successCount}개\n";
        echo "❌ 실패: {$errorCount}개\n";
    } else {
        $db->rollback();
        echo "\n⚠️  오류가 발생하여 변경사항이 롤백되었습니다.\n";
        echo "✅ 성공: {$successCount}개\n";
        echo "❌ 실패: {$errorCount}개\n";
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
?>