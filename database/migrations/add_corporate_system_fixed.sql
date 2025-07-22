-- 기업회원 시스템 추가 마이그레이션 (수정버전)
-- 생성일: 2025-06-12
-- 설명: 기업 인증 신청 및 관리 시스템을 위한 데이터베이스 구조 추가

USE `topmkt`;

-- 1. users 테이블에 기업 인증 관련 필드 추가 (이미 존재하는지 확인)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'corp_status') > 0,
    'SELECT "corp_status column already exists" as message',
    'ALTER TABLE `users` ADD COLUMN `corp_status` ENUM(''none'', ''pending'', ''approved'', ''rejected'') DEFAULT ''none'' AFTER `role`'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'corp_approved_at') > 0,
    'SELECT "corp_approved_at column already exists" as message',
    'ALTER TABLE `users` ADD COLUMN `corp_approved_at` TIMESTAMP NULL AFTER `corp_status`'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. 기업 프로필 테이블 생성 (이미 존재하는지 확인)
CREATE TABLE IF NOT EXISTS `company_profiles` (
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

-- 3. 기업 인증 신청/수정 이력 관리 테이블 생성 (이미 존재하는지 확인)
CREATE TABLE IF NOT EXISTS `company_application_history` (
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

-- 4. 인덱스 추가 (이미 존재하는지 확인 후 추가)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_corp_status') > 0,
    'SELECT "idx_corp_status index already exists" as message',
    'ALTER TABLE `users` ADD INDEX `idx_corp_status` (`corp_status`)'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_corp_approved_at') > 0,
    'SELECT "idx_corp_approved_at index already exists" as message',
    'ALTER TABLE `users` ADD INDEX `idx_corp_approved_at` (`corp_approved_at`)'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. settings 테이블 존재 여부 확인 후 설정값 추가
SET @settings_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
                       WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'settings');

-- settings 테이블이 존재하고 is_public 컬럼이 있는 경우
SET @is_public_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                        WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'is_public');

-- 설정값 추가 (안전하게)
INSERT IGNORE INTO `settings` (`key_name`, `value`, `description`, `type`) VALUES
('corp_approval_required', '1', '기업회원 승인 필수 여부', 'BOOLEAN'),
('corp_file_max_size', '10485760', '기업 서류 최대 업로드 크기 (10MB)', 'INTEGER'),
('corp_allowed_extensions', 'jpg,jpeg,png,pdf', '허용된 파일 확장자', 'STRING');

-- 6. 마이그레이션 완료 확인
SELECT 
    '기업회원 시스템 마이그레이션 완료' as status,
    CURRENT_TIMESTAMP as completed_at,
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'company_profiles') as company_profiles_created,
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'company_application_history') as history_table_created,
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'corp_status') as corp_status_added;

COMMIT;