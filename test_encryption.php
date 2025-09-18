<?php
/**
 * 암호화 시스템 테스트 스크립트
 */

// 프로젝트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

require_once SRC_PATH . '/config/config.php';
require_once SRC_PATH . '/helpers/SecurityHelper.php';

echo "=== 암호화 시스템 테스트 ===\n\n";

// 테스트 데이터
$testData = [
    'phone' => '010-1234-5678',
    'email' => 'test@example.com',
    'birth_date' => '1990-05-15'
];

echo "1. 기본 암호화/복호화 테스트\n";
echo "-----------------------------------\n";

foreach ($testData as $field => $value) {
    echo "📱 {$field}: {$value}\n";

    // 암호화
    $encrypted = SecurityHelper::encrypt($value);
    echo "🔐 암호화: " . substr($encrypted, 0, 50) . "...\n";

    // 복호화
    $decrypted = SecurityHelper::decrypt($encrypted);
    echo "🔓 복호화: {$decrypted}\n";

    // 검증
    if ($value === $decrypted) {
        echo "✅ 성공\n\n";
    } else {
        echo "❌ 실패\n\n";
    }
}

echo "2. 검색 가능한 암호화 테스트\n";
echo "-----------------------------------\n";

$phone1 = '010-1234-5678';
$phone2 = '010-1234-5678'; // 동일
$phone3 = '010-9876-5432'; // 다름

$hash1 = SecurityHelper::encryptSearchable($phone1);
$hash2 = SecurityHelper::encryptSearchable($phone2);
$hash3 = SecurityHelper::encryptSearchable($phone3);

echo "📱 전화번호 1: {$phone1}\n";
echo "🔐 해시 1: {$hash1}\n\n";

echo "📱 전화번호 2: {$phone2}\n";
echo "🔐 해시 2: {$hash2}\n";
echo "✅ 동일 확인: " . ($hash1 === $hash2 ? '성공' : '실패') . "\n\n";

echo "📱 전화번호 3: {$phone3}\n";
echo "🔐 해시 3: {$hash3}\n";
echo "✅ 다름 확인: " . ($hash1 !== $hash3 ? '성공' : '실패') . "\n\n";

echo "3. 연령대 변환 테스트\n";
echo "-----------------------------------\n";

$birthDates = [
    '1990-05-15' => '30대',
    '2000-01-01' => '20대',
    '1980-12-31' => '40대',
    '1970-06-15' => '50대'
];

foreach ($birthDates as $birthDate => $expectedAge) {
    $ageGroup = SecurityHelper::convertToAgeGroup($birthDate);
    echo "🎂 생년월일: {$birthDate} → 연령대: {$ageGroup}";

    if ($ageGroup === $expectedAge) {
        echo " ✅\n";
    } else {
        echo " ❌ (예상: {$expectedAge})\n";
    }
}

echo "\n4. IP 주소 마스킹 테스트\n";
echo "-----------------------------------\n";

$ipAddresses = [
    '192.168.1.100',
    '10.0.0.1',
    '2001:db8::1'
];

foreach ($ipAddresses as $ip) {
    $masked = SecurityHelper::maskIpAddress($ip);
    echo "🌐 원본: {$ip} → 마스킹: {$masked}\n";
}

echo "\n=== 테스트 완료 ===\n";
?>