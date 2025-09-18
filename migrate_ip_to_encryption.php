<?php
/**
 * IP 주소 해시화 → 암호화 전환 마이그레이션 스크립트
 * 기존에 해시화된 IP를 다시 암호화 방식으로 변경
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/helpers/SecurityHelper.php';

echo "=== IP 주소 해시화 → 암호화 전환 마이그레이션 ===\n\n";

// 데이터베이스 연결
require_once SRC_PATH . '/config/database.php';
$db = Database::getInstance();

echo "⚠️  현재 상황:\n";
echo "- 기존 IP 주소가 해시화되어 원본 복원이 불가능합니다.\n";
echo "- 앞으로 수집되는 IP 주소는 암호화 방식으로 저장됩니다.\n";
echo "- 관리자가 IP 주소를 확인할 수 있도록 개선됩니다.\n\n";

try {
    echo "1. 현재 IP 데이터 상태 확인\n";
    echo "-----------------------------------\n";

    // user_logs 테이블 확인
    $sql = "SELECT COUNT(*) as total,
                   COUNT(CASE WHEN LENGTH(ip_address) > 20 THEN 1 END) as hashed_count
            FROM user_logs WHERE ip_address IS NOT NULL";
    $logStats = $db->fetch($sql);

    echo "📊 user_logs: 총 {$logStats['total']}개, 해시화된 것 {$logStats['hashed_count']}개\n";

    // user_sessions 테이블 확인
    $sql = "SELECT COUNT(*) as total,
                   COUNT(CASE WHEN LENGTH(ip_address) > 20 THEN 1 END) as hashed_count
            FROM user_sessions WHERE ip_address IS NOT NULL";
    $sessionStats = $db->fetch($sql);

    echo "📊 user_sessions: 총 {$sessionStats['total']}개, 해시화된 것 {$sessionStats['hashed_count']}개\n\n";

    echo "2. 데이터베이스 스키마 업데이트\n";
    echo "-----------------------------------\n";

    $db->beginTransaction();

    // ip_address 컬럼을 TEXT로 변경 (암호화된 데이터 저장용)
    $sql = "ALTER TABLE user_logs MODIFY COLUMN ip_address TEXT";
    $db->execute($sql);
    echo "✅ user_logs.ip_address → TEXT 타입 변경\n";

    $sql = "ALTER TABLE user_sessions MODIFY COLUMN ip_address TEXT";
    $db->execute($sql);
    echo "✅ user_sessions.ip_address → TEXT 타입 변경\n";

    $db->commit();

    echo "\n3. 향후 IP 주소 처리 방식\n";
    echo "-----------------------------------\n";
    echo "🔄 앞으로의 처리 방식:\n";
    echo "- 수집: 원본 IP 주소 그대로 수집\n";
    echo "- 저장: SecurityHelper::encryptIpAddress() 암호화 저장\n";
    echo "- 조회: SecurityHelper::decryptIpAddress() 복호화 조회\n";
    echo "- 표시: SecurityHelper::maskIpAddress() 마스킹 표시\n\n";

    echo "4. 보안 사고 대응을 위한 IP 관리 방식\n";
    echo "-----------------------------------\n";
    echo "```php\n";
    echo "// 1. IP 주소 저장 (로그인, 접속 기록 등)\n";
    echo "\$clientIp = \$_SERVER['REMOTE_ADDR']; // 예: 121.78.45.123\n";
    echo "\$encryptedIp = SecurityHelper::encryptIpAddress(\$clientIp);\n";
    echo "\$sql = \"INSERT INTO user_logs (user_id, action, ip_address) VALUES (?, ?, ?)\";\n";
    echo "\$db->execute(\$sql, [\$userId, 'LOGIN', \$encryptedIp]);\n\n";

    echo "// 2. 관리자용 완전한 IP 조회 (보안 분석, 수사 협력)\n";
    echo "\$logs = \$db->fetchAll(\"SELECT * FROM user_logs WHERE action = 'SUSPICIOUS'\");\n";
    echo "foreach (\$logs as \$log) {\n";
    echo "    \$realIp = SecurityHelper::decryptIpAddress(\$log['ip_address']);\n";
    echo "    \$location = SecurityHelper::getIpLocationInfo(\$realIp);\n";
    echo "    \$security = SecurityHelper::evaluateIpSecurity(\$realIp);\n";
    echo "    \n";
    echo "    echo \"완전한 IP: {\$realIp}\"; // 121.78.45.123\n";
    echo "    echo \"지역: {\$location['country']}\"; // Korea/Foreign\n";
    echo "    echo \"위험도: {\$security['risk']}\"; // low/medium/high\n";
    echo "}\n\n";

    echo "// 3. 보안 사고 발생 시 추적\n";
    echo "\$suspiciousIp = '121.78.45.123';\n";
    echo "\$encryptedTarget = SecurityHelper::encryptIpAddress(\$suspiciousIp);\n";
    echo "\$allLogs = \$db->fetchAll(\n";
    echo "    \"SELECT * FROM user_logs WHERE ip_address = ? ORDER BY created_at\",\n";
    echo "    [\$encryptedTarget]\n";
    echo ");\n";
    echo "// → 해당 IP의 모든 활동 기록 완전 추적 가능\n";
    echo "```\n\n";

    echo "5. 테스트 실행\n";
    echo "-----------------------------------\n";

    $testIp = "192.168.1.100";
    echo "🧪 테스트 IP: {$testIp}\n";

    $encrypted = SecurityHelper::encryptIpAddress($testIp);
    echo "🔐 암호화: " . substr($encrypted, 0, 30) . "...\n";

    $decrypted = SecurityHelper::decryptIpAddress($encrypted);
    echo "🔓 복호화: {$decrypted}\n";

    $locationInfo = SecurityHelper::getIpLocationInfo($decrypted);
    echo "🌍 지역 정보: {$locationInfo['country']}\n";

    if ($testIp === $decrypted) {
        echo "✅ 암호화/복호화 정상 작동!\n\n";
    } else {
        echo "❌ 암호화/복호화 실패!\n\n";
    }

    echo "🎉 마이그레이션 완료!\n";
    echo "💡 참고: 기존 해시화된 데이터는 복원 불가능하지만,\n";
    echo "    앞으로 수집되는 모든 IP는 관리자가 확인 가능합니다.\n";

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }

    echo "\n💥 마이그레이션 실패: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== 마이그레이션 완료 ===\n";
?>