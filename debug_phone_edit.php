<?php
/**
 * 전화번호 편집 기능 디버깅
 */

// 세션 시작 (다른 출력 전에)
session_start();

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';

echo "🔍 전화번호 편집 기능 디버깅\n\n";

try {
    // 세션 설정
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    $_SESSION['csrf_token'] = 'test_token_' . uniqid();
    
    echo "1. 세션 설정 완료\n";
    
    // User 모델 테스트
    $userModel = new User();
    $userId = 5;
    
    echo "2. User 모델 인스턴스 생성 완료\n";
    
    // 현재 사용자 정보 확인
    $currentUser = $userModel->findById($userId);
    echo "3. 현재 사용자 정보:\n";
    echo "   - ID: {$currentUser['id']}\n";
    echo "   - 닉네임: {$currentUser['nickname']}\n";
    echo "   - 현재 전화번호: {$currentUser['phone']}\n";
    echo "   - 이메일: {$currentUser['email']}\n\n";
    
    // 테스트 편집 데이터 (전화번호 포함)
    $editData = [
        'nickname' => $currentUser['nickname'] . '_폰테스트_' . date('His'),
        'email' => 'phone_test_' . date('His') . '@example.com',
        'phone' => '010-9999-' . date('His') // 고유한 전화번호
    ];
    
    echo "4. 편집 데이터 준비:\n";
    foreach ($editData as $key => $value) {
        echo "   - {$key}: {$value}\n";
    }
    echo "\n";
    
    // 전화번호 중복 검사 테스트
    echo "5. 전화번호 중복 검사 테스트...\n";
    $existingUser = $userModel->findByPhone($editData['phone']);
    
    if ($existingUser && $existingUser['id'] != $userId) {
        echo "❌ 전화번호 중복: 이미 사용 중인 전화번호입니다.\n";
        echo "   사용 중인 사용자 ID: {$existingUser['id']}\n";
    } else {
        echo "✅ 전화번호 중복 없음: 사용 가능한 전화번호입니다.\n";
    }
    
    // User 모델의 updateProfile 메서드 직접 호출
    echo "\n6. User::updateProfile 메서드 호출 (전화번호 포함)...\n";
    
    $result = $userModel->updateProfile($userId, $editData);
    
    echo "📊 업데이트 결과: " . ($result ? '성공' : '실패') . "\n\n";
    
    if ($result) {
        // 변경된 데이터 확인
        $updatedUser = $userModel->findById($userId);
        echo "✅ 업데이트 성공! 변경된 데이터:\n";
        echo "  - ID: {$updatedUser['id']}\n";
        echo "  - 닉네임: {$updatedUser['nickname']}\n";
        echo "  - 이메일: {$updatedUser['email']}\n";
        echo "  - 전화번호: {$updatedUser['phone']}\n";
        echo "  - 수정일: {$updatedUser['updated_at']}\n\n";
        
        echo "🎉 전화번호 편집 기능 정상 작동!\n";
        echo "❓ 문제는 AdminController의 전화번호 검증 로직에 있을 수 있습니다.\n";
    } else {
        echo "❌ 편집 실패. User 모델에 문제가 있습니다.\n";
    }
    
    // 7. 전화번호 형식 검증 테스트
    echo "\n7. 전화번호 형식 검증 테스트...\n";
    $testPhones = [
        '010-1234-5678' => true,    // 일반적인 휴대폰 번호
        '010-123-4567' => true,     // 국번이 3자리인 경우
        '02-1234-5678' => true,     // 서울 지역번호
        '031-123-4567' => true,     // 경기 지역번호
        '01012345678' => false,     // 하이픈 없음 (허용 안함)
        '1234567890' => false,      // 0으로 시작하지 않음
        '010-12-5678' => false,     // 국번이 2자리 (허용 안함)
        'abc-def-ghij' => false     // 문자 포함
    ];
    
    $phonePattern = '/^0[0-9]{1,2}-[0-9]{3,4}-[0-9]{4}$/';
    
    foreach ($testPhones as $phone => $expected) {
        $result = preg_match($phonePattern, $phone);
        $status = $result ? '✅ 유효' : '❌ 무효';
        $expectText = $expected ? '(예상: 유효)' : '(예상: 무효)';
        $match = ($result && $expected) || (!$result && !$expected) ? '✅' : '❌';
        
        echo "  {$match} {$phone}: {$status} {$expectText}\n";
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📋 스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>