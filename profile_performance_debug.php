<?php
// 경로 설정
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');

require_once 'src/config/database.php';
require_once 'src/models/User.php';
require_once 'src/middlewares/AuthMiddleware.php';

// 세션 시작
session_start();

// 모킹된 로그인 상태 설정
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';
$_SESSION['is_logged_in'] = true;

try {
    echo "=== 프로필 성능 디버깅 ===\n\n";
    
    $userModel = new User();
    $userId = 4; // 대용량 데이터 사용자
    
    // 전체 프로필 로딩 시간 측정
    $overallStart = microtime(true);
    
    // 1. 기본 사용자 정보 조회
    echo "1. 기본 사용자 정보 조회\n";
    $start = microtime(true);
    $user = $userModel->getFullProfile($userId);
    $time = (microtime(true) - $start) * 1000;
    echo "   시간: " . round($time, 2) . "ms\n";
    
    // 2. 통계 정보 조회
    echo "\n2. 통계 정보 조회\n";
    $start = microtime(true);
    $stats = $userModel->getProfileStats($userId);
    $time = (microtime(true) - $start) * 1000;
    echo "   시간: " . round($time, 2) . "ms\n";
    echo "   게시글 수: " . number_format($stats['post_count']) . "\n";
    echo "   댓글 수: " . number_format($stats['comment_count']) . "\n";
    echo "   좋아요 수: " . number_format($stats['like_count']) . "\n";
    
    // 3. 최근 게시글 조회
    echo "\n3. 최근 게시글 조회\n";
    $start = microtime(true);
    $recentPosts = $userModel->getRecentPosts($userId, 5);
    $time = (microtime(true) - $start) * 1000;
    echo "   시간: " . round($time, 2) . "ms\n";
    echo "   게시글 수: " . count($recentPosts) . "\n";
    
    // 4. 최근 댓글 조회
    echo "\n4. 최근 댓글 조회\n";
    $start = microtime(true);
    $recentComments = $userModel->getRecentComments($userId, 5);
    $time = (microtime(true) - $start) * 1000;
    echo "   시간: " . round($time, 2) . "ms\n";
    echo "   댓글 수: " . count($recentComments) . "\n";
    
    // 5. 통합 쿼리 조회 (최적화된 방식)
    echo "\n5. 통합 쿼리 조회 (최적화된 방식)\n";
    $start = microtime(true);
    $optimizedData = $userModel->getOptimizedProfileData($userId);
    $time = (microtime(true) - $start) * 1000;
    echo "   시간: " . round($time, 2) . "ms\n";
    
    // 전체 시간 출력
    $overallTime = (microtime(true) - $overallStart) * 1000;
    echo "\n=== 전체 프로필 로딩 시간 ===\n";
    echo "총 시간: " . round($overallTime, 2) . "ms\n";
    
    // 6. 메모리 사용량 확인
    echo "\n=== 메모리 사용량 ===\n";
    echo "현재 메모리 사용량: " . round(memory_get_usage(true) / 1024 / 1024, 2) . "MB\n";
    echo "최대 메모리 사용량: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . "MB\n";
    
    // 7. 파일 시스템 접근 테스트
    echo "\n=== 파일 시스템 접근 테스트 ===\n";
    $start = microtime(true);
    $profileImagePath = '/var/www/html/topmkt/public/assets/uploads/profiles/2025/user_1_1734679999_profile.jpg';
    if (file_exists($profileImagePath)) {
        echo "프로필 이미지 파일 존재\n";
        $fileSize = filesize($profileImagePath);
        echo "파일 크기: " . round($fileSize / 1024, 2) . "KB\n";
    } else {
        echo "프로필 이미지 파일 없음\n";
    }
    $time = (microtime(true) - $start) * 1000;
    echo "파일 시스템 접근 시간: " . round($time, 2) . "ms\n";
    
    // 8. 프로파일링 - 구체적인 병목 지점 찾기
    echo "\n=== 구체적인 병목 분석 ===\n";
    
    // 각 쿼리별 실행 시간 측정
    $db = Database::getInstance();
    
    // 좋아요 수 계산 (가장 많은 레코드를 처리하는 쿼리)
    $start = microtime(true);
    $result = $db->fetch('SELECT SUM(like_count) as total_likes FROM posts WHERE user_id = ? AND status = "published"', [$userId]);
    $time = (microtime(true) - $start) * 1000;
    echo "좋아요 수 계산 시간: " . round($time, 2) . "ms\n";
    
    // 게시글 수 계산
    $start = microtime(true);
    $result = $db->fetch('SELECT COUNT(*) as post_count FROM posts WHERE user_id = ? AND status = "published"', [$userId]);
    $time = (microtime(true) - $start) * 1000;
    echo "게시글 수 계산 시간: " . round($time, 2) . "ms\n";
    
    // 댓글 수 계산
    $start = microtime(true);
    $result = $db->fetch('SELECT COUNT(*) as comment_count FROM comments WHERE user_id = ? AND status = "active"', [$userId]);
    $time = (microtime(true) - $start) * 1000;
    echo "댓글 수 계산 시간: " . round($time, 2) . "ms\n";
    
    // 9. 테이블 상태 확인
    echo "\n=== 테이블 상태 확인 ===\n";
    $tableStatus = $db->fetchAll('SHOW TABLE STATUS LIKE "posts"');
    if (!empty($tableStatus)) {
        $status = $tableStatus[0];
        echo "posts 테이블 행 수: " . number_format($status['Rows']) . "\n";
        echo "posts 테이블 크기: " . round($status['Data_length'] / 1024 / 1024, 2) . "MB\n";
        echo "posts 테이블 인덱스 크기: " . round($status['Index_length'] / 1024 / 1024, 2) . "MB\n";
    }
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage() . "\n";
    echo "오류 위치: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>