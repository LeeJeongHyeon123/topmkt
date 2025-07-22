-- 강의 시스템 긴급 복구 SQL
-- 생성일: 2025-07-01
-- 목적: topmktx.com/lectures 시스템 오류 해결

USE `topmkt`;

-- 1. 기존 강의 테이블 백업 (안전 조치)
CREATE TABLE IF NOT EXISTS `lectures_backup_20250701` AS SELECT * FROM `lectures` LIMIT 0;
INSERT INTO `lectures_backup_20250701` SELECT * FROM `lectures`;

-- 2. 강의 테이블에 누락된 필드 추가 (안전하게)
ALTER TABLE `lectures` 
ADD COLUMN IF NOT EXISTS `max_participants` INT NULL COMMENT '최대 참가자 수',
ADD COLUMN IF NOT EXISTS `current_participants` INT DEFAULT 0 COMMENT '현재 참가자 수',
ADD COLUMN IF NOT EXISTS `auto_approval` BOOLEAN DEFAULT FALSE COMMENT '자동 승인 여부',
ADD COLUMN IF NOT EXISTS `registration_start_date` DATETIME NULL COMMENT '신청 시작일시',
ADD COLUMN IF NOT EXISTS `registration_end_date` DATETIME NULL COMMENT '신청 마감일시',
ADD COLUMN IF NOT EXISTS `allow_waiting_list` BOOLEAN DEFAULT FALSE COMMENT '대기자 명단 허용';

-- 3. 기존 강의들의 current_participants 초기화
UPDATE `lectures` 
SET `current_participants` = 0 
WHERE `current_participants` IS NULL;

-- 4. 강의 신청 테이블이 없는 경우에만 생성
CREATE TABLE IF NOT EXISTS `lecture_registrations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `lecture_id` INT(11) NOT NULL COMMENT '강의 ID',
  `user_id` INT(11) NOT NULL COMMENT '신청자 사용자 ID',
  
  -- 신청자 정보 (기존 테이블 필드명과 일치)
  `participant_name` VARCHAR(100) NOT NULL COMMENT '신청자명',
  `participant_email` VARCHAR(255) NOT NULL COMMENT '신청자 이메일',
  `participant_phone` VARCHAR(20) NOT NULL COMMENT '신청자 연락처',
  `company_name` VARCHAR(255) NULL COMMENT '회사명',
  `position` VARCHAR(100) NULL COMMENT '직책',
  
  -- 신청 상태 관리
  `status` ENUM('pending', 'approved', 'rejected', 'cancelled', 'waiting') DEFAULT 'pending' COMMENT '신청 상태',
  `is_waiting_list` BOOLEAN DEFAULT FALSE COMMENT '대기자 여부',
  `waiting_order` INT NULL COMMENT '대기 순번',
  
  -- 추가 정보
  `motivation` TEXT NULL COMMENT '참가 동기',
  `special_requests` TEXT NULL COMMENT '특별 요청사항',
  `how_did_you_know` VARCHAR(255) NULL COMMENT '강의를 알게 된 경로',
  
  -- 관리자 처리 정보
  `processed_by` INT(11) NULL COMMENT '처리한 관리자 ID',
  `processed_at` TIMESTAMP NULL COMMENT '처리 일시',
  `admin_notes` TEXT NULL COMMENT '관리자 메모',
  
  -- 기존 테이블과 호환성을 위한 필드
  `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '신청일',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  
  -- 외래키 (존재하는 경우에만)
  FOREIGN KEY (`lecture_id`) REFERENCES `lectures`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  
  -- 중복 신청 방지
  UNIQUE KEY `unique_user_lecture` (`lecture_id`, `user_id`),
  
  -- 인덱스
  INDEX `idx_lecture_status` (`lecture_id`, `status`),
  INDEX `idx_user_registrations` (`user_id`, `registration_date`),
  INDEX `idx_status_date` (`status`, `registration_date`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='강의 신청 관리';

-- 5. 신청 이력 테이블 생성
CREATE TABLE IF NOT EXISTS `registration_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `registration_id` INT(11) NOT NULL,
  `action_type` ENUM('apply', 'approve', 'reject', 'cancel') NOT NULL,
  `old_status` VARCHAR(50) NULL,
  `new_status` VARCHAR(50) NULL,
  `notes` TEXT NULL,
  `performed_by` INT(11) NOT NULL,
  `performed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  FOREIGN KEY (`registration_id`) REFERENCES `lecture_registrations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. 통계 뷰 생성 (기존 것이 있으면 대체)
DROP VIEW IF EXISTS `registration_statistics`;

CREATE VIEW `registration_statistics` AS
SELECT 
    l.id as lecture_id,
    l.title as lecture_title,
    l.content_type,
    l.max_participants,
    l.current_participants,
    COALESCE(stats.total_applications, 0) as total_applications,
    COALESCE(stats.pending_count, 0) as pending_count,
    COALESCE(stats.approved_count, 0) as approved_count,
    COALESCE(stats.rejected_count, 0) as rejected_count,
    COALESCE(stats.waiting_count, 0) as waiting_count,
    CASE 
        WHEN l.max_participants IS NULL THEN 0
        WHEN l.max_participants > 0 THEN ROUND((COALESCE(stats.approved_count, 0) / l.max_participants) * 100, 2)
        ELSE 0
    END as capacity_percentage
FROM lectures l
LEFT JOIN (
    SELECT 
        lecture_id,
        COUNT(*) as total_applications,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
        SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting_count
    FROM lecture_registrations 
    GROUP BY lecture_id
) stats ON l.id = stats.lecture_id;

-- 7. 기존 강의 상태 정리
UPDATE `lectures` 
SET `status` = 'published' 
WHERE `status` = 'active' OR `status` IS NULL;

-- 8. 인덱스 최적화
ALTER TABLE `lectures` ADD INDEX IF NOT EXISTS `idx_status_date` (`status`, `start_date`);
ALTER TABLE `lectures` ADD INDEX IF NOT EXISTS `idx_user_status` (`user_id`, `status`);

-- 9. 문제가 있는 강의 데이터 정리
UPDATE `lectures` 
SET 
    `start_time` = '00:00:00' 
WHERE `start_time` IS NULL OR `start_time` = '';

UPDATE `lectures` 
SET 
    `end_time` = '23:59:59' 
WHERE `end_time` IS NULL OR `end_time` = '';

-- 10. 강의 이미지 테이블 구조 확인 및 수정
-- 이미지 관련 display_order, file_name 누락 문제 해결
ALTER TABLE `lecture_images` 
ADD COLUMN IF NOT EXISTS `display_order` INT DEFAULT 0 COMMENT '표시 순서',
ADD COLUMN IF NOT EXISTS `file_name` VARCHAR(255) NULL COMMENT '원본 파일명';

-- 기존 이미지들의 display_order 설정
UPDATE `lecture_images` 
SET `display_order` = `id` 
WHERE `display_order` = 0 OR `display_order` IS NULL;

-- file_name 추출 (URL에서)
UPDATE `lecture_images` 
SET `file_name` = SUBSTRING_INDEX(`url`, '/', -1)
WHERE `file_name` IS NULL OR `file_name` = '';

-- 11. 트리거 생성 (참가자 수 자동 업데이트)
DROP TRIGGER IF EXISTS `update_participant_count`;

DELIMITER $$
CREATE TRIGGER `update_participant_count` 
AFTER UPDATE ON `lecture_registrations`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        UPDATE lectures 
        SET current_participants = (
            SELECT COUNT(*) 
            FROM lecture_registrations 
            WHERE lecture_id = NEW.lecture_id AND status = 'approved'
        )
        WHERE id = NEW.lecture_id;
    END IF;
END$$
DELIMITER ;

-- 12. 권한 관련 문제 해결
-- 강의 작성자 권한 확인
UPDATE `lectures` l
JOIN `users` u ON l.user_id = u.id
SET l.status = 'published'
WHERE u.role IN ('ROLE_CORP', 'ROLE_ADMIN') 
AND l.status = 'draft';

-- 13. 시스템 상태 확인 쿼리
SELECT 
    '강의 시스템 복구 완료' as status,
    COUNT(*) as total_lectures,
    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_lectures,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_lectures
FROM lectures;

-- 14. 신청 테이블 상태 확인
SELECT 
    'lecture_registrations 테이블 상태' as check_type,
    COUNT(*) as total_registrations
FROM lecture_registrations;

-- 15. 완료 로그
INSERT INTO `user_logs` (`user_id`, `action`, `description`, `ip_address`, `user_agent`) 
VALUES (NULL, 'SYSTEM_EMERGENCY_FIX', '강의 시스템 긴급 복구 완료', '127.0.0.1', 'Emergency Fix Script');

COMMIT;

-- 마지막 확인
SELECT 
    'System Health Check' as type,
    TABLE_NAME,
    TABLE_ROWS
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'TOPMKT' 
AND TABLE_NAME IN ('lectures', 'lecture_registrations', 'users')
ORDER BY TABLE_NAME;