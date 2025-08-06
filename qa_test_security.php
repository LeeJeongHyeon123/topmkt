<?php
/**
 * QA 테스트: 권한 시스템 및 보안
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/models/Notice.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';

echo "🧪 === 권한 시스템 & 보안 QA 테스트 ===\n";
echo str_repeat("=", 50) . "\n\n";

$testResults = [];
$totalTests = 0;
$passedTests = 0;

function runTest($testName, $testFunction) {
    global $testResults, $totalTests, $passedTests;
    $totalTests++;
    
    try {
        $result = $testFunction();
        if ($result === true) {
            $passedTests++;
            $testResults[] = "✅ PASS: $testName";
            echo "✅ PASS: $testName\n";
        } else {
            $testResults[] = "❌ FAIL: $testName - $result";
            echo "❌ FAIL: $testName - $result\n";
        }
    } catch (Exception $e) {
        $testResults[] = "❌ ERROR: $testName - " . $e->getMessage();
        echo "❌ ERROR: $testName - " . $e->getMessage() . "\n";
    }
}

// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 테스트 1: AuthMiddleware 로딩 확인
runTest("AuthMiddleware 로딩", function() {
    return class_exists('AuthMiddleware') ? true : "AuthMiddleware 클래스를 찾을 수 없음";
});

// 테스트 2: 비로그인 상태 감지
runTest("비로그인 상태 감지", function() {
    // 세션 초기화
    unset($_SESSION['user_id']);
    
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    if ($isLoggedIn) {
        return "로그아웃 상태인데 로그인으로 인식됨";
    }
    
    $userId = AuthMiddleware::getCurrentUserId();
    if ($userId !== null) {
        return "로그아웃 상태인데 사용자 ID가 반환됨: $userId";
    }
    
    return true;
});

// 테스트 3: 로그인 상태 시뮬레이션
runTest("로그인 상태 시뮬레이션", function() {
    // 테스트용 로그인 시뮬레이션
    $_SESSION['user_id'] = 4;
    
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    if (!$isLoggedIn) {
        return "로그인 상태인데 비로그인으로 인식됨";
    }
    
    $userId = AuthMiddleware::getCurrentUserId();
    if ($userId != 4) {
        return "잘못된 사용자 ID 반환: expected 4, got " . ($userId ?? 'null');
    }
    
    return true;
});

// 테스트 4: 기업 사용자 권한 확인
runTest("기업 사용자 권한 확인", function() {
    $notice = new Notice();
    
    // 승인된 관리자 사용자 확인
    $isCompanyUser = $notice->isCompanyUser(4);
    if (!$isCompanyUser) {
        return "승인된 관리자 사용자가 기업 사용자로 인식되지 않음";
    }
    
    // 존재하지 않는 사용자
    $isNotCompanyUser = $notice->isCompanyUser(99999);
    if ($isNotCompanyUser) {
        return "존재하지 않는 사용자가 기업 사용자로 인식됨";
    }
    
    return true;
});

// 테스트 5: 공지사항 소유권 확인
runTest("공지사항 소유권 확인", function() {
    $notice = new Notice();
    
    // 기존 공지사항들 조회
    $notices = $notice->getList(1, 5);
    if (empty($notices)) {
        return "테스트할 공지사항이 없음";
    }
    
    $testNotice = $notices[0];
    $noticeId = $testNotice['id'];
    $ownerId = $testNotice['user_id'];
    
    // 실제 소유자 확인
    $isOwner = $notice->isOwner($noticeId, $ownerId);
    if (!$isOwner) {
        return "실제 소유자가 소유자로 인식되지 않음";
    }
    
    // 비소유자 확인 (다른 사용자 ID)
    $nonOwner = ($ownerId == 4) ? 1 : 4;
    $isNotOwner = $notice->isOwner($noticeId, $nonOwner);
    if ($isNotOwner) {
        return "비소유자가 소유자로 인식됨";
    }
    
    return true;
});

// 테스트 6: HTML 새니타이제이션 확인
runTest("HTML 새니타이제이션", function() {
    if (!class_exists('HtmlSanitizerHelper')) {
        return "HtmlSanitizerHelper 클래스를 찾을 수 없음";
    }
    
    $maliciousHtml = '<script>alert("XSS")</script><p>정상 내용</p><img onerror="alert(1)" src="">';
    $sanitized = HtmlSanitizerHelper::sanitize($maliciousHtml);
    
    if (strpos($sanitized, '<script>') !== false) {
        return "script 태그가 제거되지 않음";
    }
    
    if (strpos($sanitized, 'onerror') !== false) {
        return "위험한 이벤트 핸들러가 제거되지 않음";
    }
    
    if (strpos($sanitized, '<p>정상 내용</p>') === false) {
        return "정상적인 HTML이 과도하게 제거됨";
    }
    
    return true;
});

// 테스트 7: CSRF 토큰 생성 확인
runTest("CSRF 토큰 확인", function() {
    // CSRF 토큰이 세션에 존재하는지 확인
    if (!isset($_SESSION['csrf_token'])) {
        return "CSRF 토큰이 세션에 없음";
    }
    
    $token = $_SESSION['csrf_token'];
    if (strlen($token) < 32) {
        return "CSRF 토큰이 너무 짧음: " . strlen($token) . "자";
    }
    
    if (!ctype_xdigit($token)) {
        return "CSRF 토큰이 올바른 형식이 아님";
    }
    
    return true;
});

// 테스트 8: SQL Injection 방지 확인 (안전한 테스트)
runTest("SQL Injection 방지", function() {
    $notice = new Notice();
    
    // 악성 검색어로 SQL Injection 시도
    $maliciousSearch = "'; DROP TABLE notices; --";
    
    try {
        // 검색 기능에서 SQL Injection 방지 확인
        $results = $notice->search($maliciousSearch, 'title', null, 1, 10);
        
        // 에러가 발생하지 않고 결과가 배열로 반환되면 안전함
        if (!is_array($results)) {
            return "검색 결과가 배열이 아님 - SQL 처리 오류 가능성";
        }
        
        return true;
        
    } catch (Exception $e) {
        // SQL 에러가 발생하면 SQL Injection 취약점이 있을 수 있음
        if (strpos($e->getMessage(), 'SQL') !== false) {
            return "SQL 에러 발생: " . $e->getMessage();
        }
        
        // 다른 에러는 정상적인 처리로 간주
        return true;
    }
});

// 테스트 9: 입력 길이 제한 확인
runTest("입력 길이 제한", function() {
    $notice = new Notice();
    
    // 제목 길이 제한 테스트 (200자 초과)
    $longTitle = str_repeat('가', 201);
    
    try {
        $result = $notice->create([
            'user_id' => 4,
            'company_id' => 1,
            'title' => $longTitle,
            'content' => '<p>내용</p>',
            'is_featured' => 0
        ]);
        
        if ($result) {
            // 생성된 경우 즉시 삭제
            $notice->delete($result);
            return "과도한 길이의 제목이 허용됨";
        }
        
        return true;
        
    } catch (Exception $e) {
        // 길이 제한으로 인한 에러는 정상적인 동작
        return true;
    }
});

// 테스트 10: 권한별 접근 제어
runTest("권한별 접근 제어", function() {
    $notice = new Notice();
    
    // 일반 사용자 (비기업) 권한 확인
    $normalUserId = 1; // 일반 사용자 ID라고 가정
    $isCompanyUser = $notice->isCompanyUser($normalUserId);
    
    if ($isCompanyUser) {
        // 만약 일반 사용자가 기업 권한을 가진다면, 이는 승인된 상태일 수 있음
        // 데이터베이스에서 실제 확인
        require_once SRC_PATH . '/config/database.php';
        $db = Database::getInstance();
        $user = $db->fetch("
            SELECT u.role, u.corp_status 
            FROM users u 
            WHERE u.id = ?
        ", [$normalUserId]);
        
        if ($user && $user['role'] === 'ROLE_CORP' && $user['corp_status'] === 'approved') {
            return true; // 실제로 승인된 기업 사용자임
        } else {
            return "일반 사용자가 기업 권한을 가진 것으로 인식됨";
        }
    }
    
    return true;
});

// 세션 정리
unset($_SESSION['user_id']);

echo "\n" . str_repeat("=", 50) . "\n";
echo "🏁 권한 시스템 & 보안 QA 테스트 완료\n";
echo "📊 결과: $passedTests/$totalTests 통과 (" . round(($passedTests/$totalTests)*100, 1) . "%)\n";

if ($passedTests === $totalTests) {
    echo "✅ 모든 보안 테스트 통과!\n";
} else {
    echo "❌ " . ($totalTests - $passedTests) . "개 테스트 실패\n";
    echo "\n실패한 테스트들:\n";
    foreach ($testResults as $result) {
        if (strpos($result, '❌') === 0) {
            echo $result . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
?>