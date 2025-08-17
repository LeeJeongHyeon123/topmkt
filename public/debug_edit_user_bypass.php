<?php
/**
 * AdminController 인증 우회하여 editUser 핵심 로직 테스트
 */

// 세션 시작 (헤더 전에)
session_start();

// 에러 리포팅 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Content-Type 설정
header('Content-Type: application/json; charset=utf-8');

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

try {
    require_once SRC_PATH . '/config/database.php';
    require_once SRC_PATH . '/models/User.php';
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    // 관리자 세션 설정
    $_SESSION['user'] = [
        'id' => 4,
        'nickname' => '우리집탄이',
        'role' => 'ROLE_ADMIN'
    ];
    $_SESSION['csrf_token'] = 'bypass_test_' . uniqid();
    
    // POST 요청 시뮬레이션
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'nickname' => '안계현_인증우회_' . date('His'),
        'email' => 'bypass_test_' . date('His') . '@topmktx.com', 
        'phone' => '010-6666-' . substr(date('His'), 0, 4),
        'status' => 'active',
        'role' => 'ROLE_USER',
        'status_reason' => '인증 우회 테스트',
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    $userId = 5;
    $adminId = 4;
    
    // AdminController의 editUser 메서드 핵심 로직 복제 (인증 체크 제외)
    
    // 1. CSRF 토큰 검증
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    
    if (!hash_equals($sessionToken, $token)) {
        echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
        exit;
    }
    
    // 2. 자기 자신 편집 방지
    if ($userId == $adminId) {
        echo json_encode(['error' => '자신의 계정은 편집할 수 없습니다.']);
        exit;
    }
    
    $userModel = new User();
    $editData = [];
    $changes = [];
    
    // 3. 닉네임 검증
    if (isset($_POST['nickname'])) {
        $nickname = trim($_POST['nickname']);
        if (empty($nickname)) {
            echo json_encode(['error' => '닉네임은 필수입니다.']);
            exit;
        }
        
        if (mb_strlen($nickname, 'UTF-8') < 2 || mb_strlen($nickname, 'UTF-8') > 20) {
            echo json_encode(['error' => '닉네임은 2-20자 사이여야 합니다.']);
            exit;
        }
        
        $editData['nickname'] = $nickname;
        $changes[] = "닉네임 → {$nickname}";
    }
    
    // 4. 이메일 검증
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => '유효하지 않은 이메일 형식입니다.']);
            exit;
        }
        
        $editData['email'] = $email;
        $changes[] = "이메일 → {$email}";
    }
    
    // 5. 전화번호 검증
    if (isset($_POST['phone'])) {
        $phone = trim($_POST['phone']);
        if (empty($phone)) {
            echo json_encode(['error' => '전화번호는 필수입니다.']);
            exit;
        }
        
        // 전화번호 형식 검증
        $phonePattern = '/^0[0-9]{1,2}-[0-9]{3,4}-[0-9]{4}$/';
        if (!preg_match($phonePattern, $phone)) {
            echo json_encode(['error' => '올바른 전화번호 형식이 아닙니다. (예: 010-1234-5678)']);
            exit;
        }
        
        // 전화번호 중복 검사
        $existingUser = $userModel->findByPhone($phone);
        if ($existingUser && $existingUser['id'] != $userId) {
            echo json_encode(['error' => '이미 사용 중인 전화번호입니다.']);
            exit;
        }
        
        $editData['phone'] = $phone;
        $changes[] = "전화번호 → {$phone}";
    }
    
    // 6. 기본 정보 업데이트
    if (!empty($editData)) {
        $result = $userModel->updateProfile($userId, $editData);
        
        if (!$result) {
            echo json_encode(['error' => '프로필 업데이트에 실패했습니다.']);
            exit;
        }
    }
    
    // 7. 현재 사용자 정보 조회
    $currentUser = $userModel->findById($userId);
    if (!$currentUser) {
        echo json_encode(['error' => '사용자를 찾을 수 없습니다.']);
        exit;
    }
    
    // 8. 상태 업데이트 (변경사항이 있을 때만)
    if (isset($_POST['status'])) {
        $newStatus = trim($_POST['status']);
        $reason = trim($_POST['status_reason'] ?? '');
        
        if ($currentUser['status'] !== $newStatus) {
            $statusResult = $userModel->updateUserStatus($userId, $newStatus, $adminId, $reason);
            
            if (!$statusResult) {
                echo json_encode(['error' => '상태 업데이트에 실패했습니다.']);
                exit;
            }
            
            $changes[] = "상태: {$currentUser['status']} → {$newStatus}";
        }
    }
    
    // 9. 권한 업데이트 (변경사항이 있을 때만)
    if (isset($_POST['role'])) {
        $newRole = trim($_POST['role']);
        $reason = trim($_POST['status_reason'] ?? '');
        
        if ($currentUser['role'] !== $newRole) {
            $roleResult = $userModel->updateUserRole($userId, $newRole, $adminId, $reason);
            
            if (!$roleResult) {
                echo json_encode(['error' => '권한 업데이트에 실패했습니다.']);
                exit;
            }
            
            $changes[] = "권한: {$currentUser['role']} → {$newRole}";
        }
    }
    
    // 성공 응답
    echo json_encode([
        'success' => true,
        'message' => '사용자 정보가 성공적으로 업데이트되었습니다.',
        'changes' => $changes,
        'user_id' => $userId,
        'debug' => '인증 우회 테스트 성공'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'debug' => '인증 우회 테스트 실행 중 오류 발생'
    ]);
}
?>