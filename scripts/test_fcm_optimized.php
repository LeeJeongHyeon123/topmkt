#!/usr/bin/env php
<?php
/**
 * FCM API 최적화 버전 테스트
 * DB 부하 최소화 검증 (중복 호출 시 SKIP 확인)
 *
 * 실행: php scripts/test_fcm_optimized.php
 */

// 기본 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

// 필요한 파일 로드
require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/FcmToken.php';
require_once SRC_PATH . '/helpers/WebLogger.php';

echo "\n";
echo "========================================\n";
echo "FCM API 최적화 검증 테스트\n";
echo "========================================\n\n";

// 색상 코드
$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$BLUE = "\033[0;34m";
$NC = "\033[0m"; // No Color

// 테스트 사용자 ID
function getTestUserId() {
    try {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT id, nickname FROM users WHERE role IN ('ROLE_ADMIN', 'ROLE_CORP') AND status = 'active' LIMIT 1");

        if ($user) {
            echo "📋 테스트 사용자: ID={$user['id']}, 닉네임={$user['nickname']}\n\n";
            return $user['id'];
        }
    } catch (Exception $e) {
        echo "❌ 테스트 사용자 조회 실패: {$e->getMessage()}\n";
    }

    return null;
}

$testUserId = getTestUserId();

if (!$testUserId) {
    echo "{$RED}❌ 테스트 사용자를 찾을 수 없습니다.{$NC}\n";
    exit(1);
}

$fcmToken = new FcmToken();
$testToken = 'optimized_test_token_' . time();

echo "========================================\n";
echo "테스트 시나리오: 메인 페이지 10번 진입\n";
echo "========================================\n\n";

echo "상황: 사용자가 앱으로 메인 페이지를 10번 방문\n";
echo "      (앱은 매번 registerFCMTokenFromApp 호출)\n\n";

$results = [];
$dbWrites = 0;

for ($i = 1; $i <= 10; $i++) {
    echo "{$BLUE}[{$i}/10]{$NC} 메인 페이지 진입 시뮬레이션...\n";

    // 앱에서 JavaScript 함수 호출 시뮬레이션
    $result = $fcmToken->registerToken(
        $testUserId,
        $testToken,
        'android',
        'Samsung Galaxy S23',
        '1.0.0'
    );

    $action = $result['action'];
    $changed = $result['changed'] ? 'DB 쓰기' : 'DB 읽기만';

    if ($result['changed']) {
        $dbWrites++;
    }

    if ($action === 'skipped') {
        echo "   └─ {$GREEN}⏭️  SKIP{$NC} ({$changed})\n";
    } elseif ($action === 'inserted') {
        echo "   └─ {$YELLOW}➕ INSERT{$NC} ({$changed})\n";
    } elseif ($action === 'updated') {
        echo "   └─ {$YELLOW}🔄 UPDATE{$NC} ({$changed})\n";
    }

    $results[] = $result;

    // 0.1초 대기 (실제 사용자 행동 시뮬레이션)
    usleep(100000);
}

echo "\n========================================\n";
echo "📊 테스트 결과 분석\n";
echo "========================================\n\n";

// 통계 집계
$actions = array_count_values(array_column($results, 'action'));
$insertCount = $actions['inserted'] ?? 0;
$updateCount = $actions['updated'] ?? 0;
$skipCount = $actions['skipped'] ?? 0;

echo "총 호출 횟수: {$BLUE}10회{$NC}\n";
echo "  ➕ INSERT: {$insertCount}회\n";
echo "  🔄 UPDATE: {$updateCount}회\n";
echo "  ⏭️  SKIP:   {$GREEN}{$skipCount}회{$NC}\n\n";

echo "DB 부하:\n";
echo "  쓰기 (INSERT/UPDATE): {$YELLOW}{$dbWrites}회{$NC}\n";
echo "  읽기 (SELECT):        {$GREEN}10회{$NC}\n\n";

// 최적화 효과 계산
$optimizationRate = (1 - ($dbWrites / 10)) * 100;

echo "최적화 효과:\n";
echo "  기존 방식 (매번 UPSERT): 10회 쓰기\n";
echo "  최적화 방식:             {$dbWrites}회 쓰기\n";
echo "  절감률:                  {$GREEN}" . round($optimizationRate, 1) . "%{$NC}\n\n";

// 결과 평가
if ($skipCount >= 8) {
    echo "{$GREEN}✅ 최적화 성공!{$NC}\n";
    echo "   80% 이상의 호출이 SKIP되었습니다 (DB 부하 최소화)\n\n";
} else {
    echo "{$RED}⚠️  최적화 미흡{$NC}\n";
    echo "   SKIP 비율이 낮습니다 (예상: 80% 이상, 실제: " . ($skipCount / 10 * 100) . "%)\n\n";
}

// 정리
echo "========================================\n";
echo "정리 작업\n";
echo "========================================\n\n";

$db = Database::getInstance();
$db->execute("DELETE FROM fcm_tokens WHERE fcm_token = ?", [$testToken]);
echo "✅ 테스트 토큰 삭제 완료\n\n";

echo "========================================\n";
echo "FCM API 최적화 검증 완료\n";
echo "========================================\n\n";

exit($skipCount >= 8 ? 0 : 1);
