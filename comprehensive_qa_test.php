<?php
/**
 * 포괄적 QA 테스트 스크립트
 * 전체 보안 강화 시스템 통합 검증
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/models/Corporate.php';
require_once SRC_PATH . '/models/VerificationCode.php';

echo "🔍 === 탑마케팅 시스템 포괄적 QA 테스트 === 🔍\n\n";

$testResults = [];
$startTime = microtime(true);

try {
    // 1. 개인정보 암호화 시스템 테스트
    echo "1️⃣ 개인정보 암호화 시스템 테스트\n";
    echo "===============================================\n";

    $userModel = new User();

    // 사용자 조회 및 복호화 테스트
    $user = $userModel->findById(4);
    if ($user && $user['nickname'] === '우리집탄이') {
        echo "✅ 사용자 데이터 조회 성공\n";
        echo "📱 복호화된 전화번호: " . ($user['phone'] ?? 'NULL') . "\n";
        echo "📧 복호화된 이메일: " . ($user['email'] ?? 'NULL') . "\n";
        echo "🎂 복호화된 생년월일: " . ($user['birth_date'] ?? 'NULL') . "\n";
        $testResults['personal_data_encryption'] = 'PASS';
    } else {
        echo "❌ 사용자 데이터 조회 실패\n";
        $testResults['personal_data_encryption'] = 'FAIL';
    }

    // 2. 검색 해시 기능 테스트
    echo "\n2️⃣ 검색 해시 기능 테스트\n";
    echo "===============================================\n";

    if (isset($user['phone'])) {
        $foundByPhone = $userModel->findByPhone($user['phone']);
        if ($foundByPhone && $foundByPhone['id'] == 4) {
            echo "✅ 전화번호 해시 검색 성공\n";
            $testResults['phone_hash_search'] = 'PASS';
        } else {
            echo "❌ 전화번호 해시 검색 실패\n";
            $testResults['phone_hash_search'] = 'FAIL';
        }
    }

    if (isset($user['email'])) {
        $foundByEmail = $userModel->findByEmail($user['email']);
        if ($foundByEmail && $foundByEmail['id'] == 4) {
            echo "✅ 이메일 해시 검색 성공\n";
            $testResults['email_hash_search'] = 'PASS';
        } else {
            echo "❌ 이메일 해시 검색 실패\n";
            $testResults['email_hash_search'] = 'FAIL';
        }
    }

    // 3. 로그인 시스템 테스트
    echo "\n3️⃣ 로그인 시스템 테스트\n";
    echo "===============================================\n";

    if (isset($user['phone'])) {
        try {
            $loginResult = $userModel->login($user['phone'], 'Dnlszkem1!');
            if ($loginResult && $loginResult['id'] == 4) {
                echo "✅ 로그인 성공\n";
                echo "👤 로그인 사용자: {$loginResult['nickname']}\n";
                $testResults['login_function'] = 'PASS';
            } else {
                echo "❌ 로그인 실패\n";
                $testResults['login_function'] = 'FAIL';
            }
        } catch (Exception $e) {
            echo "❌ 로그인 예외: " . $e->getMessage() . "\n";
            $testResults['login_function'] = 'FAIL';
        }
    }

    // 4. 기업정보 암호화 테스트
    echo "\n4️⃣ 기업정보 암호화 테스트\n";
    echo "===============================================\n";

    $corporateModel = new Corporate();
    $profile = $corporateModel->getCompanyProfile(4); // 우리집탄이 계정

    if ($profile) {
        echo "✅ 기업 프로필 조회 성공\n";
        echo "🏢 회사명: " . ($profile['company_name'] ?? 'NULL') . "\n";
        echo "📄 사업자번호: " . ($profile['business_number'] ?? 'NULL') . "\n";
        echo "📞 대표자 전화: " . ($profile['representative_phone'] ?? 'NULL') . "\n";
        $testResults['corporate_data_encryption'] = 'PASS';
    } else {
        echo "⚠️ 기업 프로필 없음 (정상 - 기업 회원 아님)\n";
        $testResults['corporate_data_encryption'] = 'SKIP';
    }

    // 5. 인증 코드 시스템 테스트
    echo "\n5️⃣ 인증 코드 시스템 테스트\n";
    echo "===============================================\n";

    $verificationModel = new VerificationCode();

    // 인증 코드 생성 테스트
    $testPhone = '010-9999-8888';
    $codeResult = $verificationModel->createVerificationCode($testPhone, 'signup', 1);

    if ($codeResult['success']) {
        echo "✅ 인증 코드 생성 성공\n";
        echo "🔢 생성된 코드: {$codeResult['code']}\n";

        // 인증 코드 검증 테스트
        $verifyResult = $verificationModel->verifyCode($testPhone, $codeResult['code'], 'signup');
        if ($verifyResult['success']) {
            echo "✅ 인증 코드 검증 성공\n";
            $testResults['verification_code_system'] = 'PASS';
        } else {
            echo "❌ 인증 코드 검증 실패: {$verifyResult['message']}\n";
            $testResults['verification_code_system'] = 'FAIL';
        }
    } else {
        echo "❌ 인증 코드 생성 실패\n";
        if (isset($codeResult['message'])) {
            echo "🚫 오류 메시지: {$codeResult['message']}\n";
        }
        echo "🔍 반환된 데이터: " . json_encode($codeResult) . "\n";
        $testResults['verification_code_system'] = 'FAIL';
    }

    // 6. IP 주소 암호화 테스트
    echo "\n6️⃣ IP 주소 암호화 시스템 테스트\n";
    echo "===============================================\n";

    $testIps = [
        '192.168.1.100',    // 내부 IP
        '121.78.45.123',    // 외부 IP (가상)
        '8.8.8.8'           // 해외 IP
    ];

    $ipTestPassed = 0;
    foreach ($testIps as $testIp) {
        echo "🧪 테스트 IP: {$testIp}\n";

        $encrypted = SecurityHelper::encryptIpAddress($testIp);
        echo "🔐 암호화: " . substr($encrypted, 0, 30) . "...\n";

        $decrypted = SecurityHelper::decryptIpAddress($encrypted);
        echo "🔓 복호화: {$decrypted}\n";

        $locationInfo = SecurityHelper::getIpLocationInfo($decrypted);
        echo "🌍 지역 정보: {$locationInfo['country']}\n";

        $securityInfo = SecurityHelper::evaluateIpSecurity($decrypted);
        echo "🛡️ 위험도: {$securityInfo['risk']}\n";

        if ($testIp === $decrypted) {
            echo "✅ 암복호화 성공\n\n";
            $ipTestPassed++;
        } else {
            echo "❌ 암복호화 실패\n\n";
        }
    }

    $testResults['ip_encryption_system'] = ($ipTestPassed === count($testIps)) ? 'PASS' : 'FAIL';

    // 7. 데이터베이스 보안 검증
    echo "7️⃣ 데이터베이스 보안 검증\n";
    echo "===============================================\n";

    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();

    // 암호화된 데이터 확인
    $sql = "SELECT phone, email, birth_date FROM users WHERE id = 4";
    $rawData = $db->fetch($sql);

    if ($rawData) {
        $encryptedFields = 0;

        // 전화번호 암호화 확인
        if (strlen($rawData['phone']) > 20) {
            echo "🔐 전화번호: 암호화됨 (" . strlen($rawData['phone']) . "자)\n";
            $encryptedFields++;
        } else {
            echo "⚠️ 전화번호: 평문 상태\n";
        }

        // 이메일 암호화 확인
        if (strlen($rawData['email']) > 20) {
            echo "🔐 이메일: 암호화됨 (" . strlen($rawData['email']) . "자)\n";
            $encryptedFields++;
        } else {
            echo "⚠️ 이메일: 평문 상태\n";
        }

        // 생년월일 암호화 확인
        if (strlen($rawData['birth_date']) > 20) {
            echo "🔐 생년월일: 암호화됨 (" . strlen($rawData['birth_date']) . "자)\n";
            $encryptedFields++;
        } else {
            echo "⚠️ 생년월일: 평문 상태\n";
        }

        $testResults['database_security'] = ($encryptedFields === 3) ? 'PASS' : 'PARTIAL';
    } else {
        echo "❌ 데이터베이스 데이터 조회 실패\n";
        $testResults['database_security'] = 'FAIL';
    }

    // 8. 보안 사고 대응 시나리오 테스트
    echo "\n8️⃣ 보안 사고 대응 시나리오 테스트\n";
    echo "===============================================\n";

    // 가상의 보안 사고 시나리오
    $suspiciousIp = '121.78.45.123';
    echo "🚨 가상 보안 사고: IP {$suspiciousIp}에서 의심스러운 활동 감지\n";

    // IP 암호화하여 검색 준비
    $encryptedSuspiciousIp = SecurityHelper::encryptIpAddress($suspiciousIp);
    echo "🔍 암호화된 IP로 로그 검색 준비 완료\n";

    // IP 지역 정보 및 위험도 평가
    $locationInfo = SecurityHelper::getIpLocationInfo($suspiciousIp);
    $securityInfo = SecurityHelper::evaluateIpSecurity($suspiciousIp);

    echo "📍 IP 지역: {$locationInfo['country']}\n";
    echo "⚠️ 위험도: {$securityInfo['risk']}\n";
    echo "📝 보안 노트: " . implode(', ', $securityInfo['notes']) . "\n";

    // 관리자가 완전한 IP 추적 가능한지 확인
    $decryptedForInvestigation = SecurityHelper::decryptIpAddress($encryptedSuspiciousIp);
    if ($suspiciousIp === $decryptedForInvestigation) {
        echo "✅ 수사 협력용 완전한 IP 복원 가능\n";
        echo "🚔 수사기관 제공 가능한 IP: {$decryptedForInvestigation}\n";
        $testResults['security_incident_response'] = 'PASS';
    } else {
        echo "❌ IP 복원 실패 - 수사 협력 불가\n";
        $testResults['security_incident_response'] = 'FAIL';
    }

} catch (Exception $e) {
    echo "\n💥 테스트 중 예외 발생: " . $e->getMessage() . "\n";
    echo "📍 스택 추적: " . $e->getTraceAsString() . "\n";
}

// 9. 성능 테스트
echo "\n9️⃣ 성능 테스트\n";
echo "===============================================\n";

$endTime = microtime(true);
$executionTime = round(($endTime - $startTime) * 1000, 2);

echo "⏱️ 전체 테스트 실행 시간: {$executionTime}ms\n";

if ($executionTime < 5000) {
    echo "✅ 성능: 우수 (5초 미만)\n";
    $testResults['performance'] = 'PASS';
} elseif ($executionTime < 10000) {
    echo "⚠️ 성능: 보통 (5-10초)\n";
    $testResults['performance'] = 'ACCEPTABLE';
} else {
    echo "❌ 성능: 느림 (10초 이상)\n";
    $testResults['performance'] = 'FAIL';
}

// 10. 종합 결과 분석
echo "\n🏆 === 종합 테스트 결과 === 🏆\n";
echo "===============================================\n";

$passCount = 0;
$failCount = 0;
$skipCount = 0;
$totalTests = count($testResults);

foreach ($testResults as $testName => $result) {
    switch ($result) {
        case 'PASS':
            echo "✅ {$testName}: 통과\n";
            $passCount++;
            break;
        case 'FAIL':
            echo "❌ {$testName}: 실패\n";
            $failCount++;
            break;
        case 'SKIP':
            echo "⏭️ {$testName}: 스킵\n";
            $skipCount++;
            break;
        case 'PARTIAL':
            echo "🔶 {$testName}: 부분 성공\n";
            $passCount += 0.5;
            break;
        case 'ACCEPTABLE':
            echo "🟡 {$testName}: 허용 범위\n";
            $passCount++;
            break;
    }
}

$successRate = $totalTests > 0 ? round(($passCount / $totalTests) * 100, 1) : 0;

echo "\n📊 최종 통계:\n";
echo "- 전체 테스트: {$totalTests}개\n";
echo "- 통과: {$passCount}개\n";
echo "- 실패: {$failCount}개\n";
echo "- 스킵: {$skipCount}개\n";
echo "- 성공률: {$successRate}%\n\n";

if ($successRate >= 90) {
    echo "🎉 종합 평가: 우수 (90% 이상)\n";
    echo "✨ 시스템이 안정적으로 작동하고 있습니다!\n";
} elseif ($successRate >= 80) {
    echo "👍 종합 평가: 양호 (80-90%)\n";
    echo "🔧 일부 개선이 필요하지만 전반적으로 안정적입니다.\n";
} elseif ($successRate >= 70) {
    echo "⚠️ 종합 평가: 주의 (70-80%)\n";
    echo "🛠️ 여러 개선사항이 필요합니다.\n";
} else {
    echo "🚨 종합 평가: 위험 (70% 미만)\n";
    echo "🔥 시급한 수정이 필요합니다!\n";
}

echo "\n=== QA 테스트 완료 ===\n";
?>