-- 탑마케팅 플랫폼 데이터베이스 스키마
-- 생성일: 2025-06-12
-- 이 파일은 서버 복구 시 데이터베이스 구조를 재생성하기 위한 스키마입니다.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- 데이터베이스 생성
CREATE DATABASE IF NOT EXISTS `topmkt` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `topmkt`;

-- 1. users 테이블
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `bio` text NULL,
  `birth_date` date NULL,
  `gender` enum('M','F','OTHER') NULL,
  `profile_image_original` varchar(255) NULL,
  `profile_image_profile` varchar(255) NULL,
  `profile_image_thumb` varchar(255) NULL,
  `website_url` varchar(255) NULL,
  `social_links` longtext NULL,
  `profile_image` varchar(255) NULL,
  `password_hash` varchar(255) NOT NULL,
  `marketing_agreed` boolean DEFAULT 0,
  `phone_verified` boolean DEFAULT 0,
  `email_verified` boolean DEFAULT 0,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL,
  `last_login` timestamp NULL,
  `remember_token` varchar(64) NULL,
  `remember_expires` datetime NULL,
  `status` enum('active','inactive','suspended','deleted') DEFAULT 'active',
  `role` enum('ROLE_USER', 'ROLE_CORP', 'ROLE_ADMIN') DEFAULT 'ROLE_USER',
  `extra_data` longtext NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. user_sessions 테이블
CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NULL,
  `last_activity` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. user_logs 테이블
CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL,
  `action` varchar(50) NOT NULL,
  `description` text NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NULL,
  `extra_data` json NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. verification_codes 테이블
CREATE TABLE `verification_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(13) NOT NULL,
  `code` varchar(6) NOT NULL,
  `type` enum('SIGNUP', 'LOGIN', 'PASSWORD_RESET') NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `is_used` boolean DEFAULT FALSE,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_phone` (`phone`),
  KEY `idx_code` (`code`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. posts 테이블
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) NULL,
  `view_count` int(11) DEFAULT 0,
  `like_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `status` enum('published','draft','deleted') DEFAULT 'published',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`),
  KEY `idx_posts_list_performance` (`status`, `created_at` DESC),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. comments 테이블
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) NULL,
  `content` text NOT NULL,
  `status` enum('active','deleted') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. settings 테이블
CREATE TABLE `settings` (
  `key_name` varchar(100) NOT NULL,
  `value` text NULL,
  `description` varchar(255) NULL,
  `type` enum('STRING', 'INTEGER', 'BOOLEAN', 'JSON') DEFAULT 'STRING',
  `is_public` boolean DEFAULT FALSE,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. lectures 테이블 (강의/이벤트)
CREATE TABLE `lectures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `instructor_name` varchar(100) NOT NULL,
  `instructor_bio` text NULL,
  `instructor_image` varchar(255) NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(200) NOT NULL,
  `address` varchar(500) NULL,
  `latitude` decimal(10, 8) NULL,
  `longitude` decimal(11, 8) NULL,
  `max_participants` int(11) NULL,
  `current_participants` int(11) DEFAULT 0,
  `price` decimal(10, 2) DEFAULT 0.00,
  `content_type` enum('lecture', 'event') DEFAULT 'lecture',
  `event_scale` enum('small', 'medium', 'large') NULL,
  `has_networking` boolean DEFAULT FALSE,
  `sponsor_info` text NULL,
  `dress_code` enum('casual', 'business_casual', 'business', 'formal') NULL,
  `parking_info` varchar(500) NULL,
  `youtube_video` varchar(255) NULL,
  `status` enum('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_content_type` (`content_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. event_images 테이블
CREATE TABLE `event_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(200) NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_display_order` (`display_order`),
  FOREIGN KEY (`event_id`) REFERENCES `lectures`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FULLTEXT 인덱스 추가 (검색 성능 최적화)
ALTER TABLE `posts` ADD FULLTEXT(`title`, `content`);

-- users 테이블에 기업 인증 상태 필드 추가
ALTER TABLE `users` ADD COLUMN `corp_status` ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none' AFTER `role`;
ALTER TABLE `users` ADD COLUMN `corp_approved_at` TIMESTAMP NULL AFTER `corp_status`;

-- 기업 프로필 테이블
CREATE TABLE `company_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL UNIQUE,
  `company_name` varchar(255) NOT NULL,
  `business_number` varchar(100) NOT NULL,
  `representative_name` varchar(100) NOT NULL,
  `representative_phone` varchar(20) NOT NULL,
  `company_address` text NOT NULL,
  `business_registration_file` varchar(255) NOT NULL,
  `is_overseas` boolean DEFAULT 0,
  
  -- 심사 관련
  `status` enum('pending', 'approved', 'rejected') DEFAULT 'pending',
  `admin_notes` text NULL,
  `processed_by` int(11) NULL,
  `processed_at` timestamp NULL,
  
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_business_number` (`business_number`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 기업 인증 신청/수정 이력 관리
CREATE TABLE `company_application_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_type` enum('apply', 'reapply', 'modify', 'approve', 'reject') NOT NULL,
  `old_data` longtext NULL,
  `new_data` longtext NULL,
  `admin_notes` text NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 기본 설정 데이터 삽입
INSERT INTO `settings` (`key_name`, `value`, `description`, `type`, `is_public`) VALUES
('site_name', '탑마케팅', '사이트 이름', 'STRING', TRUE),
('site_description', '마케팅 전문가들의 커뮤니티', '사이트 설명', 'STRING', TRUE),
('admin_email', 'admin@topmktx.com', '관리자 이메일', 'STRING', FALSE),
('default_timezone', 'Asia/Seoul', '기본 시간대', 'STRING', FALSE),
('max_upload_size', '10485760', '최대 업로드 크기 (바이트)', 'INTEGER', FALSE);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;