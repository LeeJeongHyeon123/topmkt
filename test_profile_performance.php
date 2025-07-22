<?php
require_once 'src/config/database.php';

// 데이터베이스 연결
try {
    $db = Database::getInstance();
    echo "데이터베이스 연결 성공\n";
    
    // 성능 테스트를 위한 사용자 ID
    $userId = 1;
    
    // 시간 측정 함수
    function timeQuery($db, $sql, $params, $name) {
        $start = microtime(true);
        $result = $db->fetch($sql, $params);
        $end = microtime(true);
        $time = ($end - $start) * 1000;
        echo "$name: " . round($time, 2) . "ms\n";
        return $result;
    }
    
    // 1. 기본 사용자 정보 조회
    timeQuery($db, 'SELECT * FROM users WHERE id = ?', [$userId], '사용자 정보 조회');
    
    // 2. 게시글 수 조회
    timeQuery($db, 'SELECT COUNT(*) as count FROM posts WHERE user_id = ? AND status = "published"', [$userId], '게시글 수 조회');
    
    // 3. 댓글 수 조회
    timeQuery($db, 'SELECT COUNT(*) as count FROM comments WHERE user_id = ? AND status = "active"', [$userId], '댓글 수 조회');
    
    // 4. 좋아요 수 조회
    timeQuery($db, 'SELECT SUM(like_count) as total_likes FROM posts WHERE user_id = ? AND status = "published"', [$userId], '좋아요 수 조회');
    
    // 5. 최근 게시글 조회
    timeQuery($db, 'SELECT id, title, created_at, view_count, like_count, comment_count FROM posts WHERE user_id = ? AND status = "published" ORDER BY created_at DESC LIMIT 5', [$userId], '최근 게시글 조회');
    
    // 6. 최근 댓글 조회
    timeQuery($db, 'SELECT c.id, c.content, c.created_at, p.title as post_title, p.id as post_id FROM comments c JOIN posts p ON c.post_id = p.id WHERE c.user_id = ? AND c.status = "active" ORDER BY c.created_at DESC LIMIT 5', [$userId], '최근 댓글 조회');
    
    // 7. 통합 쿼리 테스트
    $sql = 'SELECT 
                u.*,
                (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id AND p.status = "published") as post_count,
                (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id AND c.status = "active") as comment_count,
                (SELECT COALESCE(SUM(p.like_count), 0) FROM posts p WHERE p.user_id = u.id AND p.status = "published") as total_likes,
                DATEDIFF(NOW(), u.created_at) as join_days
            FROM users u 
            WHERE u.id = ? AND u.status = "active"';
    
    timeQuery($db, $sql, [$userId], '통합 쿼리 조회');
    
    // 8. 테이블별 레코드 수 확인
    echo "\n=== 테이블별 레코드 수 ===\n";
    $result = $db->fetch('SELECT COUNT(*) as count FROM users');
    echo "사용자 수: " . number_format($result['count']) . "\n";
    
    $result = $db->fetch('SELECT COUNT(*) as count FROM posts');
    echo "게시글 수: " . number_format($result['count']) . "\n";
    
    $result = $db->fetch('SELECT COUNT(*) as count FROM comments');
    echo "댓글 수: " . number_format($result['count']) . "\n";
    
    $result = $db->fetch('SELECT COUNT(*) as count FROM likes');
    echo "좋아요 수: " . number_format($result['count']) . "\n";
    
} catch (Exception $e) {
    echo "오류: " . $e->getMessage() . "\n";
}
?>