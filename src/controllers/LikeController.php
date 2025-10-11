<?php
/**
 * 좋아요 관련 컨트롤러
 */

require_once SRC_PATH . '/controllers/BaseController.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

class LikeController extends BaseController {
    public function __construct() {
        parent::__construct(); // BaseController의 생성자 호출
    }
    
    /**
     * 게시글 좋아요 토글
     */
    public function togglePostLike($postId = null) {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                ResponseHelper::error('로그인이 필요합니다.', 401);
                return;
            }
            
            // URL에서 post ID 가져오기
            if (!$postId) {
                ResponseHelper::error('게시글 ID가 필요합니다.', 400);
                return;
            }
            
            $postId = intval($postId);
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 게시글 존재 확인
            $post = $this->db->fetch("SELECT id FROM posts WHERE id = ?", [$postId]);
            if (!$post) {
                ResponseHelper::error('존재하지 않는 게시글입니다.', 404);
                return;
            }
            
            // 이미 좋아요 했는지 확인
            $existingLike = $this->db->fetch("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?", [$postId, $userId]);
            
            $this->db->beginTransaction();
            
            try {
                if ($existingLike) {
                    // 좋아요 취소
                    $this->db->execute("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?", [$postId, $userId]);
                    
                    // posts 테이블의 like_count 감소
                    $this->db->execute("UPDATE posts SET like_count = GREATEST(like_count - 1, 0) WHERE id = ?", [$postId]);
                    
                    $action = 'unliked';
                    $message = '좋아요를 취소했습니다.';
                } else {
                    // 좋아요 추가
                    $this->db->execute("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)", [$postId, $userId]);
                    
                    // posts 테이블의 like_count 증가
                    $this->db->execute("UPDATE posts SET like_count = like_count + 1 WHERE id = ?", [$postId]);
                    
                    $action = 'liked';
                    $message = '좋아요를 눌렀습니다.';
                }
                
                // 현재 좋아요 수 조회
                $likeCountResult = $this->db->fetch("SELECT like_count FROM posts WHERE id = ?", [$postId]);
                $likeCount = $likeCountResult['like_count'] ?? 0;
                
                $this->db->commit();
                
                ResponseHelper::success([
                    'action' => $action,
                    'like_count' => intval($likeCount),
                    'is_liked' => ($action === 'liked')
                ], $message);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("좋아요 처리 중 오류: " . $e->getMessage());
            ResponseHelper::error('좋아요 처리 중 오류가 발생했습니다.', 500);
        }
    }
    
    /**
     * 게시글 좋아요 상태 조회
     */
    public function getPostLikeStatus($postId) {
        try {
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 게시글 좋아요 수 조회
            $post = $this->db->fetch("SELECT like_count FROM posts WHERE id = ?", [$postId]);
            
            if (!$post) {
                ResponseHelper::error('존재하지 않는 게시글입니다.', 404);
                return;
            }
            
            $likeCount = $post['like_count'] ?? 0;
            
            // 사용자 좋아요 상태 조회
            $isLiked = false;
            if ($userId) {
                $userLike = $this->db->fetch("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?", [$postId, $userId]);
                $isLiked = !empty($userLike);
            }
            
            ResponseHelper::success([
                'like_count' => intval($likeCount),
                'is_liked' => $isLiked
            ]);
            
        } catch (Exception $e) {
            error_log("좋아요 상태 조회 중 오류: " . $e->getMessage());
            ResponseHelper::error('좋아요 상태 조회 중 오류가 발생했습니다.', 500);
        }
    }
}