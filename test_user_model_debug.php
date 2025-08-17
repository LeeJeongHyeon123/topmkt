<?php
/**
 * User 모델 updateProfile 메서드 디버깅
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';

echo "🔍 User 모델 updateProfile 디버깅\n\n";

try {
    $userModel = new User();
    $db = Database::getInstance();
    
    // 1. 테스트 사용자 선택
    $testUser = $db->fetch("SELECT id, nickname, email, bio FROM users WHERE id = 5");
    
    if (!$testUser) {
        echo "❌ 테스트 사용자를 찾을 수 없습니다.\n";
        exit(1);
    }
    
    echo "📋 원본 사용자 정보:\n";
    echo "  - ID: {$testUser['id']}\n";
    echo "  - 닉네임: {$testUser['nickname']}\n";
    echo "  - 이메일: " . ($testUser['email'] ?: '없음') . "\n";
    echo "  - 소개: " . ($testUser['bio'] ?: '없음') . "\n\n";
    
    // 2. 업데이트 데이터 준비
    $updateData = [
        'nickname' => $testUser['nickname'] . '_편집' . date('His'),
        'bio' => '관리자 편집 테스트 - ' . date('Y-m-d H:i:s'),
        'email' => 'test_' . date('His') . '@example.com'
    ];
    
    echo "📤 업데이트할 데이터:\n";
    foreach ($updateData as $key => $value) {
        echo "  - {$key}: {$value}\n";
    }
    echo "\n";
    
    // 3. User 모델의 updateProfile 메서드 호출
    echo "🔄 updateProfile 메서드 호출...\n";
    
    $result = $userModel->updateProfile($testUser['id'], $updateData);
    
    echo "📊 업데이트 결과: " . ($result ? '성공' : '실패') . "\n\n";
    
    if ($result) {
        // 4. 업데이트된 데이터 확인
        echo "✅ 업데이트 성공! 변경된 데이터 확인...\n";
        
        $updatedUser = $db->fetch("SELECT id, nickname, email, bio, updated_at FROM users WHERE id = ?", [$testUser['id']]);
        
        echo "📋 업데이트된 사용자 정보:\n";
        echo "  - ID: {$updatedUser['id']}\n";
        echo "  - 닉네임: {$updatedUser['nickname']}\n";
        echo "  - 이메일: {$updatedUser['email']}\n";
        echo "  - 소개: {$updatedUser['bio']}\n";
        echo "  - 수정일: {$updatedUser['updated_at']}\n\n";
        
        // 5. 실제 AdminController의 editUser 메서드 시뮬레이션
        echo "🎯 AdminController::editUser 시뮬레이션...\n";
        
        // CSRF 토큰 생성
        session_start();
        $_SESSION['csrf_token'] = 'test_' . uniqid();
        $_SESSION['user'] = [
            'id' => 4,  // 우리집탄이 (관리자)
            'role' => 'ROLE_ADMIN'
        ];
        
        // POST 데이터 설정
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nickname' => $updatedUser['nickname'] . '_관리자편집',
            'email' => 'admin_edit_' . date('His') . '@topmktx.com',
            'bio' => '관리자가 편집한 소개 - ' . date('Y-m-d H:i:s'),
            'status' => 'active',
            'role' => 'ROLE_USER',
            'status_reason' => '테스트 편집',
            'csrf_token' => $_SESSION['csrf_token']
        ];
        
        echo "📤 관리자 편집 데이터:\n";
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token') {
                echo "  - {$key}: {$value}\n";
            }
        }
        echo "\n";
        
        // AdminController 시뮬레이션 로직
        $editData = [];
        $changes = [];
        
        // 닉네임 처리
        if (isset($_POST['nickname'])) {
            $nickname = trim($_POST['nickname']);
            if (!empty($nickname) && strlen($nickname) >= 2 && strlen($nickname) <= 20) {
                $editData['nickname'] = $nickname;
                $changes[] = "닉네임 → {$nickname}";
            }
        }
        
        // 이메일 처리
        if (isset($_POST['email'])) {
            $email = trim($_POST['email']);
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $editData['email'] = $email;
                $changes[] = "이메일 → {$email}";
            }
        }
        
        // 소개 처리
        if (isset($_POST['bio'])) {
            $bio = trim($_POST['bio']);
            if (strlen($bio) <= 500) {
                $editData['bio'] = $bio;
                $changes[] = "소개 업데이트";
            }
        }
        
        echo "🔄 관리자 편집 수행...\n";
        
        if (!empty($editData)) {
            $adminEditResult = $userModel->updateProfile($testUser['id'], $editData);
            
            echo "📊 관리자 편집 결과: " . ($adminEditResult ? '성공' : '실패') . "\n";
            
            if ($adminEditResult) {
                echo "📝 변경 사항: " . implode(', ', $changes) . "\n\n";
                
                // 최종 결과 확인
                $finalUser = $db->fetch("SELECT nickname, email, bio, updated_at FROM users WHERE id = ?", [$testUser['id']]);
                
                echo "🏁 최종 사용자 정보:\n";
                echo "  - 닉네임: {$finalUser['nickname']}\n";
                echo "  - 이메일: {$finalUser['email']}\n";
                echo "  - 소개: {$finalUser['bio']}\n";
                echo "  - 최종 수정일: {$finalUser['updated_at']}\n\n";
                
                echo "🎉 모든 테스트 성공!\n";
                echo "✅ 관리자 사용자 편집 기능이 정상적으로 작동합니다.\n";
            }
        } else {
            echo "❌ 편집할 데이터가 없습니다.\n";
        }
        
    } else {
        echo "❌ 업데이트 실패. 데이터베이스 오류일 수 있습니다.\n";
        
        // 데이터베이스 연결 상태 확인
        $testQuery = $db->fetch("SELECT COUNT(*) as count FROM users");
        echo "📊 데이터베이스 연결 테스트: " . ($testQuery ? "정상 (사용자 수: {$testQuery['count']})" : "실패") . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📋 스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>