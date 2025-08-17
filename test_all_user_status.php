<?php
/**
 * 모든 사용자 상태별 로그인 동작 테스트
 */

define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/helpers/JWTHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

echo "🔍 모든 사용자 상태별 로그인 동작 테스트\n\n";

try {
    $userModel = new User();
    $db = Database::getInstance();
    
    // 테스트할 상태들
    $statusList = ['active', 'inactive', 'suspended', 'deleted'];
    $testPhone = '010-9999-TEST';
    $testPassword = 'test123';
    
    $results = [];
    
    foreach ($statusList as $status) {
        echo "=== 상태: {$status} 테스트 ===\n";
        
        // 기존 테스트 사용자 삭제
        $db->execute("DELETE FROM users WHERE phone = ?", [$testPhone]);
        
        // 해당 상태로 테스트 사용자 생성
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $db->execute("
            INSERT INTO users (nickname, phone, password_hash, email, status, role, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", ["테스트_{$status}", $testPhone, $hashedPassword, "test_{$status}@topmktx.com", $status, 'ROLE_USER']);
        
        $testUserId = $db->fetch("SELECT id FROM users WHERE phone = ?", [$testPhone])['id'];
        echo "✅ 테스트 사용자 생성: ID {$testUserId} (상태: {$status})\n";
        
        // 1. User 모델 findByPhone 테스트
        echo "1️⃣ findByPhone 테스트: ";
        $foundUser = $userModel->findByPhone($testPhone);
        $canFind = $foundUser ? true : false;
        echo ($canFind ? "✅ 발견됨" : "❌ 발견안됨") . "\n";
        $results[$status]['findByPhone'] = $canFind;
        
        // 2. User 모델 login 테스트
        echo "2️⃣ login 테스트: ";
        try {
            $loginResult = $userModel->login($testPhone, $testPassword);
            $canLogin = $loginResult ? true : false;
            echo ($canLogin ? "✅ 로그인 성공" : "❌ 로그인 실패") . "\n";
            $results[$status]['login'] = $canLogin;
            
            if ($canLogin) {
                // 3. JWT 토큰 생성 및 AuthMiddleware 테스트
                echo "3️⃣ AuthMiddleware 테스트: ";
                $payload = [
                    'user_id' => $loginResult['id'],
                    'email' => $loginResult['email'],
                    'role' => $loginResult['role'],
                    'exp' => time() + (7 * 24 * 60 * 60)
                ];
                
                $jwt = JWTHelper::createToken($payload);
                $_COOKIE['auth_token'] = $jwt;
                
                // AuthMiddleware로 확인
                $isLoggedIn = AuthMiddleware::isLoggedIn();
                echo ($isLoggedIn ? "✅ 인증됨" : "❌ 인증실패") . "\n";
                $results[$status]['authMiddleware'] = $isLoggedIn;
                
                if ($isLoggedIn) {
                    $currentUser = AuthMiddleware::getCurrentUser();
                    echo "   사용자 정보: {$currentUser['nickname']} (상태: {$currentUser['status']})\n";
                }
            } else {
                $results[$status]['authMiddleware'] = false;
            }
        } catch (Exception $e) {
            echo "❌ 로그인 예외: " . $e->getMessage() . "\n";
            $results[$status]['login'] = false;
            $results[$status]['authMiddleware'] = false;
        }
        
        // 사용자 삭제
        $db->execute("DELETE FROM users WHERE phone = ?", [$testPhone]);
        echo "\n";
    }
    
    // 결과 요약표
    echo "📊 상태별 동작 비교표\n";
    echo "+" . str_repeat("-", 70) . "+\n";
    echo "| 상태       | findByPhone | login       | AuthMiddleware | 실제 결과 |\n";
    echo "+" . str_repeat("-", 70) . "+\n";
    
    foreach ($statusList as $status) {
        $findIcon = $results[$status]['findByPhone'] ? '✅' : '❌';
        $loginIcon = $results[$status]['login'] ? '✅' : '❌';
        $authIcon = $results[$status]['authMiddleware'] ? '✅' : '❌';
        
        // 실제 사용자 경험 판단
        $actualResult = '접근불가';
        if ($results[$status]['login'] && $results[$status]['authMiddleware']) {
            $actualResult = '완전허용';
        } elseif ($results[$status]['login'] && !$results[$status]['authMiddleware']) {
            $actualResult = '로그인후차단';
        } elseif (!$results[$status]['login']) {
            $actualResult = '로그인불가';
        }
        
        printf("| %-10s | %-11s | %-11s | %-14s | %-9s |\n", 
               $status, $findIcon, $loginIcon, $authIcon, $actualResult);
    }
    echo "+" . str_repeat("-", 70) . "+\n";
    
    echo "\n📝 결론:\n";
    echo "1. active: 완전한 접근 허용\n";
    echo "2. inactive: 로그인은 되지만 즉시 차단 (실질적 접근 불가)\n"; 
    echo "3. suspended: 로그인은 되지만 즉시 차단 (실질적 접근 불가)\n";
    echo "4. deleted: 완전한 접근 차단 (로그인조차 불가)\n";
    
    echo "\n💡 inactive vs suspended 차이점:\n";
    echo "- 기술적 동작: 동일함 (둘 다 로그인 후 AuthMiddleware에서 차단)\n";
    echo "- 의미적 차이: \n";
    echo "  * inactive: 비활성화 (일시적, 사용자 요청 등)\n";
    echo "  * suspended: 정지 (징계적, 관리자 조치)\n";
    echo "- 관리 목적: 상태별로 다른 처리 로직이나 UI 표시 가능\n";
    
} catch (Exception $e) {
    echo "❌ 테스트 실패: " . $e->getMessage() . "\n";
    echo "파일: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>