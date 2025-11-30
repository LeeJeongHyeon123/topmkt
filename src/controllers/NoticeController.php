<?php
/**
 * 공지사항 컨트롤러
 * 기존 CommunityController 패턴을 준수하여 개발
 */

require_once SRC_PATH . '/controllers/BaseController.php';
require_once SRC_PATH . '/models/Notice.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/helpers/ValidationHelper.php';
require_once SRC_PATH . '/helpers/SearchHelper.php';
require_once SRC_PATH . '/helpers/PerformanceDebugger.php';
require_once SRC_PATH . '/helpers/WebLogger.php';
require_once SRC_PATH . '/helpers/HtmlSanitizerHelper.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/FcmHelper.php';
require_once SRC_PATH . '/models/FcmToken.php';

class NoticeController extends BaseController {
    private $noticeModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct(); // BaseController의 생성자 호출
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
            $filter = $_GET['filter'] ?? 'all'; // all, title, content
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
            
            // 댓글 데이터 조회
            require_once SRC_PATH . '/models/NoticeComment.php';
            $commentModel = new NoticeComment();
            $comments = $commentModel->getAllByNoticeId($noticeId);
            
            // 🚀 Ultra Think v3.10.0: Notice 모델의 getNoticeImages 결과 사용
            // Notice 모델에서 이미 포괄적인 이미지 검색을 완료했으므로 별도 처리 불필요
            // images 배열은 Notice::getById()에서 이미 설정됨
            
            // 🚀 Ultra Think v3.13.0: 본문 내용의 빈 img 태그 처리 개선
            $content = $notice['content'];
            
            if (!empty($notice['images'])) {
                // 이미지가 있는 경우: 빈 img 태그들을 실제 이미지로 교체
                $imageIndex = 0;
                $content = preg_replace_callback('/<img[^>]*>/i', function($matches) use ($notice, &$imageIndex) {
                    if ($imageIndex < count($notice['images'])) {
                        $image = $notice['images'][$imageIndex];
                        $imageIndex++;
                        return '<img src="' . htmlspecialchars($image['file_path']) . '" alt="' . htmlspecialchars($image['filename']) . '" class="content-image" loading="lazy">';
                    }
                    return $matches[0]; // 원본 유지
                }, $content);
            } else {
                // 이미지가 없는 경우: 빈 img 태그를 적절한 메시지로 교체
                $content = preg_replace_callback('/<img[^>]*>/i', function($matches) {
                    // src 속성이 있는 이미지는 그대로 유지
                    if (preg_match('/src\s*=\s*["\'][^"\']+["\']/', $matches[0])) {
                        return $matches[0];
                    }
                    
                    // 빈 img 태그는 업로드 실패 안내로 교체
                    return '<div class="missing-image-notice" style="padding: 15px; margin: 10px 0; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; text-align: center; color: #92400e;">' .
                           '<i data-lucide="alert-triangle" style="display:inline-block; width:16px; height:16px; margin-right:8px; vertical-align:middle;"></i>' .
                           '이미지 업로드 중 오류가 발생했습니다. 다시 시도해 주세요.' .
                           '</div>';
                }, $content);
            }
            
            $notice['content'] = $content;
            
            error_log("✅ 공지사항 상세 조회 완료: {$notice['title']} (작성자: {$notice['company_name']}) - 댓글 " . count($comments) . "개, 이미지 " . count($notice['images']) . "개");
            
            // 편집 권한 확인
            $canEdit = $isLoggedIn && $this->noticeModel->isOwner($noticeId, $currentUserId);
            
            // 회사 이름 추출 (뷰에서 필요)
            $companyName = $notice['company_name'] ?? '알 수 없음';
            
            // 뷰 변수 설정
            $data = [
                'notice' => $notice,
                'comments' => $comments,
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
            require_once SRC_PATH . '/views/notices/detail.php';
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
                ],
                'canWrite' => true
            ];
            
            // 🚀 Ultra Think: write.php에서 필요한 변수들 설정
            $action = 'write'; // 작성 모드
            $notice = null; // 새 공지사항이므로 기존 데이터 없음
            
            // 뷰 변수 추출
            extract($data);
            extract($data['user']);
            
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
            
            // 🚀 Ultra Think: 본문 내용의 빈 img 태그들을 실제 이미지로 교체 (편집용)
            if (!empty($notice['images'])) {
                $content = $notice['content'];
                
                // 빈 img 태그들을 찾아서 실제 이미지로 교체
                $imageIndex = 0;
                $content = preg_replace_callback('/<img[^>]*>/i', function($matches) use ($notice, &$imageIndex) {
                    if ($imageIndex < count($notice['images'])) {
                        $image = $notice['images'][$imageIndex];
                        $imageIndex++;
                        return '<img src="' . htmlspecialchars($image['file_path']) . '" alt="' . htmlspecialchars($image['filename']) . '" class="content-image" loading="lazy">';
                    }
                    return $matches[0]; // 원본 유지
                }, $content);
                
                $notice['content'] = $content;
                error_log("📝 공지사항 수정 페이지: 본문 이미지 교체 완료 (" . count($notice['images']) . "개)");
            }
            
            // 뷰에서 사용할 변수 직접 설정
            $user = [
                'currentUserId' => $currentUserId
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
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }
            
            $currentUserId = AuthMiddleware::getCurrentUserId();
            
            // 기업 사용자 권한 확인
            if (!$this->noticeModel->isCompanyUser($currentUserId)) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '공지사항 작성 권한이 없습니다.'], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }
            
            // CSRF 토큰 검증
            $csrfToken = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
            if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }
            
            // 입력 데이터 읽기 (JSON 또는 FormData 지원)
            $input = null;
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                // JSON 형식
                $input = json_decode(file_get_contents('php://input'), true);
            } else {
                // FormData 형식 (multipart/form-data)
                $input = $_POST;
            }
            
            if (!$input) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '잘못된 요청 형식입니다.'], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }
            
            // 입력 데이터 검증
            $title = trim($input['title'] ?? '');
            $content = trim($input['content'] ?? '');
            $imagePaths = $input['image_paths'] ?? [];
            $isFeatured = false;
            
            // 이미지 경로 배열을 처리 (현재는 첫 번째 이미지만 사용, 추후 다중 이미지 지원 가능)
            $imagePath = null;
            if (!empty($imagePaths) && is_array($imagePaths)) {
                $imagePath = $imagePaths[0] ?? null;
            }
            
            if (empty($title) || empty($content)) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '제목과 내용을 모두 입력해주세요.'], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }
            
            // 제목 길이 검증
            if (mb_strlen($title) > 200) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '제목은 200자를 초과할 수 없습니다.'], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }
            
            // 내용 정리 (HTML 정리)
            $content = HtmlSanitizerHelper::sanitizeRichText($content);
            
            // 사용자의 기업 ID 조회
            $userCompanyId = $this->noticeModel->getUserCompanyId($currentUserId);
            if (!$userCompanyId) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '기업 정보를 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
                exit;
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

                // 🔔 FCM 푸시 알림 전송 (모든 회원에게)
                try {
                    $truncatedTitle = mb_strlen($title) > 20 ? mb_substr($title, 0, 20) . '...' : $title;

                    // FcmHelper::sendByNotificationType() 사용하여 알림 설정 확인하고 전송
                    $result = FcmHelper::sendByNotificationType(
                        'notices',
                        '새 공지사항 알림',
                        '새로운 공지사항 "' . $truncatedTitle . '"가 등록되었습니다.',
                        [
                            'type' => 'notice',
                            'notice_id' => $noticeId
                        ]
                    );

                    WebLogger::info('신규 공지사항 알림 전송 완료', [
                        'notice_id' => $noticeId,
                        'title' => $title,
                        'sent_count' => $result['sent_count'] ?? 0,
                        'total_users' => $result['total_users'] ?? 0
                    ]);
                } catch (Exception $e) {
                    WebLogger::error('신규 공지사항 알림 전송 실패', [
                        'error' => $e->getMessage(),
                        'notice_id' => $noticeId
                    ]);
                    // 알림 실패해도 공지사항 등록은 성공 처리
                }

                http_response_code(200);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => '공지사항이 성공적으로 작성되었습니다.',
                    'data' => [
                        'id' => $noticeId,
                        'redirect' => '/notices/' . $noticeId
                    ]
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else{
                throw new Exception('공지사항 생성에 실패했습니다.');
            }
            
        } catch (Exception $e) {
            error_log("❌ 공지사항 생성 중 오류: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => '공지사항 작성 중 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    /**
     * 공지사항 수정 처리 (API)
     */
    public function update($id) {
        error_log("✏️ NoticeController::update({$id}) 호출");
        
        try {
            file_put_contents('/tmp/notice_debug.log', "🚀 NoticeController::update($id) 시작\n", FILE_APPEND);
            
            // 로그인 확인
            $isLoggedIn = AuthMiddleware::isLoggedIn();
            file_put_contents('/tmp/notice_debug.log', "로그인 상태: " . ($isLoggedIn ? 'OK' : 'NO') . "\n", FILE_APPEND);
            
            if (!$isLoggedIn) {
                file_put_contents('/tmp/notice_debug.log', "❌ 로그인 안됨으로 종료\n", FILE_APPEND);
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $currentUserId = AuthMiddleware::getCurrentUserId();
            $noticeId = intval($id);
            file_put_contents('/tmp/notice_debug.log', "사용자 ID: $currentUserId, 공지사항 ID: $noticeId\n", FILE_APPEND);
            
            // 소유자 권한 확인
            $isOwner = $this->noticeModel->isOwner($noticeId, $currentUserId);
            file_put_contents('/tmp/notice_debug.log', "권한 확인: 공지사항 ID $noticeId, 사용자 ID $currentUserId, 권한: " . ($isOwner ? 'OK' : 'NO') . "\n", FILE_APPEND);

            if (!$isOwner) {
                file_put_contents('/tmp/notice_debug.log', "❌ 권한 없음으로 종료\n", FILE_APPEND);
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '수정 권한이 없습니다.'], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }

            // ✅ [SECURITY-FIX] 2025-10-20: CSRF 토큰 검증 추가
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
                file_put_contents('/tmp/notice_debug.log', "❌ CSRF 토큰 검증 실패\n", FILE_APPEND);
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }
            
            // 메서드 오버라이드 지원 (_method 필드 확인)
            $actualMethod = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
            file_put_contents('/tmp/notice_debug.log', "실제 메서드: $actualMethod (원본: {$_SERVER['REQUEST_METHOD']})\n", FILE_APPEND);
            
            // FormData와 JSON 모두 지원
            $isFormData = !empty($_POST) || !empty($_FILES);
            
            if ($isFormData) {
                // FormData 처리 (편집 페이지에서 파일 업로드 포함)
                $title = trim($_POST['title'] ?? '');
                $content = trim($_POST['content'] ?? '');
                $removedImages = json_decode($_POST['removed_images'] ?? '[]', true);
                $isFeatured = false;
                
                error_log("📝 FormData 수정 요청 상세:");
                error_log("   - 제목: '$title' (길이: " . mb_strlen($title) . ")");
                error_log("   - 내용: '" . substr($content, 0, 100) . "...' (길이: " . mb_strlen($content) . ")");
                error_log("   - 상태: 일반 공지");
                error_log("   - 제거된 이미지: " . count($removedImages) . "개");
                
            } else {
                // JSON 처리 (기존 API 호환)
                $input = json_decode(file_get_contents('php://input'), true);
                if (!$input) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => '잘못된 요청 형식입니다.'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                
                $title = trim($input['title'] ?? '');
                $content = trim($input['content'] ?? '');
                $imagePaths = $input['image_paths'] ?? [];
                $removedImages = [];
                $isFeatured = false;
                
                error_log("📝 JSON 수정 요청: 제목='$title'");
            }
            
            // HTML 태그 제거한 순수 텍스트 내용으로 검증
            $contentText = strip_tags($content);
            $contentText = trim(preg_replace('/\s+/', ' ', $contentText)); // 공백 정리
            
            // 디버깅용 로그 파일에 직접 작성
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'title' => $title,
                'title_length' => mb_strlen($title),
                'content_original' => substr($content, 0, 200),
                'content_text' => substr($contentText, 0, 100),
                'content_text_length' => mb_strlen($contentText),
                'is_featured' => 'false'
            ];
            file_put_contents('/tmp/notice_debug.log', json_encode($logData, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
            
            if (empty($title) || empty($contentText)) {
                $errorMsg = empty($title) ? '제목을 입력해주세요.' : '내용을 입력해주세요.';
                file_put_contents('/tmp/notice_debug.log', "❌ 검증 실패: $errorMsg\n", FILE_APPEND);
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $errorMsg], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }
            
            // 제목 길이 검증
            if (mb_strlen($title) > 200) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '제목은 200자를 초과할 수 없습니다.'], JSON_UNESCAPED_UNICODE);
                exit;
                return;
            }
            
            // 내용 정리 (HTML 정리)
            $content = HtmlSanitizerHelper::sanitizeRichText($content);
            
            // 이미지 처리
            $imagePath = null;
            $uploadedImages = [];
            
            if ($isFormData) {
                // 기존 공지사항 조회하여 현재 이미지 확인
                $currentNotice = $this->noticeModel->getById($noticeId);
                $imagePath = $currentNotice['image_path'] ?? null;
                
                // 새 이미지 파일 업로드 처리 (공통 타임스탬프 사용)
                if (!empty($_FILES['new_images']['name'][0])) {
                    error_log("📤 새 이미지 업로드 시작: " . count($_FILES['new_images']['name']) . "개");
                    
                    // 모든 새 이미지에 사용할 공통 타임스탬프 생성
                    $commonTimestamp = date('YmdHis');
                    error_log("🕒 새 이미지용 공통 타임스탬프: {$commonTimestamp}");
                    
                    for ($i = 0; $i < count($_FILES['new_images']['name']); $i++) {
                        if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                            $uploadedFile = $this->handleImageUpload([
                                'name' => $_FILES['new_images']['name'][$i],
                                'type' => $_FILES['new_images']['type'][$i],
                                'tmp_name' => $_FILES['new_images']['tmp_name'][$i],
                                'error' => $_FILES['new_images']['error'][$i],
                                'size' => $_FILES['new_images']['size'][$i]
                            ], $commonTimestamp, $currentNotice);
                            
                            if ($uploadedFile) {
                                $uploadedImages[] = $uploadedFile;
                                // 첫 번째 업로드된 이미지를 메인 이미지로 설정
                                if (!$imagePath) {
                                    $imagePath = $uploadedFile;
                                }
                                error_log("✅ 새 이미지 업로드 완료: {$uploadedFile}");
                            }
                        }
                    }
                    
                    error_log("📋 전체 업로드 완료: " . count($uploadedImages) . "개 이미지, 메인 이미지: " . ($imagePath ?: '없음'));
                }
                
                // 기존 이미지 제거 처리 (실제 파일 및 DB 삭제)
                if (!empty($removedImages)) {
                    error_log("🗑️ 기존 이미지 제거 시작: " . implode(', ', $removedImages));
                    $this->noticeModel->removeImages($noticeId, $removedImages);
                }
                
            } else {
                // JSON 처리 (기존 방식)
                if (!empty($imagePaths) && is_array($imagePaths)) {
                    $imagePath = $imagePaths[0] ?? null;
                }
            }
            
            // 공지사항 수정
            $noticeData = [
                'title' => $title,
                'content' => $content,
                'image_path' => $imagePath,
                'is_featured' => $isFeatured
            ];
            
            $success = $this->noticeModel->update($noticeId, $noticeData);
            
            if ($success) {
                error_log("✅ 공지사항 수정 성공: ID $noticeId, 제목: '$title', 이미지: " . ($imagePath ?: '없음'));
                
                // JavaScript와 호환되는 직접 JSON 응답
                http_response_code(200);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => '공지사항이 성공적으로 수정되었습니다.',
                    'notice_id' => $noticeId,
                    'redirect' => '/notices/' . $noticeId
                ], JSON_UNESCAPED_UNICODE);
                exit;
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
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
                exit;
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
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], JSON_UNESCAPED_UNICODE);
                exit;
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
    
    /**
     * 공지사항 조회수 증가 (AJAX API)
     */
    public function incrementView($id) {
        try {
            $noticeId = intval($id);
            
            if (!$noticeId) {
                ResponseHelper::json(['success' => false, 'message' => '잘못된 공지사항 ID입니다.'], 400);
                return;
            }
            
            // 공지사항 존재 확인
            $notice = $this->noticeModel->getById($noticeId);
            if (!$notice) {
                ResponseHelper::json(['success' => false, 'message' => '공지사항을 찾을 수 없습니다.'], 404);
                return;
            }
            
            // 조회수 증가
            $success = $this->noticeModel->incrementViewCount($noticeId);
            
            if ($success) {
                ResponseHelper::json([
                    'success' => true,
                    'message' => '조회수가 증가되었습니다.',
                    'data' => [
                        'view_count' => $notice['view_count'] + 1
                    ]
                ]);
            } else {
                ResponseHelper::json(['success' => false, 'message' => '조회수 증가에 실패했습니다.'], 500);
            }
            
        } catch (Exception $e) {
            error_log("❌ 공지사항 조회수 증가 중 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '조회수 처리 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 단일 이미지 파일 업로드 처리 (기존 이미지와 동일한 디렉토리 구조 사용)
     */
    private function handleImageUpload($file, $baseTimestamp = null, $existingNotice = null) {
        require_once SRC_PATH . '/config/upload.php';
        
        try {
            // 파일 검증
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                error_log("❌ 파일 업로드 오류: " . ($file['error'] ?? 'UNKNOWN'));
                return null;
            }
            
            // 파일 크기 검증
            $validation = UploadConfig::validateImageFile($file);
            if (!$validation['success']) {
                error_log("❌ 파일 검증 실패: " . $validation['message']);
                return null;
            }
            
            // 기존 공지사항의 이미지 경로에서 디렉토리 구조 추출
            $yearMonthPath = '';
            if ($existingNotice && !empty($existingNotice['image_path'])) {
                // 기존 이미지 경로에서 연도/월 추출 (예: /assets/uploads/notices/2025/08/filename.jpg)
                $pathInfo = pathinfo($existingNotice['image_path']);
                $dirPath = $pathInfo['dirname'];
                if (preg_match('/notices\/(\d{4}\/\d{2})$/', $dirPath, $matches)) {
                    $yearMonthPath = $matches[1] . '/';
                }
            }
            
            // 연도/월 경로가 없으면 현재 날짜로 생성
            if (empty($yearMonthPath)) {
                $yearMonthPath = date('Y/m') . '/';
            }
            
            // 업로드 디렉토리 설정 (연도/월 서브디렉토리 포함)
            $uploadDir = '/var/www/html/topmkt/public/assets/uploads/notices/' . $yearMonthPath;
            $webPath = '/assets/uploads/notices/' . $yearMonthPath;
            
            // 업로드 디렉토리 생성
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("❌ 업로드 디렉토리 생성 실패: $uploadDir");
                    return null;
                }
            }
            
            // 파일명 생성 (공통 타임스탬프 사용 + 랜덤해시)
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $timestamp = $baseTimestamp ?: date('YmdHis');
            $randomHash = bin2hex(random_bytes(8));
            $filename = $timestamp . '_' . $randomHash . '.' . $extension;
            
            $uploadPath = $uploadDir . $filename;
            $webFilePath = $webPath . $filename;
            
            // 파일 이동
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // 파일 권한 설정
                chmod($uploadPath, 0644);

                // 🚀 Phase 2: WebP 변환 (EventController와 동일한 로직)
                $webpFilename = $timestamp . '_' . $randomHash . '.webp';
                $webpUploadPath = $uploadDir . $webpFilename;
                $webpWebFilePath = $webPath . $webpFilename;

                try {
                    // GD 라이브러리로 이미지 로드
                    $sourceImage = null;
                    switch ($extension) {
                        case 'jpg':
                        case 'jpeg':
                            $sourceImage = imagecreatefromjpeg($uploadPath);
                            break;
                        case 'png':
                            $sourceImage = imagecreatefrompng($uploadPath);
                            break;
                        case 'gif':
                            $sourceImage = imagecreatefromgif($uploadPath);
                            break;
                        case 'webp':
                            // 이미 WebP인 경우 변환 불필요
                            error_log("✅ 이미 WebP 형식: " . $webFilePath);
                            return $webFilePath;
                    }

                    if ($sourceImage) {
                        // WebP로 변환 (품질 80)
                        if (imagewebp($sourceImage, $webpUploadPath, 80)) {
                            chmod($webpUploadPath, 0644);
                            imagedestroy($sourceImage);

                            // 원본 파일 삭제 (WebP만 유지)
                            if (file_exists($uploadPath)) {
                                unlink($uploadPath);
                            }

                            error_log("✅ WebP 변환 성공: " . $webpWebFilePath);
                            return $webpWebFilePath;
                        } else {
                            imagedestroy($sourceImage);
                            error_log("⚠️ WebP 변환 실패, 원본 반환: " . $webFilePath);
                            return $webFilePath;
                        }
                    } else {
                        error_log("⚠️ 이미지 로드 실패, 원본 반환: " . $webFilePath);
                        return $webFilePath;
                    }
                } catch (Exception $e) {
                    error_log("⚠️ WebP 변환 중 오류: " . $e->getMessage() . ", 원본 반환");
                    return $webFilePath;
                }
            } else {
                error_log("❌ 파일 이동 실패: " . $uploadPath);
                return null;
            }
            
        } catch (Exception $e) {
            error_log("❌ 이미지 업로드 중 오류: " . $e->getMessage());
            return null;
        }
    }
} 