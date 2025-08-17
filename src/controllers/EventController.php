<?php
/**
 * 행사 일정 컨트롤러
 * 행사 일정 관리 기능 (강의 시스템 확장)
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/models/User.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/helpers/ValidationHelper.php';
require_once SRC_PATH . '/helpers/WebLogger.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/controllers/LectureController.php';
require_once SRC_PATH . '/config/upload.php';
require_once SRC_PATH . '/helpers/FirebaseHelper.php';

class EventController extends LectureController {
    private $db;
    private $userModel;
    
    public function __construct() {
        try {
            // WebLogger 초기화
            WebLogger::init([
                'log_dir' => ROOT_PATH . '/logs',
                'max_file_size' => 10 * 1024 * 1024, // 10MB
                'use_unified_log' => true
            ]);
            
            $this->db = Database::getInstance();
            $this->userModel = new User();
        } catch (Exception $e) {
            error_log("EventController 초기화 오류: " . $e->getMessage());
            header('Location: /?error=db_connection');
            exit;
        }
    }
    
    /**
     * 행사 일정 메인 페이지 (캘린더 뷰)
     */
    public function index() {
        try {
            // 데이터베이스 테이블 존재 확인
            if (!$this->checkTablesExist()) {
                $this->showSetupPage();
                return;
            }
            
            // 현재 월/년도 파라미터 처리
            $year = $_GET['year'] ?? date('Y');
            $month = $_GET['month'] ?? date('m');
            
            // 디바이스 감지 및 기본 뷰 설정 (공통 시스템 사용)
            require_once SRC_PATH . '/helpers/DeviceHelper.php';
            $view = DeviceHelper::getDefaultView($_GET['view'] ?? null);
            
            // 유효성 검사
            $year = intval($year);
            $month = intval($month);
            
            if ($year < 2020 || $year > 2030) $year = date('Y');
            if ($month < 1 || $month > 12) $month = date('m');
            
            // 해당 월의 행사 일정 조회 (content_type = 'event')
            $events = $this->getEventsByMonth($year, $month);
            
            // 캘린더 데이터 생성
            $calendarData = $this->generateCalendarData($year, $month, $events);
            
            // 뷰에 전달할 데이터
            $data = [
                'page_title' => '행사 일정',
                'page_description' => '다양한 마케팅 행사와 네트워킹 행사 일정을 확인하세요.',
                'year' => $year,
                'month' => $month,
                'view' => $view,
                'events' => $events,
                'calendar_data' => $calendarData,
                'prev_month' => $this->getPrevMonth($year, $month),
                'next_month' => $this->getNextMonth($year, $month),
                'current_user' => $this->getCurrentUser()
            ];
            
            // 뷰 렌더링
            echo "<!-- 디버그: \$view = {$view} -->";
            if ($view === 'list') {
                echo "<!-- 디버그: 리스트 뷰 렌더링 -->";
                $this->render('events/list', $data);
            } else {
                echo "<!-- 디버그: 캘린더 뷰 렌더링 (events/index) -->";
                $this->render('events/index', $data);
            }
            
        } catch (Exception $e) {
            error_log("EventController::index 오류: " . $e->getMessage());
            $this->showErrorPage("행사 일정을 불러오는 중 오류가 발생했습니다.");
        }
    }
    
    /**
     * 특정 월의 행사 일정 조회
     */
    private function getEventsByMonth($year, $month) {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate)); // 해당 월의 마지막 날
        
        $sql = "SELECT 
                    id, title, description, instructor_name, instructor_info,
                    start_date, end_date, start_time, end_time,
                    location_type, venue_name, venue_address, online_link,
                    max_participants, registration_fee, category, status,
                    content_type, created_at
                FROM lectures 
                WHERE content_type = 'event'
                    AND status = 'published'
                    AND start_date BETWEEN ? AND ?
                ORDER BY start_date ASC, start_time ASC";
        
        return $this->db->fetchAll($sql, [$startDate, $endDate]);
    }
    
    /**
     * 행사 상세 페이지
     */
    public function detail() {
        error_log("EventController::detail 메소드 호출됨");
        
        $eventId = $_GET['id'] ?? null;
        error_log("Event ID: " . ($eventId ?? 'NULL'));
        
        if (!$eventId || !is_numeric($eventId)) {
            error_log("EventController::detail - 잘못된 이벤트 ID: " . ($eventId ?? 'NULL'));
            $this->showErrorPage("올바르지 않은 행사 ID입니다.", 400);
            return;
        }
        
        try {
            error_log("EventController::detail - 행사 정보 조회 시작");
            // 행사 정보 조회
            $event = $this->getEventById($eventId);
            
            if (!$event) {
                error_log("EventController::detail - 행사를 찾을 수 없음: " . $eventId);
                $this->showErrorPage("존재하지 않는 행사입니다.", 404);
                return;
            }
            
            error_log("EventController::detail - 행사 정보 조회 성공: " . $event['title']);
            
            // OG 메타 태그용 깨끗한 설명 생성
            $cleanDescription = $this->generateCleanDescription($event['description']);
            
            // OG 이미지 설정 (행사 이미지가 있으면 첫 번째 이미지 사용)
            $ogImage = 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/topmkt-og-image.png?v=' . date('Ymd');
            if (!empty($event['images']) && isset($event['images'][0]['url'])) {
                $ogImage = 'https://' . $_SERVER['HTTP_HOST'] . $event['images'][0]['url'];
            }
            
            // 뷰에 전달할 데이터
            $data = [
                'page_title' => $event['title'],
                'page_description' => $cleanDescription,
                'event' => $event,
                'current_user' => $this->getCurrentUser(),
                'og_title' => $event['title'] . ' - 탑마케팅 행사',
                'og_description' => $cleanDescription,
                'og_image' => $ogImage,
                'og_type' => 'article'
            ];
            
            // 뷰 렌더링
            error_log("EventController::detail - 뷰 렌더링 시작");
            $this->render('events/detail', $data);
            error_log("EventController::detail - 뷰 렌더링 완료");
            
        } catch (Exception $e) {
            error_log("EventController::detail 오류: " . $e->getMessage());
            error_log("EventController::detail 스택 트레이스: " . $e->getTraceAsString());
            $this->showErrorPage("행사 정보를 불러오는 중 오류가 발생했습니다.");
        }
    }
    
    /**
     * 행사 ID로 단일 행사 조회
     */
    private function getEventById($eventId) {
        $sql = "SELECT 
                    l.id, l.title, l.description, l.instructor_name, l.instructor_info,
                    l.start_date, l.end_date, l.start_time, l.end_time,
                    l.location_type, l.venue_name, l.venue_address, l.venue_latitude, l.venue_longitude, l.online_link,
                    l.max_participants, l.registration_fee, l.category, l.status,
                    l.content_type, l.created_at, l.user_id, l.instructor_image, l.youtube_video,
                    l.registration_deadline, l.allow_online_registration,
                    u.nickname as author_name, u.bio as author_bio, 
                    u.profile_image, u.profile_image_original
                FROM lectures l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.id = ? AND l.content_type = 'event' AND l.status = 'published'";
        
        $event = $this->db->fetch($sql, [$eventId]);
        
        if ($event) {
            // 행사 이미지 추가
            $event['images'] = $this->getEventImages($eventId);
            
            // 다중 강사 정보 추가 (lecture_instructors 테이블에서)
            $event['instructors'] = $this->getEventInstructors($eventId);
        }
        
        return $event;
    }
    
    /**
     * 수정 모드용 행사 정보 조회 (status 조건 제거)
     */
    private function getEventByIdForEdit($eventId) {
        $sql = "SELECT 
                    l.id, l.title, l.description, l.instructor_name, l.instructor_info,
                    l.start_date, l.end_date, l.start_time, l.end_time,
                    l.location_type, l.venue_name, l.venue_address, l.venue_latitude, l.venue_longitude, l.online_link,
                    l.max_participants, l.registration_fee, l.category, l.status,
                    l.content_type, l.created_at, l.user_id, l.instructor_image, l.youtube_video,
                    l.registration_deadline, l.allow_online_registration,
                    u.nickname as author_name, u.bio as author_bio, 
                    u.profile_image, u.profile_image_original
                FROM lectures l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.id = ? AND l.content_type = 'event'";
        
        $event = $this->db->fetch($sql, [$eventId]);
        
        if ($event) {
            // 행사 이미지 추가
            $event['images'] = $this->getEventImages($eventId);
            
            // 다중 강사 정보 추가 (lecture_instructors 테이블에서)
            $event['instructors'] = $this->getEventInstructors($eventId);
        }
        
        return $event;
    }
    
    /**
     * 행사 이미지 조회
     */
    private function getEventImages($eventId) {
        try {
            $sql = "
                SELECT * FROM event_images 
                WHERE event_id = ? 
                ORDER BY sort_order ASC, id ASC
            ";
            
            $images = $this->db->fetchAll($sql, [$eventId]);
            
            // 샘플 이미지 fallback (행사 122번용)
            if (empty($images) && $eventId == 122) {
                return [
                    [
                        'id' => 1,
                        'url' => '<?= EVENTS_WEB_PATH ?>/marketing-workshop-main.jpg',
                        'alt_text' => '여름 마케팅 전략 워크샵 메인 이미지'
                    ],
                    [
                        'id' => 2,
                        'url' => '<?= EVENTS_WEB_PATH ?>/marketing-workshop-audience.jpg',
                        'alt_text' => '워크샵 참가자들 모습'
                    ],
                    [
                        'id' => 3,
                        'url' => '<?= EVENTS_WEB_PATH ?>/marketing-workshop-presentation.jpg',
                        'alt_text' => '강의 진행 모습'
                    ],
                    [
                        'id' => 4,
                        'url' => '<?= EVENTS_WEB_PATH ?>/marketing-workshop-networking.jpg',
                        'alt_text' => '네트워킹 세션 모습'
                    ]
                ];
            }
            
            // 데이터베이스 결과를 URL 형식으로 변환
            return array_map(function($image) {
                return [
                    'id' => $image['id'],
                    'url' => $image['image_path'],
                    'alt_text' => $image['alt_text'] ?? ''
                ];
            }, $images);
            
        } catch (Exception $e) {
            error_log("행사 이미지 조회 오류: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 행사 강사 정보 조회 (lecture_instructors 테이블에서)
     */
    private function getEventInstructors($eventId) {
        try {
            $sql = "
                SELECT instructor_name as name, instructor_info as info, instructor_image as image, sort_order
                FROM lecture_instructors 
                WHERE lecture_id = ? 
                ORDER BY sort_order ASC, id ASC
            ";
            
            $instructors = $this->db->fetchAll($sql, [$eventId]);
            
            return $instructors;
            
        } catch (Exception $e) {
            error_log("EventController::getEventInstructors 오류: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 행사 생성 페이지
     */
    public function create() {
        // 로그인 확인
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            header('Location: /auth/login?redirect=' . urlencode('/events/create'));
            exit;
        }
        
        // 기업회원 권한 확인
        require_once SRC_PATH . '/middleware/CorporateMiddleware.php';
        $permission = CorporateMiddleware::checkLectureEventPermission();
        
        if (!$permission['hasPermission']) {
            $_SESSION['error_message'] = $permission['message'];
            header('Location: /corp/info');
            exit;
        }
        
        // 수정 모드 확인 (URL에서 ID 파라미터가 있으면 수정 모드)
        $isEditMode = false;
        $event = null;
        $eventId = $_GET['id'] ?? null;
        
        if ($eventId) {
            error_log("EventController::create - 수정 모드 진입, eventId: " . $eventId);
            $event = $this->getEventByIdForEdit($eventId);
            error_log("EventController::create - 조회된 이벤트: " . ($event ? "존재함" : "없음"));
            
            if (!$event) {
                error_log("EventController::create - 이벤트 없음, 리다이렉트");
                header('Location: /events?error=event_not_found');
                exit;
            }
            
            // 수정 권한 확인 (행사 작성자이거나 관리자인지 확인)
            require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
            $userRole = AuthMiddleware::getCurrentUserRole();
            error_log("EventController::create - 사용자 역할: " . $userRole);
            error_log("EventController::create - 이벤트 소유자: " . $event['user_id'] . ", 현재 사용자: " . $currentUser['id']);
            
            if ($userRole !== 'ROLE_ADMIN' && intval($event['user_id']) !== intval($currentUser['id'])) {
                error_log("EventController::create - 권한 없음, 리다이렉트");
                header('Location: /events/detail?id=' . $eventId . '&error=no_permission');
                exit;
            }
            
            error_log("EventController::create - 권한 확인 완료, 수정 모드 활성화");
            
            $isEditMode = true;
        }
        
        $data = [
            'page_title' => $isEditMode ? '행사 수정' : '새 행사 등록',
            'page_description' => $isEditMode ? '행사 정보를 수정하세요.' : '새로운 마케팅 행사를 등록하세요.',
            'current_user' => $currentUser,
            'action' => $isEditMode ? 'edit' : 'create',
            'event' => $event,
            'instructors' => $event ? $event['instructors'] : [],
            'images' => $event ? $event['images'] : []
        ];
        
        error_log("EventController::create - 뷰 렌더링 시작, isEditMode: " . ($isEditMode ? 'true' : 'false'));
        $this->render('events/create', $data);
        error_log("EventController::create - 뷰 렌더링 완료");
    }
    
    /**
     * 행사 생성 처리
     */
    public function store() {
        WebLogger::controllerStart('EventController', 'store');
        WebLogger::info('EventController::store 호출 시작', [
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'user_id' => AuthMiddleware::getCurrentUserId(),
            'post_data_size' => strlen(json_encode($_POST)),
            'files_count' => count($_FILES)
        ]);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showErrorPage("잘못된 요청입니다.", 405);
            return;
        }
        
        // Check if event_id is provided in POST data - if so, redirect to update method
        if (!empty($_POST['event_id'])) {
            $eventId = intval($_POST['event_id']);
            $this->update($eventId);
            return;
        }
        
        // 로그인 및 기업회원 권한 확인
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
            return;
        }
        
        require_once SRC_PATH . '/middleware/CorporateMiddleware.php';
        $permission = CorporateMiddleware::checkLectureEventPermission();
        
        if (!$permission['hasPermission']) {
            ResponseHelper::json(['success' => false, 'message' => $permission['message']], 403);
            return;
        }
        
        try {
            // 📊 요청 데이터 디버깅 로그
            WebLogger::info('EventController::store 요청 분석', [
                'method' => $_SERVER['REQUEST_METHOD'],
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'N/A',
                'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'N/A',
                'post_count' => count($_POST),
                'files_count' => count($_FILES),
                'post_keys' => array_keys($_POST),
                'files_keys' => array_keys($_FILES),
                'raw_input_length' => strlen(file_get_contents('php://input'))
            ]);
            
            // POST 데이터가 비어있으면 php://input에서 JSON 파싱 시도
            if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
                $rawInput = file_get_contents('php://input');
                WebLogger::info('Raw input 데이터 확인', [
                    'raw_input_preview' => substr($rawInput, 0, 500),
                    'raw_input_length' => strlen($rawInput)
                ]);
                
                // JSON 데이터인지 확인
                if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
                    $jsonData = json_decode($rawInput, true);
                    if ($jsonData) {
                        $_POST = $jsonData;
                        WebLogger::info('JSON 데이터를 POST로 변환', ['post_count' => count($_POST)]);
                    }
                }
            }
            
            // 입력 데이터 검증
            $data = $this->validateEventData($_POST);
            
            // 행사 생성
            $eventId = $this->createEvent($data, $currentUser['id']);
            
            // 다중 강사 정보 저장
            $this->saveMultipleInstructors($eventId, $_POST);
            
            // 행사 이미지 처리 (파일 업로드)
            WebLogger::info('행사 이미지 처리 시작', [
                'event_id' => $eventId,
                'files_event_images_exists' => !empty($_FILES['event_images']),
                'post_event_images_exists' => !empty($_POST['event_images']),
                'files_event_images_count' => !empty($_FILES['event_images']['name']) ? count($_FILES['event_images']['name']) : 0,
                'post_event_images_count' => !empty($_POST['event_images']) ? count($_POST['event_images']) : 0
            ]);
            
            if (!empty($_FILES['event_images'])) {
                WebLogger::info('FILES 방식으로 이미지 처리', [
                    'event_id' => $eventId,
                    'method' => 'FILES upload'
                ]);
                $this->processUploadedEventImages($eventId, $_FILES['event_images']);
            } elseif (!empty($_POST['event_images'])) {
                WebLogger::info('POST 방식으로 이미지 처리 (하위 호환성)', [
                    'event_id' => $eventId,
                    'method' => 'POST JSON'
                ]);
                // 기존 JSON 방식 (하위 호환성)
                $this->processEventImages($eventId, $_POST['event_images']);
            }
            
            // 강사 이미지 처리 및 lecture_instructors 테이블 업데이트
            error_log("=== 강사 이미지 처리 시작 ===");
            error_log("전체 \$_FILES 내용: " . print_r($_FILES, true));
            error_log("FILES instructor_images 존재 여부: " . (!empty($_FILES['instructor_images']) ? 'YES' : 'NO'));
            if (!empty($_FILES['instructor_images'])) {
                error_log("FILES instructor_images 내용: " . print_r($_FILES['instructor_images'], true));
                $this->processInstructorImages($eventId, $_FILES['instructor_images']);
            } else {
                error_log("강사 이미지 업로드 없음 - \$_FILES['instructor_images'] 비어있음");
                error_log("사용 가능한 \$_FILES 키들: " . implode(', ', array_keys($_FILES)));
            }
            
            // 성공 메시지 설정
            $_SESSION['success_message'] = '행사가 성공적으로 등록되었습니다.';
            
            // 행사 상세 페이지로 리다이렉트
            header('Location: /events/detail?id=' . $eventId);
            exit;
            
        } catch (Exception $e) {
            error_log("EventController::store 오류: " . $e->getMessage());
            error_log("EventController::store 스택 트레이스: " . $e->getTraceAsString());
            
            // 디버깅을 위한 추가 로그
            error_log("POST 데이터: " . print_r($_POST, true));
            error_log("FILES 데이터: " . print_r($_FILES, true));
            
            // 클라이언트에게는 간단한 오류 메시지만 전송
            header('HTTP/1.1 500 Internal Server Error');
            echo "<h1>행사 등록 오류</h1><p>행사 등록 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.</p>";
            exit;
        }
    }
    
    /**
     * 행사 데이터 검증
     */
    private function validateEventData($data) {
        $validated = [];
        
        // 필수 필드 검증
        $required = ['title', 'description', 'start_date', 'start_time', 'location_type', 'category'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("{$field} 필드는 필수입니다.");
            }
            $validated[$field] = trim($data[$field]);
        }
        
        // 행사 전용 필드 추가
        $validated['content_type'] = 'event';
        
        // instructor_names 배열 처리 (폼에서 배열로 전송됨)
        if (!empty($data['instructor_names']) && is_array($data['instructor_names'])) {
            // 첫 번째 강사명을 instructor_name으로 설정
            $validated['instructor_name'] = trim($data['instructor_names'][0]) ?: '';
        } else {
            $validated['instructor_name'] = $data['instructor_name'] ?? '';
        }
        
        // instructor_infos 배열 처리 (폼에서 배열로 전송됨)
        if (!empty($data['instructor_infos']) && is_array($data['instructor_infos'])) {
            // 첫 번째 강사 정보를 instructor_info로 설정
            $validated['instructor_info'] = trim($data['instructor_infos'][0]) ?: '';
        } else {
            $validated['instructor_info'] = $data['instructor_info'] ?? '';
        }
        
        // 선택적 필드
        $optional = [
            'end_date', 'end_time',
            'venue_name', 'venue_address', 'venue_latitude', 'venue_longitude', 
            'online_link', 'max_participants', 'registration_fee', 'youtube_video',
            'registration_deadline', 'allow_online_registration'
        ];
        
        foreach ($optional as $field) {
            $validated[$field] = $data[$field] ?? null;
        }
        
        // 데이터 타입 변환
        if ($validated['max_participants']) {
            $validated['max_participants'] = intval($validated['max_participants']);
        }
        if ($validated['registration_fee']) {
            $validated['registration_fee'] = intval($validated['registration_fee']);
        }
        
        // 참가 신청 허용 여부 처리
        if (isset($validated['allow_online_registration'])) {
            $validated['allow_online_registration'] = intval($validated['allow_online_registration']);
        } else {
            $validated['allow_online_registration'] = 1; // 기본값: 허용
        }
        
        // 카테고리 값 검증 및 변환
        $validCategories = ['seminar', 'workshop', 'conference', 'webinar', 'training'];
        if (!in_array($validated['category'], $validCategories)) {
            // 유효하지 않은 카테고리는 기본값으로 변환
            $categoryMapping = [
                'networking' => 'seminar',
                'exhibition' => 'conference', 
                'other' => 'seminar'
            ];
            $validated['category'] = $categoryMapping[$validated['category']] ?? 'seminar';
        }
        
        return $validated;
    }
    
    /**
     * 행사 생성
     */
    private function createEvent($data, $userId) {
        $sql = "INSERT INTO lectures (
                    user_id, title, description, instructor_name, instructor_info,
                    start_date, end_date, start_time, end_time,
                    location_type, venue_name, venue_address, venue_latitude, venue_longitude, online_link,
                    max_participants, registration_fee, category, content_type, youtube_video,
                    registration_deadline, allow_online_registration, status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, 'published', NOW()
                )";
        
        $params = [
            $userId,
            $data['title'],
            $data['description'],
            (!empty($data['instructor_name'])) ? trim($data['instructor_name']) : '미정', // 빈 값이면 '미정'으로 설정
            $data['instructor_info'],
            $data['start_date'],
            $data['end_date'] ?: $data['start_date'], // end_date가 없으면 start_date 사용
            $data['start_time'],
            $data['end_time'] ?: $data['start_time'], // end_time이 없으면 start_time 사용
            $data['location_type'],
            $data['venue_name'],
            $data['venue_address'],
            (!empty($data['venue_latitude']) && $data['venue_latitude'] !== '') ? $data['venue_latitude'] : null, // 빈 문자열을 NULL로 변환
            (!empty($data['venue_longitude']) && $data['venue_longitude'] !== '') ? $data['venue_longitude'] : null, // 빈 문자열을 NULL로 변환
            $data['online_link'],
            $data['max_participants'],
            $data['registration_fee'],
            $data['category'],
            $data['content_type'],
            $data['youtube_video'],
            $data['registration_deadline'],
            $data['allow_online_registration']
        ];
        
        $this->db->execute($sql, $params);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * 다중 강사 정보 저장 (lecture_instructors 테이블)
     */
    private function saveMultipleInstructors($eventId, $postData) {
        // instructor_names 배열과 instructor_infos 배열 처리
        $instructorNames = $postData['instructor_names'] ?? [];
        $instructorInfos = $postData['instructor_infos'] ?? [];
        
        if (empty($instructorNames) || !is_array($instructorNames)) {
            error_log("강사명 배열이 비어있거나 배열이 아닙니다: " . print_r($instructorNames, true));
            return;
        }
        
        // 각 강사 정보를 lecture_instructors 테이블에 저장
        for ($i = 0; $i < count($instructorNames); $i++) {
            $instructorName = trim($instructorNames[$i] ?? '');
            $instructorInfo = trim($instructorInfos[$i] ?? '');
            
            // 빈 강사명은 저장하지 않음
            if (empty($instructorName)) {
                continue;
            }
            
            $sql = "INSERT INTO lecture_instructors (lecture_id, instructor_name, instructor_info, sort_order) 
                    VALUES (?, ?, ?, ?)";
            
            $params = [
                $eventId,
                $instructorName,
                $instructorInfo,
                $i + 1 // sort_order는 1부터 시작
            ];
            
            try {
                $this->db->execute($sql, $params);
                error_log("강사 정보 저장 성공: $instructorName (순서: " . ($i + 1) . ")");
            } catch (Exception $e) {
                error_log("강사 정보 저장 실패: " . $e->getMessage());
            }
        }
        
        error_log("총 " . count($instructorNames) . "명의 강사 정보 처리 완료");
    }
    
    /**
     * 강사 이미지 처리 및 lecture_instructors 테이블 업데이트
     */
    private function processInstructorImages($eventId, $instructorImages) {
        try {
            // 기존 handleInstructorImageUploads 메서드로 이미지 업로드 처리
            $uploadedImages = $this->handleInstructorImageUploads($instructorImages);
            
            if (empty($uploadedImages)) {
                error_log("업로드된 강사 이미지가 없습니다.");
                return;
            }
            
            // lecture_instructors 테이블에서 해당 행사의 강사들을 조회
            $sql = "SELECT id, sort_order FROM lecture_instructors WHERE lecture_id = ? ORDER BY sort_order ASC";
            $instructors = $this->db->fetchAll($sql, [$eventId]);
            
            if (empty($instructors)) {
                error_log("행사 ID $eventId 에 대한 강사 정보가 없습니다.");
                return;
            }
            
            // 업로드된 이미지들을 강사 순서대로 할당
            for ($i = 0; $i < count($instructors) && $i < count($uploadedImages); $i++) {
                $instructorId = $instructors[$i]['id'];
                $imagePath = $uploadedImages[$i];
                
                // lecture_instructors 테이블의 instructor_image 필드 업데이트
                $updateSql = "UPDATE lecture_instructors SET instructor_image = ? WHERE id = ?";
                $this->db->execute($updateSql, [$imagePath, $instructorId]);
                
                error_log("강사 이미지 업데이트 성공: 강사 ID $instructorId, 이미지: $imagePath");
            }
            
            // 첫 번째 강사 이미지를 lectures 테이블의 instructor_image 필드에도 저장 (기존 호환성)
            if (!empty($uploadedImages[0])) {
                $this->updateInstructorImage($eventId, $uploadedImages[0]);
            }
            
            error_log("총 " . count($uploadedImages) . "개의 강사 이미지 처리 완료");
            
        } catch (Exception $e) {
            error_log("강사 이미지 처리 오류: " . $e->getMessage());
        }
    }
    
    /**
     * 뷰 렌더링 헬퍼
     */
    private function render($view, $data = []) {
        // 데이터 추출 (헤더에서 사용할 수 있도록 먼저 실행)
        extract($data);
        
        // 헤더 포함
        require_once SRC_PATH . '/views/templates/header.php';
        
        // 메인 뷰 파일 포함
        $viewPath = SRC_PATH . "/views/{$view}.php";
        echo "<!-- 디버그: 뷰 = {$view}, 경로 = {$viewPath} -->";
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "<h1>뷰 파일을 찾을 수 없습니다: {$view}</h1>";
            echo "<p>시도한 경로: {$viewPath}</p>";
            echo "<p>events/index.php 파일 존재 여부: " . (file_exists(SRC_PATH . "/views/events/index.php") ? "있음" : "없음") . "</p>";
        }
        
        // 푸터 포함
        require_once SRC_PATH . '/views/templates/footer.php';
    }
    
    /**
     * 에러 페이지 표시
     */
    private function showErrorPage($message, $code = 500) {
        http_response_code($code);
        $data = [
            'page_title' => 'Error',
            'error_message' => $message,
            'error_code' => $code
        ];
        $this->render('lectures/error', $data);
    }
    
    /**
     * 테이블 존재 확인
     */
    private function checkTablesExist() {
        try {
            $this->db->getConnection()->query("SELECT 1 FROM lectures LIMIT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 설정 페이지 표시
     */
    private function showSetupPage() {
        $data = [
            'page_title' => '행사 일정 설정',
            'page_description' => '행사 일정 시스템을 설정합니다.'
        ];
        $this->render('lectures/setup', $data);
    }
    
    /**
     * 캘린더 데이터 생성
     */
    private function generateCalendarData($year, $month, $events) {
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $lastDay = mktime(0, 0, 0, $month + 1, 0, $year);
        $firstWeekday = date('w', $firstDay);
        $daysInMonth = date('t', $firstDay);
        
        $calendar = [];
        $week = [];
        
        // 이전 달의 마지막 날들
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear = $month == 1 ? $year - 1 : $year;
        $daysInPrevMonth = date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));
        
        for ($i = $firstWeekday - 1; $i >= 0; $i--) {
            $day = $daysInPrevMonth - $i;
            $week[] = [
                'day' => $day,
                'date' => sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $day),
                'class' => 'other-month'
            ];
        }
        
        // 현재 달의 날들
        $today = date('Y-m-d');
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $class = '';
            if ($date === $today) {
                $class = 'today';
            }
            
            $week[] = [
                'day' => $day,
                'date' => $date,
                'class' => $class
            ];
            
            if (count($week) == 7) {
                $calendar[] = $week;
                $week = [];
            }
        }
        
        // 다음 달의 첫날들
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear = $month == 12 ? $year + 1 : $year;
        $day = 1;
        while (count($week) < 7) {
            $week[] = [
                'day' => $day,
                'date' => sprintf('%04d-%02d-%02d', $nextYear, $nextMonth, $day),
                'class' => 'other-month'
            ];
            $day++;
        }
        
        if (count($week) > 0) {
            $calendar[] = $week;
        }
        
        return $calendar;
    }
    
    /**
     * 이전 달 정보
     */
    private function getPrevMonth($year, $month) {
        if ($month == 1) {
            return ['year' => $year - 1, 'month' => 12];
        }
        return ['year' => $year, 'month' => $month - 1];
    }
    
    /**
     * 다음 달 정보
     */
    private function getNextMonth($year, $month) {
        if ($month == 12) {
            return ['year' => $year + 1, 'month' => 1];
        }
        return ['year' => $year, 'month' => $month + 1];
    }
    
    /**
     * OG 메타 태그용 깨끗한 설명 생성
     */
    private function generateCleanDescription($description) {
        // 1. Markdown 문법 제거
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $description); // **볼드** 제거
        $text = preg_replace('/\*(.*?)\*/', '$1', $text); // *이탤릭* 제거
        $text = preg_replace('/#{1,6}\s/', '', $text); // # 헤더 제거
        $text = preg_replace('/\[(.*?)\]\(.*?\)/', '$1', $text); // [링크](url) 제거
        $text = preg_replace('/```.*?```/s', '', $text); // 코드 블록 제거
        $text = preg_replace('/`(.*?)`/', '$1', $text); // 인라인 코드 제거
        
        // 2. 이모지와 특수 문자 정리
        $text = preg_replace('/[🎯💼🎁🤝📍⭐🔥💡📊🚀]+/', '', $text); // 이모지 제거
        $text = preg_replace('/•\s*/', '- ', $text); // 불릿 포인트 정리
        
        // 3. HTML 태그 제거
        $text = strip_tags($text);
        
        // 4. 연속된 공백과 줄바꿈 정리
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // 5. 첫 번째 문장만 추출하여 깔끔하게
        $sentences = preg_split('/[.!?]\s+/', $text);
        $firstSentence = trim($sentences[0]);
        
        // 6. 길이 제한 (160자)
        if (mb_strlen($firstSentence) > 160) {
            $firstSentence = mb_substr($firstSentence, 0, 157) . '...';
        }
        
        return $firstSentence;
    }
    
    /**
     * 행사 신청 API
     */
    public function register($eventId) {
        header('Content-Type: application/json');
        
        try {
            // HTTP 메소드 확인
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return ResponseHelper::json(null, 405, 'POST 메소드만 허용됩니다.');
            }
            
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 데이터 파싱 (JSON 또는 폼 데이터 지원)
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $input = [];
            
            if (strpos($contentType, 'application/json') !== false) {
                $rawInput = file_get_contents('php://input');
                if (!empty($rawInput)) {
                    $decoded = json_decode($rawInput, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $input = $decoded;
                    } else {
                        $input = $_POST;
                    }
                } else {
                    $input = $_POST;
                }
            } else {
                $input = $_POST;
            }
            
            if (empty($input)) {
                return ResponseHelper::json(null, 400, '입력 데이터가 없습니다.');
            }
            
            // CSRF 토큰 검증
            if (!$this->validateCsrfToken($input['csrf_token'] ?? '')) {
                return ResponseHelper::json(null, 403, 'CSRF 토큰이 유효하지 않습니다.');
            }
            
            // 행사 정보 조회
            $eventQuery = "
                SELECT 
                    id, title, start_date, start_time, max_participants, 
                    auto_approval, registration_start_date, 
                    registration_end_date, allow_waiting_list, status, user_id as organizer_id
                FROM lectures 
                WHERE id = ? AND content_type = 'event' AND status = 'published'
            ";
            
            $stmt = $this->db->prepare($eventQuery);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            
            if (!$event) {
                return ResponseHelper::json(null, 404, '행사를 찾을 수 없습니다.');
            }
            
            // 본인 행사 신청 방지
            if ($event['organizer_id'] == $userId) {
                return ResponseHelper::json(null, 400, '본인이 등록한 행사에는 신청할 수 없습니다.');
            }
            
            // 기존 신청 확인
            $existingQuery = "SELECT id, status FROM event_registrations WHERE event_id = ? AND user_id = ?";
            $stmt = $this->db->prepare($existingQuery);
            $stmt->bind_param("ii", $eventId, $userId);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            
            if ($existing && in_array($existing['status'], ['pending', 'approved', 'waiting'])) {
                return ResponseHelper::json(null, 400, '이미 신청하셨습니다.');
            }
            
            // 취소된 신청이 있으면 삭제 (재신청을 위해)
            if ($existing && $existing['status'] === 'cancelled') {
                $deleteQuery = "DELETE FROM event_registrations WHERE id = ?";
                $stmt = $this->db->prepare($deleteQuery);
                $stmt->bind_param("i", $existing['id']);
                $stmt->execute();
            }
            
            // 신청 기간 확인
            $now = new DateTime();
            
            if ($event['registration_start_date']) {
                $startDate = new DateTime($event['registration_start_date']);
                if ($now < $startDate) {
                    return ResponseHelper::json(null, 400, '아직 신청 기간이 아닙니다.');
                }
            }
            
            if ($event['registration_end_date']) {
                $endDate = new DateTime($event['registration_end_date']);
                if ($now > $endDate) {
                    return ResponseHelper::json(null, 400, '신청 기간이 마감되었습니다.');
                }
            }
            
            // 행사 시작 시간 확인
            $eventStart = new DateTime($event['start_date'] . ' ' . $event['start_time']);
            if ($now >= $eventStart) {
                return ResponseHelper::json(null, 400, '행사가 이미 시작되었습니다.');
            }
            
            // 사용자 정보 조회
            $userQuery = "SELECT nickname, phone, email FROM users WHERE id = ?";
            $stmt = $this->db->prepare($userQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            if (!$user) {
                return ResponseHelper::json(null, 404, '사용자 정보를 찾을 수 없습니다.');
            }
            
            // 입력 데이터 검증
            $validationErrors = $this->validateRegistrationData($input, $user);
            if (!empty($validationErrors)) {
                return ResponseHelper::json(['errors' => $validationErrors], 400, '입력 데이터에 오류가 있습니다.');
            }
            
            // 정원 확인 (승인된 신청 수 조회)
            $approvedCountQuery = "SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ? AND status = 'approved'";
            $stmt = $this->db->prepare($approvedCountQuery);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $currentParticipants = $stmt->get_result()->fetch_assoc()['count'];
            
            $isWaitingList = false;
            $waitingOrder = null;
            $status = 'pending';
            
            if ($event['max_participants'] && $currentParticipants >= $event['max_participants']) {
                if (!$event['allow_waiting_list']) {
                    return ResponseHelper::json(null, 400, '정원이 마감되었습니다.');
                }
                
                // 대기자로 등록
                $isWaitingList = true;
                $status = 'waiting';
                
                // 대기 순번 계산
                $waitingQuery = "SELECT MAX(waiting_order) as max_order FROM event_registrations WHERE event_id = ? AND is_waiting_list = 1";
                $stmt = $this->db->prepare($waitingQuery);
                $stmt->bind_param("i", $eventId);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $waitingOrder = ($result['max_order'] ?? 0) + 1;
            }
            
            // 자동 승인 확인
            if (!$isWaitingList && $event['auto_approval']) {
                $status = 'approved';
            }
            
            // 신청 데이터 구성
            $registrationData = [
                'event_id' => $eventId,
                'user_id' => $userId,
                'participant_name' => trim($input['participant_name'] ?? $user['nickname']),
                'participant_email' => trim($input['participant_email'] ?? $user['email']),
                'participant_phone' => trim($input['participant_phone'] ?? $user['phone']),
                'company_name' => trim($input['company_name'] ?? ''),
                'position' => trim($input['position'] ?? ''),
                'motivation' => trim($input['motivation'] ?? ''),
                'special_requests' => trim($input['special_requests'] ?? ''),
                'how_did_you_know' => trim($input['how_did_you_know'] ?? ''),
                'status' => $status,
                'is_waiting_list' => $isWaitingList,
                'waiting_order' => $waitingOrder
            ];
            
            // 자동 승인인 경우 처리자 정보 설정
            if ($status === 'approved') {
                $registrationData['processed_by'] = $userId;
                $registrationData['processed_at'] = date('Y-m-d H:i:s');
            }
            
            // 트랜잭션 시작
            $this->db->beginTransaction();
            
            try {
                // 신청 등록
                $insertQuery = "
                    INSERT INTO event_registrations 
                    (event_id, user_id, participant_name, participant_email, participant_phone,
                     company_name, position, motivation, special_requests, how_did_you_know,
                     status, is_waiting_list, waiting_order, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ";
                
                $stmt = $this->db->prepare($insertQuery);
                $stmt->bind_param(
                    "sisssssssssis",
                    $eventId,
                    $registrationData['user_id'],
                    $registrationData['participant_name'],
                    $registrationData['participant_email'],
                    $registrationData['participant_phone'],
                    $registrationData['company_name'],
                    $registrationData['position'],
                    $registrationData['motivation'],
                    $registrationData['special_requests'],
                    $registrationData['how_did_you_know'],
                    $registrationData['status'],
                    $registrationData['is_waiting_list'],
                    $registrationData['waiting_order']
                );
                
                if (!$stmt->execute()) {
                    throw new Exception('행사 신청 등록에 실패했습니다: ' . $stmt->error);
                }
                
                $registrationId = $this->db->lastInsertId();
                
                // 커밋
                $this->db->commit();
                
                // 행사 주최자에게 Firebase 실시간 알림 발송
                try {
                    $this->updateEventOrganizerNotification($eventId);
                } catch (Exception $e) {
                    error_log("Firebase 실시간 알림 업데이트 오류: " . $e->getMessage());
                }
                
                // 행사 신청 확인 SMS 발송
                try {
                    require_once SRC_PATH . '/helpers/SmsHelper.php';
                    $smsResult = sendEventApplicationSms($registrationData['participant_phone']);
                    if ($smsResult['success']) {
                        error_log("행사 신청 확인 SMS 발송 성공: " . $registrationData['participant_phone']);
                    } else {
                        error_log("행사 신청 확인 SMS 발송 실패: " . $smsResult['message']);
                    }
                } catch (Exception $e) {
                    error_log("SMS 발송 오류: " . $e->getMessage());
                    // SMS 실패는 전체 프로세스를 중단하지 않음
                }
                
                $message = $isWaitingList ? 
                    "대기자로 신청이 완료되었습니다. (대기순번: {$waitingOrder}번)" :
                    ($status === 'approved' ? '신청이 승인되었습니다.' : '신청이 완료되었습니다. 승인을 기다려주세요.');
                
                return ResponseHelper::json([
                    'registration_id' => $registrationId,
                    'status' => $status,
                    'is_waiting_list' => $isWaitingList,
                    'waiting_order' => $waitingOrder
                ], 200, $message);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("행사 신청 등록 오류: " . $e->getMessage());
            return ResponseHelper::json(null, 500, '신청 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
    
    /**
     * 행사 신청 상태 확인 API
     */
    public function getRegistrationStatus($eventId) {
        header('Content-Type: application/json');
        
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 행사 정보 조회
            $eventQuery = "
                SELECT 
                    l.id, l.title, l.start_date, l.start_time, l.end_date, l.end_time,
                    l.max_participants, l.auto_approval,
                    l.registration_start_date, l.registration_end_date, l.allow_waiting_list,
                    l.status as event_status,
                    COUNT(DISTINCT CASE WHEN er.status = 'approved' THEN er.id END) as current_participants
                FROM lectures l
                LEFT JOIN event_registrations er ON l.id = er.event_id
                WHERE l.id = ? AND l.content_type = 'event' AND l.status = 'published'
                GROUP BY l.id, l.title, l.start_date, l.start_time, l.end_date, l.end_time,
                         l.max_participants, l.auto_approval, l.registration_start_date, 
                         l.registration_end_date, l.allow_waiting_list, l.status
            ";
            
            $stmt = $this->db->prepare($eventQuery);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            
            if (!$event) {
                return ResponseHelper::json(null, 404, '행사를 찾을 수 없습니다.');
            }
            
            // 사용자의 신청 정보 조회
            $registrationQuery = "
                SELECT 
                    id, status, is_waiting_list, waiting_order,
                    created_at, processed_at, admin_notes
                FROM event_registrations 
                WHERE event_id = ? AND user_id = ?
                ORDER BY created_at DESC 
                LIMIT 1
            ";
            
            $stmt = $this->db->prepare($registrationQuery);
            $stmt->bind_param("ii", $eventId, $userId);
            $stmt->execute();
            $registration = $stmt->get_result()->fetch_assoc();
            
            // 응답 데이터 구성
            $responseData = [
                'event_info' => $event,
                'registration' => $registration,
                'user_id' => $userId
            ];
            
            return ResponseHelper::json($responseData, 200, '신청 상태 조회 완료');
            
        } catch (Exception $e) {
            error_log("행사 신청 상태 조회 오류: " . $e->getMessage());
            return ResponseHelper::json(null, 500, '신청 상태 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 행사 신청 취소 API
     */
    public function cancelRegistration($eventId) {
        header('Content-Type: application/json');
        
        try {
            // HTTP 메소드 확인
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                return ResponseHelper::json(null, 405, 'DELETE 메소드만 허용됩니다.');
            }
            
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 데이터 파싱 (JSON 또는 쿼리 파라미터 지원)
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                $input = json_decode(file_get_contents('php://input'), true);
            } else {
                $input = array_merge($_GET, $_POST);
            }
            
            // CSRF 토큰 검증
            $csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            
            if (!$this->validateCsrfToken($csrfToken)) {
                return ResponseHelper::json(null, 403, 'CSRF 토큰이 유효하지 않습니다.');
            }
            
            // 신청 정보 조회
            $registrationQuery = "
                SELECT r.*, l.start_date, l.start_time 
                FROM event_registrations r
                JOIN lectures l ON r.event_id = l.id
                WHERE r.event_id = ? AND r.user_id = ? 
                AND r.status IN ('pending', 'approved', 'waiting')
                ORDER BY r.created_at DESC LIMIT 1
            ";
            
            $stmt = $this->db->prepare($registrationQuery);
            $stmt->bind_param("ii", $eventId, $userId);
            $stmt->execute();
            $registration = $stmt->get_result()->fetch_assoc();
            
            if (!$registration) {
                return ResponseHelper::json(null, 404, '취소할 신청을 찾을 수 없습니다.');
            }
            
            // 행사 시작 시간 확인
            $now = new DateTime();
            $eventStart = new DateTime($registration['start_date'] . ' ' . $registration['start_time']);
            
            if ($now >= $eventStart) {
                return ResponseHelper::json(null, 400, '행사가 이미 시작되어 취소할 수 없습니다.');
            }
            
            // 신청 취소 처리
            $updateQuery = "
                UPDATE event_registrations 
                SET status = 'cancelled', processed_at = NOW() 
                WHERE id = ?
            ";
            
            $stmt = $this->db->prepare($updateQuery);
            $stmt->bind_param("i", $registration['id']);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                return ResponseHelper::json([
                    'registration_id' => $registration['id']
                ], 200, '신청이 취소되었습니다.');
            } else {
                return ResponseHelper::json(null, 500, '신청 취소에 실패했습니다.');
            }
            
        } catch (Exception $e) {
            error_log("행사 신청 취소 오류: " . $e->getMessage());
            return ResponseHelper::json(null, 500, '신청 취소 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 이전 행사 신청 데이터 조회 API
     */
    public function getPreviousRegistration($eventId) {
        header('Content-Type: application/json');
        
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            
            // 가장 최근 신청 정보 조회 (재신청 시 폼 미리 채움용)
            $query = "
                SELECT 
                    participant_name, participant_email, participant_phone,
                    company_name, position, motivation, special_requests, how_did_you_know
                FROM event_registrations 
                WHERE event_id = ? AND user_id = ? AND status IN ('cancelled', 'rejected')
                ORDER BY created_at DESC 
                LIMIT 1
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $eventId, $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result) {
                return ResponseHelper::json($result, 200, '이전 신청 데이터 조회 완료');
            } else {
                return ResponseHelper::json(null, 200, '이전 신청 데이터가 없습니다.');
            }
            
        } catch (Exception $e) {
            error_log("이전 행사 신청 데이터 조회 오류: " . $e->getMessage());
            return ResponseHelper::json(null, 500, '이전 신청 데이터 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 행사 신청 데이터 검증
     */
    private function validateRegistrationData($input, $user) {
        $errors = [];
        
        // 필수 필드 검증
        $participantName = trim($input['participant_name'] ?? '');
        $participantEmail = trim($input['participant_email'] ?? '');
        $participantPhone = trim($input['participant_phone'] ?? '');
        
        // 이름 검증
        if (empty($participantName)) {
            $errors['participant_name'] = '이름을 입력해주세요.';
        } elseif (strlen($participantName) < 2) {
            $errors['participant_name'] = '이름은 2글자 이상 입력해주세요.';
        } elseif (strlen($participantName) > 100) {
            $errors['participant_name'] = '이름이 너무 깁니다.';
        }
        
        // 이메일 검증
        if (empty($participantEmail)) {
            $errors['participant_email'] = '이메일을 입력해주세요.';
        } elseif (!filter_var($participantEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['participant_email'] = '올바른 이메일 형식을 입력해주세요.';
        } elseif (strlen($participantEmail) > 255) {
            $errors['participant_email'] = '이메일이 너무 깁니다.';
        }
        
        // 전화번호 검증
        if (empty($participantPhone)) {
            $errors['participant_phone'] = '연락처를 입력해주세요.';
        } elseif (!$this->isValidPhone($participantPhone)) {
            $errors['participant_phone'] = '올바른 연락처 형식을 입력해주세요. (예: 010-1234-5678)';
        }
        
        // 선택적 필드 길이 검증
        if (!empty($input['company_name']) && strlen(trim($input['company_name'])) > 255) {
            $errors['company_name'] = '회사명이 너무 깁니다.';
        }
        
        if (!empty($input['position']) && strlen(trim($input['position'])) > 100) {
            $errors['position'] = '직책명이 너무 깁니다.';
        }
        
        if (!empty($input['motivation']) && strlen(trim($input['motivation'])) > 1000) {
            $errors['motivation'] = '참가 동기가 너무 깁니다. (최대 1000자)';
        }
        
        if (!empty($input['special_requests']) && strlen(trim($input['special_requests'])) > 1000) {
            $errors['special_requests'] = '특별 요청사항이 너무 깁니다. (최대 1000자)';
        }
        
        // how_did_you_know 값 검증
        $validSources = ['website', 'social_media', 'friend_referral', 'company_notice', 'email', 'search_engine', 'advertisement', 'other'];
        if (!empty($input['how_did_you_know']) && !in_array($input['how_did_you_know'], $validSources)) {
            $errors['how_did_you_know'] = '올바른 항목을 선택해주세요.';
        }
        
        return $errors;
    }
    
    /**
     * 전화번호 형식 검증
     */
    private function isValidPhone($phone) {
        // 공백 제거
        $phone = preg_replace('/\s/', '', $phone);
        
        // 한국 휴대폰 번호 형식 검증
        return preg_match('/^(010|011|016|017|018|019)[-]?\d{3,4}[-]?\d{4}$/', $phone);
    }
    
    /**
     * CSRF 토큰 검증
     */
    private function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * 현재 사용자 정보 가져오기
     */
    private function getCurrentUser() {
        if (!AuthMiddleware::isLoggedIn()) {
            return null;
        }
        
        $userId = AuthMiddleware::getCurrentUserId();
        if (!$userId) {
            return null;
        }
        
        try {
            $sql = "SELECT id, nickname, email, role FROM users WHERE id = ? AND status = 'ACTIVE'";
            return $this->db->fetch($sql, [$userId]);
        } catch (Exception $e) {
            error_log("getCurrentUser 오류: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 행사 이미지 처리
     */
    /**
     * 이벤트 이미지 업로드 API
     */
    public function uploadEventImage() {
        // JSON 응답 헤더 설정
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // POST 요청만 허용
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                ResponseHelper::json(['success' => false, 'message' => 'POST 요청만 허용됩니다.'], 405);
                return;
            }
            
            // 파일 업로드 확인
            if (!isset($_FILES['event_image']) || $_FILES['event_image']['error'] !== UPLOAD_ERR_OK) {
                ResponseHelper::json(['success' => false, 'message' => '파일 업로드에 실패했습니다.'], 400);
                return;
            }
            
            $file = $_FILES['event_image'];
            
            // 파일 크기 검증 (공통 설정 사용: 30MB)
            if (!UploadConfig::validateFileSize($file['size'])) {
                ResponseHelper::json(['success' => false, 'message' => UploadConfig::getErrorMessage('file_too_large')], 400);
                return;
            }
            
            // 파일 타입 검증
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                ResponseHelper::json(['success' => false, 'message' => 'JPG, PNG, GIF, WebP 파일만 업로드 가능합니다.'], 400);
                return;
            }
            
            // 업로드 디렉토리 생성
            $uploadDir = ROOT_PATH . '/public/assets/uploads/events/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // 고유 파일명 생성
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
            $uploadPath = $uploadDir . '/' . $fileName;
            
            // 파일 이동
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // 웹 경로 생성
                $webPath = '/assets/uploads/events/' . date('Y/m') . '/' . $fileName;
                
                // 성공 응답
                ResponseHelper::json([
                    'success' => true,
                    'message' => '이미지가 성공적으로 업로드되었습니다.',
                    'data' => [
                        'url' => $webPath,
                        'filename' => $fileName,
                        'original_name' => $file['name'],
                        'size' => $file['size'],
                        'type' => $fileType
                    ]
                ], 200);
            } else {
                ResponseHelper::json(['success' => false, 'message' => '파일 저장에 실패했습니다.'], 500);
            }
            
        } catch (Exception $e) {
            error_log("이벤트 이미지 업로드 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '서버 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 강사 이미지 업데이트
     */
    private function updateInstructorImage($eventId, $imagePath) {
        try {
            $sql = "UPDATE lectures SET instructor_image = ? WHERE id = ? AND content_type = 'event'";
            $this->db->query($sql, [$imagePath, $eventId]);
            
            error_log("강사 이미지 업데이트 완료: EventID={$eventId}, ImagePath={$imagePath}");
        } catch (Exception $e) {
            error_log("강사 이미지 업데이트 오류: " . $e->getMessage());
        }
    }
    
    /**
     * 업로드된 이벤트 이미지 파일 처리
     */
    private function processUploadedEventImages($eventId, $files) {
        try {
            WebLogger::info('processUploadedEventImages 호출 시작', [
                'event_id' => $eventId,
                'files_structure' => [
                    'name_count' => isset($files['name']) ? count($files['name']) : 0,
                    'tmp_name_count' => isset($files['tmp_name']) ? count($files['tmp_name']) : 0,
                    'error_count' => isset($files['error']) ? count($files['error']) : 0,
                    'size_count' => isset($files['size']) ? count($files['size']) : 0,
                    'first_file_name' => isset($files['name'][0]) ? $files['name'][0] : 'none',
                    'first_file_error' => isset($files['error'][0]) ? $files['error'][0] : 'none'
                ],
                'caller' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1]['function'] ?? 'unknown'
            ]);
            
            // 파일 배열 구조 확인 - event_images[] 형태로 업로드됨
            $hasValidFileStructure = is_array($files) && isset($files['name']) && is_array($files['name']);
            
            if (!$hasValidFileStructure) {
                WebLogger::info('processUploadedEventImages: 파일 업로드 없음', [
                    'event_id' => $eventId,
                    'files_is_array' => is_array($files),
                    'files_has_name' => isset($files['name']),
                    'files_name_is_array' => isset($files['name']) && is_array($files['name']),
                    'will_check_deletion' => true
                ]);
                // 파일 업로드가 없어도 삭제 요청이 있을 수 있으므로 continue
            }
            
            // 파일이 하나도 없고 삭제 요청도 없으면 종료
            // 더 엄격한 파일 확인: 실제로 유효한 파일이 있는지 확인
            $hasFiles = $hasValidFileStructure && 
                       count($files['name']) > 0 && 
                       !empty(array_filter($files['name'])) && 
                       !(count($files['name']) === 1 && empty($files['name'][0]));
                       
            $hasRemoveRequest = isset($_POST['remove_images']) && 
                               is_array($_POST['remove_images']) && 
                               count($_POST['remove_images']) > 0;
            
            if (!$hasFiles && !$hasRemoveRequest) {
                WebLogger::info('processUploadedEventImages: 업로드할 파일도 삭제 요청도 없음', [
                    'event_id' => $eventId,
                    'has_files' => $hasFiles,
                    'has_remove_request' => $hasRemoveRequest,
                    'files_name' => isset($files['name']) ? $files['name'] : 'not set',
                    'remove_images' => isset($_POST['remove_images']) ? $_POST['remove_images'] : 'not set'
                ]);
                return;
            }
            
            WebLogger::info('processUploadedEventImages: 처리 시작', [
                'event_id' => $eventId,
                'has_files' => $hasFiles,
                'has_remove_request' => $hasRemoveRequest,
                'files_count' => isset($files['name']) ? count($files['name']) : 0,
                'remove_images_count' => isset($_POST['remove_images']) ? count($_POST['remove_images']) : 0
            ]);
            
            // 업로드 디렉토리 생성
            $uploadDir = ROOT_PATH . '/public/assets/uploads/events/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    WebLogger::error('processUploadedEventImages: 디렉토리 생성 실패', [
                        'event_id' => $eventId,
                        'upload_dir' => $uploadDir,
                        'error' => error_get_last()
                    ]);
                    return;
                }
                WebLogger::info('processUploadedEventImages: 디렉토리 생성 성공', [
                    'event_id' => $eventId,
                    'upload_dir' => $uploadDir
                ]);
            }
            
            // 기존 이미지 삭제는 새 이미지가 실제로 업로드된 경우에만 수행
            // 삭제 요청된 이미지만 삭제
            if (!empty($_POST['remove_images'])) {
                $removeIds = $_POST['remove_images'];
                $placeholders = str_repeat('?,', count($removeIds) - 1) . '?';
                try {
                    $this->db->execute("DELETE FROM event_images WHERE id IN ($placeholders)", $removeIds);
                    WebLogger::info('processUploadedEventImages: 선택된 이미지 삭제 완료', [
                        'event_id' => $eventId,
                        'removed_count' => count($removeIds),
                        'removed_ids' => $removeIds
                    ]);
                } catch (Exception $e) {
                    WebLogger::error('processUploadedEventImages: 선택된 이미지 삭제 실패', [
                        'event_id' => $eventId,
                        'remove_ids' => $removeIds,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 기존 이미지 정보 유지를 위한 처리
            if (!empty($_POST['existing_images'])) {
                WebLogger::info('processUploadedEventImages: 기존 이미지 유지', [
                    'event_id' => $eventId,
                    'existing_count' => count($_POST['existing_images']),
                    'existing_images' => $_POST['existing_images']
                ]);
            }
            
            $savedCount = 0;
            
            // 새 파일이 있는 경우에만 파일 처리
            if ($hasFiles && $hasValidFileStructure) {
                // 각 파일 처리
                for ($i = 0; $i < count($files['name']); $i++) {
                try {
                    // 파일 오류 확인
                    if (empty($files['name'][$i]) || $files['error'][$i] !== UPLOAD_ERR_OK) {
                        WebLogger::warning('processUploadedEventImages: 파일 스킵', [
                            'event_id' => $eventId,
                            'file_index' => $i,
                            'file_name' => $files['name'][$i] ?? 'empty',
                            'error_code' => $files['error'][$i] ?? 'N/A',
                            'error_message' => $this->getUploadErrorMessage($files['error'][$i] ?? 0)
                        ]);
                        continue;
                    }
                    
                    $file = [
                        'name' => $files['name'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'size' => $files['size'][$i],
                        'type' => $files['type'][$i]
                    ];
                    
                    WebLogger::info('processUploadedEventImages: 파일 처리 시작', [
                        'event_id' => $eventId,
                        'file_index' => $i,
                        'file_name' => $file['name'],
                        'file_size' => $file['size'],
                        'file_type' => $file['type']
                    ]);
                    
                    // 파일 검증
                    if (!$this->validateEventImageFile($file)) {
                        WebLogger::warning('processUploadedEventImages: 파일 검증 실패', [
                            'event_id' => $eventId,
                            'file_index' => $i,
                            'file_name' => $file['name'],
                            'file_size' => $file['size'],
                            'file_type' => $file['type']
                        ]);
                        continue;
                    }
                    
                    // 고유 파일명 생성 (마이크로초 포함으로 중복 방지 강화)
                    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = date('YmdHis') . '_' . substr(microtime(), 2, 6) . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
                    $uploadPath = $uploadDir . '/' . $fileName;
                    
                    WebLogger::info('processUploadedEventImages: 파일 이동 시도', [
                        'event_id' => $eventId,
                        'file_index' => $i,
                        'original_name' => $file['name'],
                        'generated_filename' => $fileName,
                        'upload_path' => $uploadPath,
                        'tmp_name' => $file['tmp_name']
                    ]);
                    
                    // 파일 이동
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $webPath = '/assets/uploads/events/' . date('Y/m') . '/' . $fileName;
                        
                        WebLogger::info('processUploadedEventImages: 데이터베이스 저장 시도', [
                            'event_id' => $eventId,
                            'file_index' => $i,
                            'web_path' => $webPath,
                            'alt_text' => $file['name'],
                            'sort_order' => $i
                        ]);
                        
                        // 데이터베이스에 저장
                        $sql = "INSERT INTO event_images (event_id, image_path, alt_text, sort_order, created_at) 
                               VALUES (?, ?, ?, ?, NOW())";
                        
                        $this->db->execute($sql, [
                            $eventId,
                            $webPath,
                            $file['name'],
                            $i
                        ]);
                        
                        $savedCount++;
                        WebLogger::info('processUploadedEventImages: 파일 저장 성공', [
                            'event_id' => $eventId,
                            'file_index' => $i,
                            'web_path' => $webPath,
                            'saved_count' => $savedCount
                        ]);
                    } else {
                        WebLogger::error('processUploadedEventImages: 파일 이동 실패', [
                            'event_id' => $eventId,
                            'file_index' => $i,
                            'upload_path' => $uploadPath,
                            'tmp_name' => $file['tmp_name'],
                            'error' => error_get_last()
                        ]);
                    }
                    
                } catch (Exception $e) {
                    WebLogger::error('processUploadedEventImages: 파일 처리 중 오류', [
                        'event_id' => $eventId,
                        'file_index' => $i,
                        'file_name' => $file['name'] ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // 다음 파일 계속 처리
                }
                }
            }
            
            WebLogger::info('processUploadedEventImages 완료', [
                'event_id' => $eventId,
                'saved_count' => $savedCount,
                'total_files_processed' => ($hasValidFileStructure && isset($files['name'])) ? count($files['name']) : 0,
                'had_valid_file_structure' => $hasValidFileStructure
            ]);
            
        } catch (Exception $e) {
            WebLogger::error('processUploadedEventImages 전체 오류', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // 오류 발생해도 예외를 다시 던지지 않음 (store 메서드가 계속 실행되도록)
        }
    }
    
    /**
     * 업로드 오류 메시지 변환
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return 'No error';
            case UPLOAD_ERR_INI_SIZE:
                return 'File too large (php.ini)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File too large (form)';
            case UPLOAD_ERR_PARTIAL:
                return 'File partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'No temp directory';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Cannot write to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload stopped by extension';
            default:
                return 'Unknown error';
        }
    }
    
    /**
     * 이벤트 이미지 파일 검증
     */
    private function validateEventImageFile($file) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = UploadConfig::getMaxFileSize(); // 30MB (공통 설정)
        
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        if ($file['size'] > $maxSize) {
            return false;
        }
        
        return true;
    }
    
    private function processEventImages($eventId, $imagesJson) {
        try {
            // JSON 디코딩
            $images = json_decode($imagesJson, true);
            if (!is_array($images)) {
                error_log("processEventImages: 유효하지 않은 이미지 데이터");
                return;
            }
            
            // 기존 이미지 삭제
            $this->db->execute("DELETE FROM event_images WHERE event_id = ?", [$eventId]);
            
            // 새 이미지 저장
            foreach ($images as $index => $image) {
                if (empty($image['url'])) continue;
                
                $sql = "INSERT INTO event_images (event_id, image_path, alt_text, sort_order, created_at) 
                       VALUES (?, ?, ?, ?, NOW())";
                        
                $this->db->execute($sql, [
                    $eventId,
                    $image['url'],
                    $image['alt'] ?? '',
                    $index + 1
                ]);
            }
            
            error_log("processEventImages: 행사 ID {$eventId}에 " . count($images) . "개 이미지 저장 완료");
            
        } catch (Exception $e) {
            error_log("processEventImages 오류: " . $e->getMessage());
        }
    }
    
    /**
     * 이벤트 수정 페이지 표시
     */
    public function edit($eventId) {
        try {
            // 로그인 확인
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) {
                header('Location: /auth/login?return_to=' . urlencode($_SERVER['REQUEST_URI']));
                exit;
            }
            
            // 이벤트 정보 조회
            $event = $this->getEventById($eventId);
            if (!$event) {
                $this->showErrorPage("존재하지 않는 행사입니다.", 404);
                return;
            }
            
            // 수정 권한 확인
            $userRole = AuthMiddleware::getUserRole();
            $canEdit = ($userRole === 'ROLE_ADMIN') || ($event['user_id'] == $currentUser['id']);
            
            if (!$canEdit) {
                $this->showErrorPage("수정 권한이 없습니다.", 403);
                return;
            }
            
            // 이벤트 강사 정보 조회
            $instructors = $this->getEventInstructors($eventId);
            
            // 이벤트 이미지 조회
            $eventImages = $this->getEventImages($eventId);
            
            // 뷰에 전달할 데이터
            $data = [
                'page_title' => '행사 수정',
                'page_description' => '행사 정보를 수정합니다.',
                'event' => $event,
                'instructors' => $instructors,
                'event_images' => $eventImages,
                'current_user' => $currentUser
            ];
            
            $this->render('events/edit', $data);
            
        } catch (Exception $e) {
            error_log("EventController::edit 오류: " . $e->getMessage());
            $this->showErrorPage("행사 수정 페이지를 불러오는 중 오류가 발생했습니다.");
        }
    }
    
    /**
     * 이벤트 업데이트 처리
     */
    public function update($eventId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showErrorPage("잘못된 요청입니다.", 405);
            return;
        }
        
        try {
            // 로그인 확인
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            // 이벤트 정보 조회
            $event = $this->getEventById($eventId);
            if (!$event) {
                ResponseHelper::json(['success' => false, 'message' => '존재하지 않는 행사입니다.'], 404);
                return;
            }
            
            // 수정 권한 확인
            $userRole = AuthMiddleware::getUserRole();
            $canEdit = ($userRole === 'ROLE_ADMIN') || ($event['user_id'] == $currentUser['id']);
            
            if (!$canEdit) {
                ResponseHelper::json(['success' => false, 'message' => '수정 권한이 없습니다.'], 403);
                return;
            }
            
            // 입력 데이터 검증
            $data = $this->validateEventData($_POST);
            
            // 이벤트 업데이트
            $this->updateEvent($eventId, $data);
            
            // 다중 강사 정보 업데이트
            $this->updateMultipleInstructors($eventId, $_POST);
            
            // 이벤트 이미지 처리 (새 이미지 업로드 또는 기존 이미지 삭제 요청이 있을 때)
            error_log("=== 수정 모드 이미지 처리 시작 ===");
            error_log("FILES event_images 존재: " . (!empty($_FILES['event_images']) ? 'YES' : 'NO'));
            error_log("POST remove_images 존재: " . (!empty($_POST['remove_images']) ? 'YES' : 'NO'));
            
            if (!empty($_FILES['event_images']) || !empty($_POST['remove_images'])) {
                error_log("수정 모드에서 이미지 처리 실행");
                $this->processUploadedEventImages($eventId, $_FILES['event_images'] ?? []);
            }
            
            // 강사 이미지 처리 (새 이미지 업로드가 있을 때만)
            if (!empty($_FILES['instructor_images'])) {
                $this->processInstructorImages($eventId, $_FILES['instructor_images']);
            }
            
            // 성공 메시지 설정
            $_SESSION['success_message'] = '행사가 성공적으로 수정되었습니다.';
            
            // 행사 상세 페이지로 리다이렉트
            header('Location: /events/detail?id=' . $eventId);
            exit;
            
        } catch (Exception $e) {
            error_log("EventController::update 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '행사 수정 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 이벤트 삭제 처리
     */
    public function delete($eventId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ResponseHelper::json(['success' => false, 'message' => '잘못된 요청입니다.'], 405);
            return;
        }
        
        try {
            // 로그인 확인
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) {
                ResponseHelper::json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
                return;
            }
            
            // 이벤트 정보 조회
            $event = $this->getEventById($eventId);
            if (!$event) {
                ResponseHelper::json(['success' => false, 'message' => '존재하지 않는 행사입니다.'], 404);
                return;
            }
            
            // 삭제 권한 확인
            $userRole = AuthMiddleware::getUserRole();
            $canDelete = ($userRole === 'ROLE_ADMIN') || ($event['user_id'] == $currentUser['id']);
            
            if (!$canDelete) {
                ResponseHelper::json(['success' => false, 'message' => '삭제 권한이 없습니다.'], 403);
                return;
            }
            
            // CSRF 토큰 확인
            $inputData = json_decode(file_get_contents('php://input'), true);
            if (!$this->validateCsrfToken($inputData['csrf_token'] ?? '')) {
                ResponseHelper::json(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.'], 400);
                return;
            }
            
            // 삭제 확인
            if (!isset($inputData['confirm_delete']) || !$inputData['confirm_delete']) {
                ResponseHelper::json(['success' => false, 'message' => '삭제 확인이 필요합니다.'], 400);
                return;
            }
            
            // 트랜잭션 시작
            $this->db->beginTransaction();
            
            try {
                // 물리적 파일 삭제 (데이터베이스 삭제 전에 수행)
                $this->deleteEventFiles($eventId);
                
                // 관련 데이터 삭제 (이제 파일 삭제 제외)
                $this->deleteEventRelatedData($eventId);
                
                // 이벤트 삭제
                $sql = "DELETE FROM lectures WHERE id = ? AND content_type = 'event'";
                $this->db->execute($sql, [$eventId]);
                
                $this->db->commit();
                
                ResponseHelper::json(['success' => true, 'message' => '행사가 성공적으로 삭제되었습니다.']);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("EventController::delete 오류: " . $e->getMessage());
            ResponseHelper::json(['success' => false, 'message' => '행사 삭제 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 이벤트 업데이트 (데이터베이스)
     */
    private function updateEvent($eventId, $data) {
        $sql = "UPDATE lectures SET 
                title = ?, description = ?, start_date = ?, end_date = ?, 
                start_time = ?, end_time = ?, location_type = ?, venue_name = ?, 
                venue_address = ?, online_link = ?, max_participants = ?, 
                registration_fee = ?, category = ?, instructor_name = ?, 
                instructor_info = ?, youtube_video = ?, registration_deadline = ?, 
                allow_online_registration = ?, updated_at = NOW()
                WHERE id = ? AND content_type = 'event'";
        
        $this->db->execute($sql, [
            $data['title'],
            $data['description'],
            $data['start_date'],
            $data['end_date'] ?? null,
            $data['start_time'],
            $data['end_time'] ?? null,
            $data['location_type'],
            $data['venue_name'] ?? null,
            $data['venue_address'] ?? null,
            $data['online_link'] ?? null,
            $data['max_participants'] ?? null,
            $data['registration_fee'] ?? null,
            $data['category'],
            $data['instructor_name'] ?? '',
            $data['instructor_info'] ?? '',
            $data['youtube_video'] ?? null,
            $data['registration_deadline'] ?? null,
            $data['allow_online_registration'] ?? 1,
            $eventId
        ]);
    }
    
    /**
     * 다중 강사 정보 업데이트 (개별 업데이트 방식)
     */
    private function updateMultipleInstructors($eventId, $postData) {
        try {
            // 기존 강사 정보 조회
            $existingInstructors = $this->db->fetchAll("SELECT * FROM lecture_instructors WHERE lecture_id = ? ORDER BY id", [$eventId]);
            
            // 강사 정보가 실제로 변경된 경우에만 업데이트
            $hasValidInstructorData = isset($postData['instructor_names']) && 
                                    is_array($postData['instructor_names']) && 
                                    count($postData['instructor_names']) > 0 && 
                                    !empty(array_filter($postData['instructor_names']));
                                    
            if ($hasValidInstructorData) {
                error_log("updateMultipleInstructors: 개별 강사 정보 업데이트 시작");
                
                $instructorNames = $postData['instructor_names'];
                $instructorInfos = $postData['instructor_infos'] ?? [];
                
                // 각 강사 정보 개별 처리
                for ($i = 0; $i < count($instructorNames); $i++) {
                    $name = trim($instructorNames[$i]);
                    $info = trim($instructorInfos[$i] ?? '');
                    
                    // 빈 이름은 스킵
                    if (empty($name)) {
                        continue;
                    }
                    
                    // 기존 강사가 있으면 업데이트, 없으면 새로 생성
                    if (isset($existingInstructors[$i])) {
                        // 기존 강사 업데이트
                        $existingInstructor = $existingInstructors[$i];
                        $currentImage = $existingInstructor['instructor_image'];
                        
                        // 새 이미지가 업로드되었는지 확인
                        $newImagePath = $this->handleSingleInstructorImage($eventId, $i, $currentImage);
                        
                        $sql = "UPDATE lecture_instructors SET 
                                instructor_name = ?, 
                                instructor_info = ?, 
                                instructor_image = ?
                                WHERE id = ?";
                        
                        $this->db->execute($sql, [
                            $name,
                            $info,
                            $newImagePath, // 새 이미지가 있으면 새 경로, 없으면 기존 경로
                            $existingInstructor['id']
                        ]);
                        
                        error_log("updateMultipleInstructors: 강사 ID " . $existingInstructor['id'] . " 업데이트 완료");
                    } else {
                        // 새 강사 생성
                        $newImagePath = $this->handleSingleInstructorImage($eventId, $i, null);
                        
                        $sql = "INSERT INTO lecture_instructors (lecture_id, instructor_name, instructor_info, instructor_image, created_at) 
                                VALUES (?, ?, ?, ?, NOW())";
                        
                        $this->db->execute($sql, [
                            $eventId,
                            $name,
                            $info,
                            $newImagePath
                        ]);
                        
                        error_log("updateMultipleInstructors: 새 강사 생성 완료");
                    }
                }
                
                // 기존 강사 수보다 적으면 남은 강사 삭제
                if (count($existingInstructors) > count($instructorNames)) {
                    for ($i = count($instructorNames); $i < count($existingInstructors); $i++) {
                        $sql = "DELETE FROM lecture_instructors WHERE id = ?";
                        $this->db->execute($sql, [$existingInstructors[$i]['id']]);
                        error_log("updateMultipleInstructors: 강사 ID " . $existingInstructors[$i]['id'] . " 삭제 완료");
                    }
                }
                
            } else {
                // 강사 정보 변경이 없으면 기존 강사 정보 유지
                error_log("updateMultipleInstructors: 강사 정보 변경 없음, 기존 정보 유지");
            }
            
        } catch (Exception $e) {
            error_log("다중 강사 정보 업데이트 오류: " . $e->getMessage());
        }
    }
    
    /**
     * 개별 강사 이미지 처리
     */
    private function handleSingleInstructorImage($eventId, $instructorIndex, $currentImagePath) {
        // 새 이미지가 업로드되었는지 확인
        if (isset($_FILES['instructor_images']) && 
            isset($_FILES['instructor_images']['name'][$instructorIndex]) && 
            !empty($_FILES['instructor_images']['name'][$instructorIndex]) &&
            $_FILES['instructor_images']['error'][$instructorIndex] === UPLOAD_ERR_OK) {
            
            // 새 이미지 업로드 처리
            $file = [
                'name' => $_FILES['instructor_images']['name'][$instructorIndex],
                'type' => $_FILES['instructor_images']['type'][$instructorIndex],
                'tmp_name' => $_FILES['instructor_images']['tmp_name'][$instructorIndex],
                'error' => $_FILES['instructor_images']['error'][$instructorIndex],
                'size' => $_FILES['instructor_images']['size'][$instructorIndex]
            ];
            
            try {
                // 업로드 디렉토리 생성
                $uploadDir = ROOT_PATH . '/public/assets/uploads/instructors';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // 파일 검증
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file['type'], $allowedTypes)) {
                    error_log("handleSingleInstructorImage: 허용되지 않는 파일 타입 - " . $file['type']);
                    return $currentImagePath; // 기존 이미지 유지
                }
                
                // 파일 크기 검증 (공통 설정 사용: 30MB)
                if (!UploadConfig::validateFileSize($file['size'])) {
                    error_log("handleSingleInstructorImage: 파일 크기 초과 - " . $file['size']);
                    return $currentImagePath; // 기존 이미지 유지
                }
                
                // 고유 파일명 생성
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $uniqueFileName = 'instructor_' . $instructorIndex . '_' . uniqid() . '.' . $fileExtension;
                $uploadPath = $uploadDir . '/' . $uniqueFileName;
                $webPath = '/assets/uploads/instructors/' . $uniqueFileName;
                
                // 파일 이동
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    // 기존 이미지 파일 삭제
                    if ($currentImagePath && file_exists(ROOT_PATH . '/public' . $currentImagePath)) {
                        unlink(ROOT_PATH . '/public' . $currentImagePath);
                    }
                    
                    error_log("handleSingleInstructorImage: 새 이미지 업로드 성공 - " . $webPath);
                    return $webPath;
                } else {
                    error_log("handleSingleInstructorImage: 파일 이동 실패");
                    return $currentImagePath; // 기존 이미지 유지
                }
                
            } catch (Exception $e) {
                error_log("handleSingleInstructorImage: 이미지 처리 오류 - " . $e->getMessage());
                return $currentImagePath; // 기존 이미지 유지
            }
        }
        
        // 새 이미지가 없으면 기존 이미지 경로 반환
        return $currentImagePath;
    }
    
    /**
     * 이벤트 관련 데이터 삭제 (데이터베이스 레코드만)
     */
    private function deleteEventRelatedData($eventId) {
        // 강사 정보 삭제
        $sql = "DELETE FROM lecture_instructors WHERE lecture_id = ?";
        $this->db->execute($sql, [$eventId]);
        
        // 이벤트 이미지 삭제
        $sql = "DELETE FROM event_images WHERE event_id = ?";
        $this->db->execute($sql, [$eventId]);
        
        // 강의 신청 정보 삭제 (필요시)
        $sql = "DELETE FROM lecture_registrations WHERE lecture_id = ?";
        $this->db->execute($sql, [$eventId]);
    }
    
    /**
     * 이벤트 관련 파일 삭제
     */
    private function deleteEventFiles($eventId) {
        try {
            error_log("=== deleteEventFiles 시작: eventId = $eventId ===");
            
            // 이벤트 이미지 파일들 조회
            $sql = "SELECT image_path FROM event_images WHERE event_id = ?";
            $imageFiles = $this->db->fetchAll($sql, [$eventId]);
            
            error_log("조회된 이미지 파일 수: " . count($imageFiles));
            
            // 각 이미지 파일 삭제
            foreach ($imageFiles as $imageFile) {
                $filePath = ROOT_PATH . '/public' . $imageFile['image_path'];
                error_log("파일 삭제 시도: " . $filePath);
                if (file_exists($filePath)) {
                    unlink($filePath);
                    error_log("✅ 삭제된 이미지 파일: " . $filePath);
                } else {
                    error_log("❌ 파일이 존재하지 않음: " . $filePath);
                }
            }
            
            // 이벤트 메인 정보에서 파일 경로 조회
            $sql = "SELECT instructor_image, banner_image, attachments FROM lectures WHERE id = ? AND content_type = 'event'";
            $eventFiles = $this->db->fetch($sql, [$eventId]);
            
            if ($eventFiles) {
                // 강사 이미지 삭제
                if (!empty($eventFiles['instructor_image'])) {
                    $filePath = ROOT_PATH . '/public' . $eventFiles['instructor_image'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                        error_log("삭제된 강사 이미지: " . $filePath);
                    }
                }
                
                // 배너 이미지 삭제
                if (!empty($eventFiles['banner_image'])) {
                    $filePath = ROOT_PATH . '/public' . $eventFiles['banner_image'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                        error_log("삭제된 배너 이미지: " . $filePath);
                    }
                }
                
                // 첨부파일 삭제
                if (!empty($eventFiles['attachments'])) {
                    $attachments = json_decode($eventFiles['attachments'], true);
                    if (is_array($attachments)) {
                        foreach ($attachments as $attachment) {
                            if (isset($attachment['file_path'])) {
                                $filePath = ROOT_PATH . '/public' . $attachment['file_path'];
                                if (file_exists($filePath)) {
                                    unlink($filePath);
                                    error_log("삭제된 첨부파일: " . $filePath);
                                }
                            }
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("이벤트 파일 삭제 오류: " . $e->getMessage());
            // 파일 삭제 실패는 전체 삭제 프로세스를 중단하지 않음
        }
    }
    
    // ========================================
    // 이벤트 등록 시스템 (event_registrations 테이블 사용)
    // ========================================
    
    /**
     * 이벤트 등록 상태 확인 API
     */
    public function registrationStatus() {
        if (!AuthMiddleware::isLoggedIn()) {
            ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            return;
        }
        
        $eventId = $_GET['event_id'] ?? $_POST['event_id'] ?? null;
        $userId = AuthMiddleware::getCurrentUserId();
        
        if (!$eventId) {
            ResponseHelper::json(null, 400, '이벤트 ID가 필요합니다.');
            return;
        }
        
        try {
            // 기존 등록 확인
            $sql = "SELECT * FROM event_registrations WHERE event_id = ? AND user_id = ?";
            $registration = $this->db->fetch($sql, [$eventId, $userId]);
            
            if ($registration) {
                ResponseHelper::json(['registration' => $registration], 200, '등록 상태를 확인했습니다.');
            } else {
                ResponseHelper::json(['registration' => null], 200, '등록 정보가 없습니다.');
            }
        } catch (Exception $e) {
            error_log("이벤트 등록 상태 확인 오류: " . $e->getMessage());
            ResponseHelper::json(null, 500, '등록 상태 확인 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 이벤트 등록 API
     */
    public function registerEvent() {
        if (!AuthMiddleware::isLoggedIn()) {
            ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            return;
        }
        
        $eventId = $_GET['event_id'] ?? $_POST['event_id'] ?? null;
        $userId = AuthMiddleware::getCurrentUserId();
        
        if (!$eventId) {
            ResponseHelper::json(null, 400, '이벤트 ID가 필요합니다.');
            return;
        }
        
        // POST 데이터 받기
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // CSRF 토큰 확인
        $csrfToken = $input['csrf_token'] ?? '';
        if (!$csrfToken || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            ResponseHelper::json(null, 403, 'CSRF 토큰이 유효하지 않습니다.');
            return;
        }
        
        try {
            // 이벤트 존재 확인
            $event = $this->getEventByIdForEdit($eventId);
            if (!$event) {
                ResponseHelper::json(null, 404, '존재하지 않는 이벤트입니다.');
                return;
            }
            
            // 등록 마감일 확인
            if (!empty($event['registration_deadline'])) {
                $now = new DateTime();
                $deadline = new DateTime($event['registration_deadline']);
                
                if ($now > $deadline) {
                    ResponseHelper::json(null, 400, '등록 마감일이 지났습니다.');
                    return;
                }
            }
            
            // 이벤트 생성자는 등록 불가
            if ($event['user_id'] == $userId) {
                ResponseHelper::json(null, 400, '본인이 생성한 이벤트에는 등록할 수 없습니다.');
                return;
            }
            
            // 중복 등록 확인
            $existingQuery = "SELECT id, status FROM event_registrations WHERE event_id = ? AND user_id = ?";
            $existing = $this->db->fetch($existingQuery, [$eventId, $userId]);
            
            if ($existing) {
                if (in_array($existing['status'], ['cancelled', 'rejected'])) {
                    // 취소된 등록 또는 거절된 등록 삭제 (다시 신청 가능)
                    $deleteQuery = "DELETE FROM event_registrations WHERE id = ?";
                    $this->db->execute($deleteQuery, [$existing['id']]);
                } else {
                    ResponseHelper::json(null, 400, '이미 등록된 이벤트입니다.');
                    return;
                }
            }
            
            // 입력값 검증
            $required = ['participant_name', 'participant_email', 'participant_phone'];
            $errors = [];
            
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    $errors[$field] = '필수 항목입니다.';
                }
            }
            
            if (!empty($errors)) {
                ResponseHelper::json(['errors' => $errors], 400, '입력 정보를 확인해주세요.');
                return;
            }
            
            // 이메일 형식 검증
            if (!filter_var($input['participant_email'], FILTER_VALIDATE_EMAIL)) {
                $errors['participant_email'] = '올바른 이메일 형식이 아닙니다.';
            }
            
            // 전화번호 형식 검증
            if (!preg_match('/^[0-9-+\s()]+$/', $input['participant_phone'])) {
                $errors['participant_phone'] = '올바른 전화번호 형식이 아닙니다.';
            }
            
            if (!empty($errors)) {
                ResponseHelper::json(['errors' => $errors], 400, '입력 정보를 확인해주세요.');
                return;
            }
            
            // 정원 및 대기열 확인
            $approvedCount = $this->getApprovedRegistrationCount($eventId);
            $maxParticipants = $event['max_participants'] ?? 0;
            
            $status = 'pending';
            $isWaitingList = 0;
            $waitingOrder = null;
            
            if ($maxParticipants > 0 && $approvedCount >= $maxParticipants) {
                // 정원 초과 - 대기열 처리
                $status = 'waiting';
                $isWaitingList = 1;
                $waitingOrder = $this->getNextWaitingOrder($eventId);
            }
            
            // 등록 데이터 준비
            $registrationData = [
                'event_id' => $eventId,
                'user_id' => $userId,
                'participant_name' => trim($input['participant_name']),
                'participant_email' => trim($input['participant_email']),
                'participant_phone' => trim($input['participant_phone']),
                'company_name' => trim($input['company_name'] ?? ''),
                'position' => trim($input['position'] ?? ''),
                'motivation' => trim($input['motivation'] ?? ''),
                'special_requests' => trim($input['special_requests'] ?? ''),
                'how_did_you_know' => trim($input['how_did_you_know'] ?? ''),
                'status' => $status,
                'is_waiting_list' => $isWaitingList,
                'waiting_order' => $waitingOrder
            ];
            
            // 등록 실행
            $insertQuery = "
                INSERT INTO event_registrations 
                (event_id, user_id, participant_name, participant_email, participant_phone,
                 company_name, position, motivation, special_requests, how_did_you_know,
                 status, is_waiting_list, waiting_order, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";
            
            $this->db->execute($insertQuery, [
                $registrationData['event_id'],
                $registrationData['user_id'],
                $registrationData['participant_name'],
                $registrationData['participant_email'],
                $registrationData['participant_phone'],
                $registrationData['company_name'],
                $registrationData['position'],
                $registrationData['motivation'],
                $registrationData['special_requests'],
                $registrationData['how_did_you_know'],
                $registrationData['status'],
                $registrationData['is_waiting_list'],
                $registrationData['waiting_order']
            ]);
            
            $registrationId = $this->db->lastInsertId();
            
            // 행사 주최자에게 Firebase 실시간 알림 발송
            try {
                $this->updateEventOrganizerNotification($eventId);
            } catch (Exception $e) {
                error_log("Firebase 실시간 알림 업데이트 오류: " . $e->getMessage());
            }
            
            // 행사 신청 확인 SMS 발송
            try {
                require_once SRC_PATH . '/helpers/SmsHelper.php';
                $smsResult = sendEventApplicationSms($registrationData['participant_phone']);
                if ($smsResult['success']) {
                    error_log("행사 신청 확인 SMS 발송 성공: " . $registrationData['participant_phone']);
                } else {
                    error_log("행사 신청 확인 SMS 발송 실패: " . $smsResult['message']);
                }
            } catch (Exception $e) {
                error_log("SMS 발송 오류: " . $e->getMessage());
                // SMS 실패는 전체 프로세스를 중단하지 않음
            }
            
            // 등록 완료 데이터 반환
            $registrationData['id'] = $registrationId;
            $registrationData['registration_date'] = date('Y-m-d H:i:s');
            
            // 상태별 메시지
            switch($status) {
                case 'pending':
                    $message = '이벤트 등록이 완료되었습니다. 승인을 기다리고 있습니다.';
                    break;
                case 'waiting':
                    $message = "이벤트 등록이 완료되었습니다. 대기순번: {$waitingOrder}번";
                    break;
                default:
                    $message = '이벤트 등록이 완료되었습니다.';
                    break;
            }
            
            ResponseHelper::json($registrationData, 200, $message);
            
        } catch (Exception $e) {
            error_log("이벤트 등록 오류: " . $e->getMessage());
            ResponseHelper::json(null, 500, '이벤트 등록 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 이벤트 등록 취소 API
     */
    public function cancelEventRegistration() {
        if (!AuthMiddleware::isLoggedIn()) {
            ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            return;
        }
        
        $eventId = $_GET['event_id'] ?? $_POST['event_id'] ?? null;
        $userId = AuthMiddleware::getCurrentUserId();
        
        if (!$eventId) {
            ResponseHelper::json(null, 400, '이벤트 ID가 필요합니다.');
            return;
        }
        
        // DELETE 요청 데이터 받기
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // CSRF 토큰 확인
        $csrfToken = $input['csrf_token'] ?? '';
        if (!$csrfToken || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            ResponseHelper::json(null, 403, 'CSRF 토큰이 유효하지 않습니다.');
            return;
        }
        
        try {
            // 기존 등록 확인
            $sql = "SELECT * FROM event_registrations WHERE event_id = ? AND user_id = ? AND status != 'cancelled'";
            $registration = $this->db->fetch($sql, [$eventId, $userId]);
            
            if (!$registration) {
                ResponseHelper::json(null, 404, '등록 정보를 찾을 수 없습니다.');
                return;
            }
            
            // 등록 취소 처리
            $updateQuery = "UPDATE event_registrations SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
            $this->db->execute($updateQuery, [$registration['id']]);
            
            // 대기열 순번 재정렬
            if ($registration['status'] === 'approved') {
                $this->reorderWaitingList($eventId);
            }
            
            ResponseHelper::json(null, 200, '이벤트 등록이 취소되었습니다.');
            
        } catch (Exception $e) {
            error_log("이벤트 등록 취소 오류: " . $e->getMessage());
            ResponseHelper::json(null, 500, '등록 취소 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 승인된 등록 수 조회
     */
    private function getApprovedRegistrationCount($eventId) {
        $sql = "SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ? AND status = 'approved'";
        $result = $this->db->fetch($sql, [$eventId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * 다음 대기 순번 조회
     */
    private function getNextWaitingOrder($eventId) {
        $sql = "SELECT MAX(waiting_order) as max_order FROM event_registrations WHERE event_id = ? AND is_waiting_list = 1";
        $result = $this->db->fetch($sql, [$eventId]);
        return ($result['max_order'] ?? 0) + 1;
    }
    
    /**
     * 대기열 순번 재정렬
     */
    private function reorderWaitingList($eventId) {
        // 대기열에서 다음 순번 승인 처리
        $sql = "SELECT * FROM event_registrations WHERE event_id = ? AND status = 'waiting' ORDER BY waiting_order ASC LIMIT 1";
        $nextWaiting = $this->db->fetch($sql, [$eventId]);
        
        if ($nextWaiting) {
            $updateQuery = "UPDATE event_registrations SET status = 'approved', is_waiting_list = 0, waiting_order = NULL WHERE id = ?";
            $this->db->execute($updateQuery, [$nextWaiting['id']]);
        }
    }
    
    /**
     * 행사 주최자에게 Firebase 실시간 알림 업데이트
     */
    private function updateEventOrganizerNotification($eventId) {
        try {
            // 행사 주최자 정보 조회
            $lectureQuery = "SELECT user_id FROM lectures WHERE id = ? AND content_type = 'event'";
            $lecture = $this->db->fetch($lectureQuery, [$eventId]);
            
            if (!$lecture) {
                error_log("행사 정보를 찾을 수 없음: Event ID {$eventId}");
                return;
            }
            
            $organizerId = $lecture['user_id'];
            
            // 주최자가 기업 회원인지 확인
            $userQuery = "SELECT role FROM users WHERE id = ?";
            $user = $this->db->fetch($userQuery, [$organizerId]);
            
            if (!$user || $user['role'] !== 'ROLE_CORP') {
                // 기업 회원이 아니면 알림 불필요
                return;
            }
            
            // 주최자의 현재 대기 신청 수 계산
            $pendingData = FirebaseHelper::calculatePendingCount($organizerId);
            
            // Firebase 실시간 알림 업데이트
            $result = FirebaseHelper::updatePendingNotification(
                $organizerId,
                $pendingData['count'],
                $pendingData['details']
            );
            
            if ($result) {
                error_log("Firebase 실시간 알림 업데이트 성공 - 행사 주최자: {$organizerId}, 대기 건수: {$pendingData['count']}");
            } else {
                error_log("Firebase 실시간 알림 업데이트 실패 - 행사 주최자: {$organizerId}");
            }
            
        } catch (Exception $e) {
            error_log("행사 주최자 Firebase 알림 업데이트 오류: " . $e->getMessage());
            throw $e;
        }
    }
    
}