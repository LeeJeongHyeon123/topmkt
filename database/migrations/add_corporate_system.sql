-- 기업회원 시스템 추가 마이그레이션
-- 생성일: 2025-06-12
-- 설명: 기업 인증 신청 및 관리 시스템을 위한 데이터베이스 구조 추가

USE `topmkt`;

-- 1. users 테이블에 기업 인증 관련 필드 추가
ALTER TABLE `users` 
ADD COLUMN `corp_status` ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none' AFTER `role`,
ADD COLUMN `corp_approved_at` TIMESTAMP NULL AFTER `corp_status`;

-- 2. 기업 프로필 테이블 생성
CREATE TABLE `company_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL UNIQUE,
  `company_name` varchar(255) NOT NULL COMMENT '회사명',
  `business_number` varchar(100) NOT NULL COMMENT '사업자등록번호',
  `representative_name` varchar(100) NOT NULL COMMENT '대표자명',
  `representative_phone` varchar(20) NOT NULL COMMENT '대표자 연락처',
  `company_address` text NOT NULL COMMENT '회사 주소',
  `business_registration_file` varchar(255) NOT NULL COMMENT '사업자등록증 파일 경로',
  `is_overseas` boolean DEFAULT 0 COMMENT '해외 기업 여부',
  
  -- 심사 관련 필드
  `status` enum('pending', 'approved', 'rejected') DEFAULT 'pending' COMMENT '심사 상태',
  `admin_notes` text NULL COMMENT '관리자 메모/거절 사유',
  `processed_by` int(11) NULL COMMENT '처리한 관리자 ID',
  `processed_at` timestamp NULL COMMENT '처리 일시',
  
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_id` (`user_id`),
  UNIQUE KEY `unique_business_number` (`business_number`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_processed_at` (`processed_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='기업 프로필 정보';

-- 3. 기업 인증 신청/수정 이력 관리 테이블 생성
CREATE TABLE `company_application_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '신청자 ID',
  `action_type` enum('apply', 'reapply', 'modify', 'approve', 'reject') NOT NULL COMMENT '액션 타입',
  `old_data` longtext NULL COMMENT '변경 전 데이터 (JSON)',
  `new_data` longtext NULL COMMENT '변경 후 데이터 (JSON)',
  `admin_notes` text NULL COMMENT '관리자 메모',
  `created_by` int(11) NOT NULL COMMENT '액션 수행자 ID',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_created_by` (`created_by`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='기업 인증 신청/수정 이력';

-- 4. 인덱스 최적화 (추가)
ALTER TABLE `users` ADD INDEX `idx_corp_status` (`corp_status`);
ALTER TABLE `users` ADD INDEX `idx_corp_approved_at` (`corp_approved_at`);

-- 5. 기본 설정값 추가 (settings 테이블이 있다면)
INSERT IGNORE INTO `settings` (`key_name`, `value`, `description`, `type`, `is_public`) VALUES
('corp_approval_required', '1', '기업회원 승인 필수 여부', 'BOOLEAN', FALSE),
('corp_file_max_size', '10485760', '기업 서류 최대 업로드 크기 (10MB)', 'INTEGER', FALSE),
('corp_allowed_extensions', 'jpg,jpeg,png,pdf', '허용된 파일 확장자', 'STRING', FALSE);

-- 마이그레이션 완료 로그
INSERT INTO `user_logs` (`user_id`, `action`, `description`, `ip_address`, `user_agent`) VALUES
(NULL, 'SYSTEM_MIGRATION', '기업회원 시스템 마이그레이션 완료', '127.0.0.1', 'System Migration Script');

COMMIT;