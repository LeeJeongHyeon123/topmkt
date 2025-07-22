-- 강의/행사 신청 관리 시스템 데이터베이스 설계
-- 생성일: 2025-07-01
-- 설명: 강의/행사 신청, 승인, 관리를 위한 테이블 구조 추가

USE `topmkt`;

-- 1. lectures 테이블에 참가자 수 관리 필드 추가
ALTER TABLE `lectures` 
ADD COLUMN `max_participants` INT NULL COMMENT '최대 참가자 수 (NULL이면 무제한)',
ADD COLUMN `current_participants` INT DEFAULT 0 COMMENT '현재 참가자 수',
ADD COLUMN `auto_approval` BOOLEAN DEFAULT FALSE COMMENT '자동 승인 여부 (false=수동승인, true=자동승인)',
ADD COLUMN `registration_start_date` DATETIME NULL COMMENT '신청 시작일시',
ADD COLUMN `registration_end_date` DATETIME NULL COMMENT '신청 마감일시',
ADD COLUMN `allow_waiting_list` BOOLEAN DEFAULT FALSE COMMENT '대기자 명단 허용 여부';

-- 2. 강의/행사 신청 테이블 생성
CREATE TABLE `lecture_registrations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `lecture_id` INT(11) NOT NULL COMMENT '강의/행사 ID',
  `user_id` INT(11) NOT NULL COMMENT '신청자 사용자 ID',
  `company_id` INT(11) NULL COMMENT '신청자 소속 기업 ID (기업회원인 경우)',
  
  -- 신청자 기본 정보
  `applicant_name` VARCHAR(100) NOT NULL COMMENT '신청자명',
  `applicant_phone` VARCHAR(20) NOT NULL COMMENT '신청자 연락처',
  `applicant_email` VARCHAR(255) NOT NULL COMMENT '신청자 이메일',
  `applicant_position` VARCHAR(100) NULL COMMENT '신청자 직책/직위',
  
  -- 기업 정보 (기업 신청시)
  `company_name` VARCHAR(255) NULL COMMENT '회사명',
  `company_size` ENUM('startup', 'small', 'medium', 'large') NULL COMMENT '기업 규모 (스타트업/소기업/중기업/대기업)',
  
  -- 신청 상태 관리
  `status` ENUM('pending', 'approved', 'rejected', 'cancelled', 'attended', 'no_show', 'waiting') DEFAULT 'pending' COMMENT '신청 상태',
  `is_waiting_list` BOOLEAN DEFAULT FALSE COMMENT '대기자 명단 여부',
  `waiting_order` INT NULL COMMENT '대기 순번',
  
  -- 추가 정보
  `motivation` TEXT NULL COMMENT '참가 동기/목적',
  `special_requests` TEXT NULL COMMENT '특별 요청사항 (식단, 접근성 등)',
  `how_did_you_know` VARCHAR(255) NULL COMMENT '어떻게 알게 되었는지',
  
  -- 관리자 처리 정보
  `processed_by` INT(11) NULL COMMENT '처리한 관리자/기업 사용자 ID',
  `processed_at` TIMESTAMP NULL COMMENT '처리 일시',
  `admin_notes` TEXT NULL COMMENT '관리자 메모/거절 사유',
  
  -- 출석 관리
  `attendance_checked_at` TIMESTAMP NULL COMMENT '출석 체크 시간',
  `attendance_checked_by` INT(11) NULL COMMENT '출석 체크한 사용자 ID',
  
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  
  -- 외래키 제약조건
  FOREIGN KEY (`lecture_id`) REFERENCES `lectures`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`company_id`) REFERENCES `company_profiles`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`attendance_checked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  
  -- 중복 신청 방지
  UNIQUE KEY `unique_user_lecture` (`lecture_id`, `user_id`),
  
  -- 성능 최적화 인덱스
  INDEX `idx_lecture_status` (`lecture_id`, `status`),
  INDEX `idx_user_registrations` (`user_id`, `created_at`),
  INDEX `idx_status_created` (`status`, `created_at`),
  INDEX `idx_waiting_list` (`lecture_id`, `is_waiting_list`, `waiting_order`),
  INDEX `idx_processed` (`processed_by`, `processed_at`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='강의/행사 신청 관리';

-- 3. 신청 처리 이력 테이블 생성 (감사 로그)
CREATE TABLE `registration_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `registration_id` INT(11) NOT NULL COMMENT '신청 ID',
  `action_type` ENUM('apply', 'approve', 'reject', 'cancel', 'attend', 'no_show', 'move_to_waiting', 'move_from_waiting') NOT NULL COMMENT '액션 타입',
  `old_status` VARCHAR(50) NULL COMMENT '변경 전 상태',
  `new_status` VARCHAR(50) NULL COMMENT '변경 후 상태',
  `notes` TEXT NULL COMMENT '변경 사유/메모',
  `performed_by` INT(11) NOT NULL COMMENT '액션 수행자 ID',
  `performed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  FOREIGN KEY (`registration_id`) REFERENCES `lecture_registrations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  
  INDEX `idx_registration_history` (`registration_id`, `performed_at`),
  INDEX `idx_performer` (`performed_by`, `performed_at`),
  INDEX `idx_action_type` (`action_type`, `performed_at`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='신청 처리 이력';

-- 4. 알림 설정 테이블 생성
CREATE TABLE `notification_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL COMMENT '사용자 ID',
  `lecture_id` INT(11) NULL COMMENT '강의 ID (NULL이면 전체 설정)',
  
  -- 이메일 알림 설정
  `email_new_registration` BOOLEAN DEFAULT TRUE COMMENT '새 신청시 이메일 알림',
  `email_registration_approved` BOOLEAN DEFAULT TRUE COMMENT '신청 승인시 이메일 알림',
  `email_registration_rejected` BOOLEAN DEFAULT TRUE COMMENT '신청 거절시 이메일 알림',
  `email_lecture_reminder` BOOLEAN DEFAULT TRUE COMMENT '강의 시작 전 리마인더 이메일',
  `email_deadline_reminder` BOOLEAN DEFAULT TRUE COMMENT '신청 마감 임박 알림',
  
  -- 시스템 알림 설정 (대시보드 내)
  `system_new_registration` BOOLEAN DEFAULT TRUE COMMENT '새 신청시 시스템 알림',
  `system_capacity_warning` BOOLEAN DEFAULT TRUE COMMENT '정원 임박 시스템 알림',
  
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lecture_id`) REFERENCES `lectures`(`id`) ON DELETE CASCADE,
  
  UNIQUE KEY `unique_user_lecture_setting` (`user_id`, `lecture_id`),
  INDEX `idx_user_settings` (`user_id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='알림 설정';

-- 5. 신청 관련 통계 뷰 생성
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
    COALESCE(stats.cancelled_count, 0) as cancelled_count,
    COALESCE(stats.attended_count, 0) as attended_count,
    COALESCE(stats.no_show_count, 0) as no_show_count,
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
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
        SUM(CASE WHEN status = 'attended' THEN 1 ELSE 0 END) as attended_count,
        SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show_count,
        SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting_count
    FROM lecture_registrations 
    GROUP BY lecture_id
) stats ON l.id = stats.lecture_id;

-- 6. 기본 알림 설정 삽입 (기존 기업 사용자들에게)
INSERT INTO `notification_settings` (user_id, lecture_id, email_new_registration, email_registration_approved, email_registration_rejected, email_lecture_reminder, email_deadline_reminder, system_new_registration, system_capacity_warning)
SELECT 
    u.id as user_id,
    NULL as lecture_id,
    TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE
FROM users u 
WHERE u.role = 'ROLE_CORP' AND u.corp_status = 'approved';

-- 7. 트리거 생성: 신청 승인시 current_participants 자동 업데이트
DELIMITER $$

CREATE TRIGGER `update_participant_count_after_registration` 
AFTER UPDATE ON `lecture_registrations`
FOR EACH ROW
BEGIN
    -- 상태가 변경된 경우에만 처리
    IF OLD.status != NEW.status THEN
        -- 신청 처리 이력에 기록
        INSERT INTO registration_history (registration_id, action_type, old_status, new_status, notes, performed_by)
        VALUES (NEW.id, NEW.status, OLD.status, NEW.status, '상태 변경', NEW.processed_by);
        
        -- current_participants 업데이트
        UPDATE lectures 
        SET current_participants = (
            SELECT COUNT(*) 
            FROM lecture_registrations 
            WHERE lecture_id = NEW.lecture_id 
            AND status = 'approved'
        )
        WHERE id = NEW.lecture_id;
    END IF;
END$$

CREATE TRIGGER `update_participant_count_after_insert` 
AFTER INSERT ON `lecture_registrations`
FOR EACH ROW
BEGIN
    -- 신청 처리 이력에 기록
    INSERT INTO registration_history (registration_id, action_type, old_status, new_status, notes, performed_by)
    VALUES (NEW.id, 'apply', NULL, NEW.status, '신규 신청', NEW.user_id);
    
    -- 자동 승인인 경우 즉시 승인 처리
    IF (SELECT auto_approval FROM lectures WHERE id = NEW.lecture_id) = TRUE THEN
        UPDATE lecture_registrations 
        SET status = 'approved', processed_at = NOW(), processed_by = NEW.user_id
        WHERE id = NEW.id;
    END IF;
    
    -- current_participants 업데이트
    UPDATE lectures 
    SET current_participants = (
        SELECT COUNT(*) 
        FROM lecture_registrations 
        WHERE lecture_id = NEW.lecture_id 
        AND status = 'approved'
    )
    WHERE id = NEW.lecture_id;
END$$

DELIMITER ;

-- 8. 기존 강의들의 current_participants 초기화
UPDATE lectures 
SET current_participants = 0 
WHERE current_participants IS NULL;

-- 9. 완료 메시지 및 확인
SELECT 
    '강의/행사 신청 관리 시스템 설치 완료!' as status,
    COUNT(*) as total_lectures
FROM lectures;

SELECT 
    TABLE_NAME,
    TABLE_COMMENT
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'TOPMKT' 
AND TABLE_NAME IN ('lecture_registrations', 'registration_history', 'notification_settings')
ORDER BY TABLE_NAME;

-- 마이그레이션 완료 로그
INSERT INTO `user_logs` (`user_id`, `action`, `description`, `ip_address`, `user_agent`) VALUES
(NULL, 'SYSTEM_MIGRATION', '강의/행사 신청 관리 시스템 마이그레이션 완료', '127.0.0.1', 'System Migration Script');

COMMIT;