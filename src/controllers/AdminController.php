<?php
/**
 * AdminController 클래스
 * 관리자 페이지 전용 컨트롤러
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

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
            header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        // 관리자 권한 체크
        $user = AuthMiddleware::getCurrentUser();
        if ($user['role'] !== 'ROLE_ADMIN') {
            header('HTTP/1.1 403 Forbidden');
            include SRC_PATH . '/views/templates/403.php';
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
            'reports' => [], // TODO: 신고 시스템 구현 시 추가
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
        // 대기 중인 기업인증 목록 조회
        $pendingApplications = $this->getPendingCorporateApplications();
        
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
        // 전체 기업회원 목록 조회
        $corporateMembers = $this->getCorporateMembers();
        
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
            $applicationDetail = $this->getApplicationDetail($applicationId);
            if (!$applicationDetail) {
                echo json_encode(['error' => '신청 정보를 찾을 수 없습니다.']);
                exit;
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
            
            // 사용자 정보 조회
            $user = $this->db->fetch("SELECT phone, nickname FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                error_log("SMS 발송 실패: 사용자를 찾을 수 없음 (ID: {$userId})");
                return false;
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
            
            // 사용자 정보 조회
            $user = $this->db->fetch("SELECT phone, nickname FROM users WHERE id = ?", [$userId]);
            
            if (!$user || !$user['phone']) {
                return false;
            }
            
            // SMS 메시지 작성
            if ($newStatus === 'approved') {
                $message = "[탑마케팅] 기업인증이 재승인되었습니다. 서비스를 계속 이용하실 수 있습니다.";
            } else {
                $message = "[탑마케팅] 기업회원 서비스가 일시정지되었습니다. 문의: 070-4138-8899";
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
     * CSRF 토큰 검증
     */
    private function verifyCsrfToken() {
        $token = $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
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