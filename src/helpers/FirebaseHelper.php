<?php
/**
 * Firebase Realtime Database 헬퍼 클래스
 * REST API를 통한 Firebase 데이터 업데이트
 */

require_once SRC_PATH . '/config/database.php';

class FirebaseHelper {
    private static $firebaseUrl;
    private static $initialized = false;
    
    /**
     * Firebase 초기화
     */
    private static function init() {
        if (!self::$initialized) {
            // ChatController에서 사용하는 것과 동일한 설정
            $databaseUrl = $_ENV['FIREBASE_DATABASE_URL'] ?? "https://topmkt-832f2-default-rtdb.asia-southeast1.firebasedatabase.app/";
            self::$firebaseUrl = rtrim($databaseUrl, '/');
            self::$initialized = true;
        }
    }
    
    /**
     * 신청 대기 알림 업데이트
     * 
     * @param int $userId 사용자 ID
     * @param int $count 대기 중인 신청 수
     * @param array $details 상세 정보 (lectures, events)
     * @return bool 성공 여부
     */
    public static function updatePendingNotification($userId, $count, $details = []) {
        self::init();
        
        try {
            $data = [
                'count' => $count,
                'message' => $count > 0 ? "{$count}개의 신청이 처리 대기 중입니다" : "대기 중인 신청이 없습니다",
                'timestamp' => time() * 1000, // JavaScript Date 호환
                'lastUpdated' => date('Y-m-d H:i:s'),
                'details' => $details
            ];
            
            // Firebase REST API로 데이터 업데이트
            $url = self::$firebaseUrl . "/pendingRegistrations/{$userId}.json";
            
            $result = self::sendFirebaseRequest($url, 'PUT', $data);
            
            if ($result !== false) {
                error_log("Firebase 알림 업데이트 성공 - 사용자: {$userId}, 건수: {$count}");
                return true;
            } else {
                error_log("Firebase 알림 업데이트 실패 - 사용자: {$userId}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Firebase 알림 업데이트 오류: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 신청 대기 알림 제거
     * 
     * @param int $userId 사용자 ID
     * @return bool 성공 여부
     */
    public static function removePendingNotification($userId) {
        return self::updatePendingNotification($userId, 0);
    }
    
    /**
     * Firebase REST API 요청 전송
     * 
     * @param string $url 요청 URL
     * @param string $method HTTP 메소드
     * @param array $data 전송할 데이터
     * @return mixed 응답 데이터 또는 false
     */
    private static function sendFirebaseRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: TopMKT-Firebase-Helper/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || !empty($error)) {
            error_log("Firebase API 요청 오류: {$error}");
            return false;
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        } else {
            error_log("Firebase API HTTP 오류: {$httpCode}, 응답: {$response}");
            return false;
        }
    }
    
    /**
     * 사용자의 현재 대기 신청 수 조회 (DB 기반)
     * 
     * @param int $userId 사용자 ID
     * @return array ['count' => int, 'details' => array]
     */
    public static function calculatePendingCount($userId) {
        try {
            $db = Database::getInstance();
            
            // 강의 신청 대기 건수
            $lectureQuery = "
                SELECT COUNT(*) as count
                FROM lecture_registrations lr
                JOIN lectures l ON lr.lecture_id = l.id
                WHERE l.user_id = ? AND lr.status = 'pending'
            ";
            
            $lectureStmt = $db->prepare($lectureQuery);
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
            
            $eventStmt = $db->prepare($eventQuery);
            $eventStmt->bind_param("i", $userId);
            $eventStmt->execute();
            $eventResult = $eventStmt->get_result()->fetch_assoc();
            $eventCount = $eventResult['count'] ?? 0;
            
            return [
                'count' => $lectureCount + $eventCount,
                'details' => [
                    'lectures' => $lectureCount,
                    'events' => $eventCount
                ]
            ];
            
        } catch (Exception $e) {
            error_log("대기 신청 건수 계산 오류: " . $e->getMessage());
            return ['count' => 0, 'details' => ['lectures' => 0, 'events' => 0]];
        }
    }
}
?>