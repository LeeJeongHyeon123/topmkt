<?php
/**
 * 댓글 관련 컨트롤러
 */

require_once SRC_PATH . '/models/Comment.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

class CommentController {
    private $commentModel;
    
    public function __construct() {
        $this->commentModel = new Comment();
    }
    
    /**
     * 댓글 작성 처리
     */
    public function store() {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            // CSRF 토큰 검증
            $csrfToken = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
            if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
                ResponseHelper::json(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
                return;
            }
            
            // JSON 입력 데이터 읽기
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                ResponseHelper::json(['success' => false, 'message' => '잘못된 요청 형식입니다.'], 400);
                return;
            }
            
            // 입력 데이터 검증
            $postId = intval($input['post_id'] ?? 0);
            $parentId = isset($input['parent_id']) ? intval($input['parent_id']) : null;
            $content = trim($input['content'] ?? '');
            
            // 내용 정규화 - 불필요한 줄바꿈 제거
            $content = preg_replace('/\r\n|\r/', "\n", $content); // 윈도우/맥 줄바꿈을 유닉스 스타일로
            $content = preg_replace('/\n+/', "\n", $content); // 연속된 줄바꿈을 하나로
            $content = trim($content); // 앞뒤 공백 제거
            
            if (!$postId || empty($content)) {
                ResponseHelper::json(['success' => false, 'message' => '필수 입력값이 누락되었습니다.'], 400);
                return;
            }
            
            // 댓글 저장
            $commentId = $this->commentModel->create([
                'post_id' => $postId,
                'user_id' => AuthMiddleware::getCurrentUserId(),
                'parent_id' => $parentId,
                'content' => $content
            ]);
            
            if ($commentId) {
                ResponseHelper::json([
                    'success' => true,
                    'message' => '댓글이 작성되었습니다.',
                    'comment_id' => $commentId
                ]);
            } else {
                ResponseHelper::json(['success' => false, 'message' => '댓글 작성에 실패했습니다.'], 500);
            }
            
        } catch (Exception $e) {
            error_log("댓글 작성 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '댓글 작성 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 댓글 수정 처리
     */
    public function update($commentId) {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            // CSRF 토큰 검증
            $csrfToken = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
            if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
                ResponseHelper::json(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
                return;
            }
            
            // JSON 입력 데이터 읽기
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                ResponseHelper::json(['success' => false, 'message' => '잘못된 요청 형식입니다.'], 400);
                return;
            }
            
            $content = trim($input['content'] ?? '');
            
            // 내용 정규화 - 불필요한 줄바꿈 제거
            $content = preg_replace('/\r\n|\r/', "\n", $content); // 윈도우/맥 줄바꿈을 유닉스 스타일로
            $content = preg_replace('/\n+/', "\n", $content); // 연속된 줄바꿈을 하나로
            $content = trim($content); // 앞뒤 공백 제거
            
            if (empty($content)) {
                ResponseHelper::json(['success' => false, 'message' => '댓글 내용을 입력해주세요.'], 400);
                return;
            }
            
            // 댓글 작성자 확인
            if (!$this->commentModel->isOwner($commentId, AuthMiddleware::getCurrentUserId())) {
                ResponseHelper::json(['success' => false, 'message' => '수정 권한이 없습니다.'], 403);
                return;
            }
            
            // 댓글 수정
            $success = $this->commentModel->update($commentId, [
                'content' => $content
            ]);
            
            if ($success) {
                ResponseHelper::json([
                    'success' => true,
                    'message' => '댓글이 수정되었습니다.'
                ]);
            } else {
                ResponseHelper::json(['success' => false, 'message' => '댓글 수정에 실패했습니다.'], 500);
            }
            
        } catch (Exception $e) {
            error_log("댓글 수정 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '댓글 수정 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 댓글 삭제 처리
     */
    public function delete($commentId) {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            // CSRF 토큰 검증
            $csrfToken = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
            if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
                ResponseHelper::json(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
                return;
            }
            
            // 댓글 작성자 확인
            if (!$this->commentModel->isOwner($commentId, AuthMiddleware::getCurrentUserId())) {
                ResponseHelper::json(['success' => false, 'message' => '삭제 권한이 없습니다.'], 403);
                return;
            }
            
            // 댓글 삭제 (soft delete)
            $success = $this->commentModel->delete($commentId);
            
            if ($success) {
                ResponseHelper::json([
                    'success' => true,
                    'message' => '댓글이 삭제되었습니다.'
                ]);
            } else {
                ResponseHelper::json(['success' => false, 'message' => '댓글 삭제에 실패했습니다.'], 500);
            }
            
        } catch (Exception $e) {
            error_log("댓글 삭제 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '댓글 삭제 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 댓글 목록 조회
     */
    public function list() {
        try {
            // 쿼리 파라미터에서 post_id 가져오기
            $postId = intval($_GET['post_id'] ?? 0);
            
            if (!$postId) {
                ResponseHelper::json(['success' => false, 'message' => 'post_id가 필요합니다.'], 400);
                return;
            }
            
            $comments = $this->commentModel->getByPostId($postId);
            
            ResponseHelper::json([
                'success' => true,
                'comments' => $comments,
                'count' => count($comments)
            ]);
            
        } catch (Exception $e) {
            error_log("댓글 목록 조회 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '댓글 목록 조회 중 오류가 발생했습니다.'], 500);
        }
    }
}