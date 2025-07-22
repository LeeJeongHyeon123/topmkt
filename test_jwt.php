<?php
/**
 * JWT 시스템 테스트 스크립트
 */

// 필요한 파일들 로드
require_once 'src/config/config.php';
require_once 'src/helpers/JWTHelper.php';

echo "=== JWT 시스템 테스트 ===\n\n";

// 1. 테스트 사용자 데이터
$testUser = [
    'id' => 123,
    'nickname' => 'testuser',
    'phone' => '01012345678',
    'role' => 'GENERAL'
];

echo "1. 토큰 생성 테스트:\n";
try {
    $tokens = JWTHelper::createTokenPair($testUser);
    echo "✅ 액세스 토큰 생성 성공\n";
    echo "✅ 리프레시 토큰 생성 성공\n";
    
    echo "\n2. 토큰 검증 테스트:\n";
    
    // 액세스 토큰 검증
    $accessPayload = JWTHelper::validateToken($tokens['access_token']);
    if ($accessPayload) {
        echo "✅ 액세스 토큰 검증 성공\n";
        echo "   사용자 ID: " . $accessPayload['user_id'] . "\n";
        echo "   만료 시간: " . date('Y-m-d H:i:s', $accessPayload['exp']) . "\n";
    } else {
        echo "❌ 액세스 토큰 검증 실패\n";
    }
    
    // 리프레시 토큰 검증  
    $refreshPayload = JWTHelper::validateToken($tokens['refresh_token']);
    if ($refreshPayload) {
        echo "✅ 리프레시 토큰 검증 성공\n";
        echo "   사용자 ID: " . $refreshPayload['user_id'] . "\n";
        echo "   만료 시간: " . date('Y-m-d H:i:s', $refreshPayload['exp']) . "\n";
    } else {
        echo "❌ 리프레시 토큰 검증 실패\n";
    }
    
    echo "\n3. 사용자 정보 추출 테스트:\n";
    $userInfo = JWTHelper::getUserFromToken($tokens['access_token']);
    if ($userInfo) {
        echo "✅ 사용자 정보 추출 성공\n";
        echo "   사용자 ID: " . $userInfo['user_id'] . "\n";
        echo "   사용자명: " . $userInfo['username'] . "\n";
        echo "   전화번호: " . $userInfo['phone'] . "\n";
        echo "   역할: " . $userInfo['user_role'] . "\n";
    } else {
        echo "❌ 사용자 정보 추출 실패\n";
    }
    
    echo "\n4. 토큰 남은 시간 테스트:\n";
    $timeLeft = JWTHelper::getTokenTimeLeft($tokens['access_token']);
    echo "✅ 액세스 토큰 남은 시간: " . $timeLeft . "초\n";
    
    $refreshTimeLeft = JWTHelper::getTokenTimeLeft($tokens['refresh_token']);
    echo "✅ 리프레시 토큰 남은 시간: " . $refreshTimeLeft . "초\n";
    
    echo "\n5. 디버그 정보 테스트:\n";
    $debugInfo = JWTHelper::debugToken($tokens['access_token']);
    echo "✅ 디버그 정보 생성 성공\n";
    echo "   만료 여부: " . ($debugInfo['is_expired'] ? '만료됨' : '유효함') . "\n";
    echo "   남은 시간: " . $debugInfo['time_left_formatted'] . "\n";
    
    echo "\n6. 잘못된 토큰 테스트:\n";
    $invalidToken = 'invalid.token.here';
    $invalidResult = JWTHelper::validateToken($invalidToken);
    if ($invalidResult === false) {
        echo "✅ 잘못된 토큰 검증 실패 (정상 동작)\n";
    } else {
        echo "❌ 잘못된 토큰이 검증 통과 (오류)\n";
    }
    
} catch (Exception $e) {
    echo "❌ 테스트 중 오류 발생: " . $e->getMessage() . "\n";
}

echo "\n=== 테스트 완료 ===\n";
?>