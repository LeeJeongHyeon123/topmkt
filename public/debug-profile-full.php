<?php
/**
 * 전체 프로필 로딩 디버그
 */

// 상대 경로 설정
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');

require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/helpers/JWTHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/models/User.php';

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    echo "<h1>프로필 로딩 단계별 디버그</h1>";
    
    // 1. 인증 확인
    echo "<h2>1. 인증 확인</h2>";
    if (!AuthMiddleware::isLoggedIn()) {
        echo "<p style='color: red;'>❌ 로그인되지 않음</p>";
        exit;
    }
    echo "<p style='color: green;'>✅ 로그인됨</p>";
    
    $currentUserId = AuthMiddleware::getCurrentUserId();
    echo "<p>사용자 ID: {$currentUserId}</p>";
    
    // 2. User 모델 생성
    echo "<h2>2. User 모델 생성</h2>";
    $userModel = new User();
    echo "<p style='color: green;'>✅ User 모델 생성됨</p>";
    
    // 3. 프로필 정보 조회
    echo "<h2>3. 프로필 정보 조회</h2>";
    $user = $userModel->getFullProfile($currentUserId);
    if (!$user) {
        echo "<p style='color: red;'>❌ 사용자 정보 조회 실패</p>";
        exit;
    }
    echo "<p style='color: green;'>✅ 사용자 정보 조회 성공</p>";
    echo "<p>닉네임: {$user['nickname']}</p>";
    
    // 4. 활동 통계 조회
    echo "<h2>4. 활동 통계 조회</h2>";
    try {
        $stats = $userModel->getProfileStats($currentUserId);
        echo "<p style='color: green;'>✅ 활동 통계 조회 성공</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ 활동 통계 조회 실패: " . $e->getMessage() . "</p>";
        $stats = [];
    }
    
    // 5. 최근 게시글 조회
    echo "<h2>5. 최근 게시글 조회</h2>";
    try {
        $recentPosts = $userModel->getRecentPosts($currentUserId, 5);
        echo "<p style='color: green;'>✅ 최근 게시글 조회 성공</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ 최근 게시글 조회 실패: " . $e->getMessage() . "</p>";
        $recentPosts = [];
    }
    
    // 6. 최근 댓글 조회
    echo "<h2>6. 최근 댓글 조회</h2>";
    try {
        $recentComments = $userModel->getRecentComments($currentUserId, 5);
        echo "<p style='color: green;'>✅ 최근 댓글 조회 성공</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ 최근 댓글 조회 실패: " . $e->getMessage() . "</p>";
        $recentComments = [];
    }
    
    // 7. 변수 설정
    echo "<h2>7. 페이지 변수 설정</h2>";
    $pageSection = 'profile';
    $page_title = $user['nickname'] . '님의 프로필';
    $isOwnProfile = true;
    
    $page_description = !empty($user['bio']) ? 
        htmlspecialchars(strip_tags(mb_substr($user['bio'], 0, 150))) : 
        $user['nickname'] . '님의 탑마케팅 프로필입니다.';
    
    $og_title = $user['nickname'] . '님의 프로필 - 탑마케팅';
    $og_description = $page_description;
    $og_type = 'profile';
    
    $og_image = 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/topmkt-og-image.png?v=' . date('Ymd');
    if (!empty($user['profile_image_original'])) {
        $og_image = 'https://' . $_SERVER['HTTP_HOST'] . $user['profile_image_original'];
    } elseif (!empty($user['profile_image_profile'])) {
        $og_image = 'https://' . $_SERVER['HTTP_HOST'] . $user['profile_image_profile'];
    }
    
    $keywords = '탑마케팅, ' . $user['nickname'] . ', 프로필, 마케팅 전문가, 네트워크 마케팅';
    
    echo "<p style='color: green;'>✅ 페이지 변수 설정 완료</p>";
    echo "<p>페이지 제목: {$page_title}</p>";
    
    // 8. 헤더 파일 존재 확인
    echo "<h2>8. 템플릿 파일 확인</h2>";
    $headerPath = SRC_PATH . '/views/templates/header.php';
    $profilePath = SRC_PATH . '/views/user/profile.php';
    $footerPath = SRC_PATH . '/views/templates/footer.php';
    
    if (file_exists($headerPath)) {
        echo "<p style='color: green;'>✅ 헤더 템플릿 존재: {$headerPath}</p>";
    } else {
        echo "<p style='color: red;'>❌ 헤더 템플릿 없음: {$headerPath}</p>";
    }
    
    if (file_exists($profilePath)) {
        echo "<p style='color: green;'>✅ 프로필 템플릿 존재: {$profilePath}</p>";
    } else {
        echo "<p style='color: red;'>❌ 프로필 템플릿 없음: {$profilePath}</p>";
    }
    
    if (file_exists($footerPath)) {
        echo "<p style='color: green;'>✅ 푸터 템플릿 존재: {$footerPath}</p>";
    } else {
        echo "<p style='color: red;'>❌ 푸터 템플릿 없음: {$footerPath}</p>";
    }
    
    // 9. 헤더 파일 포함 테스트 (출력 시작)
    echo "<h2>9. 실제 프로필 페이지 렌더링 시작</h2>";
    echo "<p style='color: blue;'>📄 헤더 포함 중...</p>";
    
    ob_start();
    try {
        require_once $headerPath;
        echo "<p style='color: green;'>✅ 헤더 로딩 성공</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ 헤더 로딩 실패: " . $e->getMessage() . "</p>";
    }
    
    try {
        require_once $profilePath;
        echo "<p style='color: green;'>✅ 프로필 페이지 로딩 성공</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ 프로필 페이지 로딩 실패: " . $e->getMessage() . "</p>";
    }
    
    try {
        require_once $footerPath;
        echo "<p style='color: green;'>✅ 푸터 로딩 성공</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ 푸터 로딩 실패: " . $e->getMessage() . "</p>";
    }
    
    $output = ob_get_clean();
    
    echo "<h2>완료!</h2>";
    echo "<p style='color: green;'>모든 단계가 성공적으로 완료되었습니다.</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>치명적 오류 발생</h2>";
    echo "<p>오류: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "<h2 style='color: red;'>PHP 오류 발생</h2>";
    echo "<p>오류: " . $e->getMessage() . "</p>";
    echo "<p>파일: " . $e->getFile() . "</p>";
    echo "<p>라인: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>