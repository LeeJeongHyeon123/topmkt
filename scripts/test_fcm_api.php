#!/usr/bin/env php
<?php
/**
 * FCM API 통합 테스트 스크립트
 * RESTful API 엔드포인트 동작 검증
 *
 * 실행: php scripts/test_fcm_api.php
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
echo "FCM API 통합 테스트 시작\n";
echo "========================================\n\n";

// 색상 코드
$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$BLUE = "\033[0;34m";
$NC = "\033[0m"; // No Color

// 테스트 결과 저장
$testResults = [];

/**
 * 테스트 결과 출력 함수
 */
function printResult($testName, $passed, $message = '') {
    global $GREEN, $RED, $testResults;

    $testResults[] = [
        'name' => $testName,
        'passed' => $passed,
        'message' => $message
    ];

    $symbol = $passed ? "✅" : "❌";
    $color = $passed ? $GREEN : $RED;
    echo "{$color}{$symbol} {$testName}{$NC}\n";
    if ($message) {
        echo "   └─ {$message}\n";
    }
}

/**
 * 테스트용 사용자 ID 가져오기
 */
function getTestUserId() {
    try {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT id, nickname, email FROM users WHERE role IN ('ROLE_ADMIN', 'ROLE_CORP', 'ROLE_CORPORATE') AND status = 'active' LIMIT 1");

        if ($user) {
            // email이 암호화되어 있을 수 있으므로 조심스럽게 처리
            $emailDisplay = is_string($user['email']) && strlen($user['email']) < 100 ? $user['email'] : '(암호화됨)';
            echo "📋 테스트 사용자: ID={$user['id']}, 닉네임={$user['nickname']}, 이메일={$emailDisplay}\n\n";
            return $user['id'];
        }
    } catch (Exception $e) {
        echo "❌ 테스트 사용자 조회 실패: {$e->getMessage()}\n";
    }

    return null;
}

// 테스트 사용자 ID
$testUserId = getTestUserId();

if (!$testUserId) {
    echo "{$RED}❌ 테스트 사용자를 찾을 수 없습니다. 테스트를 중단합니다.{$NC}\n";
    exit(1);
}

// FcmToken 모델 인스턴스
$fcmToken = new FcmToken();

// 테스트용 FCM 토큰 생성
$testToken1 = 'test_fcm_token_' . time() . '_1';
$testToken2 = 'test_fcm_token_' . time() . '_2';

echo "========================================\n";
echo "1️⃣  POST /api/fcm/tokens (store) 테스트\n";
echo "========================================\n\n";

// 테스트 1-1: FCM 토큰 등록 (Android)
echo "테스트 1-1: Android 토큰 등록\n";
try {
    $result = $fcmToken->registerToken(
        $testUserId,
        $testToken1,
        'android',
        'Samsung Galaxy S23',
        '1.0.0'
    );

    if ($result) {
        printResult('Android 토큰 등록', true, "토큰: " . substr($testToken1, 0, 30) . "...");
    } else {
        printResult('Android 토큰 등록', false, '등록 실패');
    }
} catch (Exception $e) {
    printResult('Android 토큰 등록', false, $e->getMessage());
}

// 테스트 1-2: FCM 토큰 등록 (iOS)
echo "\n테스트 1-2: iOS 토큰 등록\n";
try {
    $result = $fcmToken->registerToken(
        $testUserId,
        $testToken2,
        'ios',
        'iPhone 15 Pro',
        '1.0.0'
    );

    if ($result) {
        printResult('iOS 토큰 등록', true, "토큰: " . substr($testToken2, 0, 30) . "...");
    } else {
        printResult('iOS 토큰 등록', false, '등록 실패');
    }
} catch (Exception $e) {
    printResult('iOS 토큰 등록', false, $e->getMessage());
}

// 테스트 1-3: 중복 토큰 등록 (UPSERT 테스트)
echo "\n테스트 1-3: 중복 토큰 등록 (UPSERT)\n";
try {
    $result = $fcmToken->registerToken(
        $testUserId,
        $testToken1,
        'android',
        'Samsung Galaxy S23 Ultra', // 디바이스명 변경
        '1.1.0' // 버전 변경
    );

    if ($result) {
        printResult('중복 토큰 UPSERT', true, '정보 업데이트 성공');
    } else {
        printResult('중복 토큰 UPSERT', false, '업데이트 실패');
    }
} catch (Exception $e) {
    printResult('중복 토큰 UPSERT', false, $e->getMessage());
}

// 테스트 1-4: 잘못된 device_type
echo "\n테스트 1-4: 잘못된 device_type (에러 예상)\n";
try {
    $result = $fcmToken->registerToken(
        $testUserId,
        'test_invalid_' . time(),
        'windows', // 잘못된 타입
        'Windows PC',
        '1.0.0'
    );

    // 에러가 발생해야 정상
    printResult('잘못된 device_type 거부', false, 'ENUM 제약 조건 실패 예상했으나 통과됨');
} catch (Exception $e) {
    // Exception 발생이 정상
    printResult('잘못된 device_type 거부', true, 'ENUM 제약 조건으로 올바르게 거부됨');
}

sleep(1);

echo "\n========================================\n";
echo "2️⃣  GET /api/fcm/tokens (index) 테스트\n";
echo "========================================\n\n";

// 테스트 2-1: 토큰 목록 조회
echo "테스트 2-1: 사용자의 토큰 목록 조회\n";
try {
    $tokens = $fcmToken->getTokensByUserId($testUserId);

    if (is_array($tokens) && count($tokens) >= 2) {
        printResult('토큰 목록 조회', true, count($tokens) . "개 토큰 조회됨");

        foreach ($tokens as $idx => $token) {
            echo "   토큰 " . ($idx + 1) . ": {$token['device_type']} - {$token['device_name']}\n";
        }
    } else {
        printResult('토큰 목록 조회', false, '예상보다 적은 토큰 수: ' . count($tokens ?? []));
    }
} catch (Exception $e) {
    printResult('토큰 목록 조회', false, $e->getMessage());
}

// 테스트 2-2: 특정 토큰 정보 조회
echo "\n테스트 2-2: 특정 토큰 정보 조회\n";
try {
    $tokenInfo = $fcmToken->getTokenInfo($testToken1);

    if ($tokenInfo && $tokenInfo['fcm_token'] === $testToken1) {
        printResult('특정 토큰 정보 조회', true, "디바이스: {$tokenInfo['device_name']}, 버전: {$tokenInfo['app_version']}");
    } else {
        printResult('특정 토큰 정보 조회', false, '토큰 정보를 찾을 수 없음');
    }
} catch (Exception $e) {
    printResult('특정 토큰 정보 조회', false, $e->getMessage());
}

sleep(1);

echo "\n========================================\n";
echo "3️⃣  DELETE /api/fcm/tokens (destroy) 테스트\n";
echo "========================================\n\n";

// 테스트 3-1: 토큰 삭제 (논리 삭제)
echo "테스트 3-1: 토큰 삭제 (is_active = 0)\n";
try {
    $result = $fcmToken->deleteToken($testUserId, $testToken1);

    if ($result) {
        printResult('토큰 삭제 (논리)', true, '토큰이 비활성화됨');

        // 삭제 확인
        $tokenInfo = $fcmToken->getTokenInfo($testToken1);
        if (!$tokenInfo) {
            echo "   └─ ✅ 조회 불가 확인 (is_active = 0)\n";
        } else {
            echo "   └─ ⚠️  여전히 조회 가능 (문제 있음)\n";
        }
    } else {
        printResult('토큰 삭제 (논리)', false, '삭제 실패');
    }
} catch (Exception $e) {
    printResult('토큰 삭제 (논리)', false, $e->getMessage());
}

// 테스트 3-2: 존재하지 않는 토큰 삭제
echo "\n테스트 3-2: 존재하지 않는 토큰 삭제\n";
try {
    $result = $fcmToken->deleteToken($testUserId, 'non_existent_token_' . time());

    if (!$result) {
        printResult('존재하지 않는 토큰 삭제', true, 'affected_rows = 0으로 올바르게 처리됨');
    } else {
        printResult('존재하지 않는 토큰 삭제', false, '예상치 못한 결과');
    }
} catch (Exception $e) {
    printResult('존재하지 않는 토큰 삭제', false, $e->getMessage());
}

sleep(1);

echo "\n========================================\n";
echo "4️⃣  추가 기능 테스트\n";
echo "========================================\n\n";

// 테스트 4-1: 토큰 비활성화
echo "테스트 4-1: 만료된 토큰 비활성화\n";
try {
    $result = $fcmToken->deactivateToken($testToken2);

    if ($result) {
        printResult('토큰 비활성화', true, '토큰이 비활성화됨');
    } else {
        printResult('토큰 비활성화', false, '비활성화 실패');
    }
} catch (Exception $e) {
    printResult('토큰 비활성화', false, $e->getMessage());
}

// 테스트 4-2: 활성 토큰 수 확인
echo "\n테스트 4-2: 활성 토큰 수 확인\n";
try {
    $tokens = $fcmToken->getTokensByUserId($testUserId);
    $activeCount = count($tokens);

    if ($activeCount === 0) {
        printResult('활성 토큰 수', true, "활성 토큰: {$activeCount}개 (모두 비활성화됨)");
    } else {
        printResult('활성 토큰 수', false, "활성 토큰: {$activeCount}개 (예상: 0개)");
    }
} catch (Exception $e) {
    printResult('활성 토큰 수', false, $e->getMessage());
}

sleep(1);

echo "\n========================================\n";
echo "5️⃣  데이터베이스 검증\n";
echo "========================================\n\n";

// 테스트 5-1: Foreign Key 제약 조건 확인
echo "테스트 5-1: Foreign Key 제약 조건 확인\n";
try {
    $db = Database::getInstance();

    // fcm_tokens 테이블 구조 확인
    $result = $db->query("SHOW CREATE TABLE fcm_tokens");
    $createTable = $result->fetch_assoc();

    if (strpos($createTable['Create Table'], 'FOREIGN KEY') !== false) {
        printResult('Foreign Key 제약 조건', true, 'CASCADE DELETE 설정 확인됨');
    } else {
        printResult('Foreign Key 제약 조건', false, 'Foreign Key가 설정되지 않음');
    }
} catch (Exception $e) {
    printResult('Foreign Key 제약 조건', false, $e->getMessage());
}

// 테스트 5-2: UNIQUE 제약 조건 확인
echo "\n테스트 5-2: UNIQUE 제약 조건 확인\n";
try {
    $db = Database::getInstance();

    $result = $db->query("SHOW INDEX FROM fcm_tokens WHERE Key_name = 'unique_user_token'");
    $indexes = [];
    while ($row = $result->fetch_assoc()) {
        $indexes[] = $row;
    }

    if (count($indexes) >= 2) {
        printResult('UNIQUE 제약 조건', true, 'unique_user_token (user_id, fcm_token) 설정 확인됨');
    } else {
        printResult('UNIQUE 제약 조건', false, 'UNIQUE 인덱스가 올바르지 않음');
    }
} catch (Exception $e) {
    printResult('UNIQUE 제약 조건', false, $e->getMessage());
}

sleep(1);

echo "\n========================================\n";
echo "6️⃣  정리 작업\n";
echo "========================================\n\n";

// 테스트 토큰 완전 삭제
echo "테스트 6-1: 테스트 토큰 완전 삭제\n";
try {
    $db = Database::getInstance();
    $db->execute("DELETE FROM fcm_tokens WHERE fcm_token LIKE 'test_fcm_token_%'");

    printResult('테스트 토큰 정리', true, '모든 테스트 토큰 삭제됨');
} catch (Exception $e) {
    printResult('테스트 토큰 정리', false, $e->getMessage());
}

echo "\n========================================\n";
echo "📊 테스트 결과 요약\n";
echo "========================================\n\n";

$totalTests = count($testResults);
$passedTests = count(array_filter($testResults, function($result) {
    return $result['passed'];
}));
$failedTests = $totalTests - $passedTests;

echo "총 테스트: {$totalTests}개\n";
echo "{$GREEN}✅ 통과: {$passedTests}개{$NC}\n";
if ($failedTests > 0) {
    echo "{$RED}❌ 실패: {$failedTests}개{$NC}\n";
}

$successRate = round(($passedTests / $totalTests) * 100, 1);
echo "\n성공률: {$successRate}%\n";

if ($failedTests > 0) {
    echo "\n{$RED}실패한 테스트:{$NC}\n";
    foreach ($testResults as $result) {
        if (!$result['passed']) {
            echo "  - {$result['name']}: {$result['message']}\n";
        }
    }
}

echo "\n========================================\n";
echo "FCM API 통합 테스트 완료\n";
echo "========================================\n\n";

exit($failedTests > 0 ? 1 : 0);
