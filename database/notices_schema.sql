-- 공지사항 시스템 테이블 생성
-- 생성일: 2025-08-06
-- 기존 posts/comments 패턴을 준수하여 설계

USE TOPMKT;

-- 1. notices 테이블 (공지사항 메인 테이블)
CREATE TABLE `notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` longtext NOT NULL,
  `image_path` varchar(255) NULL,
  `view_count` int(11) DEFAULT 0,
  `like_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `is_featured` boolean DEFAULT FALSE,
  `status` enum('published','draft','deleted') DEFAULT 'published',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status_created` (`status`, `created_at` DESC),
  KEY `idx_company_published` (`company_id`, `status`, `created_at` DESC),
  KEY `idx_notices_list_performance` (`status`, `created_at` DESC),
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`company_id`) REFERENCES `company_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. notice_comments 테이블 (공지사항 댓글 테이블)
CREATE TABLE `notice_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notice_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) NULL,
  `content` text NOT NULL,
  `status` enum('active','deleted') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_notice_id` (`notice_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_status` (`status`),
  KEY `idx_notice_comments_list` (`notice_id`, `status`, `created_at` DESC),
  
  FOREIGN KEY (`notice_id`) REFERENCES `notices`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `notice_comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FULLTEXT 인덱스 추가 (검색 성능 최적화)
ALTER TABLE `notices` ADD FULLTEXT(`title`, `content`);

-- 초기 설정 데이터 삽입
INSERT INTO `settings` (`key_name`, `value`, `description`, `type`, `is_public`) VALUES
('notices_per_page', '20', '공지사항 페이지당 항목 수', 'INTEGER', FALSE),
('notices_allow_comments', '1', '공지사항 댓글 허용 여부', 'BOOLEAN', FALSE),
('notices_moderation', '0', '공지사항 사전 승인 필요 여부', 'BOOLEAN', FALSE);

-- 성공 메시지
SELECT '✅ 공지사항 시스템 테이블 생성 완료!' as result;