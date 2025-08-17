<?php
/**
 * editUser 메서드 격리된 테스트 (생성자 우회)
 */

define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

// 에러 리포팅 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "🔍 editUser 로직 격리 테스트\n\n";

// 세션 시작 (바로 시작)
session_start();

try {
    // 세션 설정
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    $_SESSION['csrf_token'] = 'test_token_' . uniqid();
    
    // POST 데이터 설정
    $_POST = [
        'nickname' => '안계현_' . date('His'),
        'email' => 'test_' . date('His') . '@topmktx.com',
        'phone' => '010-7777-' . substr(date('His'), 0, 4),
        'status' => 'active',
        'role' => 'ROLE_USER',
        'status_reason' => '격리 테스트',
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    echo "1. 세션 및 POST 데이터 설정 완료\n\n";
    
    // editUser 메서드의 핵심 로직만 추출하여 테스트
    $userId = 5;
    
    echo "2. editUser 메서드 로직 시뮬레이션...\n\n";
    
    // CSRF 토큰 검증 시뮬레이션
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    
    if (!hash_equals($sessionToken, $token)) {
        echo "❌ CSRF 토큰 검증 실패\n";
        echo "   - 세션 토큰: {$sessionToken}\n";
        echo "   - POST 토큰: {$token}\n";
        exit;
    }
    
    echo "✅ CSRF 토큰 검증 성공\n\n";
    
    // User 모델 생성
    $userModel = new User();
    $adminId = 4; // AuthMiddleware::getCurrentUserId() 대신 직접 설정
    
    echo "3. User 모델 생성 및 관리자 ID 설정 완료\n\n";
    
    // 자기 자신 편집 방지 체크
    if ($userId == $adminId) {
        echo "❌ 자신의 계정은 편집할 수 없습니다.\n";
        exit;
    }
    
    echo "✅ 자기 자신 편집 방지 체크 통과\n\n";
    
    // 편집할 데이터 수집
    $editData = [];
    $changes = [];
    
    echo "4. 입력값 검증 및 데이터 수집...\n";
    
    // 닉네임 검증
    if (isset($_POST['nickname'])) {
        $nickname = trim($_POST['nickname']);
        if (empty($nickname)) {
            echo "❌ 닉네임은 필수입니다.\n";
            exit;
        }
        
        if (strlen($nickname) < 2 || strlen($nickname) > 20) {
            echo "❌ 닉네임은 2-20자 사이여야 합니다.\n";
            exit;
        }
        
        $editData['nickname'] = $nickname;
        $changes[] = "닉네임 → {$nickname}";
        echo "   ✅ 닉네임: {$nickname}\n";
    }
    
    // 이메일 검증
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "❌ 유효하지 않은 이메일 형식입니다.\n";
            exit;
        }
        
        $editData['email'] = $email;
        $changes[] = "이메일 → {$email}";
        echo "   ✅ 이메일: {$email}\n";
    }
    
    // 전화번호 검증
    if (isset($_POST['phone'])) {
        $phone = trim($_POST['phone']);
        if (empty($phone)) {
            echo "❌ 전화번호는 필수입니다.\n";
            exit;
        }
        
        // 전화번호 형식 검증
        $phonePattern = '/^0[0-9]{1,2}-[0-9]{3,4}-[0-9]{4}$/';
        if (!preg_match($phonePattern, $phone)) {
            echo "❌ 올바른 전화번호 형식이 아닙니다. (예: 010-1234-5678)\n";
            exit;
        }
        
        // 전화번호 중복 검사
        $existingUser = $userModel->findByPhone($phone);
        if ($existingUser && $existingUser['id'] != $userId) {
            echo "❌ 이미 사용 중인 전화번호입니다. (사용자 ID: {$existingUser['id']})\n";
            exit;
        }
        
        $editData['phone'] = $phone;
        $changes[] = "전화번호 → {$phone}";
        echo "   ✅ 전화번호: {$phone}\n";
    }
    
    echo "\n5. 모든 검증 통과!\n\n";
    
    // 기본 정보 업데이트
    if (!empty($editData)) {
        echo "6. 프로필 업데이트 실행...\n";
        $result = $userModel->updateProfile($userId, $editData);
        
        if (!$result) {
            echo "❌ 프로필 업데이트 실패\n";
            exit;
        }
        
        echo "✅ 프로필 업데이트 성공\n";
        echo "   변경 사항: " . implode(', ', $changes) . "\n\n";
    }
    
    // 상태 업데이트 (별도 처리)
    if (isset($_POST['status'])) {
        $newStatus = trim($_POST['status']);
        $reason = trim($_POST['status_reason'] ?? '');
        
        echo "7. 상태 업데이트 실행...\n";
        echo "   새 상태: {$newStatus}\n";
        echo "   사유: {$reason}\n";
        
        $statusResult = $userModel->updateUserStatus($userId, $newStatus, $adminId, $reason);
        
        if ($statusResult) {
            echo "✅ 상태 업데이트 성공\n";
        } else {
            echo "❌ 상태 업데이트 실패\n";
        }
    }
    
    echo "\n🎉 모든 편집 작업 완료!\n";
    echo "📊 최종 응답: {\"success\": true, \"message\": \"사용자 정보가 성공적으로 업데이트되었습니다.\"}\n";
    
} catch (Exception $e) {
    echo "❌ 격리 테스트 실패: " . $e->getMessage() . "\n";
    echo "📍 파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📋 스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>