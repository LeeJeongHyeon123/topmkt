<?php
/**
 * 로그 데이터 해시화 마이그레이션 스크립트
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/helpers/SecurityHelper.php';

echo "=== 로그 데이터 해시화 마이그레이션 시작 ===\n\n";

// 데이터베이스 연결
require_once SRC_PATH . '/config/database.php';
$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 1. user_logs 테이블 처리
    echo "🔄 user_logs IP 주소 해시화 중...\n";

    $sql = "SELECT id, ip_address FROM user_logs WHERE ip_address IS NOT NULL AND LENGTH(ip_address) > 10";
    $userLogs = $db->fetchAll($sql);

    $logCount = 0;
    foreach ($userLogs as $log) {
        if (!empty($log['ip_address'])) {
            $hashedIp = SecurityHelper::maskIpAddress($log['ip_address']);
            $updateSql = "UPDATE user_logs SET ip_address = ? WHERE id = ?";
            $db->execute($updateSql, [$hashedIp, $log['id']]);
            $logCount++;
        }
    }

    echo "✅ user_logs 처리 완료: {$logCount}개 레코드\n";

    // 2. user_sessions 테이블 처리
    echo "🔄 user_sessions IP 주소 해시화 중...\n";

    $sql = "SELECT id, ip_address FROM user_sessions WHERE ip_address IS NOT NULL AND LENGTH(ip_address) > 10";
    $userSessions = $db->fetchAll($sql);

    $sessionCount = 0;
    foreach ($userSessions as $session) {
        if (!empty($session['ip_address'])) {
            $hashedIp = SecurityHelper::maskIpAddress($session['ip_address']);
            $updateSql = "UPDATE user_sessions SET ip_address = ? WHERE id = ?";
            $db->execute($updateSql, [$hashedIp, $session['id']]);
            $sessionCount++;
        }
    }

    echo "✅ user_sessions 처리 완료: {$sessionCount}개 레코드\n";

    $db->commit();

    echo "\n🎉 로그 데이터 해시화 완료!\n";
    echo "📊 총 처리된 레코드: " . ($logCount + $sessionCount) . "개\n";

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }

    echo "\n💥 마이그레이션 실패: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== 마이그레이션 완료 ===\n";
?>