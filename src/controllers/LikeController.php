<?php
/**
 * 좋아요 관련 컨트롤러
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

class LikeController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * 게시글 좋아요 토글
     */
    public function togglePostLike($postId = null) {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            // URL에서 post ID 가져오기
            if (!$postId) {
                ResponseHelper::json(['success' => false, 'message' => '게시글 ID가 필요합니다.'], 400);
                return;
            }
            
            $postId = intval($postId);
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 게시글 존재 확인
            $postStmt = $this->db->prepare("SELECT id FROM posts WHERE id = :post_id");
            $postStmt->execute(['post_id' => $postId]);
            if (!$postStmt->fetch()) {
                ResponseHelper::json(['success' => false, 'message' => '존재하지 않는 게시글입니다.'], 404);
                return;
            }
            
            // 이미 좋아요 했는지 확인
            $likeStmt = $this->db->prepare("SELECT id FROM post_likes WHERE post_id = :post_id AND user_id = :user_id");
            $likeStmt->execute(['post_id' => $postId, 'user_id' => $userId]);
            $existingLike = $likeStmt->fetch();
            
            $this->db->beginTransaction();
            
            try {
                if ($existingLike) {
                    // 좋아요 취소
                    $deleteLikeStmt = $this->db->prepare("DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id");
                    $deleteLikeStmt->execute(['post_id' => $postId, 'user_id' => $userId]);
                    
                    // posts 테이블의 like_count 감소
                    $updatePostStmt = $this->db->prepare("UPDATE posts SET like_count = GREATEST(like_count - 1, 0) WHERE id = :post_id");
                    $updatePostStmt->execute(['post_id' => $postId]);
                    
                    $action = 'unliked';
                    $message = '좋아요를 취소했습니다.';
                } else {
                    // 좋아요 추가
                    $insertLikeStmt = $this->db->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (:post_id, :user_id)");
                    $insertLikeStmt->execute(['post_id' => $postId, 'user_id' => $userId]);
                    
                    // posts 테이블의 like_count 증가
                    $updatePostStmt = $this->db->prepare("UPDATE posts SET like_count = like_count + 1 WHERE id = :post_id");
                    $updatePostStmt->execute(['post_id' => $postId]);
                    
                    $action = 'liked';
                    $message = '좋아요를 눌렀습니다.';
                }
                
                // 현재 좋아요 수 조회
                $countStmt = $this->db->prepare("SELECT like_count FROM posts WHERE id = :post_id");
                $countStmt->execute(['post_id' => $postId]);
                $likeCount = $countStmt->fetchColumn();
                
                $this->db->commit();
                
                ResponseHelper::json([
                    'success' => true,
                    'message' => $message,
                    'action' => $action,
                    'like_count' => intval($likeCount),
                    'is_liked' => ($action === 'liked')
                ]);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("좋아요 처리 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '좋아요 처리 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 게시글 좋아요 상태 조회
     */
    public function getPostLikeStatus($postId) {
        try {
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 게시글 좋아요 수 조회
            $countStmt = $this->db->prepare("SELECT like_count FROM posts WHERE id = :post_id");
            $countStmt->execute(['post_id' => $postId]);
            $likeCount = $countStmt->fetchColumn();
            
            if ($likeCount === false) {
                ResponseHelper::json(['success' => false, 'message' => '존재하지 않는 게시글입니다.'], 404);
                return;
            }
            
            // 사용자 좋아요 상태 조회
            $isLiked = false;
            if ($userId) {
                $likeStmt = $this->db->prepare("SELECT id FROM post_likes WHERE post_id = :post_id AND user_id = :user_id");
                $likeStmt->execute(['post_id' => $postId, 'user_id' => $userId]);
                $isLiked = (bool)$likeStmt->fetch();
            }
            
            ResponseHelper::json([
                'success' => true,
                'like_count' => intval($likeCount),
                'is_liked' => $isLiked
            ]);
            
        } catch (Exception $e) {
            error_log("좋아요 상태 조회 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '좋아요 상태 조회 중 오류가 발생했습니다.'], 500);
        }
    }
}