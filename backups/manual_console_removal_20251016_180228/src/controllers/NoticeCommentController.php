<?php

require_once SRC_PATH . '/controllers/BaseController.php';
/**
 * 공지사항 댓글 관련 컨트롤러
 * 기존 CommentController를 기반으로 notice_comments 테이블 사용
 */

require_once SRC_PATH . '/models/NoticeComment.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

class NoticeCommentController extends BaseController {
    private $commentModel;
    
    public function __construct() {
        parent::__construct(); // BaseController의 생성자 호출$this->commentModel = new NoticeComment();
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
            
            // JSON 입력 데이터 읽기
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                ResponseHelper::json(['success' => false, 'message' => '잘못된 요청 형식입니다.'], 400);
                return;
            }
            
            // CSRF 토큰 검증 (JSON 내부에서 확인)
            $csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
            if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
                ResponseHelper::json(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
                return;
            }
            
            // 입력 데이터 검증
            $noticeId = intval($input['notice_id'] ?? 0);
            $parentId = isset($input['parent_id']) ? intval($input['parent_id']) : null;
            $content = trim($input['content'] ?? '');
            
            // 내용 정규화 - 불필요한 줄바꿈 제거
            $content = preg_replace('/\r\n|\r/', "\n", $content); // 윈도우/맥 줄바꿈을 유닉스 스타일로
            $content = preg_replace('/\n+/', "\n", $content); // 연속된 줄바꿈을 하나로
            $content = trim($content); // 앞뒤 공백 제거
            
            if (!$noticeId || empty($content)) {
                ResponseHelper::json(['success' => false, 'message' => '필수 입력값이 누락되었습니다.'], 400);
                return;
            }
            
            // 댓글 저장
            $commentId = $this->commentModel->create([
                'notice_id' => $noticeId,
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
            error_log("공지사항 댓글 작성 중 오류: " . $e->getMessage());
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
            
            // JSON 입력 데이터 읽기
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                ResponseHelper::json(['success' => false, 'message' => '잘못된 요청 형식입니다.'], 400);
                return;
            }
            
            // CSRF 토큰 검증 (JSON 내부에서 확인)
            $csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
            if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
                ResponseHelper::json(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
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
            error_log("공지사항 댓글 수정 중 오류: " . $e->getMessage());
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
            
            // JSON 입력이 있을 수도 있으므로 확인
            $input = json_decode(file_get_contents('php://input'), true);
            
            // CSRF 토큰 검증 (JSON 내부, POST, REQUEST 순서로 확인)
            $csrfToken = ($input['csrf_token'] ?? null) ?: ($_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '');
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
            error_log("공지사항 댓글 삭제 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '댓글 삭제 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 댓글 목록 조회
     */
    public function list() {
        try {
            // 쿼리 파라미터에서 notice_id 가져오기
            $noticeId = intval($_GET['notice_id'] ?? 0);
            
            if (!$noticeId) {
                ResponseHelper::json(['success' => false, 'message' => 'notice_id가 필요합니다.'], 400);
                return;
            }
            
            $comments = $this->commentModel->getByNoticeId($noticeId);
            
            ResponseHelper::json([
                'success' => true,
                'comments' => $comments,
                'count' => count($comments)
            ]);
            
        } catch (Exception $e) {
            error_log("공지사항 댓글 목록 조회 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '댓글 목록 조회 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 댓글 통계 조회 (관리자용)
     */
    public function stats() {
        try {
            // 관리자 권한 확인
            if (!AuthMiddleware::isLoggedIn() || !AuthMiddleware::isAdmin()) {
                ResponseHelper::json(['success' => false, 'message' => '관리자 권한이 필요합니다.'], 403);
                return;
            }
            
            $noticeId = intval($_GET['notice_id'] ?? 0);
            
            if (!$noticeId) {
                ResponseHelper::json(['success' => false, 'message' => 'notice_id가 필요합니다.'], 400);
                return;
            }
            
            $stats = $this->commentModel->getCommentStats($noticeId);
            
            ResponseHelper::json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            error_log("공지사항 댓글 통계 조회 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '댓글 통계 조회 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 최근 댓글 목록 조회 (관리자용)
     */
    public function recent() {
        try {
            // 관리자 권한 확인
            if (!AuthMiddleware::isLoggedIn() || !AuthMiddleware::isAdmin()) {
                ResponseHelper::json(['success' => false, 'message' => '관리자 권한이 필요합니다.'], 403);
                return;
            }
            
            $limit = min(50, max(1, intval($_GET['limit'] ?? 10)));
            
            $comments = $this->commentModel->getRecentComments($limit);
            
            ResponseHelper::json([
                'success' => true,
                'comments' => $comments,
                'count' => count($comments)
            ]);
            
        } catch (Exception $e) {
            error_log("최근 공지사항 댓글 조회 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '최근 댓글 조회 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 댓글 검색 (관리자용)
     */
    public function search() {
        try {
            // 관리자 권한 확인
            if (!AuthMiddleware::isLoggedIn() || !AuthMiddleware::isAdmin()) {
                ResponseHelper::json(['success' => false, 'message' => '관리자 권한이 필요합니다.'], 403);
                return;
            }
            
            $keyword = trim($_GET['keyword'] ?? '');
            $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
            
            if (empty($keyword)) {
                ResponseHelper::json(['success' => false, 'message' => '검색 키워드가 필요합니다.'], 400);
                return;
            }
            
            $comments = $this->commentModel->searchComments($keyword, $limit);
            
            ResponseHelper::json([
                'success' => true,
                'comments' => $comments,
                'count' => count($comments),
                'keyword' => $keyword
            ]);
            
        } catch (Exception $e) {
            error_log("공지사항 댓글 검색 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '댓글 검색 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 사용자별 댓글 목록 조회
     */
    public function userComments() {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            $limit = min(50, max(1, intval($_GET['limit'] ?? 10)));
            
            $comments = $this->commentModel->getByUserId($userId, $limit);
            
            ResponseHelper::json([
                'success' => true,
                'comments' => $comments,
                'count' => count($comments)
            ]);
            
        } catch (Exception $e) {
            error_log("사용자 공지사항 댓글 조회 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '사용자 댓글 조회 중 오류가 발생했습니다.'], 500);
        }
    }
} 