<?php
define('ROOT_PATH', '/var/www/html/topmkt');
define('SRC_PATH', ROOT_PATH . '/src');

require_once 'src/config/database.php';

try {
    $db = Database::getInstance();
    echo "=== 프로필 성능 최적화 적용 ===\n\n";
    
    // 1. 새로운 인덱스 생성
    echo "1. 새로운 인덱스 생성 중...\n";
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_posts_user_stats ON posts (user_id, status, like_count, created_at)",
        "CREATE INDEX IF NOT EXISTS idx_comments_user_stats ON comments (user_id, status, created_at)"
    ];
    
    foreach ($indexes as $sql) {
        try {
            $db->query($sql);
            echo "   - 인덱스 생성 완료: " . substr($sql, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "   - 인덱스 생성 실패: " . $e->getMessage() . "\n";
        }
    }
    
    // 2. 캐시 테이블 생성
    echo "\n2. 캐시 테이블 생성 중...\n";
    $cacheTableSql = "CREATE TABLE IF NOT EXISTS user_stats_cache (
        user_id INT PRIMARY KEY,
        post_count INT DEFAULT 0,
        comment_count INT DEFAULT 0,
        like_count INT DEFAULT 0,
        join_days INT DEFAULT 0,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_last_updated (last_updated)
    )";
    
    try {
        $db->query($cacheTableSql);
        echo "   - 캐시 테이블 생성 완료\n";
    } catch (Exception $e) {
        echo "   - 캐시 테이블 생성 실패: " . $e->getMessage() . "\n";
    }
    
    // 3. 테이블 분석
    echo "\n3. 테이블 분석 중...\n";
    $analyzeTables = ['posts', 'comments', 'users'];
    foreach ($analyzeTables as $table) {
        try {
            $db->query("ANALYZE TABLE $table");
            echo "   - $table 테이블 분석 완료\n";
        } catch (Exception $e) {
            echo "   - $table 테이블 분석 실패: " . $e->getMessage() . "\n";
        }
    }
    
    // 4. 대용량 데이터 사용자의 캐시 미리 생성
    echo "\n4. 대용량 데이터 사용자 캐시 생성 중...\n";
    $largeDataUsers = $db->fetchAll("SELECT u.id FROM users u 
                                     JOIN posts p ON u.id = p.user_id 
                                     GROUP BY u.id 
                                     HAVING COUNT(*) > 1000 
                                     LIMIT 10");
    
    foreach ($largeDataUsers as $user) {
        $userId = $user['id'];
        echo "   - 사용자 $userId 캐시 생성 중...\n";
        
        // 통계 계산
        $postCount = $db->fetch("SELECT COUNT(*) as count FROM posts WHERE user_id = ? AND status = 'published'", [$userId]);
        $commentCount = $db->fetch("SELECT COUNT(*) as count FROM comments WHERE user_id = ? AND status = 'active'", [$userId]);
        $likeCount = $db->fetch("SELECT COALESCE(SUM(like_count), 0) as total FROM posts WHERE user_id = ? AND status = 'published'", [$userId]);
        $joinDays = $db->fetch("SELECT DATEDIFF(NOW(), created_at) as days FROM users WHERE id = ?", [$userId]);
        
        // 캐시 저장
        $cacheSql = "INSERT INTO user_stats_cache (user_id, post_count, comment_count, like_count, join_days) 
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                         post_count = VALUES(post_count),
                         comment_count = VALUES(comment_count),
                         like_count = VALUES(like_count),
                         join_days = VALUES(join_days)";
        
        $db->execute($cacheSql, [
            $userId,
            $postCount['count'],
            $commentCount['count'],
            $likeCount['total'],
            $joinDays['days']
        ]);
        
        echo "     게시글: " . number_format($postCount['count']) . 
             ", 댓글: " . number_format($commentCount['count']) . 
             ", 좋아요: " . number_format($likeCount['total']) . "\n";
    }
    
    echo "\n=== 최적화 완료 ===\n";
    echo "총 " . count($largeDataUsers) . "명의 대용량 사용자 캐시가 생성되었습니다.\n";
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage() . "\n";
    echo "위치: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>