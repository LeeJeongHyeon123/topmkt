<?php
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once 'src/config/database.php';
require_once 'src/models/UserOptimized.php';

try {
    echo "=== 최적화된 프로필 성능 테스트 ===\n\n";
    
    $userOptimized = new UserOptimized();
    $userId = 4; // 대용량 데이터 사용자
    
    // 1. 첫 번째 호출 (캐시 생성)
    echo "1. 첫 번째 호출 (캐시 생성)\n";
    $start = microtime(true);
    $profileData = $userOptimized->getOptimizedProfileDataWithCache($userId);
    $time = (microtime(true) - $start) * 1000;
    echo "   시간: " . round($time, 2) . "ms\n";
    echo "   게시글 수: " . number_format($profileData['stats']['post_count']) . "\n";
    echo "   댓글 수: " . number_format($profileData['stats']['comment_count']) . "\n";
    echo "   좋아요 수: " . number_format($profileData['stats']['like_count']) . "\n";
    
    // 2. 두 번째 호출 (캐시 사용)
    echo "\n2. 두 번째 호출 (캐시 사용)\n";
    $start = microtime(true);
    $profileData = $userOptimized->getOptimizedProfileDataWithCache($userId);
    $time = (microtime(true) - $start) * 1000;
    echo "   시간: " . round($time, 2) . "ms\n";
    
    // 3. 세 번째 호출 (캐시 사용)
    echo "\n3. 세 번째 호출 (캐시 사용)\n";
    $start = microtime(true);
    $profileData = $userOptimized->getOptimizedProfileDataWithCache($userId);
    $time = (microtime(true) - $start) * 1000;
    echo "   시간: " . round($time, 2) . "ms\n";
    
    // 4. 캐시 무효화 후 재생성
    echo "\n4. 캐시 무효화 후 재생성\n";
    $userOptimized->invalidateProfileCache($userId);
    $start = microtime(true);
    $profileData = $userOptimized->getOptimizedProfileDataWithCache($userId);
    $time = (microtime(true) - $start) * 1000;
    echo "   시간: " . round($time, 2) . "ms\n";
    
    // 5. 메모리 사용량 확인
    echo "\n=== 메모리 사용량 ===\n";
    echo "현재 메모리 사용량: " . round(memory_get_usage(true) / 1024 / 1024, 2) . "MB\n";
    echo "최대 메모리 사용량: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . "MB\n";
    
    // 6. 캐시 파일 확인
    echo "\n=== 캐시 파일 확인 ===\n";
    $cacheFile = ROOT_PATH . '/cache/profile_' . $userId . '.json';
    if (file_exists($cacheFile)) {
        echo "캐시 파일 크기: " . round(filesize($cacheFile) / 1024, 2) . "KB\n";
        echo "캐시 파일 생성 시간: " . date('Y-m-d H:i:s', filemtime($cacheFile)) . "\n";
    } else {
        echo "캐시 파일 없음\n";
    }
    
    // 7. 데이터베이스 캐시 테이블 확인
    echo "\n=== 데이터베이스 캐시 확인 ===\n";
    $db = Database::getInstance();
    $cacheData = $db->fetch("SELECT * FROM user_stats_cache WHERE user_id = ?", [$userId]);
    if ($cacheData) {
        echo "DB 캐시 존재: " . $cacheData['last_updated'] . "\n";
        echo "DB 캐시 데이터: 게시글 " . number_format($cacheData['post_count']) . 
             ", 댓글 " . number_format($cacheData['comment_count']) . 
             ", 좋아요 " . number_format($cacheData['like_count']) . "\n";
    } else {
        echo "DB 캐시 없음\n";
    }
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage() . "\n";
    echo "위치: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>