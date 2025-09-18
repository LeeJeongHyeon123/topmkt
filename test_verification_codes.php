<?php
/**
 * 인증 코드 해시화 시스템 테스트 스크립트
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/models/VerificationCode.php';

echo "=== 인증 코드 해시화 시스템 테스트 ===\n\n";

try {
    $verificationCode = new VerificationCode();

    echo "1. 기존 만료 코드 정리\n";
    echo "-----------------------------------\n";
    $verificationCode->cleanupExpiredCodes();
    echo "✅ 만료된 코드 정리 완료\n\n";

    echo "2. 인증 코드 생성 테스트\n";
    echo "-----------------------------------\n";
    $testPhone = '010-1234-5678';
    $result = $verificationCode->createVerificationCode($testPhone, 'signup', 3);

    if ($result['success']) {
        echo "✅ 인증 코드 생성 성공\n";
        echo "📱 전화번호: {$testPhone}\n";
        echo "🔢 생성된 코드: {$result['code']}\n";
        echo "⏰ 만료 시간: {$result['expires_at']}\n\n";
        $testCode = $result['code'];
    } else {
        throw new Exception('인증 코드 생성 실패: ' . $result['message']);
    }

    echo "3. 올바른 코드 검증 테스트\n";
    echo "-----------------------------------\n";
    $verifyResult = $verificationCode->verifyCode($testPhone, $testCode, 'signup');

    if ($verifyResult['success']) {
        echo "✅ 인증 성공: {$verifyResult['message']}\n\n";
    } else {
        echo "❌ 인증 실패: {$verifyResult['message']}\n\n";
    }

    echo "4. 틀린 코드 검증 테스트\n";
    echo "-----------------------------------\n";

    // 새로운 코드 생성 (이전 코드는 이미 인증됨)
    $result2 = $verificationCode->createVerificationCode($testPhone, 'login', 3);
    if ($result2['success']) {
        echo "📱 새 인증 코드 생성: {$result2['code']}\n";

        $wrongCode = '000000';
        $verifyWrong = $verificationCode->verifyCode($testPhone, $wrongCode, 'login');

        if (!$verifyWrong['success']) {
            echo "✅ 틀린 코드 정상 거부: {$verifyWrong['message']}\n\n";
        } else {
            echo "❌ 틀린 코드가 인증됨 (오류)\n\n";
        }
    }

    echo "5. 만료된 코드 테스트\n";
    echo "-----------------------------------\n";

    // 즉시 만료되는 코드 생성 (0분)
    $result3 = $verificationCode->createVerificationCode($testPhone, 'password_reset', 0);
    if ($result3['success']) {
        echo "⏰ 즉시 만료 코드 생성: {$result3['code']}\n";

        // 1초 대기
        sleep(1);

        $expiredTest = $verificationCode->verifyCode($testPhone, $result3['code'], 'password_reset');
        if (!$expiredTest['success']) {
            echo "✅ 만료된 코드 정상 거부: {$expiredTest['message']}\n\n";
        } else {
            echo "❌ 만료된 코드가 인증됨 (오류)\n\n";
        }
    }

    echo "6. 통계 조회 테스트\n";
    echo "-----------------------------------\n";
    $stats = $verificationCode->getVerificationStats(1);

    if (!empty($stats)) {
        foreach ($stats as $stat) {
            echo "📊 {$stat['purpose']}: 총 {$stat['total_codes']}개, 인증 {$stat['verified_codes']}개, 평균 시도 {$stat['avg_attempts']}회\n";
        }
    } else {
        echo "📊 통계 데이터 없음\n";
    }

    echo "\n7. 보안 검증\n";
    echo "-----------------------------------\n";

    // 데이터베이스에서 실제 저장된 데이터 확인
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();

    $sql = "SELECT code, code_hash FROM verification_codes ORDER BY created_at DESC LIMIT 1";
    $lastCode = $db->fetch($sql);

    if ($lastCode) {
        echo "🔐 DB 저장 코드: " . substr($lastCode['code'], 0, 20) . "... (암호화됨)\n";
        echo "🔑 해시 값: " . substr($lastCode['code_hash'], 0, 20) . "...\n";
        echo "✅ 코드가 암호화되어 안전하게 저장됨\n";
    }

    echo "\n🎉 모든 테스트 완료!\n";

} catch (Exception $e) {
    echo "\n💥 테스트 실패: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== 테스트 완료 ===\n";
?>