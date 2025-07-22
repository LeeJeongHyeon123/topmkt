<?php
/**
 * 신청 대기 알림 컨트롤러
 * 강의/행사 신청 대기 건수를 확인하고 알림을 제공
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/middlewares/AuthMiddleware.php';
require_once SRC_PATH . '/helpers/ResponseHelper.php';

class RegistrationNotificationController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 기업 유저의 대기 중인 신청 건수 조회
     */
    public function getPendingCount() {
        header('Content-Type: application/json');
        
        try {
            // 로그인 확인
            if (!AuthMiddleware::isLoggedIn()) {
                return ResponseHelper::json(null, 401, '로그인이 필요합니다.');
            }
            
            $userId = AuthMiddleware::getCurrentUserId();
            $userRole = AuthMiddleware::getUserRole();
            
            // 기업 유저만 알림 표시
            if ($userRole !== 'ROLE_CORP') {
                return ResponseHelper::json(['count' => 0], 200, '일반 사용자는 알림 대상이 아닙니다.');
            }
            
            // 사용자가 등록한 강의/행사 중 대기 중인 신청 건수 조회
            $pendingCount = $this->getPendingRegistrationCount($userId);
            
            return ResponseHelper::json([
                'count' => $pendingCount,
                'message' => $pendingCount > 0 ? "{$pendingCount}개의 신청이 처리 대기 중입니다." : '대기 중인 신청이 없습니다.'
            ], 200, '대기 건수 조회 완료');
            
        } catch (Exception $e) {
            error_log("신청 대기 알림 조회 오류: " . $e->getMessage());
            return ResponseHelper::json(null, 500, '대기 건수 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 기업 유저가 등록한 강의/행사의 대기 중인 신청 건수 조회
     */
    private function getPendingRegistrationCount($userId) {
        try {
            // 강의 신청 대기 건수
            $lectureQuery = "
                SELECT COUNT(*) as count
                FROM lecture_registrations lr
                JOIN lectures l ON lr.lecture_id = l.id
                WHERE l.user_id = ? AND lr.status = 'pending'
            ";
            
            $lectureStmt = $this->db->prepare($lectureQuery);
            $lectureStmt->bind_param("i", $userId);
            $lectureStmt->execute();
            $lectureResult = $lectureStmt->get_result()->fetch_assoc();
            $lectureCount = $lectureResult['count'] ?? 0;
            
            // 행사 신청 대기 건수
            $eventQuery = "
                SELECT COUNT(*) as count
                FROM event_registrations er
                JOIN lectures l ON er.event_id = l.id
                WHERE l.user_id = ? AND er.status = 'pending' AND l.content_type = 'event'
            ";
            
            $eventStmt = $this->db->prepare($eventQuery);
            $eventStmt->bind_param("i", $userId);
            $eventStmt->execute();
            $eventResult = $eventStmt->get_result()->fetch_assoc();
            $eventCount = $eventResult['count'] ?? 0;
            
            return $lectureCount + $eventCount;
            
        } catch (Exception $e) {
            error_log("대기 신청 건수 조회 오류: " . $e->getMessage());
            return 0;
        }
    }
}
?>