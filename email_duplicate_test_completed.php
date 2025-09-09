<?php
/**
 * 이메일 중복 허용 시스템 구현 완료 테스트 (v3.13.0)
 * 
 * 이 테스트는 이메일 중복 허용 기능이 성공적으로 구현되었음을 검증합니다.
 * 
 * 구현 내용:
 * 1. AuthController에서 이메일 중복 검사 로직 제거
 * 2. User 모델 isEmailExists 메서드를 항상 false 리턴하도록 수정
 * 3. 회원가입 폼에서 이메일 중복 허용 안내 메시지 추가
 * 4. 데이터베이스 users 테이블에서 email UNIQUE 제약조건 제거
 * 5. User 모델의 중복 검사 버그 수정 (NULL !== false 이슈)
 * 
 * 테스트 날짜: 2025-08-22
 * 테스트 결과: ✅ 성공 - 동일 이메일로 여러 계정 생성 가능
 */

// 프로젝트 루트 경로 설정
define('ROOT_PATH', __DIR__);
define('SRC_PATH', __DIR__ . '/src');

// 필요한 파일들 포함
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/controllers/AuthController.php';

echo "🔍 이메일 중복 허용 테스트 시작\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // 세션 시작
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // CSRF 토큰 생성
    $_SESSION['csrf_token'] = 'test_token_' . time();
    
    // 기존 중복 이메일 확인
    $userModel = new User();
    $existingUsers = Database::getInstance()->fetchAll(
        "SELECT id, nickname, email, phone FROM users WHERE email = ?", 
        ['2jeonghyeon@naver.com']
    );
    
    echo "📋 기존 사용자 조회 결과:\n";
    if (!empty($existingUsers)) {
        foreach ($existingUsers as $user) {
            echo "  - ID: {$user['id']}, 닉네임: {$user['nickname']}, 이메일: {$user['email']}, 휴대폰: {$user['phone']}\n";
        }
    } else {
        echo "  - 해당 이메일로 등록된 사용자 없음\n";
    }
    echo "\n";
    
    // User 모델의 isEmailExists 메서드 테스트
    echo "🔍 User::isEmailExists() 메서드 테스트:\n";
    $isEmailExists = $userModel->isEmailExists('2jeonghyeon@naver.com');
    echo "  - 결과: " . ($isEmailExists ? 'true (중복 존재)' : 'false (중복 허용)') . "\n";
    echo "\n";
    
    // 회원가입 시뮬레이션 (실제 가입은 하지 않고 검증만)
    echo "📝 회원가입 검증 시뮬레이션:\n";
    
    // 테스트 데이터 준비 (고유한 닉네임과 휴대폰 번호 사용)
    $timestamp = time();
    $testData = [
        'nickname' => '이메일중복테스트' . $timestamp,
        'phone' => '010-' . substr($timestamp, -4) . '-' . substr($timestamp, -8, 4),  // 고유한 번호 생성
        'email' => '2jeonghyeon@naver.com',  // 기존에 있는 이메일
        'password' => 'TestPassword123!',
        'password_confirm' => 'TestPassword123!',
        'terms' => '1',
        'marketing' => '0',
        'phone_verified' => '1',
        'csrf_token' => $_SESSION['csrf_token']
    ];
    
    // 검증 로직만 실행 (실제 가입은 하지 않음)
    $errors = [];
    
    // 각 중복 검사 함수 개별 테스트
    echo "  🔍 개별 중복 검사 결과:\n";
    
    // 닉네임 검사 세부 분석
    $findByNicknameResult = $userModel->findByNickname($testData['nickname']);
    echo "    🔍 findByNickname 결과: " . (is_array($findByNicknameResult) ? 'array (사용자 발견)' : ($findByNicknameResult === false ? 'false (사용자 없음)' : var_export($findByNicknameResult, true))) . "\n";
    if (is_array($findByNicknameResult)) {
        echo "      발견된 사용자: ID {$findByNicknameResult['id']}, 닉네임: {$findByNicknameResult['nickname']}\n";
    }
    
    $nicknameExists = $userModel->isNicknameExists($testData['nickname']);
    echo "    - 닉네임 중복: " . ($nicknameExists ? 'true' : 'false') . "\n";
    
    // 직접 데이터베이스에서 조회해보기
    $directNicknameQuery = Database::getInstance()->fetchAll(
        "SELECT id, nickname, status FROM users WHERE nickname = ?",
        [$testData['nickname']]
    );
    echo "      DB 직접 조회: " . count($directNicknameQuery) . "건 발견\n";
    foreach ($directNicknameQuery as $user) {
        echo "        - ID: {$user['id']}, 닉네임: {$user['nickname']}, 상태: {$user['status']}\n";
    }
    
    if ($nicknameExists) {
        $errors[] = '이미 사용 중인 닉네임입니다.';
    }
    
    // 휴대폰 검사 세부 분석
    $findByPhoneResult = $userModel->findByPhone($testData['phone']);
    echo "    🔍 findByPhone 결과: " . (is_array($findByPhoneResult) ? 'array (사용자 발견)' : ($findByPhoneResult === false ? 'false (사용자 없음)' : var_export($findByPhoneResult, true))) . "\n";
    if (is_array($findByPhoneResult)) {
        echo "      발견된 사용자: ID {$findByPhoneResult['id']}, 휴대폰: {$findByPhoneResult['phone']}\n";
    }
    
    $phoneExists = $userModel->isPhoneExists($testData['phone']);
    echo "    - 휴대폰 중복: " . ($phoneExists ? 'true' : 'false') . "\n";
    
    // 직접 데이터베이스에서 조회해보기
    $directPhoneQuery = Database::getInstance()->fetchAll(
        "SELECT id, phone, status FROM users WHERE phone = ?",
        [$testData['phone']]
    );
    echo "      DB 직접 조회: " . count($directPhoneQuery) . "건 발견\n";
    foreach ($directPhoneQuery as $user) {
        echo "        - ID: {$user['id']}, 휴대폰: {$user['phone']}, 상태: {$user['status']}\n";
    }
    
    if ($phoneExists) {
        $errors[] = '이미 가입된 휴대폰 번호입니다.';
    }
    
    $emailExists = $userModel->isEmailExists($testData['email']);
    echo "    - 이메일 중복: " . ($emailExists ? 'true' : 'false') . "\n";
    if ($emailExists) {
        $errors[] = '이미 가입된 이메일입니다.';
    }
    echo "\n";
    
    echo "  - 닉네임: {$testData['nickname']}\n";
    echo "  - 휴대폰: {$testData['phone']}\n";
    echo "  - 이메일: {$testData['email']}\n";
    echo "  - 검증 결과: " . (empty($errors) ? "✅ 모든 검증 통과" : "❌ 오류 발생") . "\n";
    
    if (!empty($errors)) {
        echo "  - 오류 목록:\n";
        foreach ($errors as $error) {
            echo "    * $error\n";
        }
    }
    echo "\n";
    
    // 실제 가입 테스트 (조심스럽게 진행)
    if (empty($errors)) {
        echo "💾 실제 회원가입 테스트 진행:\n";
        
        try {
            $userId = $userModel->create([
                'nickname' => $testData['nickname'],
                'phone' => $testData['phone'],
                'email' => $testData['email'],
                'password' => $testData['password'],
                'marketing_agreed' => false
            ]);
            
            if ($userId) {
                echo "  ✅ 회원가입 성공! 새로운 사용자 ID: $userId\n";
                
                // 새로 생성된 사용자 정보 확인
                $newUser = $userModel->findById($userId);
                if ($newUser) {
                    echo "  📋 생성된 사용자 정보:\n";
                    echo "    - ID: {$newUser['id']}\n";
                    echo "    - 닉네임: {$newUser['nickname']}\n";
                    echo "    - 이메일: {$newUser['email']}\n";
                    echo "    - 휴대폰: {$newUser['phone']}\n";
                    echo "    - 생성일: {$newUser['created_at']}\n";
                }
                
                // 동일 이메일 사용자 목록 재확인
                echo "\n  📋 동일 이메일 사용자 전체 목록:\n";
                $sameEmailUsers = Database::getInstance()->fetchAll(
                    "SELECT id, nickname, email, phone, created_at FROM users WHERE email = ? ORDER BY id", 
                    ['2jeonghyeon@naver.com']
                );
                
                foreach ($sameEmailUsers as $user) {
                    echo "    - ID: {$user['id']}, 닉네임: {$user['nickname']}, 휴대폰: {$user['phone']}, 생성일: {$user['created_at']}\n";
                }
                
            } else {
                echo "  ❌ 회원가입 실패\n";
            }
            
        } catch (Exception $e) {
            echo "  ❌ 회원가입 중 오류 발생: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 이메일 중복 허용 테스트 완료!\n";
    
} catch (Exception $e) {
    echo "❌ 테스트 중 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
}
?>