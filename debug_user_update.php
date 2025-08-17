<?php
/**
 * User 모델 updateProfile 메서드 직접 테스트
 */

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';

echo "🔍 User 모델 updateProfile 직접 테스트\n\n";

try {
    $userModel = new User();
    
    // 먼저 사용자 5의 현재 정보 확인
    echo "1단계: 사용자 ID 5 현재 정보 확인\n";
    $currentUser = $userModel->findById(5);
    if ($currentUser) {
        echo "현재 정보:\n";
        echo "- 닉네임: {$currentUser['nickname']}\n";
        echo "- 이메일: {$currentUser['email']}\n";
        echo "- 전화번호: {$currentUser['phone']}\n";
        echo "- 권한: {$currentUser['role']}\n";
        echo "- 상태: {$currentUser['status']}\n\n";
    } else {
        throw new Exception("사용자 ID 5를 찾을 수 없습니다");
    }
    
    // updateProfile 메서드 직접 호출
    echo "2단계: updateProfile 메서드 직접 호출\n";
    $updateData = [
        'nickname' => '기업권한직접테스트',
        'email' => 'corp_direct@topmktx.com',
        'phone' => '010-3333-3333',
        'role' => 'ROLE_CORPORATE',  // 기업 권한
        'status' => 'active'
    ];
    
    echo "업데이트할 데이터:\n";
    foreach ($updateData as $key => $value) {
        echo "- $key: $value\n";
    }
    echo "\n";
    
    // updateProfile 메서드 호출
    $result = $userModel->updateProfile(5, $updateData);
    
    if ($result) {
        echo "✅ updateProfile 성공!\n\n";
        
        // 업데이트 후 정보 다시 확인
        echo "3단계: 업데이트 후 정보 확인\n";
        $updatedUser = $userModel->findById(5);
        if ($updatedUser) {
            echo "업데이트 후 정보:\n";
            echo "- 닉네임: {$updatedUser['nickname']}\n";
            echo "- 이메일: {$updatedUser['email']}\n";
            echo "- 전화번호: {$updatedUser['phone']}\n";
            echo "- 권한: {$updatedUser['role']}\n";
            echo "- 상태: {$updatedUser['status']}\n\n";
            
            if ($updatedUser['role'] === 'ROLE_CORPORATE') {
                echo "🎉 기업 권한 변경 완전 성공!\n";
            } else {
                echo "❌ 권한 변경 실패: 예상값 ROLE_CORPORATE, 실제값 {$updatedUser['role']}\n";
            }
        }
    } else {
        echo "❌ updateProfile 실패\n";
    }
    
} catch (Exception $e) {
    echo "❌ 테스트 실패: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
}
?>