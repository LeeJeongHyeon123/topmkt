<?php
/**
 * AdminController 클래스
 * 관리자 페이지 전용 컨트롤러
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

// WebLogger 로드 (v4.0.0 통합 로깅 시스템)
if (file_exists(SRC_PATH . '/helpers/WebLogger.php')) {
    require_once SRC_PATH . '/helpers/WebLogger.php';
}

class AdminController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // 관리자 권한 체크
        $this->checkAdminAccess();
    }
    
    /**
     * 관리자 권한 체크
     */
    private function checkAdminAccess() {
        // 로그인 체크
        if (!AuthMiddleware::isLoggedIn()) {
            // AJAX/JSON 요청인 경우 JSON 에러 응답
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                header('HTTP/1.1 401 Unauthorized');
                echo json_encode(['error' => '로그인이 필요합니다.']);
                exit;
            }
            
            // POST 요청으로 JSON을 기대하는 경우도 JSON 응답
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/users/') !== false) {
                header('Content-Type: application/json; charset=utf-8');
                header('HTTP/1.1 401 Unauthorized');
                echo json_encode(['error' => '로그인이 필요합니다.']);
                exit;
            }
            
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/admin';
            header('Location: /auth/login?redirect=' . urlencode($requestUri));
            exit;
        }
        
        // 관리자 권한 체크
        $user = AuthMiddleware::getCurrentUser();
        $allowedRoles = ['ROLE_ADMIN', 'SUPER_ADMIN'];
        if (!in_array($user['role'], $allowedRoles)) {
            header('HTTP/1.1 403 Forbidden');
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '관리자 권한이 필요합니다.']);
            } else {
                include SRC_PATH . '/views/templates/403.php';
            }
            exit;
        }
        
        // 관리자 활동 로깅
        $this->logAdminActivity();
    }
    
    /**
     * 관리자 활동 로깅
     */
    private function logAdminActivity() {
        $userId = AuthMiddleware::getCurrentUserId();
        $action = $_SERVER['REQUEST_URI'];
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $this->db->execute("
            INSERT INTO user_logs (user_id, action, description, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ", [$userId, 'ADMIN_ACCESS', $action, $ip, $userAgent]);
    }
    
    /**
     * 관리자 대시보드 메인 페이지
     */
    public function dashboard() {
        // 대시보드 데이터 수집
        $dashboardData = $this->getDashboardData();
        
        // 헤더 데이터
        $headerData = [
            'title' => '관리자 대시보드 - 탑마케팅',
            'description' => '탑마케팅 관리자 페이지',
            'pageSection' => 'admin'
        ];
        
        // 뷰 렌더링
        $this->renderView('admin/dashboard', $dashboardData, $headerData);
    }
    
    /**
     * 대시보드 데이터 수집
     */
    private function getDashboardData() {
        // 오늘의 통계
        $todayStats = $this->getTodayStats();
        
        // 주간 트렌드
        $weeklyTrend = $this->getWeeklyTrend();
        
        // 긴급 처리 사항
        $urgentTasks = $this->getUrgentTasks();
        
        // 최근 활동
        $recentActivities = $this->getRecentActivities();
        
        return [
            'todayStats' => $todayStats,
            'weeklyTrend' => $weeklyTrend,
            'urgentTasks' => $urgentTasks,
            'recentActivities' => $recentActivities
        ];
    }
    
    /**
     * 오늘의 통계
     */
    private function getTodayStats() {
        $today = date('Y-m-d');
        
        // 신규 가입자
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?", [$today]);
        $todaySignups = $result ? $result['count'] : 0;
        
        // 오늘 게시글
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM posts WHERE DATE(created_at) = ?", [$today]);
        $todayPosts = $result ? $result['count'] : 0;
        
        // 활성 사용자 (최근 30분)
        $result = $this->db->fetch("
            SELECT COUNT(DISTINCT user_id) as count FROM user_sessions 
            WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ");
        $activeUsers = $result ? $result['count'] : 0;
        
        // 대기 중인 기업인증
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM company_profiles WHERE status = 'pending'");
        $pendingCorps = $result ? $result['count'] : 0;
        
        return [
            'signups' => (int)$todaySignups,
            'posts' => (int)$todayPosts,
            'activeUsers' => (int)$activeUsers,
            'pendingCorps' => (int)$pendingCorps
        ];
    }
    
    /**
     * 주간 트렌드 (최근 7일)
     */
    private function getWeeklyTrend() {
        $weeklySignups = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?", [$date]);
            $weeklySignups[] = [
                'date' => $date,
                'count' => $result ? (int)$result['count'] : 0
            ];
        }
        
        return [
            'signups' => $weeklySignups
        ];
    }
    
    /**
     * 긴급 처리 사항
     */
    private function getUrgentTasks() {
        // 대기 중인 기업인증
        $pendingCorps = $this->db->fetchAll("
            SELECT cp.*, u.nickname 
            FROM company_profiles cp 
            JOIN users u ON cp.user_id = u.id 
            WHERE cp.status = 'pending' 
            ORDER BY cp.created_at ASC 
            LIMIT 5
        ");
        
        return [
            'pendingCorps' => $pendingCorps,
            'systemAlerts' => []
        ];
    }
    
    /**
     * 최근 활동
     */
    private function getRecentActivities() {
        // 최근 게시글
        $recentPosts = $this->db->fetchAll("
            SELECT p.*, u.nickname 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC 
            LIMIT 5
        ");
        
        // 최근 댓글
        $recentComments = $this->db->fetchAll("
            SELECT c.*, u.nickname, p.title as post_title 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            JOIN posts p ON c.post_id = p.id 
            ORDER BY c.created_at DESC 
            LIMIT 5
        ");
        
        return [
            'posts' => $recentPosts,
            'comments' => $recentComments
        ];
    }
    
    /**
     * 기업인증 대기 목록 페이지
     */
    public function corporatePending() {
        require_once SRC_PATH . '/helpers/SecurityHelper.php';

        // 대기 중인 기업인증 목록 조회
        $pendingApplications = $this->getPendingCorporateApplications();

        // 암호화된 데이터 복호화
        foreach ($pendingApplications as &$app) {
            if (!empty($app['phone'])) {
                $app['phone'] = SecurityHelper::isEncrypted($app['phone'])
                    ? SecurityHelper::decrypt($app['phone'])
                    : $app['phone'];
            }
            if (!empty($app['email'])) {
                $app['email'] = SecurityHelper::isEncrypted($app['email'])
                    ? SecurityHelper::decrypt($app['email'])
                    : $app['email'];
            }
            if (!empty($app['business_number'])) {
                $app['business_number'] = SecurityHelper::isEncrypted($app['business_number'])
                    ? SecurityHelper::decrypt($app['business_number'])
                    : $app['business_number'];
            }
            if (!empty($app['representative_phone'])) {
                $app['representative_phone'] = SecurityHelper::isEncrypted($app['representative_phone'])
                    ? SecurityHelper::decrypt($app['representative_phone'])
                    : $app['representative_phone'];
            }
        }

        // 헤더 데이터
        $headerData = [
            'title' => '기업인증 대기 목록 - 관리자',
            'description' => '승인 대기 중인 기업인증 신청 목록',
            'pageSection' => 'admin-corporate-pending'
        ];

        // 뷰 렌더링
        $this->renderView('admin/corporate/pending', ['applications' => $pendingApplications], $headerData);
    }
    
    /**
     * 기업회원 목록 페이지
     */
    public function corporateList() {
        require_once SRC_PATH . '/helpers/SecurityHelper.php';

        // 전체 기업회원 목록 조회
        $corporateMembers = $this->getCorporateMembers();

        // 암호화된 데이터 복호화
        foreach ($corporateMembers as &$member) {
            if (!empty($member['phone'])) {
                $member['phone'] = SecurityHelper::isEncrypted($member['phone'])
                    ? SecurityHelper::decrypt($member['phone'])
                    : $member['phone'];
            }
            if (!empty($member['email'])) {
                $member['email'] = SecurityHelper::isEncrypted($member['email'])
                    ? SecurityHelper::decrypt($member['email'])
                    : $member['email'];
            }
            if (!empty($member['business_number'])) {
                $member['business_number'] = SecurityHelper::isEncrypted($member['business_number'])
                    ? SecurityHelper::decrypt($member['business_number'])
                    : $member['business_number'];
            }
            if (!empty($member['representative_phone'])) {
                $member['representative_phone'] = SecurityHelper::isEncrypted($member['representative_phone'])
                    ? SecurityHelper::decrypt($member['representative_phone'])
                    : $member['representative_phone'];
            }
        }

        // 헤더 데이터
        $headerData = [
            'title' => '기업회원 목록 - 관리자',
            'description' => '승인된 기업회원 목록 및 관리',
            'pageSection' => 'admin-corporate-list'
        ];

        // 뷰 렌더링
        $this->renderView('admin/corporate/list', ['members' => $corporateMembers], $headerData);
    }
    
    /**
     * 기업인증 승인/거절 처리
     */
    public function corporateProcess() {
        // 에러 출력 방지
        ini_set('display_errors', 0);
        error_reporting(0);
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['error' => '허용되지 않은 요청 방식입니다.']);
            exit;
        }
        
        // CSRF 토큰 검증
        if (!$this->verifyCsrfToken()) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
            exit;
        }
        
        $applicationId = $_POST['application_id'] ?? null;
        $action = $_POST['action'] ?? null; // 'approve' 또는 'reject'
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        if (!$applicationId || !in_array($action, ['approve', 'reject'])) {
            echo json_encode(['error' => '잘못된 요청입니다.']);
            exit;
        }
        
        try {
            $result = $this->processCorporateApplication($applicationId, $action, $adminNotes);
            echo json_encode(['success' => true, 'message' => $result['message']]);
        } catch (Exception $e) {
            error_log('기업인증 처리 오류: ' . $e->getMessage());
            echo json_encode(['error' => '처리 중 오류가 발생했습니다.']);
        } catch (Throwable $e) {
            error_log('기업인증 처리 치명적 오류: ' . $e->getMessage());
            echo json_encode(['error' => '시스템 오류가 발생했습니다.']);
        }
    }
    
    /**
     * 대기 중인 기업인증 신청 목록 조회
     */
    private function getPendingCorporateApplications() {
        return $this->db->fetchAll("
            SELECT cp.*, u.nickname, u.phone, u.email, u.created_at as user_created_at
            FROM company_profiles cp 
            JOIN users u ON cp.user_id = u.id 
            WHERE cp.status = 'pending' 
            ORDER BY cp.created_at ASC
        ");
    }
    
    /**
     * 전체 기업회원 목록 조회
     */
    private function getCorporateMembers() {
        return $this->db->fetchAll("
            SELECT cp.*, u.nickname, u.phone, u.email, u.corp_approved_at,
                   (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count,
                   (SELECT COUNT(*) FROM lectures WHERE user_id = u.id) as lecture_count
            FROM company_profiles cp 
            JOIN users u ON cp.user_id = u.id 
            WHERE cp.status IN ('approved', 'rejected', 'suspended') 
            ORDER BY cp.processed_at DESC, cp.created_at DESC
        ");
    }
    
    /**
     * 기업인증 승인/거절 처리
     */
    private function processCorporateApplication($applicationId, $action, $adminNotes) {
        $this->db->beginTransaction();
        
        try {
            $adminUserId = AuthMiddleware::getCurrentUserId();
            
            // 신청 정보 조회
            $application = $this->db->fetch("SELECT * FROM company_profiles WHERE id = ? AND status = 'pending'", [$applicationId]);
            
            if (!$application) {
                throw new Exception('유효하지 않은 신청입니다.');
            }
            
            // company_profiles 테이블 업데이트
            $this->db->execute("
                UPDATE company_profiles 
                SET status = ?, admin_notes = ?, processed_by = ?, processed_at = NOW() 
                WHERE id = ?
            ", [$action === 'approve' ? 'approved' : 'rejected', $adminNotes, $adminUserId, $applicationId]);
            
            // users 테이블의 corp_status 업데이트
            $corpStatus = $action === 'approve' ? 'approved' : 'rejected';
            $this->db->execute("
                UPDATE users 
                SET corp_status = ?, corp_approved_at = " . ($action === 'approve' ? 'NOW()' : 'NULL') . " 
                WHERE id = ?
            ", [$corpStatus, $application['user_id']]);
            
            // 이력 기록
            $this->db->execute("
                INSERT INTO company_application_history 
                (user_id, action_type, admin_notes, created_by, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ", [
                $application['user_id'], 
                $action, 
                $adminNotes, 
                $adminUserId
            ]);
            
            $this->db->commit();
            
            // SMS 알림 발송
            $smsResult = $this->sendApprovalNotification($application['user_id'], $action, $adminNotes, $application['company_name']);
            if (!$smsResult) {
                error_log("SMS 발송 실패 - 사용자 ID: {$application['user_id']}, 액션: {$action}");
            }
            
            $message = $action === 'approve' ? '기업인증이 승인되었습니다.' : '기업인증이 거절되었습니다.';
            return ['success' => true, 'message' => $message];
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 기업인증 신청 상세보기
     */
    public function corporateApplicationDetail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        $applicationId = $_POST['application_id'] ?? null;

        if (!$applicationId) {
            echo json_encode(['error' => '신청 ID가 필요합니다.']);
            exit;
        }

        try {
            require_once SRC_PATH . '/helpers/SecurityHelper.php';

            $applicationDetail = $this->getApplicationDetail($applicationId);
            if (!$applicationDetail) {
                echo json_encode(['error' => '신청 정보를 찾을 수 없습니다.']);
                exit;
            }

            // 암호화된 데이터 복호화
            if (!empty($applicationDetail['phone'])) {
                $applicationDetail['phone'] = SecurityHelper::isEncrypted($applicationDetail['phone'])
                    ? SecurityHelper::decrypt($applicationDetail['phone'])
                    : $applicationDetail['phone'];
            }
            if (!empty($applicationDetail['email'])) {
                $applicationDetail['email'] = SecurityHelper::isEncrypted($applicationDetail['email'])
                    ? SecurityHelper::decrypt($applicationDetail['email'])
                    : $applicationDetail['email'];
            }
            if (!empty($applicationDetail['business_number'])) {
                $applicationDetail['business_number'] = SecurityHelper::isEncrypted($applicationDetail['business_number'])
                    ? SecurityHelper::decrypt($applicationDetail['business_number'])
                    : $applicationDetail['business_number'];
            }
            if (!empty($applicationDetail['representative_phone'])) {
                $applicationDetail['representative_phone'] = SecurityHelper::isEncrypted($applicationDetail['representative_phone'])
                    ? SecurityHelper::decrypt($applicationDetail['representative_phone'])
                    : $applicationDetail['representative_phone'];
            }

            echo json_encode(['success' => true, 'data' => $applicationDetail]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * 사업자등록증 문서 다운로드/보기
     */
    public function viewDocument() {
        $filename = $_GET['file'] ?? '';
        
        if (empty($filename)) {
            header('HTTP/1.1 400 Bad Request');
            exit('파일명이 필요합니다.');
        }
        
        // 보안: 파일명 검증 (경로 조작 방지)
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            header('HTTP/1.1 403 Forbidden');
            exit('유효하지 않은 파일명입니다.');
        }
        
        $filePath = ROOT_PATH . '/public/PUBLIC_PATH/assets/uploads/corp_docs/' . $filename;
        
        if (!file_exists($filePath)) {
            header('HTTP/1.1 404 Not Found');
            exit('파일을 찾을 수 없습니다.');
        }
        
        // 파일 타입 확인
        $fileInfo = pathinfo($filePath);
        $extension = strtolower($fileInfo['extension']);
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        // 헤더 설정
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: inline; filename="' . basename($filename) . '"');
        header('Cache-Control: private, max-age=3600');
        
        // 파일 출력
        readfile($filePath);
        exit;
    }
    
    /**
     * 기업인증 신청 상세 정보 조회
     */
    private function getApplicationDetail($applicationId) {
        $application = $this->db->fetch("
            SELECT cp.*, u.nickname, u.phone, u.email, u.created_at as user_created_at,
                   u.corp_status, u.corp_approved_at,
                   admin_user.nickname as processed_by_name
            FROM company_profiles cp 
            LEFT JOIN users u ON cp.user_id = u.id 
            LEFT JOIN users admin_user ON cp.processed_by = admin_user.id
            WHERE cp.id = ?
        ", [$applicationId]);
        
        if (!$application) {
            return null;
        }
        
        // 처리 이력 조회
        $history = $this->db->fetchAll("
            SELECT cah.*, u.nickname as created_by_name
            FROM company_application_history cah
            LEFT JOIN users u ON cah.created_by = u.id
            WHERE cah.user_id = ?
            ORDER BY cah.created_at DESC
        ", [$application['user_id']]);
        
        $application['history'] = $history;
        
        return $application;
    }
    
    /**
     * 승인/거절 SMS 알림 발송
     */
    private function sendApprovalNotification($userId, $action, $adminNotes, $companyName) {
        try {
            require_once SRC_PATH . '/helpers/SmsHelper.php';
            require_once SRC_PATH . '/helpers/SecurityHelper.php';

            // 사용자 정보 조회
            $user = $this->db->fetch("SELECT phone, nickname FROM users WHERE id = ?", [$userId]);

            if (!$user) {
                error_log("SMS 발송 실패: 사용자를 찾을 수 없음 (ID: {$userId})");
                return false;
            }

            // 전화번호 복호화
            if (!empty($user['phone'])) {
                $user['phone'] = SecurityHelper::isEncrypted($user['phone'])
                    ? SecurityHelper::decrypt($user['phone'])
                    : $user['phone'];
            }

            if (!$user['phone']) {
                error_log("SMS 발송 실패: 휴대폰 번호가 없음 (사용자: {$user['nickname']})");
                return false;
            }
            
            $status = $action === 'approve' ? '승인' : '거절';
            $statusIcon = $action === 'approve' ? '✅' : '❌';
            
            // SMS 메시지 작성
            if ($action === 'approve') {
                $message = "[탑마케팅] 기업인증 승인완료! 강의등록 가능합니다. topmktx.com/lectures/create";
            } else {
                $message = "[탑마케팅] 기업인증이 거절되었습니다. 재신청은 topmktx.com/corp/apply에서 가능합니다.";
            }
            
            // SMS 발송
            error_log("SMS 발송 시도: {$user['phone']}, 액션: {$action}, 메시지 길이: " . strlen($message));
            
            $smsHelper = new SmsHelper();
            $result = $smsHelper->send($user['phone'], $message);
            
            if ($result['success']) {
                error_log("기업인증 {$status} SMS 발송 성공: {$user['phone']}");
                return true;
            } else {
                error_log("기업인증 {$status} SMS 발송 실패: " . json_encode($result));
                return false;
            }
            
        } catch (Exception $e) {
            error_log('SMS 발송 오류: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 기업회원 관리 처리 (상태 변경, 메모, 연락처 수정)
     */
    public function manageCorporateMember() {
        // 에러 출력 방지
        ini_set('display_errors', 0);
        error_reporting(0);
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['error' => '허용되지 않은 요청 방식입니다.']);
            exit;
        }
        
        // CSRF 토큰 검증
        if (!$this->verifyCsrfToken()) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
            exit;
        }
        
        $memberId = $_POST['member_id'] ?? null;
        $action = $_POST['action'] ?? null; // 'status_change', 'update_memo', 'update_contact'
        
        if (!$memberId || !$action) {
            echo json_encode(['error' => '필수 정보가 누락되었습니다.']);
            exit;
        }
        
        try {
            switch ($action) {
                case 'status_change':
                    $newStatus = $_POST['new_status'] ?? null;
                    $reason = $_POST['reason'] ?? '';
                    $result = $this->changeMemberStatus($memberId, $newStatus, $reason);
                    break;
                    
                case 'update_memo':
                    $adminMemo = $_POST['admin_memo'] ?? '';
                    $result = $this->updateAdminMemo($memberId, $adminMemo);
                    break;
                    
                case 'update_contact':
                    $repName = $_POST['representative_name'] ?? '';
                    $repPhone = $_POST['representative_phone'] ?? '';
                    $result = $this->updateContactInfo($memberId, $repName, $repPhone);
                    break;
                    
                default:
                    echo json_encode(['error' => '유효하지 않은 액션입니다.']);
                    exit;
            }
            
            echo json_encode(['success' => true, 'message' => $result['message']]);
            
        } catch (Exception $e) {
            error_log('기업회원 관리 오류: ' . $e->getMessage());
            error_log('스택 트레이스: ' . $e->getTraceAsString());
            echo json_encode(['error' => '처리 중 오류가 발생했습니다: ' . $e->getMessage()]);
        } catch (Throwable $e) {
            error_log('기업회원 관리 치명적 오류: ' . $e->getMessage());
            error_log('스택 트레이스: ' . $e->getTraceAsString());
            echo json_encode(['error' => '시스템 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 기업회원 상태 변경
     */
    private function changeMemberStatus($memberId, $newStatus, $reason) {
        if (!in_array($newStatus, ['approved', 'suspended'])) {
            throw new Exception('유효하지 않은 상태입니다.');
        }
        
        $this->db->beginTransaction();
        
        try {
            $adminUserId = AuthMiddleware::getCurrentUserId();
            
            // 기업 정보 조회
            $member = $this->db->fetch("
                SELECT cp.*, u.nickname 
                FROM company_profiles cp 
                JOIN users u ON cp.user_id = u.id 
                WHERE cp.id = ?
            ", [$memberId]);
            
            if (!$member) {
                throw new Exception('기업회원을 찾을 수 없습니다.');
            }
            
            // company_profiles 테이블 업데이트
            $this->db->execute("
                UPDATE company_profiles 
                SET status = ?, processed_by = ?, processed_at = NOW() 
                WHERE id = ?
            ", [$newStatus, $adminUserId, $memberId]);
            
            // users 테이블의 corp_status 업데이트
            $userStatus = $newStatus;
            $this->db->execute("
                UPDATE users 
                SET corp_status = ?, corp_approved_at = " . ($newStatus === 'approved' ? 'NOW()' : 'NULL') . " 
                WHERE id = ?
            ", [$userStatus, $member['user_id']]);
            
            // 이력 기록
            $actionType = $newStatus === 'approved' ? 'reapprove' : 'suspend';
            $this->db->execute("
                INSERT INTO company_application_history 
                (user_id, action_type, admin_notes, created_by, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ", [
                $member['user_id'], 
                $actionType, 
                $reason, 
                $adminUserId
            ]);
            
            $this->db->commit();
            
            // SMS 알림 발송
            $this->sendStatusChangeNotification($member['user_id'], $newStatus, $member['company_name']);
            
            $message = $newStatus === 'approved' ? '기업회원이 재승인되었습니다.' : '기업회원이 일시정지되었습니다.';
            return ['success' => true, 'message' => $message];
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 관리자 메모 업데이트
     */
    private function updateAdminMemo($memberId, $adminMemo) {
        $this->db->execute("
            UPDATE company_profiles 
            SET admin_memo = ? 
            WHERE id = ?
        ", [$adminMemo, $memberId]);
        
        return ['success' => true, 'message' => '관리자 메모가 업데이트되었습니다.'];
    }
    
    /**
     * 연락처 정보 업데이트
     */
    private function updateContactInfo($memberId, $repName, $repPhone) {
        if (empty($repName) || empty($repPhone)) {
            throw new Exception('대표자명과 연락처는 필수입니다.');
        }
        
        $this->db->execute("
            UPDATE company_profiles 
            SET representative_name = ?, representative_phone = ? 
            WHERE id = ?
        ", [$repName, $repPhone, $memberId]);
        
        return ['success' => true, 'message' => '연락처 정보가 업데이트되었습니다.'];
    }
    
    /**
     * 상태 변경 SMS 알림 발송
     */
    private function sendStatusChangeNotification($userId, $newStatus, $companyName) {
        try {
            require_once SRC_PATH . '/helpers/SmsHelper.php';
            require_once SRC_PATH . '/helpers/SecurityHelper.php';

            // 사용자 정보 조회
            $user = $this->db->fetch("SELECT phone, nickname FROM users WHERE id = ?", [$userId]);

            if (!$user) {
                return false;
            }

            // 전화번호 복호화
            if (!empty($user['phone'])) {
                $user['phone'] = SecurityHelper::isEncrypted($user['phone'])
                    ? SecurityHelper::decrypt($user['phone'])
                    : $user['phone'];
            }

            if (!$user['phone']) {
                return false;
            }
            
            // SMS 메시지 작성
            if ($newStatus === 'approved') {
                $message = "[탑마케팅] 기업인증이 재승인되었습니다. 서비스를 계속 이용하실 수 있습니다.";
            } else {
                $message = "[탑마케팅] 기업회원 서비스가 일시정지되었습니다. 문의: 1577-9794";
            }
            
            // SMS 발송
            $smsHelper = new SmsHelper();
            $result = $smsHelper->send($user['phone'], $message);
            
            if ($result['success']) {
                error_log("기업회원 상태변경 SMS 발송 성공: {$user['phone']}");
                return true;
            } else {
                error_log("기업회원 상태변경 SMS 발송 실패: " . json_encode($result));
                return false;
            }
            
        } catch (Exception $e) {
            error_log('상태변경 SMS 발송 오류: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * CSRF 토큰 생성
     */
    private function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * CSRF 토큰 검증
     */
    private function verifyCsrfToken() {
        // JSON 요청에서 헤더 토큰 확인
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
    
    /**
     * 사용자 목록 페이지
     */
    public function userList() {
        // 헤더 데이터
        $headerData = [
            'page_title' => '회원 관리',
            'page_description' => '사용자 계정 및 권한을 관리하세요',
            'current_page' => 'users'
        ];
        
        // 사용자 통계 조회
        require_once SRC_PATH . '/models/User.php';
        $userModel = new User();
        $userStats = $userModel->getUserStatistics();
        
        // 뷰 렌더링 (호환성 문제 해결을 위한 직접 렌더링 버전 사용)
        $this->renderView('admin/users/list_direct', ['userStats' => $userStats], $headerData);
    }
    
    /**
     * 사용자 데이터 Ajax 조회
     */
    public function getUsersData() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $filters = [
                'status' => $_GET['status'] ?? 'all',
                'role' => $_GET['role'] ?? 'all',
                'corp_status' => $_GET['corp_status'] ?? 'all',
                'verified_status' => $_GET['verified_status'] ?? 'all',
                'login_activity' => $_GET['login_activity'] ?? 'all',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'search' => $_GET['search'] ?? '',
                'sort' => $_GET['sort'] ?? 'created_at'
            ];
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
            
            require_once SRC_PATH . '/models/User.php';
            require_once SRC_PATH . '/helpers/SecurityHelper.php';

            $userModel = new User();
            $result = $userModel->getFilteredUsers($filters, $page, $limit);

            // 암호화된 데이터 복호화 및 민감한 정보 처리
            foreach ($result['users'] as &$user) {
                // 전화번호 복호화
                if (!empty($user['phone'])) {
                    $decryptedPhone = SecurityHelper::isEncrypted($user['phone'])
                        ? SecurityHelper::decrypt($user['phone'])
                        : $user['phone'];
                    $user['phone'] = $decryptedPhone;
                }

                // 이메일 복호화
                if (!empty($user['email'])) {
                    $decryptedEmail = SecurityHelper::isEncrypted($user['email'])
                        ? SecurityHelper::decrypt($user['email'])
                        : $user['email'];
                    $user['email'] = $decryptedEmail;
                }

                // 불필요한 민감 정보 제거
                unset($user['password_hash'], $user['remember_token']);
            }
            
            // 로깅
            if (class_exists('WebLogger')) {
                WebLogger::info('관리자 사용자 목록 조회', [
                    'admin_id' => AuthMiddleware::getCurrentUserId(),
                    'filters' => $filters,
                    'page' => $page,
                    'total_results' => $result['total']
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $result,
                'timestamp' => date('c')
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            error_log('사용자 데이터 조회 오류: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => '사용자 목록을 불러오는 중 오류가 발생했습니다.',
                'timestamp' => date('c')
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * 사용자 상태 업데이트
     */
    public function updateUserStatus($userId) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['error' => '허용되지 않은 요청 방식입니다.']);
            exit;
        }
        
        // CSRF 토큰 검증
        if (!$this->verifyCsrfToken()) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
            exit;
        }
        
        // JSON 데이터 파싱
        $input = json_decode(file_get_contents('php://input'), true);
        $newStatus = $input['status'] ?? $_POST['status'] ?? '';
        $reason = $input['reason'] ?? $_POST['reason'] ?? '';
        
        $allowedStatuses = ['active', 'inactive', 'suspended'];
        if (!in_array($newStatus, $allowedStatuses)) {
            echo json_encode(['error' => '유효하지 않은 상태입니다.']);
            exit;
        }
        
        try {
            require_once SRC_PATH . '/models/User.php';
            $userModel = new User();
            $adminId = AuthMiddleware::getCurrentUserId();
            
            // 자기 자신의 상태는 변경할 수 없음
            if ($userId == $adminId) {
                echo json_encode(['error' => '자신의 계정 상태는 변경할 수 없습니다.']);
                exit;
            }
            
            $result = $userModel->updateUserStatus($userId, $newStatus, $adminId, $reason);
            
            if ($result) {
                // SMS 알림 발송 (선택적)
                $user = $userModel->findById($userId);
                if ($user && !empty($user['phone'])) {
                    $this->sendUserStatusNotification($user, $newStatus, $reason);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => '사용자 상태가 성공적으로 변경되었습니다.'
                ]);
            } else {
                echo json_encode(['error' => '상태 변경에 실패했습니다.']);
            }
            
        } catch (Exception $e) {
            error_log('사용자 상태 업데이트 오류: ' . $e->getMessage());
            echo json_encode(['error' => '처리 중 오류가 발생했습니다.']);
        }
    }
    
    /**
     * 사용자 권한 업데이트
     */
    public function updateUserRole($userId) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['error' => '허용되지 않은 요청 방식입니다.']);
            exit;
        }
        
        // CSRF 토큰 검증
        if (!$this->verifyCsrfToken()) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
            exit;
        }
        
        $newRole = $_POST['role'] ?? '';
        $reason = $_POST['reason'] ?? '';
        
        $allowedRoles = ['ROLE_USER', 'ROLE_CORPORATE', 'ROLE_ADMIN'];
        if (!in_array($newRole, $allowedRoles)) {
            echo json_encode(['error' => '유효하지 않은 권한입니다.']);
            exit;
        }
        
        try {
            require_once SRC_PATH . '/models/User.php';
            $userModel = new User();
            $adminId = AuthMiddleware::getCurrentUserId();
            
            // 자기 자신의 권한은 변경할 수 없음
            if ($userId == $adminId) {
                echo json_encode(['error' => '자신의 계정 권한은 변경할 수 없습니다.']);
                exit;
            }
            
            $result = $userModel->updateUserRole($userId, $newRole, $adminId, $reason);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => '사용자 권한이 성공적으로 변경되었습니다.'
                ]);
            } else {
                echo json_encode(['error' => '권한 변경에 실패했습니다.']);
            }
            
        } catch (Exception $e) {
            error_log('사용자 권한 업데이트 오류: ' . $e->getMessage());
            echo json_encode(['error' => '처리 중 오류가 발생했습니다.']);
        }
    }
    
    /**
     * 사용자 일괄 처리
     */
    public function bulkUserAction() {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['error' => '허용되지 않은 요청 방식입니다.']);
            exit;
        }
        
        // CSRF 토큰 검증
        if (!$this->verifyCsrfToken()) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
            exit;
        }
        
        $userIds = $_POST['user_ids'] ?? [];
        $action = $_POST['action'] ?? '';
        $value = $_POST['value'] ?? '';
        $reason = $_POST['reason'] ?? '';
        
        if (empty($userIds) || !is_array($userIds)) {
            echo json_encode(['error' => '처리할 사용자를 선택해주세요.']);
            exit;
        }
        
        try {
            require_once SRC_PATH . '/models/User.php';
            $userModel = new User();
            $adminId = AuthMiddleware::getCurrentUserId();
            
            // 자기 자신은 제외
            $userIds = array_filter($userIds, function($id) use ($adminId) {
                return $id != $adminId;
            });
            
            $successCount = 0;
            $failCount = 0;
            
            foreach ($userIds as $userId) {
                try {
                    if ($action === 'status') {
                        $result = $userModel->updateUserStatus($userId, $value, $adminId, $reason);
                    } elseif ($action === 'role') {
                        $result = $userModel->updateUserRole($userId, $value, $adminId, $reason);
                    } else {
                        continue;
                    }
                    
                    if ($result) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                } catch (Exception $e) {
                    $failCount++;
                    error_log("일괄 처리 오류 (사용자 ID: {$userId}): " . $e->getMessage());
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => "처리 완료: 성공 {$successCount}건, 실패 {$failCount}건",
                'success_count' => $successCount,
                'fail_count' => $failCount
            ]);
            
        } catch (Exception $e) {
            error_log('일괄 처리 오류: ' . $e->getMessage());
            echo json_encode(['error' => '처리 중 오류가 발생했습니다.']);
        }
    }
    
    
    /**
     * 사용자 알림 발송
     */
    public function notifyUser($userId) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['error' => '허용되지 않은 요청 방식입니다.']);
            exit;
        }
        
        // CSRF 토큰 검증
        if (!$this->verifyCsrfToken()) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
            exit;
        }
        
        $message = $_POST['message'] ?? '';
        $type = $_POST['type'] ?? 'info'; // info, warning, important
        
        if (empty($message)) {
            echo json_encode(['error' => '메시지를 입력해주세요.']);
            exit;
        }
        
        try {
            require_once SRC_PATH . '/models/User.php';
            $userModel = new User();
            $user = $userModel->findById($userId);
            
            if (!$user) {
                echo json_encode(['error' => '사용자를 찾을 수 없습니다.']);
                exit;
            }
            
            // SMS 알림 발송
            $smsResult = $this->sendAdminNotificationSms($user, $message, $type);
            
            if ($smsResult) {
                // 관리자 활동 로그
                $userModel->logUserActivity(
                    AuthMiddleware::getCurrentUserId(),
                    'USER_NOTIFICATION_SENT',
                    "사용자 ID {$userId}에게 알림 발송: {$message}",
                    ['target_user_id' => $userId, 'message' => $message, 'type' => $type]
                );
                
                echo json_encode([
                    'success' => true,
                    'message' => '알림이 성공적으로 발송되었습니다.'
                ]);
            } else {
                echo json_encode(['error' => 'SMS 발송에 실패했습니다.']);
            }
            
        } catch (Exception $e) {
            error_log('사용자 알림 발송 오류: ' . $e->getMessage());
            echo json_encode(['error' => '처리 중 오류가 발생했습니다.']);
        }
    }
    
    /**
     * 사용자 데이터 내보내기
     */
    public function exportUsers() {
        try {
            $format = $_GET['format'] ?? 'csv';
            $filters = [
                'status' => $_GET['status'] ?? 'all',
                'role' => $_GET['role'] ?? 'all',
                'corp_status' => $_GET['corp_status'] ?? 'all',
                'search' => $_GET['search'] ?? ''
            ];
            
            require_once SRC_PATH . '/models/User.php';
            $userModel = new User();
            $result = $userModel->getFilteredUsers($filters, 1, 10000); // 최대 10,000개
            
            if ($format === 'csv') {
                $this->exportUsersAsCsv($result['users']);
            } elseif ($format === 'excel') {
                $this->exportUsersAsExcel($result['users']);
            } else {
                header('HTTP/1.1 400 Bad Request');
                echo '지원하지 않는 형식입니다.';
            }
            
        } catch (Exception $e) {
            error_log('사용자 데이터 내보내기 오류: ' . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo '내보내기 중 오류가 발생했습니다.';
        }
    }
    
    /**
     * 사용자 통계 조회
     */
    public function getUserStats() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            require_once SRC_PATH . '/models/User.php';
            $userModel = new User();
            $stats = $userModel->getUserStatistics();
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            error_log('사용자 통계 조회 오류: ' . $e->getMessage());
            echo json_encode(['error' => '통계를 불러오는 중 오류가 발생했습니다.']);
        }
    }
    
    /**
     * 사용자 상세 정보 조회
     */
    public function getUserDetail($userId) {
        header('Content-Type: application/json; charset=utf-8');
        
        $requestId = uniqid('user_detail_');
        
        try {
            // WebLogger 사용 (v4.0.0 표준)
            if (class_exists('WebLogger')) {
                WebLogger::controllerStart('AdminController', 'getUserDetail');
                WebLogger::info('사용자 상세 정보 조회 시작', [
                    'request_id' => $requestId,
                    'user_id' => $userId,
                    'request_user' => $_SESSION['user']['id'] ?? 'unknown'
                ]);
            }
            
            if (!$userId || !is_numeric($userId)) {
                if (class_exists('WebLogger')) {
                    WebLogger::warning('잘못된 사용자 ID', [
                        'request_id' => $requestId,
                        'provided_user_id' => $userId
                    ]);
                }
                echo json_encode(['error' => '유효하지 않은 사용자 ID입니다.']);
                return;
            }
            
            // 기본 사용자 정보 조회 (안전한 쿼리)
            if (class_exists('WebLogger')) {
                WebLogger::info('기본 사용자 정보 쿼리 시작', [
                    'request_id' => $requestId,
                    'target_user_id' => $userId
                ]);
            }
            
            $userQuery = "
                SELECT u.*, 
                       cp.company_name, 
                       cp.business_number, 
                       cp.representative_name,
                       cp.representative_phone,
                       cp.company_address,
                       cp.status as corp_status
                FROM users u 
                LEFT JOIN company_profiles cp ON u.id = cp.user_id 
                WHERE u.id = ?
            ";
            
            $queryStart = microtime(true);
            $user = $this->db->fetch($userQuery, [$userId]);
            $queryDuration = microtime(true) - $queryStart;
            
            if (class_exists('WebLogger')) {
                WebLogger::info('기본 사용자 정보 쿼리 완료', [
                    'request_id' => $requestId,
                    'duration' => $queryDuration,
                    'user_found' => $user ? 'yes' : 'no',
                    'user_data_preview' => $user ? [
                        'id' => $user['id'],
                        'nickname' => $user['nickname'],
                        'email' => $user['email']
                    ] : null
                ]);
            }
            
            if (!$user) {
                if (class_exists('WebLogger')) {
                    WebLogger::warning('사용자를 찾을 수 없음', [
                        'request_id' => $requestId,
                        'target_user_id' => $userId
                    ]);
                }
                echo json_encode(['error' => '사용자를 찾을 수 없습니다.']);
                return;
            }
            
            // 추가 통계 정보 조회 (안전하게)
            if (class_exists('WebLogger')) {
                WebLogger::info('추가 통계 정보 조회 시작', [
                    'request_id' => $requestId
                ]);
            }
            
            try {
                // 게시글 수 조회
                $postCountQuery = "SELECT COUNT(*) as count FROM posts WHERE author_id = ?";
                $postCount = $this->db->fetch($postCountQuery, [$userId]);
                $user['post_count'] = $postCount ? $postCount['count'] : 0;
                
                if (class_exists('WebLogger')) {
                    WebLogger::info('게시글 수 조회 성공', [
                        'request_id' => $requestId,
                        'post_count' => $user['post_count']
                    ]);
                }
            } catch (Exception $e) {
                $user['post_count'] = 0;
                if (class_exists('WebLogger')) {
                    WebLogger::warning('게시글 테이블 접근 실패', [
                        'request_id' => $requestId,
                        'error' => $e->getMessage(),
                        'table' => 'posts'
                    ]);
                }
            }
            
            try {
                // 댓글 수 조회
                $commentCountQuery = "SELECT COUNT(*) as count FROM comments WHERE user_id = ?";
                $commentCount = $this->db->fetch($commentCountQuery, [$userId]);
                $user['comment_count'] = $commentCount ? $commentCount['count'] : 0;
                
                if (class_exists('WebLogger')) {
                    WebLogger::info('댓글 수 조회 성공', [
                        'request_id' => $requestId,
                        'comment_count' => $user['comment_count']
                    ]);
                }
            } catch (Exception $e) {
                $user['comment_count'] = 0;
                if (class_exists('WebLogger')) {
                    WebLogger::warning('댓글 테이블 접근 실패', [
                        'request_id' => $requestId,
                        'error' => $e->getMessage(),
                        'table' => 'comments'
                    ]);
                }
            }
            
            // 로그인 정보 조회 (테이블이 없으면 기본값 사용)
            try {
                $lastLoginQuery = "
                    SELECT login_time 
                    FROM login_logs 
                    WHERE user_id = ? AND status = 'success' 
                    ORDER BY login_time DESC 
                    LIMIT 1
                ";
                $lastLogin = $this->db->fetch($lastLoginQuery, [$userId]);
                $user['last_login'] = $lastLogin ? $lastLogin['login_time'] : null;
                
                // 로그인 시도 횟수 조회 (최근 30일)
                $loginAttemptsQuery = "
                    SELECT COUNT(*) as attempts 
                    FROM login_logs 
                    WHERE user_id = ? AND login_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ";
                $loginAttempts = $this->db->fetch($loginAttemptsQuery, [$userId]);
                $user['login_attempts'] = $loginAttempts ? $loginAttempts['attempts'] : 0;
            } catch (Exception $loginLogError) {
                // login_logs 테이블이 없는 경우 기본값 설정
                $user['last_login'] = $user['updated_at'] ?? $user['created_at'];
                $user['login_attempts'] = 0;
            }
            
            // 암호화된 데이터 복호화
            require_once SRC_PATH . '/helpers/SecurityHelper.php';

            if (!empty($user['phone'])) {
                $user['phone'] = SecurityHelper::isEncrypted($user['phone'])
                    ? SecurityHelper::decrypt($user['phone'])
                    : $user['phone'];
            }
            if (!empty($user['email'])) {
                $user['email'] = SecurityHelper::isEncrypted($user['email'])
                    ? SecurityHelper::decrypt($user['email'])
                    : $user['email'];
            }
            if (!empty($user['business_number'])) {
                $user['business_number'] = SecurityHelper::isEncrypted($user['business_number'])
                    ? SecurityHelper::decrypt($user['business_number'])
                    : $user['business_number'];
            }
            if (!empty($user['representative_phone'])) {
                $user['representative_phone'] = SecurityHelper::isEncrypted($user['representative_phone'])
                    ? SecurityHelper::decrypt($user['representative_phone'])
                    : $user['representative_phone'];
            }

            // 성공 응답 로깅
            if (class_exists('WebLogger')) {
                WebLogger::info('사용자 상세 정보 조회 성공', [
                    'request_id' => $requestId,
                    'user_id' => $user['id'],
                    'nickname' => $user['nickname'],
                    'data_size' => strlen(json_encode($user))
                ]);
                WebLogger::controllerEnd('AdminController', 'getUserDetail', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
            }

            echo json_encode([
                'success' => true,
                'data' => $user
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            // 표준 로깅 시스템 사용
            if (class_exists('WebLogger')) {
                WebLogger::exception($e, [
                    'request_id' => $requestId ?? 'unknown',
                    'method' => 'getUserDetail',
                    'target_user_id' => $userId ?? 'unknown',
                    'error_context' => 'AdminController::getUserDetail 메인 catch 블록'
                ]);
            } else {
                // 폴백 로깅
                error_log('사용자 상세 정보 조회 오류: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                error_log('스택 트레이스: ' . $e->getTraceAsString());
            }
            
            echo json_encode([
                'error' => '사용자 정보를 불러오는 중 오류가 발생했습니다.',
                'debug_info' => getenv('APP_ENV') === 'development' ? [
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ] : null
            ]);
        }
    }
    
    /**
     * 사용자 상태 변경 SMS 알림 발송
     */
    private function sendUserStatusNotification($user, $status, $reason) {
        try {
            require_once SRC_PATH . '/helpers/SmsHelper.php';
            require_once SRC_PATH . '/helpers/SecurityHelper.php';

            // 전화번호 복호화
            $phone = $user['phone'];
            if (!empty($phone)) {
                $phone = SecurityHelper::isEncrypted($phone)
                    ? SecurityHelper::decrypt($phone)
                    : $phone;
            }

            if (!$phone) {
                return false;
            }

            $statusMessages = [
                'active' => '계정이 활성화되었습니다.',
                'inactive' => '계정이 비활성화되었습니다.',
                'suspended' => '계정이 일시정지되었습니다.'
            ];

            $message = "[탑마케팅] {$statusMessages[$status]}";
            if (!empty($reason)) {
                $message .= " 사유: {$reason}";
            }
            $message .= " 문의: 1577-9794";

            $smsHelper = new SmsHelper();
            return $smsHelper->send($phone, $message);
            
        } catch (Exception $e) {
            error_log('사용자 상태 변경 SMS 발송 오류: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 관리자 알림 SMS 발송
     */
    private function sendAdminNotificationSms($user, $message, $type) {
        try {
            require_once SRC_PATH . '/helpers/SmsHelper.php';
            require_once SRC_PATH . '/helpers/SecurityHelper.php';

            // 전화번호 복호화
            $phone = $user['phone'];
            if (!empty($phone)) {
                $phone = SecurityHelper::isEncrypted($phone)
                    ? SecurityHelper::decrypt($phone)
                    : $phone;
            }

            if (!$phone) {
                return false;
            }

            $typePrefix = [
                'info' => '[알림]',
                'warning' => '[주의]',
                'important' => '[중요]'
            ];

            $smsMessage = "[탑마케팅] {$typePrefix[$type]} {$message}";

            $smsHelper = new SmsHelper();
            return $smsHelper->send($phone, $smsMessage);
            
        } catch (Exception $e) {
            error_log('관리자 알림 SMS 발송 오류: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * CSV 형식으로 사용자 데이터 내보내기
     */
    private function exportUsersAsCsv($users) {
        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM 추가 (Excel 호환)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // 헤더
        fputcsv($output, [
            'ID', '닉네임', '이메일', '전화번호', '권한', '상태', '기업상태',
            '휴대폰인증', '이메일인증', '게시글수', '댓글수', '가입일', '마지막로그인'
        ]);
        
        // 데이터
        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['nickname'],
                $user['email'],
                $user['phone'],
                $user['role'],
                $user['status'],
                $user['corp_status'],
                $user['phone_verified'] ? 'Y' : 'N',
                $user['email_verified'] ? 'Y' : 'N',
                $user['post_count'],
                $user['comment_count'],
                $user['created_at'],
                $user['last_login'] ?? '없음'
            ]);
        }
        
        fclose($output);
    }
    
    /**
     * Excel 형식으로 사용자 데이터 내보내기 (간단한 구현)
     */
    private function exportUsersAsExcel($users) {
        // 간단한 Excel 형태 (실제로는 CSV지만 확장자만 xls)
        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.xls';
        
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo chr(0xEF).chr(0xBB).chr(0xBF); // UTF-8 BOM
        
        echo "ID\t닉네임\t이메일\t전화번호\t권한\t상태\t기업상태\t휴대폰인증\t이메일인증\t게시글수\t댓글수\t가입일\t마지막로그인\n";
        
        foreach ($users as $user) {
            echo "{$user['id']}\t{$user['nickname']}\t{$user['email']}\t{$user['phone']}\t{$user['role']}\t{$user['status']}\t{$user['corp_status']}\t";
            echo ($user['phone_verified'] ? 'Y' : 'N') . "\t";
            echo ($user['email_verified'] ? 'Y' : 'N') . "\t";
            echo "{$user['post_count']}\t{$user['comment_count']}\t{$user['created_at']}\t";
            echo ($user['last_login'] ?? '없음') . "\n";
        }
    }
    
    /**
     * 사용자 편집 기능 (관리자용)
     */
    public function editUser($userId) {
        // 🔥 Ultra Think 디버깅: 에러 발생 지점 추적
        error_log("🔥 Ultra Think Debug: editUser 메서드 시작 - userId: $userId");
        error_log("🔥 Ultra Think Debug: POST 데이터: " . json_encode($_POST));
        error_log("🔥 Ultra Think Debug: 세션 데이터: " . json_encode($_SESSION ?? []));
        
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo json_encode(['error' => '허용되지 않은 요청 방식입니다.']);
            exit;
        }
        
        // CSRF 토큰 검증
        if (!$this->verifyCsrfToken()) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
            exit;
        }
        
        // 유효성 검사
        if (!$userId || !is_numeric($userId)) {
            echo json_encode(['error' => '유효하지 않은 사용자 ID입니다.']);
            exit;
        }
        
        try {
            error_log("🔥 Ultra Think Debug: try 블록 시작");
            require_once SRC_PATH . '/models/User.php';
            error_log("🔥 Ultra Think Debug: User.php 로딩 성공");
            // ValidationHelper 제거 - PHP 내장 함수 사용
            
            $userModel = new User();
            error_log("🔥 Ultra Think Debug: User 모델 인스턴스 생성 성공");
            $adminId = AuthMiddleware::getCurrentUserId();
            error_log("🔥 Ultra Think Debug: 현재 관리자 ID: $adminId");
            
            // 자기 자신 편집 방지 (관리자 계정 보호)
            if ($userId == $adminId) {
                echo json_encode(['error' => '자신의 계정은 편집할 수 없습니다.']);
                exit;
            }
            
            // 편집할 데이터 수집
            $editData = [];
            $changes = []; // 변경 사항 추적용
            
            // 닉네임 검증 및 업데이트
            if (isset($_POST['nickname'])) {
                $nickname = trim($_POST['nickname']);
                if (empty($nickname)) {
                    echo json_encode(['error' => '닉네임은 필수입니다.']);
                    exit;
                }
                
                if (mb_strlen($nickname, 'UTF-8') < 2 || mb_strlen($nickname, 'UTF-8') > 20) {
                    echo json_encode(['error' => '닉네임은 2-20자 사이여야 합니다.']);
                    exit;
                }
                
                $editData['nickname'] = $nickname;
                $changes[] = "닉네임 → {$nickname}";
            }
            
            // 이메일 검증 및 업데이트
            if (isset($_POST['email'])) {
                $email = trim($_POST['email']);
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['error' => '유효하지 않은 이메일 형식입니다.']);
                    exit;
                }
                
                $editData['email'] = $email;
                $changes[] = "이메일 → {$email}";
            }
            
            // 전화번호 검증 및 업데이트
            if (isset($_POST['phone'])) {
                $phone = trim($_POST['phone']);
                if (empty($phone)) {
                    echo json_encode(['error' => '전화번호는 필수입니다.']);
                    exit;
                }
                
                // 전화번호 형식 검증 (010-1234-5678 형태만 허용)
                $phonePattern = '/^0[0-9]{1,2}-[0-9]{3,4}-[0-9]{4}$/';
                if (!preg_match($phonePattern, $phone)) {
                    echo json_encode(['error' => '올바른 전화번호 형식이 아닙니다. (예: 010-1234-5678)']);
                    exit;
                }
                
                // 전화번호 중복 검사 (자신 제외)
                $existingUser = $userModel->findByPhone($phone);
                if ($existingUser && $existingUser['id'] != $userId) {
                    echo json_encode(['error' => '이미 사용 중인 전화번호입니다.']);
                    exit;
                }
                
                $editData['phone'] = $phone;
                $changes[] = "전화번호 → {$phone}";
            }
            
            // 기본 정보가 있는 경우에만 프로필 업데이트
            if (!empty($editData)) {
                $result = $userModel->updateProfile($userId, $editData);
                
                if (!$result) {
                    echo json_encode(['error' => '프로필 업데이트에 실패했습니다.']);
                    exit;
                }
            }
            
            // 사용자 상태 업데이트 (별도 처리)
            if (isset($_POST['status'])) {
                $newStatus = $_POST['status'];
                $allowedStatuses = ['active', 'inactive', 'suspended'];
                
                if (!in_array($newStatus, $allowedStatuses)) {
                    echo json_encode(['error' => '유효하지 않은 상태입니다.']);
                    exit;
                }
                
                $reason = $_POST['status_reason'] ?? '관리자에 의한 상태 변경';
                $statusResult = $userModel->updateUserStatus($userId, $newStatus, $adminId, $reason);
                
                if ($statusResult) {
                    $changes[] = "상태 → {$newStatus}";
                    
                    // 상태 변경 알림 발송
                    $user = $this->db->fetch("SELECT nickname, phone FROM users WHERE id = ?", [$userId]);
                    if ($user && !empty($user['phone'])) {
                        $this->sendUserStatusNotification($user, $newStatus, $reason);
                    }
                }
            }
            
            // 사용자 권한 업데이트 (별도 처리)
            if (isset($_POST['role'])) {
                $newRole = $_POST['role'];
                $allowedRoles = ['ROLE_USER', 'ROLE_CORPORATE', 'ROLE_ADMIN'];
                
                if (!in_array($newRole, $allowedRoles)) {
                    echo json_encode(['error' => '유효하지 않은 권한입니다.']);
                    exit;
                }
                
                $reason = $_POST['role_reason'] ?? '관리자에 의한 권한 변경';
                $roleResult = $userModel->updateUserRole($userId, $newRole, $adminId, $reason);
                
                if ($roleResult) {
                    $changes[] = "권한 → {$newRole}";
                }
            }
            
            // 관리자 활동 로그 기록
            if (!empty($changes)) {
                $changeLog = implode(', ', $changes);
                $this->logUserActivity(
                    $adminId,
                    'USER_EDIT',
                    "사용자 ID {$userId} 정보 편집: {$changeLog}",
                    [
                        'target_user_id' => $userId,
                        'changes' => $changes,
                        'admin_id' => $adminId
                    ]
                );
                
                // WebLogger 사용 (가능한 경우)
                if (class_exists('WebLogger')) {
                    WebLogger::info('관리자 사용자 편집 완료', [
                        'admin_id' => $adminId,
                        'target_user_id' => $userId,
                        'changes_count' => count($changes),
                        'changes' => $changeLog
                    ]);
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => '사용자 정보가 성공적으로 업데이트되었습니다.',
                'changes' => $changes
            ]);
            
        } catch (Exception $e) {
            error_log('🔥 Ultra Think Debug: Exception 발생!');
            error_log('🔥 Ultra Think Debug: 에러 메시지: ' . $e->getMessage());
            error_log('🔥 Ultra Think Debug: 에러 파일: ' . $e->getFile());
            error_log('🔥 Ultra Think Debug: 에러 라인: ' . $e->getLine());
            error_log('🔥 Ultra Think Debug: 스택 트레이스: ' . $e->getTraceAsString());
            error_log('사용자 편집 오류: ' . $e->getMessage());
            
            if (class_exists('WebLogger')) {
                WebLogger::error('관리자 사용자 편집 실패', [
                    'admin_id' => $adminId ?? 'unknown',
                    'target_user_id' => $userId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // 개발 모드에서는 상세한 오류 정보 표시
            $isDevelopment = (getenv('APP_ENV') === 'development' || !empty($_GET['debug']));
            
            if ($isDevelopment) {
                echo json_encode([
                    'error' => '편집 중 오류가 발생했습니다.',
                    'debug' => [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => array_slice($e->getTrace(), 0, 5) // 상위 5개 스택만
                    ]
                ]);
            } else {
                echo json_encode(['error' => '편집 중 오류가 발생했습니다.']);
            }
        }
    }
    
    /**
     * 관리자 활동 로깅
     * 
     * @param int $adminId 관리자 ID
     * @param string $action 액션 타입
     * @param string $details 상세 내용  
     * @param array $metadata 추가 메타데이터
     */
    private function logUserActivity($adminId, $action, $details, $metadata = []) {
        try {
            // 기본 로깅
            $logMessage = "[ADMIN_LOG] Admin ID: {$adminId}, Action: {$action}, Details: {$details}";
            if (!empty($metadata)) {
                $logMessage .= ", Metadata: " . json_encode($metadata);
            }
            error_log($logMessage);
        } catch (Exception $e) {
            error_log("Admin activity logging failed: " . $e->getMessage());
        }
    }

    /**
     * 관리자 전용 뷰 렌더링 - 새로운 템플릿 시스템 사용
     */
    private function renderView($viewPath, $data = [], $headerData = []) {
        // 데이터를 변수로 추출
        extract($data);
        extract($headerData);
        
        // 새로운 템플릿 시스템 사용
        include SRC_PATH . '/views/' . $viewPath . '.php';
    }
}