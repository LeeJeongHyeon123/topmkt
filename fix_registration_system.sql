-- 🔧 강의 신청 시스템 수정 SQL
-- lecture_registrations 테이블이 없다면 생성

-- 기존 테이블이 있는지 확인 후 생성
CREATE TABLE IF NOT EXISTS `lecture_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lecture_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `participant_name` varchar(100) NOT NULL,
  `participant_email` varchar(255) NOT NULL,
  `participant_phone` varchar(20) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `motivation` text DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','waiting','cancelled') NOT NULL DEFAULT 'pending',
  `is_waiting_list` tinyint(1) NOT NULL DEFAULT 0,
  `waiting_order` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lecture_id` (`lecture_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_lecture_registrations_lecture` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lecture_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_lecture_registrations_processor` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 인덱스 최적화
ALTER TABLE `lecture_registrations` 
ADD INDEX `idx_lecture_status` (`lecture_id`, `status`),
ADD INDEX `idx_waiting_list` (`lecture_id`, `is_waiting_list`, `waiting_order`);

-- 테이블이 성공적으로 생성되었는지 확인
SELECT 'lecture_registrations 테이블 생성/확인 완료' as status;