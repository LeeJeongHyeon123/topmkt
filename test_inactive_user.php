<?php
/**
 * 비활성 사용자 상태 테스트
 */

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/helpers/JWTHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "🔍 비활성 사용자 상태 테스트\n\n";

try {
    $userModel = new User();
    $db = Database::getInstance();
    
    // 테스트용 사용자 생성 (일단 활성 상태로)
    $testPhone = '010-9999-TEST';
    $testPassword = 'test123';
    
    echo "1단계: 테스트 사용자 확인/생성\n";
    
    // 기존 테스트 사용자 확인
    $existingUser = $userModel->findByPhone($testPhone);
    
    if (!$existingUser) {
        // 테스트 사용자 생성
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $db->execute("
            INSERT INTO users (nickname, phone, password_hash, email, status, role, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", ['비활성테스트사용자', $testPhone, $hashedPassword, 'test_inactive@topmktx.com', 'active', 'ROLE_USER']);
        
        $testUserId = $db->fetch("SELECT id FROM users WHERE phone = ?", [$testPhone])['id'];
        echo "✅ 테스트 사용자 생성: ID $testUserId\n";
    } else {
        $testUserId = $existingUser['id'];
        echo "✅ 기존 테스트 사용자 사용: ID $testUserId\n";
    }
    
    echo "\n2단계: 활성 상태에서 로그인 테스트\n";
    
    // 활성 상태에서 로그인 시도
    $loginResult = $userModel->login($testPhone, $testPassword);
    if ($loginResult) {
        echo "✅ 활성 상태 로그인 성공\n";
        echo "사용자 정보: {$loginResult['nickname']} (상태: {$loginResult['status']})\n";
    } else {
        echo "❌ 활성 상태 로그인 실패\n";
    }
    
    echo "\n3단계: 사용자를 비활성 상태로 변경\n";
    
    // 사용자 상태를 inactive로 변경
    $updateResult = $db->execute("UPDATE users SET status = 'inactive' WHERE id = ?", [$testUserId]);
    if ($updateResult) {
        echo "✅ 사용자 상태를 비활성으로 변경\n";
    } else {
        echo "❌ 상태 변경 실패\n";
    }
    
    echo "\n4단계: 비활성 상태에서 로그인 테스트\n";
    
    // 비활성 상태에서 로그인 시도
    $loginResult2 = $userModel->login($testPhone, $testPassword);
    if ($loginResult2) {
        echo "✅ 비활성 상태에서도 로그인 성공!\n";
        echo "사용자 정보: {$loginResult2['nickname']} (상태: {$loginResult2['status']})\n";
        
        // JWT 토큰 생성해보기
        $payload = [
            'user_id' => $loginResult2['id'],
            'email' => $loginResult2['email'],
            'role' => $loginResult2['role'],
            'exp' => time() + (7 * 24 * 60 * 60)
        ];
        
        $jwt = JWTHelper::createToken($payload);
        $_COOKIE['auth_token'] = $jwt;
        
        echo "JWT 토큰 생성 완료\n";
        
    } else {
        echo "❌ 비활성 상태 로그인 실패\n";
    }
    
    echo "\n5단계: AuthMiddleware를 통한 사용자 정보 조회 테스트\n";
    
    if (isset($jwt)) {
        // AuthMiddleware로 사용자 정보 가져오기 시도
        $isLoggedIn = AuthMiddleware::isLoggedIn();
        echo "isLoggedIn() 결과: " . ($isLoggedIn ? '✅ true' : '❌ false') . "\n";
        
        if ($isLoggedIn) {
            $currentUser = AuthMiddleware::getCurrentUser();
            if ($currentUser) {
                echo "getCurrentUser() 성공: {$currentUser['nickname']} (상태: {$currentUser['status']})\n";
            } else {
                echo "❌ getCurrentUser() 실패 - 사용자 정보 없음\n";
            }
        }
    }
    
    echo "\n6단계: 정리 (테스트 사용자 삭제)\n";
    
    // 테스트 사용자 삭제
    $deleteResult = $db->execute("DELETE FROM users WHERE phone = ?", [$testPhone]);
    if ($deleteResult) {
        echo "✅ 테스트 사용자 삭제 완료\n";
    } else {
        echo "⚠️ 테스트 사용자 삭제 실패\n";
    }
    
    echo "\n📊 결론:\n";
    echo "- 비활성 사용자도 로그인은 가능합니다 (findByPhone에서 status != 'deleted'만 체크)\n";
    echo "- 하지만 AuthMiddleware에서 status = 'active'만 허용하므로\n";
    echo "- 비활성 사용자는 로그인 후 페이지 접근 시 문제가 발생할 수 있습니다\n";
    
} catch (Exception $e) {
    echo "❌ 테스트 실패: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>