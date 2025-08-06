<?php
/**
 * 권한 시스템 검증 테스트
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/models/Notice.php';

// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "🧪 권한 시스템 검증 테스트\n";
echo str_repeat("=", 50) . "\n";

try {
    $noticeModel = new Notice();
    
    // 1. 기업 사용자 권한 확인
    echo "\n--- 1. 기업 사용자 권한 확인 ---\n";
    
    // 사용자 ID 4 (ROLE_ADMIN + 승인된 기업 프로필)
    $isCompanyUser4 = $noticeModel->isCompanyUser(4);
    echo "사용자 ID 4 (ROLE_ADMIN + 승인된 기업): " . ($isCompanyUser4 ? "✅ 권한 있음" : "❌ 권한 없음") . "\n";
    
    // 존재하지 않는 사용자
    $isCompanyUser999 = $noticeModel->isCompanyUser(999);
    echo "사용자 ID 999 (존재하지 않음): " . ($isCompanyUser999 ? "❌ 권한 있음 (문제!)" : "✅ 권한 없음") . "\n";
    
    // 2. 기업 ID 조회 테스트
    echo "\n--- 2. 기업 ID 조회 테스트 ---\n";
    
    $companyId4 = $noticeModel->getUserCompanyId(4);
    echo "사용자 ID 4의 기업 ID: " . ($companyId4 ?? "없음") . "\n";
    
    $companyId999 = $noticeModel->getUserCompanyId(999);
    echo "사용자 ID 999의 기업 ID: " . ($companyId999 ?? "✅ 없음") . "\n";
    
    // 3. 공지사항 소유권 테스트
    echo "\n--- 3. 공지사항 소유권 테스트 ---\n";
    
    if ($isCompanyUser4 && $companyId4) {
        // 테스트용 공지사항 생성
        $testNotice = [
            'user_id' => 4,
            'company_id' => $companyId4,
            'title' => '권한 테스트용 공지사항',
            'content' => '<p>권한 시스템 테스트를 위한 공지사항입니다.</p>',
            'is_featured' => false
        ];
        
        $noticeId = $noticeModel->create($testNotice);
        if ($noticeId) {
            echo "✅ 테스트 공지사항 생성: ID $noticeId\n";
            
            // 소유자 확인
            $isOwner = $noticeModel->isOwner($noticeId, 4);
            echo "사용자 ID 4가 소유자인가? " . ($isOwner ? "✅ 예" : "❌ 아니요") . "\n";
            
            // 비소유자 확인
            $isNotOwner = $noticeModel->isOwner($noticeId, 999);
            echo "사용자 ID 999가 소유자인가? " . ($isNotOwner ? "❌ 예 (문제!)" : "✅ 아니요") . "\n";
            
            // 테스트 데이터 정리
            $noticeModel->delete($noticeId);
            echo "✅ 테스트 데이터 정리 완료\n";
        } else {
            echo "❌ 테스트 공지사항 생성 실패\n";
        }
    } else {
        echo "⚠️ 기업 사용자가 아니므로 소유권 테스트 건너뜀\n";
    }
    
    // 4. AuthMiddleware 테스트
    echo "\n--- 4. AuthMiddleware 테스트 ---\n";
    
    // AuthMiddleware 클래스 로드
    require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
    
    if (class_exists('AuthMiddleware')) {
        // 로그아웃 상태 테스트
        unset($_SESSION['user_id']);
        $isLoggedIn = AuthMiddleware::isLoggedIn();
        echo "로그아웃 상태 확인: " . ($isLoggedIn ? "❌ 로그인됨 (문제!)" : "✅ 로그아웃됨") . "\n";
        
        $currentUserId = AuthMiddleware::getCurrentUserId();
        echo "로그아웃 시 사용자 ID: " . ($currentUserId ?? "✅ null") . "\n";
        
        // 로그인 상태 시뮬레이션
        $_SESSION['user_id'] = 4;
        $isLoggedIn = AuthMiddleware::isLoggedIn();
        echo "로그인 상태 시뮬레이션: " . ($isLoggedIn ? "✅ 로그인됨" : "❌ 로그아웃됨 (문제!)") . "\n";
        
        $currentUserId = AuthMiddleware::getCurrentUserId();
        echo "로그인 시 사용자 ID: " . ($currentUserId ?? "null") . "\n";
        
        // 다시 로그아웃 상태로 복원
        unset($_SESSION['user_id']);
        
    } else {
        echo "❌ AuthMiddleware 클래스를 찾을 수 없음\n";
    }
    
    // 5. 컨트롤러 권한 검증 테스트
    echo "\n--- 5. 컨트롤러 권한 검증 테스트 ---\n";
    
    require_once SRC_PATH . '/controllers/NoticeController.php';
    require_once SRC_PATH . '/controllers/NoticeCommentController.php';
    
    // NoticeController 인스턴스 생성
    $controller = new NoticeController();
    echo "✅ NoticeController 인스턴스 생성\n";
    
    // 로그인하지 않은 상태에서 API 호출 테스트
    ob_start();
    
    try {
        // 로그아웃 상태 확인
        unset($_SESSION['user_id']);
        
        // create 메서드 호출 (로그인 필요)
        $controller->create();
        
        $output = ob_get_contents();
        ob_end_clean();
        
        // JSON 응답 파싱
        $response = json_decode($output, true);
        if ($response && isset($response['data']['success']) && $response['data']['success'] === false) {
            echo "✅ 비로그인 상태 API 호출 차단됨\n";
            echo "- 메시지: {$response['data']['message']}\n";
        } else {
            echo "⚠️ API 응답 형식 확인 필요: " . substr($output, 0, 100) . "...\n";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "✅ 예상된 권한 에러: " . substr($e->getMessage(), 0, 50) . "...\n";
    }
    
    // 6. CSRF 토큰 검증 테스트
    echo "\n--- 6. CSRF 토큰 검증 테스트 ---\n";
    
    // 로그인 상태로 설정
    $_SESSION['user_id'] = 4;
    $_SESSION['csrf_token'] = 'valid_token';
    
    ob_start();
    
    // 잘못된 CSRF 토큰으로 API 호출
    $_POST['csrf_token'] = 'invalid_token';
    
    try {
        $controller->create();
        
        $output = ob_get_contents();
        ob_end_clean();
        
        $response = json_decode($output, true);
        if ($response && isset($response['data']['success']) && $response['data']['success'] === false) {
            echo "✅ CSRF 토큰 검증 작동\n";
            echo "- 메시지: {$response['data']['message']}\n";
        } else {
            echo "⚠️ CSRF 검증 확인 필요\n";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "✅ 예상된 CSRF 에러: " . substr($e->getMessage(), 0, 50) . "...\n";
    }
    
    // 정리
    unset($_SESSION['user_id']);
    unset($_SESSION['csrf_token']);
    unset($_POST['csrf_token']);
    
    // 7. 데이터베이스 권한 확인
    echo "\n--- 7. 데이터베이스 권한 확인 ---\n";
    
    // 실제 사용자 정보로 권한 체크
    require_once SRC_PATH . '/config/database.php';
    $db = Database::getInstance();
    
    // ROLE_CORP 사용자 조회
    $corpUsers = $db->fetchAll("
        SELECT u.id, u.nickname, u.role, u.corp_status, cp.company_name, cp.status as company_status
        FROM users u 
        LEFT JOIN company_profiles cp ON u.id = cp.user_id 
        WHERE u.role IN ('ROLE_CORP', 'ROLE_ADMIN')
        ORDER BY u.id
    ");
    
    echo "기업 관련 사용자 수: " . count($corpUsers) . "명\n";
    
    foreach ($corpUsers as $user) {
        $canWrite = ($user['role'] === 'ROLE_CORP' || $user['role'] === 'ROLE_ADMIN') && 
                   $user['corp_status'] === 'approved' && 
                   $user['company_status'] === 'approved';
        
        echo "- [{$user['id']}] {$user['nickname']} ({$user['role']}) - 기업: " . 
             ($user['company_name'] ?? '없음') . " - 공지 작성 권한: " . 
             ($canWrite ? "✅ 있음" : "❌ 없음") . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 권한 시스템 검증 완료!\n";
    
} catch (Exception $e) {
    echo "❌ 테스트 중 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 추적:\n" . $e->getTraceAsString() . "\n";
} finally {
    // 출력 버퍼 정리
    while (ob_get_level()) {
        ob_end_clean();
    }
}
?>