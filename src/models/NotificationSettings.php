<?php
/**
 * 알림 설정 모델 클래스
 * FCM 앱 푸시 알림 설정 관리
 *
 * 작성일: 2025-10-16
 */

require_once SRC_PATH . '/config/database.php';

class NotificationSettings {
    private $db;

    /**
     * 생성자
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * 사용자 알림 설정 조회
     * 설정이 없는 경우 기본값으로 자동 생성
     *
     * @param int $userId 사용자 ID
     * @return array|null 알림 설정 정보
     */
    public function getSettings($userId) {
        try {
            $sql = "SELECT * FROM notification_settings WHERE user_id = ?";
            $settings = $this->db->fetch($sql, [$userId]);

            // 설정이 없으면 기본 설정 생성 (모든 알림 ON)
            if (!$settings) {
                $this->createDefaultSettings($userId);
                $settings = $this->db->fetch($sql, [$userId]);
            }

            return $settings;
        } catch (Exception $e) {
            error_log('NotificationSettings::getSettings 오류: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 기본 알림 설정 생성
     * 신규 가입자는 모든 알림이 기본 ON (opt-out 방식)
     *
     * @param int $userId 사용자 ID
     * @return bool 성공 여부
     */
    public function createDefaultSettings($userId) {
        try {
            $sql = "INSERT INTO notification_settings (
                        user_id,
                        all_notifications,
                        comments_enabled,
                        likes_enabled,
                        lectures_events_enabled,
                        registration_enabled,
                        notices_enabled
                    ) VALUES (?, 1, 1, 1, 1, 1, 1)";

            $result = $this->db->execute($sql, [$userId]);

            if ($result) {
                error_log("✅ 사용자 ID {$userId}의 기본 알림 설정 생성 완료");
            }

            return $result > 0;
        } catch (Exception $e) {
            // DUPLICATE KEY 에러는 무시 (이미 설정이 존재하는 경우)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                error_log("⚠️ 사용자 ID {$userId}의 알림 설정이 이미 존재함");
                return true;
            }

            error_log('NotificationSettings::createDefaultSettings 오류: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 알림 설정 업데이트
     *
     * @param int $userId 사용자 ID
     * @param array $settings 업데이트할 설정 (키-값 배열)
     * @return bool 성공 여부
     */
    public function updateSettings($userId, $settings) {
        try {
            $this->db->beginTransaction();

            // 설정이 없으면 먼저 생성
            $existingSettings = $this->getSettings($userId);
            if (!$existingSettings) {
                $this->createDefaultSettings($userId);
            }

            // 업데이트할 필드 구성
            $fields = [];
            $params = [];

            $allowedFields = [
                'all_notifications',
                'comments_enabled',
                'likes_enabled',
                'lectures_events_enabled',
                'registration_enabled',
                'notices_enabled'
            ];

            foreach ($allowedFields as $field) {
                if (isset($settings[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $settings[$field] ? 1 : 0;
                }
            }

            if (empty($fields)) {
                $this->db->rollback();
                return false;
            }

            // user_id를 마지막 파라미터로 추가
            $params[] = $userId;

            $sql = "UPDATE notification_settings
                    SET " . implode(', ', $fields) . ", updated_at = NOW()
                    WHERE user_id = ?";

            $result = $this->db->execute($sql, $params);

            $this->db->commit();

            if ($result) {
                error_log("✅ 사용자 ID {$userId}의 알림 설정 업데이트 완료");
            }

            return $result > 0;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log('NotificationSettings::updateSettings 오류: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 특정 알림 타입이 활성화되어 있는지 확인
     * 전체 알림이 꺼져 있으면 개별 설정과 관계없이 false 반환
     *
     * @param int $userId 사용자 ID
     * @param string $notificationType 알림 타입 (comments|likes|lectures_events|registration|notices)
     * @return bool 활성화 여부
     */
    public function isNotificationEnabled($userId, $notificationType) {
        try {
            $settings = $this->getSettings($userId);

            if (!$settings) {
                return true; // 기본값: 활성화
            }

            // 전체 알림이 꺼져 있으면 false
            if (!$settings['all_notifications']) {
                return false;
            }

            // 타입별 필드명 매핑
            $fieldMap = [
                'comments' => 'comments_enabled',
                'likes' => 'likes_enabled',
                'lectures_events' => 'lectures_events_enabled',
                'registration' => 'registration_enabled',
                'notices' => 'notices_enabled'
            ];

            $fieldName = $fieldMap[$notificationType] ?? null;

            if (!$fieldName) {
                error_log("⚠️ 알 수 없는 알림 타입: {$notificationType}");
                return false;
            }

            return (bool)$settings[$fieldName];

        } catch (Exception $e) {
            error_log('NotificationSettings::isNotificationEnabled 오류: ' . $e->getMessage());
            return true; // 오류 시 기본적으로 알림 허용
        }
    }

    /**
     * 전체 알림 ON/OFF 토글
     *
     * @param int $userId 사용자 ID
     * @param bool $enabled 활성화 여부
     * @return bool 성공 여부
     */
    public function toggleAllNotifications($userId, $enabled) {
        return $this->updateSettings($userId, ['all_notifications' => $enabled]);
    }

    /**
     * 여러 사용자의 알림 설정 조회 (배치 처리용)
     *
     * @param array $userIds 사용자 ID 배열
     * @return array 사용자별 알림 설정 (user_id를 키로 하는 연관 배열)
     */
    public function getBulkSettings($userIds) {
        try {
            if (empty($userIds)) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $sql = "SELECT * FROM notification_settings WHERE user_id IN ($placeholders)";

            $settings = $this->db->fetchAll($sql, $userIds);

            // user_id를 키로 하는 연관 배열로 변환
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting['user_id']] = $setting;
            }

            return $result;

        } catch (Exception $e) {
            error_log('NotificationSettings::getBulkSettings 오류: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 특정 알림 타입이 활성화된 사용자 ID 목록 조회
     * FCM 푸시 발송 시 사용
     *
     * @param string $notificationType 알림 타입
     * @param array $targetUserIds 대상 사용자 ID 배열 (선택)
     * @return array 알림이 활성화된 사용자 ID 배열
     */
    public function getEnabledUserIds($notificationType, $targetUserIds = null) {
        try {
            // 타입별 필드명 매핑
            $fieldMap = [
                'comments' => 'comments_enabled',
                'likes' => 'likes_enabled',
                'lectures_events' => 'lectures_events_enabled',
                'registration' => 'registration_enabled',
                'notices' => 'notices_enabled'
            ];

            $fieldName = $fieldMap[$notificationType] ?? null;

            if (!$fieldName) {
                error_log("⚠️ 알 수 없는 알림 타입: {$notificationType}");
                return [];
            }

            // 기본 쿼리: 전체 알림 + 해당 타입 알림 모두 활성화된 사용자
            $sql = "SELECT user_id FROM notification_settings
                    WHERE all_notifications = 1 AND {$fieldName} = 1";

            $params = [];

            // 특정 사용자만 조회하는 경우
            if ($targetUserIds && !empty($targetUserIds)) {
                $placeholders = implode(',', array_fill(0, count($targetUserIds), '?'));
                $sql .= " AND user_id IN ($placeholders)";
                $params = $targetUserIds;
            }

            $results = $this->db->fetchAll($sql, $params);

            return array_column($results, 'user_id');

        } catch (Exception $e) {
            error_log('NotificationSettings::getEnabledUserIds 오류: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 알림 설정 삭제 (회원 탈퇴 시 호출)
     * CASCADE 옵션으로 자동 삭제되지만 명시적으로 제공
     *
     * @param int $userId 사용자 ID
     * @return bool 성공 여부
     */
    public function deleteSettings($userId) {
        try {
            $sql = "DELETE FROM notification_settings WHERE user_id = ?";
            $result = $this->db->execute($sql, [$userId]);

            error_log("🗑️ 사용자 ID {$userId}의 알림 설정 삭제 완료");

            return $result > 0;

        } catch (Exception $e) {
            error_log('NotificationSettings::deleteSettings 오류: ' . $e->getMessage());
            return false;
        }
    }
}
