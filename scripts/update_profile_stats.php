<?php
/**
 * 프로필 통계 백그라운드 업데이트 스크립트
 */

// 경로 설정
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once SRC_PATH . '/config/database.php';

$userId = $argv[1] ?? null;
if (!$userId) {
    echo "Usage: php update_profile_stats.php <user_id>\n";
    exit(1);
}

try {
    $db = Database::getInstance();
    
    // 사용자 통계 계산
    $stats = [];
    
    // 게시글 수
    $result = $db->fetch('SELECT COUNT(*) as count FROM posts WHERE user_id = ? AND status = "published"', [$userId]);
    $stats['post_count'] = (int)$result['count'];
    
    // 댓글 수
    $result = $db->fetch('SELECT COUNT(*) as count FROM comments WHERE user_id = ? AND status = "active"', [$userId]);
    $stats['comment_count'] = (int)$result['count'];
    
    // 좋아요 수
    $result = $db->fetch('SELECT SUM(like_count) as total_likes FROM posts WHERE user_id = ? AND status = "published"', [$userId]);
    $stats['like_count'] = (int)($result['total_likes'] ?? 0);
    
    // 가입일
    $result = $db->fetch('SELECT DATEDIFF(NOW(), created_at) as join_days FROM users WHERE id = ?', [$userId]);
    $stats['join_days'] = (int)$result['join_days'];
    
    // 캐시 파일 업데이트
    $cacheFile = ROOT_PATH . '/cache/profile_stats_' . $userId . '.json';
    $cacheData = [
        'user_id' => $userId,
        'stats' => $stats,
        'updated_at' => time()
    ];
    
    if (!is_dir(ROOT_PATH . '/cache')) {
        mkdir(ROOT_PATH . '/cache', 0755, true);
    }
    
    file_put_contents($cacheFile, json_encode($cacheData));
    echo "Stats updated for user $userId\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>