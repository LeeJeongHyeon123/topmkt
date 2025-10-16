<?php
/**
 * FCM 토큰 모델
 * Firebase Cloud Messaging 토큰 관리
 *
 * 작성일: 2025-10-16
 */

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/WebLogger.php';

class FcmToken
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * FCM 토큰 등록 (UPSERT 방식)
     *
     * @param int $userId 사용자 ID
     * @param string $fcmToken FCM 토큰
     * @param string $deviceType 디바이스 타입 (android|ios|web)
     * @param string|null $deviceName 디바이스 이름
     * @param string|null $appVersion 앱 버전
     * @return bool 성공 여부
     */
    public function registerToken($userId, $fcmToken, $deviceType = 'android', $deviceName = null, $appVersion = null)
    {
        try {
            $sql = "INSERT INTO fcm_tokens
                    (user_id, fcm_token, device_type, device_name, app_version, is_active, last_used_at)
                    VALUES (?, ?, ?, ?, ?, 1, NOW())
                    ON DUPLICATE KEY UPDATE
                        device_type = VALUES(device_type),
                        device_name = VALUES(device_name),
                        app_version = VALUES(app_version),
                        is_active = 1,
                        last_used_at = NOW(),
                        updated_at = CURRENT_TIMESTAMP";

            $params = [$userId, $fcmToken, $deviceType, $deviceName, $appVersion];
            $this->db->execute($sql, $params);

            WebLogger::info('FCM 토큰 등록 성공', [
                'user_id' => $userId,
                'device_type' => $deviceType,
                'token_length' => strlen($fcmToken)
            ]);

            return true;
        } catch (Exception $e) {
            WebLogger::error('FCM 토큰 등록 실패', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * FCM 토큰 삭제 (논리 삭제 - is_active = 0)
     *
     * @param int $userId 사용자 ID
     * @param string $fcmToken FCM 토큰
     * @return bool 성공 여부
     */
    public function deleteToken($userId, $fcmToken)
    {
        try {
            $sql = "UPDATE fcm_tokens
                    SET is_active = 0, updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = ? AND fcm_token = ?";

            $affected = $this->db->execute($sql, [$userId, $fcmToken]);

            WebLogger::info('FCM 토큰 삭제 성공', [
                'user_id' => $userId,
                'affected_rows' => $affected
            ]);

            return $affected > 0;
        } catch (Exception $e) {
            WebLogger::error('FCM 토큰 삭제 실패', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 사용자의 모든 활성 FCM 토큰 조회
     *
     * @param int $userId 사용자 ID
     * @return array FCM 토큰 목록
     */
    public function getTokensByUserId($userId)
    {
        try {
            $sql = "SELECT id, fcm_token, device_type, device_name, app_version,
                           last_used_at, created_at, updated_at
                    FROM fcm_tokens
                    WHERE user_id = ? AND is_active = 1
                    ORDER BY updated_at DESC";

            return $this->db->fetchAll($sql, [$userId]);
        } catch (Exception $e) {
            WebLogger::error('FCM 토큰 조회 실패', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * 특정 FCM 토큰 정보 조회
     *
     * @param string $fcmToken FCM 토큰
     * @return array|false 토큰 정보 또는 false
     */
    public function getTokenInfo($fcmToken)
    {
        try {
            $sql = "SELECT * FROM fcm_tokens
                    WHERE fcm_token = ? AND is_active = 1
                    LIMIT 1";

            return $this->db->fetch($sql, [$fcmToken]);
        } catch (Exception $e) {
            WebLogger::error('FCM 토큰 정보 조회 실패', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * FCM 토큰 비활성화 (만료된 토큰 처리)
     *
     * @param string $fcmToken FCM 토큰
     * @return bool 성공 여부
     */
    public function deactivateToken($fcmToken)
    {
        try {
            $sql = "UPDATE fcm_tokens
                    SET is_active = 0, updated_at = CURRENT_TIMESTAMP
                    WHERE fcm_token = ?";

            $affected = $this->db->execute($sql, [$fcmToken]);

            WebLogger::info('FCM 토큰 비활성화', [
                'token_length' => strlen($fcmToken),
                'affected_rows' => $affected
            ]);

            return $affected > 0;
        } catch (Exception $e) {
            WebLogger::error('FCM 토큰 비활성화 실패', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 마지막 사용 시간 업데이트
     *
     * @param string $fcmToken FCM 토큰
     * @return bool 성공 여부
     */
    public function updateLastUsedAt($fcmToken)
    {
        try {
            $sql = "UPDATE fcm_tokens
                    SET last_used_at = NOW(), updated_at = CURRENT_TIMESTAMP
                    WHERE fcm_token = ? AND is_active = 1";

            $this->db->execute($sql, [$fcmToken]);
            return true;
        } catch (Exception $e) {
            WebLogger::error('FCM 토큰 last_used_at 업데이트 실패', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 알림 설정이 활성화된 사용자의 FCM 토큰 조회
     *
     * @param string $notificationType 알림 타입 (comments|likes|lectures_events|registration|notices)
     * @return array FCM 토큰 목록
     */
    public function getTokensByNotificationType($notificationType)
    {
        try {
            require_once SRC_PATH . '/models/NotificationSettings.php';
            $notificationSettings = new NotificationSettings();

            // 해당 알림 타입이 활성화된 사용자 ID 목록 조회
            $enabledUserIds = $notificationSettings->getEnabledUserIds($notificationType);

            if (empty($enabledUserIds)) {
                return [];
            }

            // IN 절을 위한 플레이스홀더 생성
            $placeholders = implode(',', array_fill(0, count($enabledUserIds), '?'));

            $sql = "SELECT ft.fcm_token, ft.user_id, ft.device_type
                    FROM fcm_tokens ft
                    WHERE ft.user_id IN ($placeholders) AND ft.is_active = 1
                    ORDER BY ft.updated_at DESC";

            return $this->db->fetchAll($sql, $enabledUserIds);
        } catch (Exception $e) {
            WebLogger::error('알림 타입별 FCM 토큰 조회 실패', [
                'notification_type' => $notificationType,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * 사용자의 모든 FCM 토큰 완전 삭제 (물리 삭제)
     *
     * @param int $userId 사용자 ID
     * @return bool 성공 여부
     */
    public function deleteAllUserTokens($userId)
    {
        try {
            $sql = "DELETE FROM fcm_tokens WHERE user_id = ?";
            $affected = $this->db->execute($sql, [$userId]);

            WebLogger::info('사용자 FCM 토큰 전체 삭제', [
                'user_id' => $userId,
                'affected_rows' => $affected
            ]);

            return true;
        } catch (Exception $e) {
            WebLogger::error('사용자 FCM 토큰 전체 삭제 실패', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
