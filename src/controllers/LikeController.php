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
        error_log("========== LikeController::togglePostLike START ==========");
        error_log("🔍 [LIKE_DEBUG] Method called with postId: " . var_export($postId, true));
        error_log("🔍 [LIKE_DEBUG] REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
        error_log("🔍 [LIKE_DEBUG] REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));

        try {
            error_log("🔍 [LIKE_DEBUG] Entering try block");

            // 로그인 확인
            error_log("🔍 [LIKE_DEBUG] Checking authentication...");
            if (!AuthMiddleware::isLoggedIn()) {
                error_log("❌ [LIKE_DEBUG] User not logged in");
                ResponseHelper::error('로그인이 필요합니다.', 401);
                return;
            }
            error_log("✅ [LIKE_DEBUG] User is logged in");

            // URL에서 post ID 가져오기
            error_log("🔍 [LIKE_DEBUG] Checking postId parameter...");
            if (!$postId) {
                error_log("❌ [LIKE_DEBUG] No postId provided");
                ResponseHelper::error('게시글 ID가 필요합니다.', 400);
                return;
            }
            error_log("✅ [LIKE_DEBUG] postId provided: " . $postId);

            $postId = intval($postId);
            $userId = AuthMiddleware::getCurrentUserId();
            error_log("🔍 [LIKE_DEBUG] postId (int): $postId, userId: $userId");
            
            // 게시글 존재 확인
            error_log("🔍 [LIKE_DEBUG] Checking if post exists...");
            $post = $this->db->fetch("SELECT id FROM posts WHERE id = ?", [$postId]);
            if (!$post) {
                error_log("❌ [LIKE_DEBUG] Post not found: $postId");
                ResponseHelper::error('존재하지 않는 게시글입니다.', 404);
                return;
            }
            error_log("✅ [LIKE_DEBUG] Post exists: $postId");

            // 이미 좋아요 했는지 확인
            error_log("🔍 [LIKE_DEBUG] Checking existing like...");
            $existingLike = $this->db->fetch("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?", [$postId, $userId]);
            error_log("🔍 [LIKE_DEBUG] Existing like: " . var_export($existingLike, true));

            error_log("🔍 [LIKE_DEBUG] Beginning transaction...");
            $this->db->beginTransaction();
            
            try {
                if ($existingLike) {
                    error_log("🔍 [LIKE_DEBUG] Unliking post...");
                    // 좋아요 취소
                    $this->db->execute("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?", [$postId, $userId]);

                    // posts 테이블의 like_count 감소
                    $this->db->execute("UPDATE posts SET like_count = GREATEST(like_count - 1, 0) WHERE id = ?", [$postId]);

                    $action = 'unliked';
                    $message = '좋아요를 취소했습니다.';
                    error_log("✅ [LIKE_DEBUG] Unlike successful");
                } else {
                    error_log("🔍 [LIKE_DEBUG] Liking post...");
                    // 좋아요 추가
                    $this->db->execute("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)", [$postId, $userId]);

                    // posts 테이블의 like_count 증가
                    $this->db->execute("UPDATE posts SET like_count = like_count + 1 WHERE id = ?", [$postId]);

                    $action = 'liked';
                    $message = '좋아요를 눌렀습니다.';
                    error_log("✅ [LIKE_DEBUG] Like successful");
                }

                // 현재 좋아요 수 조회
                error_log("🔍 [LIKE_DEBUG] Fetching like count...");
                $likeCountResult = $this->db->fetch("SELECT like_count FROM posts WHERE id = ?", [$postId]);
                $likeCount = $likeCountResult['like_count'] ?? 0;
                error_log("🔍 [LIKE_DEBUG] Like count: $likeCount");

                error_log("🔍 [LIKE_DEBUG] Committing transaction...");
                $this->db->commit();
                error_log("✅ [LIKE_DEBUG] Transaction committed");

                // 🔔 FCM 푸시 알림 전송 (좋아요 추가 시에만)
                error_log("🔍 [LIKE_DEBUG] Checking if notification needed (action: $action)...");
                if ($action === 'liked') {
                    error_log("🔍 [LIKE_DEBUG] Entering FCM notification block...");
                    try {
                        error_log("🔍 [LIKE_DEBUG] Loading Post model...");
                        require_once SRC_PATH . '/models/Post.php';
                        $postModel = new Post();
                        $fcmTokenModel = new FcmToken();
                        $currentUserId = AuthMiddleware::getCurrentUserId();
                        error_log("🔍 [LIKE_DEBUG] Models loaded, fetching post details...");

                        // 게시글 정보 조회
                        $post = $postModel->getById($postId);
                        error_log("🔍 [LIKE_DEBUG] Post details: " . var_export($post, true));

                        if ($post && $post['user_id'] != $currentUserId) {
                            error_log("🔍 [LIKE_DEBUG] Post owner is different from current user, will send notification");
                            // 🔔 알림 설정 확인: 사용자가 좋아요 알림을 활성화했는지 확인
                            // 🔥 임시 비활성화: NotificationSettings 문제 디버깅용
                            $shouldSendNotification = true;
                            error_log("🔍 [LIKE_DEBUG] shouldSendNotification: true");

                            // try {
                            //     $notificationSettings = new NotificationSettings();
                            //     $isEnabled = $notificationSettings->isNotificationEnabled($post['user_id'], 'likes');

                            //     if (!$isEnabled) {
                            //         WebLogger::info('좋아요 알림 스킵 (알림 설정 OFF)', [
                            //             'post_id' => $postId,
                            //             'recipient_id' => $post['user_id']
                            //         ]);
                            //         $shouldSendNotification = false;
                            //     }
                            // } catch (Exception $e) {
                            //     // NotificationSettings 실패 시 기본적으로 알림 전송 (opt-in 방식)
                            //     WebLogger::warning('알림 설정 확인 실패, 기본 알림 전송', [
                            //         'error' => $e->getMessage(),
                            //         'post_id' => $postId
                            //     ]);
                            // }

                            // 알림 설정이 ON이거나 확인 실패 시 알림 전송
                            if ($shouldSendNotification) {
                                error_log("🔍 [LIKE_DEBUG] Getting FCM tokens...");
                                // 자기 자신의 게시글에는 알림 X
                                $tokens = $fcmTokenModel->getTokensByUserId($post['user_id']);
                                error_log("🔍 [LIKE_DEBUG] FCM tokens: " . count($tokens) . " found");

                                if (!empty($tokens)) {
                                    error_log("🔍 [LIKE_DEBUG] Sending FCM push notifications...");
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
                                    error_log("✅ [LIKE_DEBUG] FCM push notifications sent");

                                    WebLogger::info('좋아요 알림 전송 완료', [
                                        'post_id' => $postId,
                                        'recipient_id' => $post['user_id'],
                                        'token_count' => count($tokens)
                                    ]);
                                } else {
                                    error_log("ℹ️ [LIKE_DEBUG] No FCM tokens found for user");
                                }
                            }
                        } else {
                            error_log("ℹ️ [LIKE_DEBUG] Skipping notification (same user or post not found)");
                        }
                    } catch (Exception $e) {
                        error_log("❌ [LIKE_DEBUG] FCM notification error: " . $e->getMessage());
                        error_log("❌ [LIKE_DEBUG] Stack trace: " . $e->getTraceAsString());
                        WebLogger::error('좋아요 알림 전송 실패', [
                            'error' => $e->getMessage(),
                            'post_id' => $postId,
                            'trace' => $e->getTraceAsString()
                        ]);
                        // 알림 실패해도 좋아요 처리는 성공
                    }
                } else {
                    error_log("ℹ️ [LIKE_DEBUG] Skipping FCM (action was not 'liked')");
                }

                error_log("🔍 [LIKE_DEBUG] Preparing success response...");
                error_log("🔍 [LIKE_DEBUG] Response data: action=$action, like_count=$likeCount, is_liked=" . ($action === 'liked' ? 'true' : 'false'));
                ResponseHelper::success([
                    'action' => $action,
                    'like_count' => intval($likeCount),
                    'is_liked' => ($action === 'liked')
                ], $message);
                error_log("✅ [LIKE_DEBUG] Success response sent");
                
            } catch (Exception $e) {
                error_log("❌ [LIKE_DEBUG] Inner try block exception: " . $e->getMessage());
                error_log("❌ [LIKE_DEBUG] Stack trace: " . $e->getTraceAsString());
                error_log("🔍 [LIKE_DEBUG] Rolling back transaction...");
                $this->db->rollback();
                error_log("✅ [LIKE_DEBUG] Transaction rolled back");
                throw $e;
            }

            error_log("========== LikeController::togglePostLike END (SUCCESS) ==========");

        } catch (Exception $e) {
            error_log("========== LikeController::togglePostLike END (ERROR) ==========");
            error_log("❌ [LIKE_DEBUG] Outer try block exception: " . $e->getMessage());
            error_log("❌ [LIKE_DEBUG] Stack trace: " . $e->getTraceAsString());
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