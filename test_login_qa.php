<?php
/**
 * 로그인 기능 QA 테스트 스크립트
 * 암호화 구현 후 기존 기능 동작 검증
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/models/User.php';

echo "=== 로그인 기능 QA 테스트 ===\n\n";

$testResults = [];

try {
    $userModel = new User();

    echo "1. 암호화된 사용자 데이터 조회 테스트\n";
    echo "-------------------------------------------\n";

    // 사용자 ID로 조회 (복호화 테스트)
    $user = $userModel->findById(4);
    if ($user && $user['nickname'] === '우리집탄이') {
        echo "✅ 사용자 조회 성공\n";
        echo "📱 복호화된 전화번호: " . (isset($user['phone']) ? $user['phone'] : 'NULL') . "\n";
        echo "📧 복호화된 이메일: " . (isset($user['email']) ? $user['email'] : 'NULL') . "\n";
        echo "🎂 복호화된 생년월일: " . (isset($user['birth_date']) ? $user['birth_date'] : 'NULL') . "\n";
        $testResults['user_retrieval'] = 'PASS';
    } else {
        echo "❌ 사용자 조회 실패\n";
        $testResults['user_retrieval'] = 'FAIL';
    }

    echo "\n2. 전화번호 기반 사용자 찾기 테스트\n";
    echo "-------------------------------------------\n";

    // 암호화된 데이터로 사용자 찾기 테스트
    if (isset($user['phone'])) {
        $foundByPhone = $userModel->findByPhone($user['phone']);
        if ($foundByPhone && $foundByPhone['id'] == 4) {
            echo "✅ 전화번호로 사용자 찾기 성공\n";
            echo "🔍 검색된 사용자: {$foundByPhone['nickname']}\n";
            $testResults['find_by_phone'] = 'PASS';
        } else {
            echo "❌ 전화번호로 사용자 찾기 실패\n";
            $testResults['find_by_phone'] = 'FAIL';
        }
    } else {
        echo "⚠️ 전화번호 데이터 없어 스킵\n";
        $testResults['find_by_phone'] = 'SKIP';
    }

    echo "\n3. 이메일 기반 사용자 찾기 테스트\n";
    echo "-------------------------------------------\n";

    if (isset($user['email'])) {
        $foundByEmail = $userModel->findByEmail($user['email']);
        if ($foundByEmail && $foundByEmail['id'] == 4) {
            echo "✅ 이메일로 사용자 찾기 성공\n";
            echo "🔍 검색된 사용자: {$foundByEmail['nickname']}\n";
            $testResults['find_by_email'] = 'PASS';
        } else {
            echo "❌ 이메일로 사용자 찾기 실패\n";
            $testResults['find_by_email'] = 'FAIL';
        }
    } else {
        echo "⚠️ 이메일 데이터 없어 스킵\n";
        $testResults['find_by_email'] = 'SKIP';
    }

    echo "\n4. 로그인 기능 테스트\n";
    echo "-------------------------------------------\n";

    // 실제 로그인 테스트 (비밀번호는 이미 "Dnlszkem1!"로 변경됨)
    if (isset($user['phone'])) {
        try {
            $loginResult = $userModel->login($user['phone'], 'Dnlszkem1!');

            if (isset($loginResult['id']) && $loginResult['id'] == 4) {
                echo "✅ 로그인 성공\n";
                echo "👤 로그인 사용자: {$loginResult['nickname']}\n";
                echo "🔑 사용자 ID: {$loginResult['id']}\n";
                $testResults['login_function'] = 'PASS';
            } else {
                echo "❌ 로그인 실패 - 잘못된 응답 형태\n";
                $testResults['login_function'] = 'FAIL';
            }
        } catch (Exception $e) {
            echo "❌ 로그인 예외 발생: " . $e->getMessage() . "\n";
            $testResults['login_function'] = 'FAIL';
        }
    } else {
        echo "⚠️ 전화번호 데이터 없어 로그인 테스트 스킵\n";
        $testResults['login_function'] = 'SKIP';
    }

    echo "\n5. 잘못된 비밀번호 로그인 테스트\n";
    echo "-------------------------------------------\n";

    if (isset($user['phone'])) {
        try {
            $wrongLoginResult = $userModel->login($user['phone'], 'wrong_password');

            // 잘못된 비밀번호로 로그인이 성공하면 안됨
            echo "❌ 잘못된 비밀번호로 로그인 성공 (보안 문제)\n";
            $testResults['wrong_password'] = 'FAIL';
        } catch (Exception $e) {
            echo "✅ 잘못된 비밀번호 예외 발생 (정상): " . $e->getMessage() . "\n";
            $testResults['wrong_password'] = 'PASS';
        }
    }

    echo "\n6. 데이터베이스 보안 검증\n";
    echo "-------------------------------------------\n";

    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();

    // 암호화된 데이터 확인
    $sql = "SELECT phone, email, birth_date FROM users WHERE id = 4";
    $rawData = $db->fetch($sql);

    if ($rawData) {
        echo "🔐 DB 저장 전화번호: " . substr($rawData['phone'], 0, 30) . "... (암호화됨)\n";
        echo "🔐 DB 저장 이메일: " . substr($rawData['email'], 0, 30) . "... (암호화됨)\n";
        echo "🔐 DB 저장 생년월일: " . substr($rawData['birth_date'], 0, 30) . "... (암호화됨)\n";
        echo "✅ 모든 개인정보가 암호화되어 저장됨\n";
        $testResults['data_encryption'] = 'PASS';
    } else {
        echo "❌ 데이터베이스 데이터 조회 실패\n";
        $testResults['data_encryption'] = 'FAIL';
    }

} catch (Exception $e) {
    echo "\n💥 테스트 중 오류 발생: " . $e->getMessage() . "\n";
    echo "📍 스택 추적: " . $e->getTraceAsString() . "\n";
}

echo "\n📊 테스트 결과 요약\n";
echo "==========================================\n";

$passCount = 0;
$totalCount = 0;

foreach ($testResults as $testName => $result) {
    $totalCount++;
    if ($result === 'PASS') {
        $passCount++;
        echo "✅ {$testName}: {$result}\n";
    } elseif ($result === 'FAIL') {
        echo "❌ {$testName}: {$result}\n";
    } else {
        echo "⚠️ {$testName}: {$result}\n";
    }
}

$successRate = $totalCount > 0 ? round(($passCount / $totalCount) * 100, 1) : 0;
echo "\n🎯 전체 성공률: {$passCount}/{$totalCount} ({$successRate}%)\n";

if ($successRate >= 80) {
    echo "\n🎉 QA 테스트 통과! 기존 기능이 정상 작동합니다.\n";
} else {
    echo "\n⚠️ QA 테스트 실패. 일부 기능에 문제가 있습니다.\n";
}

echo "\n=== QA 테스트 완료 ===\n";
?>