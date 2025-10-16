-- =====================================================
-- 알림 설정 테이블 생성 스크립트
-- 작성일: 2025-10-16
-- 설명: FCM 앱 푸시 알림 설정을 위한 사용자별 설정 테이블
-- =====================================================

CREATE TABLE IF NOT EXISTS `notification_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '알림 설정 ID',
    `user_id` INT UNSIGNED NOT NULL COMMENT '사용자 ID',
    `all_notifications` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '전체 알림 ON/OFF (1=ON, 0=OFF)',
    `comments_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '댓글, 대댓글 알림 (1=ON, 0=OFF)',
    `likes_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '좋아요 알림 (1=ON, 0=OFF)',
    `lectures_events_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '신규 강의, 행사 알림 (1=ON, 0=OFF)',
    `registration_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '신청 승인, 거절 알림 (1=ON, 0=OFF)',
    `notices_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '공지사항 알림 (1=ON, 0=OFF)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_id` (`user_id`),
    KEY `idx_all_notifications` (`all_notifications`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_notification_settings_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자별 FCM 푸시 알림 설정';

-- =====================================================
-- 인덱스 설명
-- =====================================================
-- unique_user_id: 사용자당 하나의 설정만 존재하도록 보장
-- idx_all_notifications: 전체 알림 활성화된 사용자 빠른 조회
-- idx_created_at: 생성일 기준 정렬 및 조회

-- =====================================================
-- 사용 예시
-- =====================================================
-- 1. 신규 사용자 기본 설정 생성 (모든 알림 ON)
-- INSERT INTO notification_settings (user_id) VALUES (1);
--
-- 2. 특정 알림만 OFF
-- INSERT INTO notification_settings (user_id, likes_enabled) VALUES (2, 0);
--
-- 3. 전체 알림 OFF
-- UPDATE notification_settings SET all_notifications = 0 WHERE user_id = 1;
--
-- 4. 사용자 설정 조회
-- SELECT * FROM notification_settings WHERE user_id = 1;
