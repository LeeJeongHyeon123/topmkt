<?php
/**
 * 게시글 999505 최신 댓글 확인
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/config/database.php';

try {
    $db = Database::getInstance();
    
    echo "📊 게시글 999505 최신 댓글 확인\n\n";
    
    // 최신 댓글 3개 조회
    $comments = $db->fetchAll("
        SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.nickname
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.post_id = 999505 AND c.status = 'active'
        ORDER BY c.created_at DESC 
        LIMIT 5
    ", []);
    
    echo "💬 최신 댓글 " . count($comments) . "개:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($comments as $comment) {
        echo "ID: {$comment['id']}\n";
        echo "작성자: {$comment['nickname']} (ID: {$comment['user_id']})\n";
        echo "내용: " . mb_substr($comment['content'], 0, 50) . "...\n";
        echo "작성시간: {$comment['created_at']}\n";
        echo str_repeat("-", 40) . "\n";
    }
    
    // 전체 댓글 수 확인
    $totalComments = $db->fetch("
        SELECT COUNT(*) as count 
        FROM comments 
        WHERE post_id = 999505 AND status = 'active'
    ", []);
    
    echo "\n📈 전체 댓글 수: " . $totalComments['count'] . "개\n";
    
    // 오늘 작성된 댓글 수
    $todayComments = $db->fetch("
        SELECT COUNT(*) as count 
        FROM comments 
        WHERE post_id = 999505 
        AND status = 'active'
        AND DATE(created_at) = CURDATE()
    ", []);
    
    echo "📅 오늘 작성된 댓글: " . $todayComments['count'] . "개\n";
    
    echo "\n✅ 데이터베이스 연결 및 쿼리 정상 작동\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
}
?>