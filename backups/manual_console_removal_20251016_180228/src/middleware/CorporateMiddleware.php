<?php
/**
 * CorporateMiddleware 클래스
 * 기업회원 권한 검증 미들웨어
 */

require_once SRC_PATH . '/models/Corporate.php';

class CorporateMiddleware {
    
    /**
     * 기업회원 권한 필요
     */
    public static function requireCorporatePermission() {
        // 로그인 확인
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        // 기업회원 권한 확인
        if (!self::hasCorpPermission()) {
            // 기업회원 안내 페이지로 리다이렉트
            header('Location: /corp/info?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
    
    /**
     * 기업회원 권한 확인 (리다이렉트 없음)
     */
    public static function hasCorpPermission() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        try {
            require_once SRC_PATH . '/config/database.php';
            $db = Database::getInstance();
            
            $sql = "SELECT corp_status FROM users WHERE id = ? AND corp_status = 'approved'";
            $result = $db->fetch($sql, [$_SESSION['user_id']]);
            
            return $result !== false;
            
        } catch (Exception $e) {
            error_log('CorporateMiddleware::hasCorpPermission() error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 기업회원 신청 상태 확인
     */
    public static function getCorpStatus() {
        if (!isset($_SESSION['user_id'])) {
            return 'none';
        }
        
        try {
            require_once SRC_PATH . '/config/database.php';
            $db = Database::getInstance();
            
            $sql = "SELECT corp_status FROM users WHERE id = ?";
            $result = $db->fetch($sql, [$_SESSION['user_id']]);
            
            return $result ? $result['corp_status'] : 'none';
            
        } catch (Exception $e) {
            error_log('CorporateMiddleware::getCorpStatus() error: ' . $e->getMessage());
            return 'none';
        }
    }
    
    /**
     * 강의/행사 등록 권한 체크
     */
    public static function checkLectureEventPermission() {
        $corpStatus = self::getCorpStatus();
        
        return [
            'hasPermission' => $corpStatus === 'approved',
            'status' => $corpStatus,
            'message' => self::getPermissionMessage($corpStatus)
        ];
    }
    
    /**
     * 권한 상태별 메시지 반환
     */
    private static function getPermissionMessage($status) {
        switch ($status) {
            case 'none':
                return '강의 및 행사 등록은 기업회원만 가능합니다. 기업 인증을 신청해주세요.';
            case 'pending':
                return '기업 인증 심사가 진행 중입니다. 승인 후 강의 및 행사를 등록하실 수 있습니다.';
            case 'rejected':
                return '기업 인증이 거절되었습니다. 거절 사유를 확인하고 재신청해주세요.';
            case 'approved':
                return '기업회원으로 인증되었습니다. 강의 및 행사를 자유롭게 등록하실 수 있습니다.';
            default:
                return '기업 인증 상태를 확인할 수 없습니다.';
        }
    }
    
    /**
     * AJAX 요청용 권한 체크 (JSON 응답)
     */
    public static function checkPermissionAjax() {
        $permission = self::checkLectureEventPermission();
        
        header('Content-Type: application/json; charset=utf-8');
        
        if (!$permission['hasPermission']) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => $permission['message'],
                'status' => $permission['status'],
                'redirect_url' => '/corp/info'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        return true;
    }
    
    /**
     * 기업회원 전용 페이지 접근 제어
     */
    public static function requireApprovedCorporate() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        $status = self::getCorpStatus();
        
        if ($status !== 'approved') {
            $_SESSION['error_message'] = '승인된 기업회원만 접근할 수 있습니다.';
            header('Location: /corp/info');
            exit;
        }
    }
    
    /**
     * 관리자 권한 확인
     */
    public static function requireAdminPermission() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }
        
        try {
            require_once SRC_PATH . '/config/database.php';
            $db = Database::getInstance();
            
            $sql = "SELECT role FROM users WHERE id = ? AND role IN ('ROLE_ADMIN', 'ROLE_SUPER_ADMIN')";
            $result = $db->fetch($sql, [$_SESSION['user_id']]);
            
            if (!$result) {
                http_response_code(403);
                die('접근 권한이 없습니다.');
            }
            
        } catch (Exception $e) {
            error_log('CorporateMiddleware::requireAdminPermission() error: ' . $e->getMessage());
            http_response_code(500);
            die('시스템 오류가 발생했습니다.');
        }
    }
    
    /**
     * 페이지별 권한 제어 설정
     */
    public static function applyPagePermissions($page) {
        switch ($page) {
            case 'lectures_create':
            case 'lectures_edit':
            case 'events_create':
            case 'events_edit':
                self::requireCorporatePermission();
                break;
                
            case 'corp_edit':
                self::requireApprovedCorporate();
                break;
                
            case 'admin_corporate':
                self::requireAdminPermission();
                break;
        }
    }
}