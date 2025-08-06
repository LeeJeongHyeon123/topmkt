<?php
/**
 * 공지사항 컨트롤러
 * 기존 CommunityController 패턴을 준수하여 개발
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/Notice.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/helpers/ValidationHelper.php';
require_once SRC_PATH . '/helpers/SearchHelper.php';
require_once SRC_PATH . '/helpers/PerformanceDebugger.php';
require_once SRC_PATH . '/helpers/WebLogger.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

class NoticeController {
    private $db;
    private $noticeModel;
    private $userModel;
    
    public function __construct() {
        // 데이터베이스 연결 초기화
        $this->db = Database::getInstance();
        $this->noticeModel = new Notice();
        $this->userModel = new User();
    }
    
    /**
     * 공지사항 메인 페이지 (공지사항 목록)
     */
    public function index() {
        error_log('🏠 NoticeController::index() 호출');
        
        try {
            // 데이터베이스 연결 확인
            if (!$this->db) {
                throw new Exception('데이터베이스 연결 실패');
            }
            error_log('✅ 데이터베이스 연결 확인');
            
            // 테이블 존재 확인
            $noticesTable = $this->db->fetch("SHOW TABLES LIKE 'notices'");
            if (!$noticesTable) {
                throw new Exception('notices 테이블이 존재하지 않습니다');
            }
            
            // 쿼리 파라미터 가져오기
            $page = max(1, intval($_GET['page'] ?? 1));
            $search = trim($_GET['search'] ?? '');
            $filter = $_GET['filter'] ?? 'all'; // all, title, content, company
            $companyName = isset($_GET['company']) ? trim($_GET['company']) : null;
            $pageSize = 20; // 기본 페이지 크기
            
            error_log("📋 공지사항 목록 조회 - 페이지: $page, 검색: '$search', 필터: '$filter', 기업: " . ($companyName ?? 'all'));
            
            // 로그인 상태 확인
            $isLoggedIn = AuthMiddleware::isLoggedIn();
            $currentUserId = $isLoggedIn ? AuthMiddleware::getCurrentUserId() : null;
            $isCorpUser = false;
            $userCompanyId = null;
            
            if ($isLoggedIn) {
                $isCorpUser = $this->noticeModel->isCompanyUser($currentUserId);
                $userCompanyId = $this->noticeModel->getUserCompanyId($currentUserId);
            }
            
            // 공지사항 목록 조회
            $notices = $this->noticeModel->getList($page, $pageSize, $search, $filter, $companyName);
            $totalCount = $this->noticeModel->getTotalCount($search, $filter, $companyName);
            $totalPages = ceil($totalCount / $pageSize);
            
            // 기업 목록은 더 이상 제공하지 않음 (보안상 이유)
            
            // 추천 공지사항 조회 (첫 번째 페이지에서만)
            $featuredNotices = [];
            if ($page === 1 && empty($search) && !$companyName) {
                $featuredNotices = $this->noticeModel->getFeaturedNotices(3);
            }
            
            error_log("📊 공지사항 목록 조회 결과 - 총 {$totalCount}개, {$totalPages}페이지, 현재 페이지 결과: " . count($notices) . "개");
            
            // 뷰 변수 설정
            $data = [
                'notices' => $notices,
                'featuredNotices' => $featuredNotices,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'totalCount' => $totalCount,
                    'pageSize' => $pageSize
                ],
                'search' => [
                    'query' => $search,
                    'filter' => $filter,
                    'companyName' => $companyName
                ],
                'user' => [
                    'isLoggedIn' => $isLoggedIn,
                    'currentUserId' => $currentUserId,
                    'isCorpUser' => $isCorpUser,
                    'userCompanyId' => $userCompanyId
                ]
            ];
            
            // 뷰에서 사용할 변수들 추출
            extract($data);
            extract($data['pagination']);
            extract($data['search']);
            extract($data['user']);
            
            // 뷰 변수 직접 할당
            $currentPage = $data['pagination']['currentPage'];
            $totalPages = $data['pagination']['totalPages'];
            $totalCount = $data['pagination']['totalCount'];
            $pageSize = $data['pagination']['pageSize'];
            $search = $data['search']['query'];
            $filter = $data['search']['filter'];
            $company = $data['search']['companyName'];
            $canWrite = $isCorpUser;
            $hasNextPage = $currentPage < $totalPages;
            $hasPrevPage = $currentPage > 1;
            
            // 헤더와 뷰 렌더링
            $page_title = '공지사항';
            require_once SRC_PATH . '/views/templates/header.php';
            require_once SRC_PATH . '/views/notices/index.php';
            require_once SRC_PATH . '/views/templates/footer.php';
            
        } catch (Exception $e) {
            error_log('❌ NoticeController::index() 오류: ' . $e->getMessage());
            error_log('📍 스택 추적: ' . $e->getTraceAsString());
            
            // 오류 페이지 표시
            $page_title = '오류 발생';
            $error_message = '공지사항 목록을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage();
            require_once SRC_PATH . '/views/templates/header.php';
            require_once SRC_PATH . '/views/templates/error.php';
            require_once SRC_PATH . '/views/templates/footer.php';
        }
    }
    
    /**
     * 공지사항 상세보기
     */
    public function show($id) {
        error_log("📄 NoticeController::show({$id}) 호출");
        
        try {
            // ID 유효성 검사
            $noticeId = intval($id);
            if ($noticeId <= 0) {
                throw new Exception('잘못된 공지사항 ID입니다.');
            }
            
            // 공지사항 조회
            $notice = $this->noticeModel->getById($noticeId);
            if (!$notice) {
                error_log("❌ 공지사항을 찾을 수 없음: ID $noticeId");
                header('HTTP/1.1 404 Not Found');
                require_once SRC_PATH . '/views/templates/404.php';
                return;
            }
            
            // 로그인 상태 확인
            $isLoggedIn = AuthMiddleware::isLoggedIn();
            $currentUserId = $isLoggedIn ? AuthMiddleware::getCurrentUserId() : null;
            $isOwner = $isLoggedIn && $notice['user_id'] == $currentUserId;
            
            // 조회수 증가 (소유자가 아닌 경우에만)
            if (!$isOwner) {
                $this->noticeModel->incrementViewCount($noticeId);
            }
            
            // SEO 메타 태그를 위한 Open Graph 정보
            $ogTitle = htmlspecialchars($notice['title']) . ' - ' . htmlspecialchars($notice['company_name']);
            $ogDescription = mb_substr(strip_tags($notice['content']), 0, 150) . '...';
            $ogUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/notices/' . $noticeId;
            $ogImage = $notice['image_path'] ? 'https://' . $_SERVER['HTTP_HOST'] . $notice['image_path'] : 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/og-default.png';
            
            error_log("✅ 공지사항 상세 조회 완료: {$notice['title']} (작성자: {$notice['company_name']})");
            
            // 뷰 변수 설정
            $data = [
                'notice' => $notice,
                'user' => [
                    'isLoggedIn' => $isLoggedIn,
                    'currentUserId' => $currentUserId,
                    'isOwner' => $isOwner
                ],
                'og' => [
                    'title' => $ogTitle,
                    'description' => $ogDescription,
                    'url' => $ogUrl,
                    'image' => $ogImage
                ]
            ];
            
            // 헤더와 뷰 렌더링
            $page_title = $notice['title'] . ' - 공지사항';
            require_once SRC_PATH . '/views/templates/header.php';
            require_once SRC_PATH . '/views/notices/show.php';
            require_once SRC_PATH . '/views/templates/footer.php';
            
        } catch (Exception $e) {
            error_log('❌ NoticeController::show() 오류: ' . $e->getMessage());
            
            $page_title = '오류 발생';
            $error_message = '공지사항을 불러오는 중 오류가 발생했습니다: ' . $e->getMessage();
            require_once SRC_PATH . '/views/templates/header.php';
            require_once SRC_PATH . '/views/templates/error.php';
            require_once SRC_PATH . '/views/templates/footer.php';
        }
    }
    
    /**
     * 공지사항 작성 폼 표시
     */
    public function showWrite() {
        error_log('✏️ NoticeController::showWrite() 호출');
        
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                header('Location: /auth/login?redirect=' . urlencode('/notices/write'));
                exit;
            }
            
            $currentUserId = AuthMiddleware::getCurrentUserId();
            
            // 기업 사용자 권한 확인
            if (!$this->noticeModel->isCompanyUser($currentUserId)) {
                error_log("❌ 권한 없음: 사용자 $currentUserId는 기업 사용자가 아님");
                header('HTTP/1.1 403 Forbidden');
                $page_title = '접근 권한 없음';
                $error_message = '공지사항 작성 권한이 없습니다. 승인된 기업 회원만 공지사항을 작성할 수 있습니다.';
                require_once SRC_PATH . '/views/templates/header.php';
                require_once SRC_PATH . '/views/templates/error.php';
                require_once SRC_PATH . '/views/templates/footer.php';
                return;
            }
            
            // 사용자의 기업 정보 조회
            $userCompanyId = $this->noticeModel->getUserCompanyId($currentUserId);
            if (!$userCompanyId) {
                throw new Exception('기업 정보를 찾을 수 없습니다.');
            }
            
            error_log("✅ 공지사항 작성 권한 확인 완료: 사용자 $currentUserId, 기업 ID $userCompanyId");
            
            // 뷰 변수 설정
            $data = [
                'user' => [
                    'currentUserId' => $currentUserId,
                    'userCompanyId' => $userCompanyId
                ]
            ];
            
            // 헤더와 뷰 렌더링
            $page_title = '공지사항 작성';
            require_once SRC_PATH . '/views/templates/header.php';
            require_once SRC_PATH . '/views/notices/write.php';
            require_once SRC_PATH . '/views/templates/footer.php';
            
        } catch (Exception $e) {
            error_log('❌ NoticeController::showWrite() 오류: ' . $e->getMessage());
            
            $page_title = '오류 발생';
            $error_message = '공지사항 작성 페이지를 불러오는 중 오류가 발생했습니다: ' . $e->getMessage();
            require_once SRC_PATH . '/views/templates/header.php';
            require_once SRC_PATH . '/views/templates/error.php';
            require_once SRC_PATH . '/views/templates/footer.php';
        }
    }
    
    /**
     * 공지사항 수정 폼 표시
     */
    public function showEdit($id) {
        error_log("✏️ NoticeController::showEdit({$id}) 호출");
        
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                header('Location: /auth/login?redirect=' . urlencode('/notices/' . $id . '/edit'));
                exit;
            }
            
            $currentUserId = AuthMiddleware::getCurrentUserId();
            $noticeId = intval($id);
            
            // 공지사항 조회
            $notice = $this->noticeModel->getById($noticeId);
            if (!$notice) {
                header('HTTP/1.1 404 Not Found');
                require_once SRC_PATH . '/views/templates/404.php';
                return;
            }
            
            // 소유자 권한 확인
            if (!$this->noticeModel->isOwner($noticeId, $currentUserId)) {
                error_log("❌ 권한 없음: 사용자 $currentUserId는 공지사항 $noticeId의 소유자가 아님");
                header('HTTP/1.1 403 Forbidden');
                $page_title = '접근 권한 없음';
                $error_message = '이 공지사항을 수정할 권한이 없습니다.';
                require_once SRC_PATH . '/views/templates/header.php';
                require_once SRC_PATH . '/views/templates/error.php';
                require_once SRC_PATH . '/views/templates/footer.php';
                return;
            }
            
            error_log("✅ 공지사항 수정 권한 확인 완료: {$notice['title']}");
            
            // 뷰 변수 설정
            $data = [
                'notice' => $notice,
                'user' => [
                    'currentUserId' => $currentUserId
                ]
            ];
            
            // 헤더와 뷰 렌더링
            $page_title = $notice['title'] . ' - 공지사항 수정';
            require_once SRC_PATH . '/views/templates/header.php';
            require_once SRC_PATH . '/views/notices/edit.php';
            require_once SRC_PATH . '/views/templates/footer.php';
            
        } catch (Exception $e) {
            error_log('❌ NoticeController::showEdit() 오류: ' . $e->getMessage());
            
            $page_title = '오류 발생';
            $error_message = '공지사항 수정 페이지를 불러오는 중 오류가 발생했습니다: ' . $e->getMessage();
            require_once SRC_PATH . '/views/templates/header.php';
            require_once SRC_PATH . '/views/templates/error.php';
            require_once SRC_PATH . '/views/templates/footer.php';
        }
    }
    
    /**
     * 공지사항 생성 처리 (API)
     */
    public function create() {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            $currentUserId = AuthMiddleware::getCurrentUserId();
            
            // 기업 사용자 권한 확인
            if (!$this->noticeModel->isCompanyUser($currentUserId)) {
                ResponseHelper::json(['success' => false, 'message' => '공지사항 작성 권한이 없습니다.'], 403);
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
            $title = trim($input['title'] ?? '');
            $content = trim($input['content'] ?? '');
            $imagePath = $input['image_path'] ?? null;
            $isFeatured = isset($input['is_featured']) ? (bool)$input['is_featured'] : false;
            
            if (empty($title) || empty($content)) {
                ResponseHelper::json(['success' => false, 'message' => '제목과 내용을 모두 입력해주세요.'], 400);
                return;
            }
            
            // 제목 길이 검증
            if (mb_strlen($title) > 200) {
                ResponseHelper::json(['success' => false, 'message' => '제목은 200자를 초과할 수 없습니다.'], 400);
                return;
            }
            
            // 내용 정리 (HTML 정리)
            $content = HtmlSanitizerHelper::sanitizeRichText($content);
            
            // 사용자의 기업 ID 조회
            $userCompanyId = $this->noticeModel->getUserCompanyId($currentUserId);
            if (!$userCompanyId) {
                ResponseHelper::json(['success' => false, 'message' => '기업 정보를 찾을 수 없습니다.'], 400);
                return;
            }
            
            // 공지사항 생성
            $noticeData = [
                'user_id' => $currentUserId,
                'company_id' => $userCompanyId,
                'title' => $title,
                'content' => $content,
                'image_path' => $imagePath,
                'is_featured' => $isFeatured
            ];
            
            $noticeId = $this->noticeModel->create($noticeData);
            
            if ($noticeId) {
                WebLogger::info("✅ 공지사항 생성 성공: ID $noticeId, 제목: '$title'");
                ResponseHelper::json([
                    'success' => true,
                    'message' => '공지사항이 성공적으로 작성되었습니다.',
                    'notice_id' => $noticeId,
                    'redirect' => '/notices/' . $noticeId
                ]);
            } else {
                throw new Exception('공지사항 생성에 실패했습니다.');
            }
            
        } catch (Exception $e) {
            error_log("❌ 공지사항 생성 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '공지사항 작성 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 공지사항 수정 처리 (API)
     */
    public function update($id) {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            $currentUserId = AuthMiddleware::getCurrentUserId();
            $noticeId = intval($id);
            
            // 소유자 권한 확인
            if (!$this->noticeModel->isOwner($noticeId, $currentUserId)) {
                ResponseHelper::json(['success' => false, 'message' => '수정 권한이 없습니다.'], 403);
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
            $title = trim($input['title'] ?? '');
            $content = trim($input['content'] ?? '');
            $imagePath = $input['image_path'] ?? null;
            $isFeatured = isset($input['is_featured']) ? (bool)$input['is_featured'] : false;
            
            if (empty($title) || empty($content)) {
                ResponseHelper::json(['success' => false, 'message' => '제목과 내용을 모두 입력해주세요.'], 400);
                return;
            }
            
            // 제목 길이 검증
            if (mb_strlen($title) > 200) {
                ResponseHelper::json(['success' => false, 'message' => '제목은 200자를 초과할 수 없습니다.'], 400);
                return;
            }
            
            // 내용 정리 (HTML 정리)
            $content = HtmlSanitizerHelper::sanitizeRichText($content);
            
            // 공지사항 수정
            $noticeData = [
                'title' => $title,
                'content' => $content,
                'image_path' => $imagePath,
                'is_featured' => $isFeatured
            ];
            
            $success = $this->noticeModel->update($noticeId, $noticeData);
            
            if ($success) {
                WebLogger::info("✅ 공지사항 수정 성공: ID $noticeId, 제목: '$title'");
                ResponseHelper::json([
                    'success' => true,
                    'message' => '공지사항이 성공적으로 수정되었습니다.',
                    'redirect' => '/notices/' . $noticeId
                ]);
            } else {
                throw new Exception('공지사항 수정에 실패했습니다.');
            }
            
        } catch (Exception $e) {
            error_log("❌ 공지사항 수정 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '공지사항 수정 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 공지사항 삭제 처리 (API)
     */
    public function delete($id) {
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            $currentUserId = AuthMiddleware::getCurrentUserId();
            $noticeId = intval($id);
            
            // 소유자 권한 확인
            if (!$this->noticeModel->isOwner($noticeId, $currentUserId)) {
                ResponseHelper::json(['success' => false, 'message' => '삭제 권한이 없습니다.'], 403);
                return;
            }
            
            // CSRF 토큰 검증 (JSON 요청에서)
            $input = json_decode(file_get_contents('php://input'), true);
            $csrfToken = $input['csrf_token'] ?? '';
            if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
                ResponseHelper::json(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
                return;
            }
            
            // 공지사항 정보 조회 (로깅용)
            $notice = $this->noticeModel->getById($noticeId);
            $noticeTitle = $notice ? $notice['title'] : "ID $noticeId";
            
            // 공지사항 삭제
            $success = $this->noticeModel->delete($noticeId);
            
            if ($success) {
                WebLogger::info("✅ 공지사항 삭제 성공: $noticeTitle");
                ResponseHelper::json([
                    'success' => true,
                    'message' => '공지사항이 성공적으로 삭제되었습니다.',
                    'data' => [
                        'redirectUrl' => '/notices'
                    ]
                ]);
            } else {
                throw new Exception('공지사항 삭제에 실패했습니다.');
            }
            
        } catch (Exception $e) {
            error_log("❌ 공지사항 삭제 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '공지사항 삭제 중 오류가 발생했습니다.'], 500);
        }
    }
} 