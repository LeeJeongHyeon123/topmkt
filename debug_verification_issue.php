<?php
/**
 * 인증 코드 시스템 오류 디버깅 스크립트
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';

echo "=== 인증 코드 시스템 오류 디버깅 ===\n\n";

try {
    // 1. 데이터베이스 연결 확인
    echo "1. 데이터베이스 연결 확인\n";
    echo "-----------------------------------\n";

    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();
    echo "✅ 데이터베이스 연결 성공\n\n";

    // 2. verification_codes 테이블 확인
    echo "2. verification_codes 테이블 확인\n";
    echo "-----------------------------------\n";

    $sql = "SHOW TABLES LIKE 'verification_codes'";
    $result = $db->fetch($sql);
    if ($result) {
        echo "✅ verification_codes 테이블 존재\n";

        // 테이블 구조 확인
        $sql = "DESCRIBE verification_codes";
        $columns = $db->fetchAll($sql);
        echo "📋 테이블 구조:\n";
        foreach ($columns as $column) {
            echo "   - {$column['Field']}: {$column['Type']}\n";
        }
    } else {
        echo "❌ verification_codes 테이블 없음\n";
        exit(1);
    }

    echo "\n3. SecurityHelper 클래스 확인\n";
    echo "-----------------------------------\n";

    require_once SRC_PATH . '/helpers/SecurityHelper.php';

    // 암호화 기능 테스트
    $testData = "123456";
    $encrypted = SecurityHelper::encrypt($testData);
    if ($encrypted !== false) {
        echo "✅ SecurityHelper::encrypt() 정상 작동\n";

        $decrypted = SecurityHelper::decrypt($encrypted);
        if ($decrypted === $testData) {
            echo "✅ SecurityHelper::decrypt() 정상 작동\n";
        } else {
            echo "❌ SecurityHelper::decrypt() 실패\n";
        }
    } else {
        echo "❌ SecurityHelper::encrypt() 실패\n";
    }

    // 검색 해시 기능 테스트
    $hash = SecurityHelper::encryptSearchable($testData);
    if ($hash !== false) {
        echo "✅ SecurityHelper::encryptSearchable() 정상 작동\n";
    } else {
        echo "❌ SecurityHelper::encryptSearchable() 실패\n";
    }

    echo "\n4. VerificationCode 클래스 로드 테스트\n";
    echo "-----------------------------------\n";

    require_once SRC_PATH . '/models/VerificationCode.php';
    echo "✅ VerificationCode 클래스 로드 성공\n";

    echo "\n5. VerificationCode 인스턴스 생성 테스트\n";
    echo "-----------------------------------\n";

    $verificationModel = new VerificationCode();
    echo "✅ VerificationCode 인스턴스 생성 성공\n";

    echo "\n6. 인증 코드 생성 단계별 테스트\n";
    echo "-----------------------------------\n";

    $testPhone = '010-9999-8888';
    $purpose = 'signup';  // ENUM 허용 값 사용
    $expiryMinutes = 1;

    echo "📱 테스트 전화번호: {$testPhone}\n";
    echo "🎯 목적: {$purpose}\n";
    echo "⏰ 만료 시간: {$expiryMinutes}분\n\n";

    // 기존 코드 정리
    echo "🧹 기존 코드 정리...\n";
    $verificationModel->cleanupExpiredCodes();

    // 수동으로 기존 코드 삭제
    $sql = "DELETE FROM verification_codes WHERE phone = ? AND purpose = ? AND verified = 0";
    $db->execute($sql, [$testPhone, $purpose]);
    echo "✅ 기존 코드 정리 완료\n";

    // 랜덤 코드 생성 테스트
    echo "\n🎲 랜덤 코드 생성 테스트...\n";
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    echo "✅ 생성된 코드: {$code}\n";

    // 암호화 테스트
    echo "\n🔐 코드 암호화 테스트...\n";
    $encryptedCode = SecurityHelper::encrypt($code);
    if ($encryptedCode !== false) {
        echo "✅ 코드 암호화 성공: " . substr($encryptedCode, 0, 30) . "...\n";
    } else {
        echo "❌ 코드 암호화 실패\n";
        exit(1);
    }

    // 해시 생성 테스트
    echo "\n🔑 해시 생성 테스트...\n";
    $codeHash = SecurityHelper::encryptSearchable($code);
    if ($codeHash !== false) {
        echo "✅ 해시 생성 성공: " . substr($codeHash, 0, 30) . "...\n";
    } else {
        echo "❌ 해시 생성 실패\n";
        exit(1);
    }

    // 만료 시간 계산 테스트
    echo "\n⏰ 만료 시간 계산 테스트...\n";
    $expiresAt = date('Y-m-d H:i:s', time() + ($expiryMinutes * 60));
    echo "✅ 만료 시간: {$expiresAt}\n";

    // 데이터베이스 삽입 테스트
    echo "\n💾 데이터베이스 삽입 테스트...\n";
    $sql = "INSERT INTO verification_codes (
        phone, code, code_hash, purpose, attempts, verified, expires_at
    ) VALUES (?, ?, ?, ?, 0, 0, ?)";

    $result = $db->execute($sql, [
        $testPhone,
        $encryptedCode,
        $codeHash,
        $purpose,
        $expiresAt
    ]);

    if ($result) {
        echo "✅ 데이터베이스 삽입 성공\n";

        // 삽입된 데이터 확인
        $sql = "SELECT * FROM verification_codes WHERE phone = ? AND purpose = ? ORDER BY created_at DESC LIMIT 1";
        $inserted = $db->fetch($sql, [$testPhone, $purpose]);

        if ($inserted) {
            echo "📋 삽입된 데이터 확인:\n";
            echo "   - ID: {$inserted['id']}\n";
            echo "   - 전화번호: {$inserted['phone']}\n";
            echo "   - 목적: {$inserted['purpose']}\n";
            echo "   - 만료시간: {$inserted['expires_at']}\n";
        }
    } else {
        echo "❌ 데이터베이스 삽입 실패\n";

        // 마지막 에러 확인
        $errorInfo = $db->getLastError();
        if ($errorInfo) {
            echo "🚫 DB 오류: " . json_encode($errorInfo) . "\n";
        }
        exit(1);
    }

    echo "\n7. 실제 VerificationCode::createVerificationCode() 메서드 테스트\n";
    echo "-------------------------------------------------------------------\n";

    // 새로운 전화번호로 테스트
    $testPhone2 = '010-8888-9999';
    echo "📱 새 테스트 전화번호: {$testPhone2}\n";

    $result = $verificationModel->createVerificationCode($testPhone2, 'signup', 5);

    echo "🔍 메서드 결과: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";

    if ($result['success']) {
        echo "✅ createVerificationCode() 메서드 성공!\n";
        echo "🔢 생성된 코드: {$result['code']}\n";
    } else {
        echo "❌ createVerificationCode() 메서드 실패!\n";
        echo "🚫 오류: {$result['message']}\n";
    }

} catch (Exception $e) {
    echo "\n💥 예외 발생: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . "\n";
    echo "📍 라인: " . $e->getLine() . "\n";
    echo "📍 스택 추적:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== 디버깅 완료 ===\n";
?>