<?php
/**
 * NotificationHelper 클래스
 * 알림 관련 공통 기능을 제공합니다.
 */

require_once SRC_PATH . '/helpers/SmsHelper.php';

class NotificationHelper {

    /**
     * 사용자에게 알림 전송
     */
    public static function sendNotification($userId, $type, $title, $message, $data = []) {
        try {
            // SMS 알림
            if (self::shouldSendSMS($type)) {
                $user = self::getUserById($userId);
                if ($user && $user['phone']) {
                    SmsHelper::send($user['phone'], $message);
                }
            }

            // 이메일 알림
            if (self::shouldSendEmail($type)) {
                $user = self::getUserById($userId);
                if ($user && $user['email']) {
                    // 이메일 알림 로직 (MailHelper 사용)
                    self::sendEmailNotification($user, $title, $message, $data);
                }
            }

            // 데이터베이스에 알림 기록
            self::logNotification($userId, $type, $title, $message, $data);

            return true;
        } catch (Exception $e) {
            error_log("알림 전송 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 강의 신청 관련 알림
     */
    public static function sendLectureRegistrationNotification($userId, $lectureTitle, $status) {
        $messages = [
            'pending' => "강의 '{$lectureTitle}' 신청이 접수되었습니다. 승인 결과를 기다려주세요.",
            'approved' => "축하합니다! 강의 '{$lectureTitle}' 신청이 승인되었습니다.",
            'rejected' => "안타깝게도 강의 '{$lectureTitle}' 신청이 거절되었습니다."
        ];

        $message = $messages[$status] ?? "강의 '{$lectureTitle}' 신청 상태가 변경되었습니다.";

        return self::sendNotification(
            $userId,
            'lecture_registration',
            "강의 신청 알림",
            $message,
            ['lecture_title' => $lectureTitle, 'status' => $status]
        );
    }

    /**
     * 채팅 메시지 알림
     */
    public static function sendChatMessageNotification($userId, $senderName, $message) {
        return self::sendNotification(
            $userId,
            'chat_message',
            "새 메시지",
            "{$senderName}: {$message}",
            ['sender_name' => $senderName, 'message' => $message]
        );
    }

    /**
     * SMS 알림이 필요한 타입인지 확인
     */
    private static function shouldSendSMS($type) {
        $smsTypes = ['lecture_registration', 'system_important'];
        return in_array($type, $smsTypes);
    }

    /**
     * 이메일 알림이 필요한 타입인지 확인
     */
    private static function shouldSendEmail($type) {
        $emailTypes = ['lecture_registration', 'account_update', 'security_alert'];
        return in_array($type, $emailTypes);
    }

    /**
     * 사용자 정보 조회
     */
    private static function getUserById($userId) {
        $db = Database::getInstance();
        return $db->fetch("SELECT phone, email, nickname FROM users WHERE id = ?", [$userId]);
    }

    /**
     * 이메일 알림 전송
     */
    private static function sendEmailNotification($user, $title, $message, $data = []) {
        // MailHelper를 사용하여 이메일 전송
        if (class_exists('MailHelper')) {
            $htmlContent = "
                <h2>{$title}</h2>
                <p>{$message}</p>
            ";

            if (isset($data['action_url'])) {
                $htmlContent .= "<p><a href='{$data['action_url']}' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>바로가기</a></p>";
            }

            return MailHelper::send($user['email'], $title, $htmlContent);
        }

        return false;
    }

    /**
     * 알림 로그 기록
     */
    private static function logNotification($userId, $type, $title, $message, $data = []) {
        try {
            $db = Database::getInstance();
            $db->execute(
                "INSERT INTO notifications (user_id, type, title, message, data, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$userId, $type, $title, $message, json_encode($data)]
            );
        } catch (Exception $e) {
            error_log("알림 로그 기록 실패: " . $e->getMessage());
        }
    }

    /**
     * 사용자 알림 목록 조회
     */
    public static function getUserNotifications($userId, $limit = 20, $offset = 0) {
        try {
            $db = Database::getInstance();
            return $db->fetchAll(
                "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
                [$userId, $limit, $offset]
            );
        } catch (Exception $e) {
            error_log("알림 목록 조회 실패: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 읽지 않은 알림 수 조회
     */
    public static function getUnreadCount($userId) {
        try {
            $db = Database::getInstance();
            $result = $db->fetch(
                "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
                [$userId]
            );
            return $result ? $result['count'] : 0;
        } catch (Exception $e) {
            error_log("읽지 않은 알림 수 조회 실패: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 알림을 읽음으로 표시
     */
    public static function markAsRead($notificationId, $userId) {
        try {
            $db = Database::getInstance();
            return $db->execute(
                "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?",
                [$notificationId, $userId]
            );
        } catch (Exception $e) {
            error_log("알림 읽음 표시 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 모든 알림을 읽음으로 표시
     */
    public static function markAllAsRead($userId) {
        try {
            $db = Database::getInstance();
            return $db->execute(
                "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0",
                [$userId]
            );
        } catch (Exception $e) {
            error_log("모든 알림 읽음 표시 실패: " . $e->getMessage());
            return false;
        }
    }
}
?>

