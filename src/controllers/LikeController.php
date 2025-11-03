<?php
/**
 * 좋아요 관련 컨트롤러
 */

require_once SRC_PATH . '/controllers/BaseController.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/FcmHelper.php';
require_once SRC_PATH . '/models/FcmToken.php';
require_once SRC_PATH . '/models/NotificationSettings.php';
require_once SRC_PATH . '/helpers/WebLogger.php';

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

                // 🔔 FCM 푸시 알림 전송 (좋아요 추가 시에만)
                if ($action === 'liked') {
                    try {
                        require_once SRC_PATH . '/models/Post.php';
                        $postModel = new Post();
                        $fcmTokenModel = new FcmToken();
                        $currentUserId = AuthMiddleware::getCurrentUserId();

                        // 게시글 정보 조회
                        $post = $postModel->getPostById($postId);

                        if ($post && $post['user_id'] != $currentUserId) {
                            // 🔔 알림 설정 확인: 사용자가 좋아요 알림을 활성화했는지 확인
                            $shouldSendNotification = true;
                            try {
                                $notificationSettings = new NotificationSettings();
                                $isEnabled = $notificationSettings->isNotificationEnabled($post['user_id'], 'likes');

                                if (!$isEnabled) {
                                    WebLogger::info('좋아요 알림 스킵 (알림 설정 OFF)', [
                                        'post_id' => $postId,
                                        'recipient_id' => $post['user_id']
                                    ]);
                                    $shouldSendNotification = false;
                                }
                            } catch (Exception $e) {
                                // NotificationSettings 실패 시 기본적으로 알림 전송 (opt-in 방식)
                                WebLogger::warning('알림 설정 확인 실패, 기본 알림 전송', [
                                    'error' => $e->getMessage(),
                                    'post_id' => $postId
                                ]);
                            }

                            // 알림 설정이 ON이거나 확인 실패 시 알림 전송
                            if ($shouldSendNotification) {
                                // 자기 자신의 게시글에는 알림 X
                                $tokens = $fcmTokenModel->getTokensByUserId($post['user_id']);

                                if (!empty($tokens)) {
                                    $title = $post['title'] ?? '게시글';
                                    $truncatedTitle = mb_strlen($title) > 20 ? mb_substr($title, 0, 20) . '...' : $title;

                                    foreach ($tokens as $token) {
                                        FcmHelper::sendPush(
                                            $token['fcm_token'],
                                            '새 좋아요 알림',
                                            '회원님 게시글 "' . $truncatedTitle . '"에 좋아요를 눌렀어요.',
                                            [
                                                'type' => 'like',
                                                'post_id' => $postId,
                                                'like_count' => $likeCount
                                            ]
                                        );
                                    }

                                    WebLogger::info('좋아요 알림 전송 완료', [
                                        'post_id' => $postId,
                                        'recipient_id' => $post['user_id'],
                                        'token_count' => count($tokens)
                                    ]);
                                }
                            }
                        }
                    } catch (Exception $e) {
                        WebLogger::error('좋아요 알림 전송 실패', [
                            'error' => $e->getMessage(),
                            'post_id' => $postId,
                            'trace' => $e->getTraceAsString()
                        ]);
                        // 알림 실패해도 좋아요 처리는 성공
                    }
                }

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