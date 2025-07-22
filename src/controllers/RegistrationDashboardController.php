<?php
/**
 * 기업 신청 관리 대시보드 컨트롤러
 */

require_once SRC_PATH . '/controllers/BaseController.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';
require_once SRC_PATH . '/services/EmailService.php';

class RegistrationDashboardController extends BaseController
{
    /**
     * 대시보드 메인 페이지
     */
    public function index()
    {
        // 로그인 및 기업 권한 확인
        if (!AuthMiddleware::isLoggedIn()) {
            header('Location: /auth/login');
            exit;
        }
        
        $userRole = AuthMiddleware::getUserRole();
        // 관리자이거나 기업 회원이거나 일반 사용자(강의 생성자)라면 접근 허용
        if ($userRole !== 'ROLE_CORP' && $userRole !== 'ROLE_ADMIN' && $userRole !== 'ROLE_USER') {
            header('HTTP/1.1 403 Forbidden');
            include SRC_PATH . '/views/errors/403.php';
            exit;
        }
        
        $userId = AuthMiddleware::getCurrentUserId();
        
        try {
            // 날짜 필터 파라미터 처리
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $contentType = $_GET['type'] ?? 'lecture'; // 새로운 파라미터: lecture | event
            
            // 기본값: 최근 1개월
            if (!$startDate || !$endDate) {
                $startDate = date('Y-m-d', strtotime('-1 month'));
                $endDate = date('Y-m-d');
            }
            
            // 내 강의/행사 목록 조회 (날짜 필터 및 컨텐츠 타입 적용)
            // content_type에 따라 적절한 테이블 사용
            if ($contentType === 'event') {
                // 행사의 경우 event_registrations 테이블 사용
                $lecturesQuery = "
                    SELECT 
                        l.id, l.title, l.start_date, l.start_time, l.end_date, l.end_time,
                        l.max_participants, l.auto_approval,
                        l.registration_end_date, l.content_type, l.location_type,
                        COUNT(DISTINCT er.id) as total_applications,
                        COUNT(DISTINCT CASE WHEN er.status = 'pending' THEN er.id END) as pending_count,
                        COUNT(DISTINCT CASE WHEN er.status = 'approved' THEN er.id END) as approved_count,
                        COUNT(DISTINCT CASE WHEN er.status = 'approved' THEN er.id END) as current_participants,
                        COUNT(DISTINCT CASE WHEN er.status = 'rejected' THEN er.id END) as rejected_count,
                        COUNT(DISTINCT CASE WHEN er.status = 'waiting' THEN er.id END) as waiting_count
                    FROM lectures l
                    LEFT JOIN event_registrations er ON l.id = er.event_id
                    WHERE l.user_id = ? AND l.status = 'published' 
                    AND l.content_type = ?
                    AND l.start_date >= ? AND l.start_date <= ?
                    GROUP BY l.id, l.title, l.start_date, l.start_time, l.end_date, l.end_time,
                             l.max_participants, l.auto_approval, l.registration_end_date,
                             l.content_type, l.location_type
                    ORDER BY l.start_date DESC, l.created_at DESC
                ";
            } else {
                // 강의의 경우 lecture_registrations 테이블 사용
                $lecturesQuery = "
                    SELECT 
                        l.id, l.title, l.start_date, l.start_time, l.end_date, l.end_time,
                        l.max_participants, l.auto_approval,
                        l.registration_end_date, l.content_type, l.location_type,
                        COUNT(DISTINCT lr.id) as total_applications,
                        COUNT(DISTINCT CASE WHEN lr.status = 'pending' THEN lr.id END) as pending_count,
                        COUNT(DISTINCT CASE WHEN lr.status = 'approved' THEN lr.id END) as approved_count,
                        COUNT(DISTINCT CASE WHEN lr.status = 'approved' THEN lr.id END) as current_participants,
                        COUNT(DISTINCT CASE WHEN lr.status = 'rejected' THEN lr.id END) as rejected_count,
                        COUNT(DISTINCT CASE WHEN lr.status = 'waiting' THEN lr.id END) as waiting_count
                    FROM lectures l
                    LEFT JOIN lecture_registrations lr ON l.id = lr.lecture_id
                    WHERE l.user_id = ? AND l.status = 'published' 
                    AND l.content_type = ?
                    AND l.start_date >= ? AND l.start_date <= ?
                    GROUP BY l.id, l.title, l.start_date, l.start_time, l.end_date, l.end_time,
                             l.max_participants, l.auto_approval, l.registration_end_date,
                             l.content_type, l.location_type
                    ORDER BY l.start_date DESC, l.created_at DESC
                ";
            }
            
            $lectures = $this->db->fetchAll($lecturesQuery, [$userId, $contentType, $startDate, $endDate]);
            
            // 각 강의/행사의 신청 상태 계산
            foreach ($lectures as &$lecture) {
                $lecture['registration_status'] = $this->calculateRegistrationStatus($lecture);
            }
            
            // 대시보드 통계 계산 (컨텐츠 타입별)
            $stats = $this->getDashboardStats($userId, $contentType, $startDate, $endDate);
            
            // 최근 신청 목록 (최근 20개, 컨텐츠 타입별)
            if ($contentType === 'event') {
                // 행사의 경우 event_registrations 테이블 사용
                $recentRegistrationsQuery = "
                    SELECT 
                        r.id, r.participant_name, r.participant_email, r.status,
                        r.created_at, r.is_waiting_list, r.waiting_order,
                        l.title as lecture_title, l.id as lecture_id, l.content_type
                    FROM event_registrations r
                    JOIN lectures l ON r.event_id = l.id
                    WHERE l.user_id = ? AND l.content_type = ?
                    ORDER BY r.created_at DESC
                    LIMIT 20
                ";
            } else {
                // 강의의 경우 lecture_registrations 테이블 사용
                $recentRegistrationsQuery = "
                    SELECT 
                        r.id, r.participant_name, r.participant_email, r.status,
                        r.created_at, r.is_waiting_list, r.waiting_order,
                        l.title as lecture_title, l.id as lecture_id, l.content_type
                    FROM lecture_registrations r
                    JOIN lectures l ON r.lecture_id = l.id
                    WHERE l.user_id = ? AND l.content_type = ?
                    ORDER BY r.created_at DESC
                    LIMIT 20
                ";
            }
            
            $recentRegistrations = $this->db->fetchAll($recentRegistrationsQuery, [$userId, $contentType]);
            
            // 뷰 렌더링
            $pageTitle = '신청 관리 대시보드';
            $pageDescription = ($contentType === 'event' ? '행사' : '강의') . ' 신청 현황을 한눈에 확인하고 관리하세요.';
            
            include SRC_PATH . '/views/templates/header.php';
            include SRC_PATH . '/views/registrations/dashboard.php';
            include SRC_PATH . '/views/templates/footer.php';
            
        } catch (Exception $e) {
            error_log("대시보드 오류: " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            include SRC_PATH . '/views/errors/500.php';
        }
    }
    
    /**
     * 특정 강의/행사의 신청자 관리 페이지
     */
    public function lectureRegistrations($lectureId)
    {
        // 로그인 및 권한 확인
        if (!AuthMiddleware::isLoggedIn()) {
            header('Location: /auth/login');
            exit;
        }
        
        $userId = AuthMiddleware::getCurrentUserId();
        $userRole = AuthMiddleware::getUserRole();
        
        // 강의/행사 정보 및 권한 확인
        $lectureQuery = "
            SELECT 
                l.id, l.title, l.description, l.start_date, l.start_time, l.end_date, l.end_time,
                l.max_participants, l.current_participants, l.auto_approval,
                l.registration_start_date, l.registration_end_date, l.allow_waiting_list,
                l.content_type, l.user_id as organizer_id, u.nickname as organizer_name
            FROM lectures l
            JOIN users u ON l.user_id = u.id
            WHERE l.id = ? AND l.status = 'published'
        ";
        
        $lecture = $this->db->fetch($lectureQuery, [$lectureId]);
        
        if (!$lecture) {
            header('HTTP/1.1 404 Not Found');
            include SRC_PATH . '/views/errors/404.php';
            exit;
        }
        
        // 권한 확인 (본인 강의/행사이거나 관리자)
        if ($userRole !== 'ROLE_ADMIN' && $lecture['organizer_id'] != $userId) {
            header('HTTP/1.1 403 Forbidden');
            include SRC_PATH . '/views/errors/403.php';
            exit;
        }
        
        try {
            // 페이징 처리
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            
            // 필터링 옵션
            $statusFilter = $_GET['status'] ?? '';
            $searchQuery = trim($_GET['search'] ?? '');
            
            // 신청자 목록 조회 (content_type에 따라 적절한 테이블 사용)
            $registrations = $this->getRegistrations($lectureId, $statusFilter, $searchQuery, $offset, $perPage, $lecture['content_type']);
            $totalCount = $this->getRegistrationsCount($lectureId, $statusFilter, $searchQuery, $lecture['content_type']);
            $totalPages = ceil($totalCount / $perPage);
            
            // 통계 정보
            $lectureStats = $this->getLectureStats($lectureId, $lecture['content_type']);
            
            // 뷰 렌더링
            $contentTypeName = $lecture['content_type'] === 'event' ? '행사' : '강의';
            $pageTitle = $lecture['title'] . ' - 신청자 관리';
            $pageDescription = $contentTypeName . ' 신청자 목록을 확인하고 승인/거절을 관리하세요.';
            
            include SRC_PATH . '/views/templates/header.php';
            include SRC_PATH . '/views/registrations/lecture-detail.php';
            include SRC_PATH . '/views/templates/footer.php';
            
        } catch (Exception $e) {
            error_log("강의 신청자 관리 오류: " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            include SRC_PATH . '/views/errors/500.php';
        }
    }
    
    /**
     * 신청 승인/거절 처리 API
     */
    public function updateRegistrationStatus($registrationId)
    {
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
            $userRole = AuthMiddleware::getUserRole();
            
            // JSON 데이터 파싱
            $input = json_decode(file_get_contents('php://input'), true);
            
            // CSRF 토큰 검증
            if (!$this->validateCsrfToken($input['csrf_token'] ?? '')) {
                return ResponseHelper::json(null, 403, 'CSRF 토큰이 유효하지 않습니다.');
            }
            
            $newStatus = $input['status'] ?? '';
            $adminNotes = trim($input['admin_notes'] ?? '');
            
            // 상태 검증
            $validStatuses = ['approved', 'rejected'];
            if (!in_array($newStatus, $validStatuses)) {
                return ResponseHelper::json(null, 400, '올바른 상태를 선택해주세요.');
            }
            
            // 🔥 Ultra Think Mode: 듀얼 테이블 지원 - 신청 정보 및 권한 확인
            $registration = null;
            $registrationTable = null;
            $lectureIdField = null;
            
            // 먼저 event_registrations 테이블에서 확인
            $eventRegistrationQuery = "
                SELECT r.*, l.user_id as lecture_organizer, l.title as lecture_title,
                       l.max_participants, l.current_participants, l.start_date, l.start_time, 
                       l.content_type, r.event_id as lecture_id
                FROM event_registrations r
                JOIN lectures l ON r.event_id = l.id
                WHERE r.id = ?
            ";
            
            $registration = $this->db->fetch($eventRegistrationQuery, [$registrationId]);
            
            if ($registration) {
                $registrationTable = 'event_registrations';
                $lectureIdField = 'event_id';
            } else {
                // event_registrations에 없으면 lecture_registrations에서 확인
                $lectureRegistrationQuery = "
                    SELECT r.*, l.user_id as lecture_organizer, l.title as lecture_title,
                           l.max_participants, l.current_participants, l.start_date, l.start_time, 
                           l.content_type, r.lecture_id
                    FROM lecture_registrations r
                    JOIN lectures l ON r.lecture_id = l.id
                    WHERE r.id = ?
                ";
                
                $registration = $this->db->fetch($lectureRegistrationQuery, [$registrationId]);
                
                if ($registration) {
                    $registrationTable = 'lecture_registrations';
                    $lectureIdField = 'lecture_id';
                }
            }
            
            if (!$registration) {
                return ResponseHelper::json(null, 404, '신청을 찾을 수 없습니다.');
            }
            
            // 로그 기록
            error_log("🔥 Ultra Think: Registration ID {$registrationId} found in {$registrationTable} table");
            
            // 권한 확인
            if ($userRole !== 'ROLE_ADMIN' && $registration['lecture_organizer'] != $userId) {
                return ResponseHelper::json(null, 403, '권한이 없습니다.');
            }
            
            // 이미 처리된 신청인지 확인
            if (in_array($registration['status'], ['approved', 'rejected'])) {
                return ResponseHelper::json(null, 400, '이미 처리된 신청입니다.');
            }
            
            // 승인 시 정원 확인
            if ($newStatus === 'approved') {
                $maxParticipants = $registration['max_participants'];
                $currentParticipants = $registration['current_participants'];
                
                if ($maxParticipants && $currentParticipants >= $maxParticipants) {
                    return ResponseHelper::json(null, 400, '정원이 초과되었습니다.');
                }
            }
            
            // 🔥 Ultra Think Mode: 듀얼 테이블 지원 - 상태 업데이트
            $updateQuery = "
                UPDATE {$registrationTable}
                SET status = ?, admin_notes = ?, processed_by = ?, processed_at = NOW()
                WHERE id = ?
            ";
            
            error_log("🔥 Ultra Think: Updating status in {$registrationTable} for registration ID {$registrationId}");
            
            $result = $this->db->execute($updateQuery, [$newStatus, $adminNotes, $userId, $registrationId]);
            
            if ($result) {
                // SMS 알림 발송 (이메일 대신)
                try {
                    require_once SRC_PATH . '/helpers/SmsHelper.php';
                    
                    // 컨텐츠 타입에 따른 SMS 발송
                    $contentType = $registration['content_type'] ?? 'lecture';
                    $contentTypeName = $contentType === 'event' ? '행사' : '강의';
                    
                    if ($newStatus === 'approved') {
                        $lectureDate = $registration['start_date'] . ' ' . $registration['start_time'];
                        if ($contentType === 'event') {
                            $smsResult = sendEventApprovalSms($registration['participant_phone'], $registration['lecture_title'], $lectureDate);
                        } else {
                            $smsResult = sendLectureApprovalSms($registration['participant_phone'], $registration['lecture_title'], $lectureDate);
                        }
                        $logMessage = "{$contentTypeName} 신청 승인 SMS 발송";
                    } else {
                        $reason = !empty($adminNotes) ? $adminNotes : '';
                        if ($contentType === 'event') {
                            $smsResult = sendEventRejectionSms($registration['participant_phone'], $registration['lecture_title'], $reason);
                        } else {
                            $smsResult = sendLectureRejectionSms($registration['participant_phone'], $registration['lecture_title'], $reason);
                        }
                        $logMessage = "{$contentTypeName} 신청 거절 SMS 발송";
                    }
                    
                    if ($smsResult['success']) {
                        error_log($logMessage . " 성공: " . $registration['participant_phone']);
                    } else {
                        error_log($logMessage . " 실패: " . $smsResult['message']);
                    }
                    
                } catch (Exception $e) {
                    error_log("상태 변경 SMS 발송 실패: " . $e->getMessage());
                    // SMS 실패는 전체 프로세스를 중단하지 않음
                }
                
                $message = $newStatus === 'approved' ? '신청이 승인되었습니다.' : '신청이 거절되었습니다.';
                
                return ResponseHelper::json([
                    'registration_id' => $registrationId,
                    'new_status' => $newStatus
                ], 200, $message);
            } else {
                return ResponseHelper::json(null, 500, '상태 업데이트에 실패했습니다.');
            }
            
        } catch (Exception $e) {
            error_log("신청 상태 변경 오류: " . $e->getMessage());
            return ResponseHelper::json(null, 500, '처리 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 대시보드 통계 정보 (컨텐츠 타입별)
     */
    private function getDashboardStats($userId, $contentType = 'lecture', $startDate = null, $endDate = null)
    {
        // content_type에 따라 적절한 테이블 사용하여 직접 집계
        if ($contentType === 'event') {
            // 행사의 경우 event_registrations 테이블 사용
            $statsQuery = "
                SELECT 
                    COUNT(DISTINCT l.id) as total_lectures,
                    COUNT(DISTINCT er.id) as total_applications,
                    COUNT(DISTINCT CASE WHEN er.status = 'pending' THEN er.id END) as pending_applications,
                    COUNT(DISTINCT CASE WHEN er.status = 'approved' THEN er.id END) as approved_applications,
                    COUNT(DISTINCT CASE WHEN er.status = 'rejected' THEN er.id END) as rejected_applications
                FROM lectures l
                LEFT JOIN event_registrations er ON l.id = er.event_id
                WHERE l.user_id = ? AND l.status = 'published' AND l.content_type = ?
            ";
        } else {
            // 강의의 경우 lecture_registrations 테이블 사용
            $statsQuery = "
                SELECT 
                    COUNT(DISTINCT l.id) as total_lectures,
                    COUNT(DISTINCT lr.id) as total_applications,
                    COUNT(DISTINCT CASE WHEN lr.status = 'pending' THEN lr.id END) as pending_applications,
                    COUNT(DISTINCT CASE WHEN lr.status = 'approved' THEN lr.id END) as approved_applications,
                    COUNT(DISTINCT CASE WHEN lr.status = 'rejected' THEN lr.id END) as rejected_applications
                FROM lectures l
                LEFT JOIN lecture_registrations lr ON l.id = lr.lecture_id
                WHERE l.user_id = ? AND l.status = 'published' AND l.content_type = ?
            ";
        }
        
        $params = [$userId, $contentType];
        
        // 날짜 필터 추가
        if ($startDate && $endDate) {
            $statsQuery .= " AND l.start_date >= ? AND l.start_date <= ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        return $this->db->fetch($statsQuery, $params);
    }
    
    /**
     * 특정 강의/행사의 신청자 목록 조회
     */
    private function getRegistrations($lectureId, $statusFilter, $searchQuery, $offset, $perPage, $contentType = 'lecture')
    {
        if ($contentType === 'event') {
            // 행사의 경우 event_registrations 테이블 사용
            $whereConditions = ["r.event_id = ?"];
            $params = [$lectureId];
            
            // 상태 필터
            if (!empty($statusFilter)) {
                $whereConditions[] = "r.status = ?";
                $params[] = $statusFilter;
            }
            
            // 검색 쿼리
            if (!empty($searchQuery)) {
                $whereConditions[] = "(r.participant_name LIKE ? OR r.participant_email LIKE ? OR r.company_name LIKE ?)";
                $searchTerm = "%$searchQuery%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = implode(" AND ", $whereConditions);
            
            $query = "
                SELECT 
                    r.id, r.participant_name, r.participant_email, r.participant_phone,
                    r.company_name, r.position, r.motivation, r.special_requests,
                    r.status, r.is_waiting_list, r.waiting_order, r.created_at,
                    r.processed_at, r.admin_notes,
                    processor.nickname as processed_by_name
                FROM event_registrations r
                LEFT JOIN users processor ON r.processed_by = processor.id
                WHERE $whereClause
                ORDER BY 
                    CASE r.status 
                        WHEN 'pending' THEN 1 
                        WHEN 'waiting' THEN 2 
                        WHEN 'approved' THEN 3 
                        WHEN 'rejected' THEN 4 
                        ELSE 5 
                    END,
                    r.created_at DESC
                LIMIT ?, ?
            ";
        } else {
            // 강의의 경우 lecture_registrations 테이블 사용
            $whereConditions = ["r.lecture_id = ?"];
            $params = [$lectureId];
            
            // 상태 필터
            if (!empty($statusFilter)) {
                $whereConditions[] = "r.status = ?";
                $params[] = $statusFilter;
            }
            
            // 검색 쿼리
            if (!empty($searchQuery)) {
                $whereConditions[] = "(r.participant_name LIKE ? OR r.participant_email LIKE ? OR r.company_name LIKE ?)";
                $searchTerm = "%$searchQuery%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = implode(" AND ", $whereConditions);
            
            $query = "
                SELECT 
                    r.id, r.participant_name, r.participant_email, r.participant_phone,
                    r.company_name, r.position, r.motivation, r.special_requests,
                    r.status, r.is_waiting_list, r.waiting_order, r.created_at,
                    r.processed_at, r.admin_notes,
                    processor.nickname as processed_by_name
                FROM lecture_registrations r
                LEFT JOIN users processor ON r.processed_by = processor.id
                WHERE $whereClause
                ORDER BY 
                    CASE r.status 
                        WHEN 'pending' THEN 1 
                        WHEN 'waiting' THEN 2 
                        WHEN 'approved' THEN 3 
                        WHEN 'rejected' THEN 4 
                        ELSE 5 
                    END,
                    r.created_at DESC
                LIMIT ?, ?
            ";
        }
        
        $params[] = $offset;
        $params[] = $perPage;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * 신청자 총 개수 조회
     */
    private function getRegistrationsCount($lectureId, $statusFilter, $searchQuery, $contentType = 'lecture')
    {
        if ($contentType === 'event') {
            // 행사의 경우 event_registrations 테이블 사용
            $whereConditions = ["r.event_id = ?"];
            $params = [$lectureId];
            
            if (!empty($statusFilter)) {
                $whereConditions[] = "r.status = ?";
                $params[] = $statusFilter;
            }
            
            if (!empty($searchQuery)) {
                $whereConditions[] = "(r.participant_name LIKE ? OR r.participant_email LIKE ? OR r.company_name LIKE ?)";
                $searchTerm = "%$searchQuery%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = implode(" AND ", $whereConditions);
            $query = "SELECT COUNT(*) as count FROM event_registrations r WHERE $whereClause";
        } else {
            // 강의의 경우 lecture_registrations 테이블 사용
            $whereConditions = ["r.lecture_id = ?"];
            $params = [$lectureId];
            
            if (!empty($statusFilter)) {
                $whereConditions[] = "r.status = ?";
                $params[] = $statusFilter;
            }
            
            if (!empty($searchQuery)) {
                $whereConditions[] = "(r.participant_name LIKE ? OR r.participant_email LIKE ? OR r.company_name LIKE ?)";
                $searchTerm = "%$searchQuery%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = implode(" AND ", $whereConditions);
            $query = "SELECT COUNT(*) as count FROM lecture_registrations r WHERE $whereClause";
        }
        
        $result = $this->db->fetch($query, $params);
        return $result['count'];
    }
    
    /**
     * 특정 강의/행사의 통계 정보
     */
    private function getLectureStats($lectureId, $contentType = 'lecture')
    {
        // content_type에 따라 적절한 테이블 사용하여 실시간 통계 계산
        if ($contentType === 'event') {
            // 행사의 경우 event_registrations 테이블 사용
            $statsQuery = "
                SELECT 
                    l.id as lecture_id,
                    l.title as lecture_title,
                    l.content_type,
                    l.max_participants,
                    COUNT(DISTINCT er.id) as total_applications,
                    COUNT(DISTINCT CASE WHEN er.status = 'pending' THEN er.id END) as pending_count,
                    COUNT(DISTINCT CASE WHEN er.status = 'approved' THEN er.id END) as approved_count,
                    COUNT(DISTINCT CASE WHEN er.status = 'rejected' THEN er.id END) as rejected_count,
                    COUNT(DISTINCT CASE WHEN er.status = 'cancelled' THEN er.id END) as cancelled_count,
                    COUNT(DISTINCT CASE WHEN er.status = 'attended' THEN er.id END) as attended_count,
                    COUNT(DISTINCT CASE WHEN er.status = 'no_show' THEN er.id END) as no_show_count,
                    COUNT(DISTINCT CASE WHEN er.status = 'waiting' THEN er.id END) as waiting_count,
                    COUNT(DISTINCT CASE WHEN er.status = 'approved' THEN er.id END) as current_participants
                FROM lectures l
                LEFT JOIN event_registrations er ON l.id = er.event_id
                WHERE l.id = ?
                GROUP BY l.id, l.title, l.content_type, l.max_participants
            ";
        } else {
            // 강의의 경우 lecture_registrations 테이블 사용
            $statsQuery = "
                SELECT 
                    l.id as lecture_id,
                    l.title as lecture_title,
                    l.content_type,
                    l.max_participants,
                    COUNT(DISTINCT lr.id) as total_applications,
                    COUNT(DISTINCT CASE WHEN lr.status = 'pending' THEN lr.id END) as pending_count,
                    COUNT(DISTINCT CASE WHEN lr.status = 'approved' THEN lr.id END) as approved_count,
                    COUNT(DISTINCT CASE WHEN lr.status = 'rejected' THEN lr.id END) as rejected_count,
                    COUNT(DISTINCT CASE WHEN lr.status = 'cancelled' THEN lr.id END) as cancelled_count,
                    COUNT(DISTINCT CASE WHEN lr.status = 'attended' THEN lr.id END) as attended_count,
                    COUNT(DISTINCT CASE WHEN lr.status = 'no_show' THEN lr.id END) as no_show_count,
                    COUNT(DISTINCT CASE WHEN lr.status = 'waiting' THEN lr.id END) as waiting_count,
                    COUNT(DISTINCT CASE WHEN lr.status = 'approved' THEN lr.id END) as current_participants
                FROM lectures l
                LEFT JOIN lecture_registrations lr ON l.id = lr.lecture_id
                WHERE l.id = ?
                GROUP BY l.id, l.title, l.content_type, l.max_participants
            ";
        }
        
        $result = $this->db->fetch($statsQuery, [$lectureId]);
        
        // 정원 비율 계산
        if ($result && $result['max_participants'] > 0) {
            $result['capacity_percentage'] = round(($result['current_participants'] / $result['max_participants']) * 100, 2);
        } else {
            $result['capacity_percentage'] = 0;
        }
        
        return $result;
    }
    
    /**
     * 강의/행사의 신청 상태 계산
     */
    private function calculateRegistrationStatus($lecture)
    {
        $now = new DateTime();
        $startDate = new DateTime($lecture['start_date'] . ' ' . $lecture['start_time']);
        $registrationEndDate = $lecture['registration_end_date'] ? new DateTime($lecture['registration_end_date']) : null;
        
        // 행사/강의가 이미 시작됨
        if ($startDate <= $now) {
            return [
                'status' => 'completed',
                'label' => '완료됨',
                'color' => 'gray',
                'icon' => '✅'
            ];
        }
        
        // 신청 마감일이 설정되어 있고 지났음
        if ($registrationEndDate && $registrationEndDate <= $now) {
            return [
                'status' => 'closed',
                'label' => '신청 마감',
                'color' => 'red',
                'icon' => '🔒'
            ];
        }
        
        // 최대 참가자 수가 설정되어 있고 가득참
        if ($lecture['max_participants'] && $lecture['current_participants'] >= $lecture['max_participants']) {
            return [
                'status' => 'full',
                'label' => '정원 마감',
                'color' => 'orange',
                'icon' => '👥'
            ];
        }
        
        // 신청 가능
        return [
            'status' => 'open',
            'label' => '신청 중',
            'color' => 'green',
            'icon' => '📝'
        ];
    }
    
    /**
     * CSRF 토큰 검증
     */
    private function validateCsrfToken($token)
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>