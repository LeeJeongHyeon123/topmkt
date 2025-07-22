<?php
/**
 * 강의 162번 권한 문제 디버깅
 */

require_once '../src/config/database.php';
require_once '../src/middlewares/AuthMiddleware.php';
require_once '../src/middleware/CorporateMiddleware.php';
require_once '../src/helpers/WebLogger.php';
require_once '../src/helpers/ResponseHelper.php';

session_start();

echo "<h1>강의 162번 권한 문제 디버깅</h1>";

try {
    // 1. 현재 로그인 상태 확인
    echo "<h2>1. 현재 로그인 상태</h2>";
    $isLoggedIn = AuthMiddleware::isLoggedIn();
    $currentUserId = AuthMiddleware::getCurrentUserId();
    $isAdmin = AuthMiddleware::isAdmin();
    
    echo "<p><strong>로그인 상태:</strong> " . ($isLoggedIn ? '✅ 로그인됨' : '❌ 로그인 안됨') . "</p>";
    echo "<p><strong>현재 사용자 ID:</strong> " . ($currentUserId ?: 'None') . "</p>";
    echo "<p><strong>관리자 여부:</strong> " . ($isAdmin ? '✅ 관리자' : '❌ 일반 사용자') . "</p>";
    
    // 2. 강의 162번 정보 조회
    echo "<h2>2. 강의 162번 정보</h2>";
    $db = Database::getInstance();
    $lecture = $db->fetch("SELECT * FROM lectures WHERE id = 162");
    
    if (!$lecture) {
        echo "<p>❌ 강의 162번을 찾을 수 없습니다.</p>";
        exit;
    }
    
    echo "<p><strong>강의 제목:</strong> " . htmlspecialchars($lecture['title']) . "</p>";
    echo "<p><strong>강의 소유자 ID:</strong> " . $lecture['user_id'] . "</p>";
    echo "<p><strong>강의 상태:</strong> " . $lecture['status'] . "</p>";
    echo "<p><strong>생성일:</strong> " . $lecture['created_at'] . "</p>";
    
    // 3. 권한 검증
    echo "<h2>3. 권한 검증</h2>";
    
    // 3.1 canEditLecture 검증
    $canEdit = ($currentUserId == $lecture['user_id']) || $isAdmin;
    echo "<p><strong>수정 권한 (canEditLecture):</strong> " . ($canEdit ? '✅ 있음' : '❌ 없음') . "</p>";
    
    if (!$canEdit) {
        echo "<p>🔍 <strong>권한 없음 이유:</strong></p>";
        echo "<ul>";
        echo "<li>현재 사용자 ID: " . ($currentUserId ?: 'None') . "</li>";
        echo "<li>강의 소유자 ID: " . $lecture['user_id'] . "</li>";
        echo "<li>관리자 여부: " . ($isAdmin ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
    }
    
    // 3.2 기업회원 권한 검증
    echo "<h3>3.2 기업회원 권한 검증</h3>";
    $corporatePermission = CorporateMiddleware::checkLectureEventPermission();
    echo "<p><strong>기업회원 권한:</strong> " . ($corporatePermission['hasPermission'] ? '✅ 있음' : '❌ 없음') . "</p>";
    
    if (!$corporatePermission['hasPermission']) {
        echo "<p><strong>거부 사유:</strong> " . htmlspecialchars($corporatePermission['message']) . "</p>";
    }
    
    // 3.3 CSRF 토큰 검증
    echo "<h3>3.3 CSRF 토큰 상태</h3>";
    echo "<p><strong>세션 CSRF 토큰:</strong> " . (isset($_SESSION['csrf_token']) ? '✅ 존재' : '❌ 없음') . "</p>";
    
    if (isset($_SESSION['csrf_token'])) {
        echo "<p><strong>토큰 값:</strong> " . substr($_SESSION['csrf_token'], 0, 10) . "...</p>";
    }
    
    // 4. 사용자 정보 확인
    if ($currentUserId) {
        echo "<h2>4. 현재 사용자 정보</h2>";
        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$currentUserId]);
        
        if ($user) {
            echo "<p><strong>이름:</strong> " . htmlspecialchars($user['name']) . "</p>";
            echo "<p><strong>이메일:</strong> " . htmlspecialchars($user['email']) . "</p>";
            echo "<p><strong>역할:</strong> " . ($user['role'] ?? 'user') . "</p>";
            echo "<p><strong>계정 상태:</strong> " . ($user['status'] ?? 'active') . "</p>";
        }
        
        // 4.1 기업 정보 확인
        echo "<h3>4.1 기업 정보</h3>";
        $corporate = $db->fetch("SELECT * FROM corporate_applications WHERE user_id = ?", [$currentUserId]);
        
        if ($corporate) {
            echo "<p><strong>기업 신청 상태:</strong> " . $corporate['status'] . "</p>";
            echo "<p><strong>기업명:</strong> " . htmlspecialchars($corporate['company_name']) . "</p>";
            echo "<p><strong>승인일:</strong> " . ($corporate['approved_at'] ?? '미승인') . "</p>";
        } else {
            echo "<p>❌ 기업 신청 내역 없음</p>";
        }
    }
    
    // 5. 강의 소유자 정보
    echo "<h2>5. 강의 소유자 정보</h2>";
    $owner = $db->fetch("SELECT * FROM users WHERE id = ?", [$lecture['user_id']]);
    
    if ($owner) {
        echo "<p><strong>소유자 이름:</strong> " . htmlspecialchars($owner['name']) . "</p>";
        echo "<p><strong>소유자 이메일:</strong> " . htmlspecialchars($owner['email']) . "</p>";
        echo "<p><strong>소유자 역할:</strong> " . ($owner['role'] ?? 'user') . "</p>";
    }
    
    // 6. 해결 방안 제시
    echo "<h2>6. 해결 방안</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
    
    if (!$isLoggedIn) {
        echo "<p>❌ <strong>문제:</strong> 로그인되지 않음</p>";
        echo "<p>🔧 <strong>해결:</strong> 로그인 필요</p>";
    } elseif (!$canEdit) {
        echo "<p>❌ <strong>문제:</strong> 강의 수정 권한 없음</p>";
        echo "<p>🔧 <strong>해결:</strong> 강의 소유자({$lecture['user_id']})로 로그인하거나 관리자 권한 필요</p>";
    } elseif (!$corporatePermission['hasPermission']) {
        echo "<p>❌ <strong>문제:</strong> 기업회원 권한 없음</p>";
        echo "<p>🔧 <strong>해결:</strong> 기업 인증 승인 필요</p>";
    } else {
        echo "<p>✅ <strong>모든 권한 확인됨</strong></p>";
        echo "<p>🔧 CSRF 토큰 문제일 가능성 - 페이지 새로고침 후 재시도</p>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>오류 발생</h2>";
    echo "<p>오류: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ddd; }
h3 { color: #888; }
</style>