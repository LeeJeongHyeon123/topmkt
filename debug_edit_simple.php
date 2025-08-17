<?php
/**
 * 편집 메서드 핵심 로직만 테스트
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

// 세션 시작 (다른 출력 전에)
session_start();

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';

echo "🔍 편집 메서드 핵심 로직 테스트\n\n";

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
    
    // 테스트 편집 데이터
    $editData = [
        'nickname' => '안계현_간단테스트_' . date('His'),
        'bio' => '간단 테스트 편집 - ' . date('Y-m-d H:i:s'),
        'email' => 'simple_test_' . date('His') . '@example.com'
    ];
    
    echo "3. 편집 데이터 준비:\n";
    foreach ($editData as $key => $value) {
        echo "   - {$key}: {$value}\n";
    }
    echo "\n";
    
    // User 모델의 updateProfile 메서드 직접 호출
    echo "4. User::updateProfile 메서드 호출...\n";
    
    $result = $userModel->updateProfile($userId, $editData);
    
    echo "📊 업데이트 결과: " . ($result ? '성공' : '실패') . "\n\n";
    
    if ($result) {
        // 변경된 데이터 확인
        $updatedUser = $userModel->findById($userId);
        echo "✅ 업데이트 성공! 변경된 데이터:\n";
        echo "  - ID: {$updatedUser['id']}\n";
        echo "  - 닉네임: {$updatedUser['nickname']}\n";
        echo "  - 이메일: {$updatedUser['email']}\n";
        echo "  - 소개: {$updatedUser['bio']}\n";
        echo "  - 수정일: {$updatedUser['updated_at']}\n\n";
        
        echo "🎉 편집 메서드 핵심 로직 정상 작동!\n";
        echo "❓ 문제는 AdminController의 권한 체크나 ValidationHelper에 있을 수 있습니다.\n";
    } else {
        echo "❌ 편집 실패. User 모델에 문제가 있습니다.\n";
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📋 스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>