-- 기존 lecture_registrations 테이블에 필요한 필드 추가
-- 생성일: 2025-07-01
-- 설명: 기존 테이블 구조를 유지하면서 필요한 관리 필드만 추가

USE `topmkt`;

-- 1. 기존 테이블에 관리 필드 추가
ALTER TABLE `lecture_registrations` 
ADD COLUMN `processed_by` INT(11) NULL COMMENT '처리한 관리자/기업 사용자 ID',
ADD COLUMN `processed_at` TIMESTAMP NULL COMMENT '처리 일시',
ADD COLUMN `admin_notes` TEXT NULL COMMENT '관리자 메모/거절 사유',
ADD COLUMN `attendance_checked_at` TIMESTAMP NULL COMMENT '출석 체크 시간',
ADD COLUMN `attendance_checked_by` INT(11) NULL COMMENT '출석 체크한 사용자 ID',
ADD COLUMN `motivation` TEXT NULL COMMENT '참가 동기/목적',
ADD COLUMN `how_did_you_know` VARCHAR(255) NULL COMMENT '어떻게 알게 되었는지',
ADD COLUMN `is_waiting_list` BOOLEAN DEFAULT FALSE COMMENT '대기자 명단 여부',
ADD COLUMN `waiting_order` INT NULL COMMENT '대기 순번';

-- 2. status 필드에 새로운 값 추가 (기존 ENUM 확장)
ALTER TABLE `lecture_registrations` 
MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'cancelled', 'attended', 'no_show', 'waiting') DEFAULT 'pending' COMMENT '신청 상태';

-- 3. 외래키 제약조건 추가
ALTER TABLE `lecture_registrations` 
ADD FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`attendance_checked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- 4. 인덱스 추가
ALTER TABLE `lecture_registrations` 
ADD INDEX `idx_processed` (`processed_by`, `processed_at`),
ADD INDEX `idx_waiting_list` (`lecture_id`, `is_waiting_list`, `waiting_order`),
ADD INDEX `idx_status_date` (`status`, `registration_date`);

-- 5. 기존 트리거 삭제 후 재생성 (기존 필드명에 맞춤)
DROP TRIGGER IF EXISTS `update_participant_count_after_registration`;
DROP TRIGGER IF EXISTS `update_participant_count_after_insert`;

DELIMITER $$

CREATE TRIGGER `update_participant_count_after_registration` 
AFTER UPDATE ON `lecture_registrations`
FOR EACH ROW
BEGIN
    -- 상태가 변경된 경우에만 처리
    IF OLD.status != NEW.status THEN
        -- 신청 처리 이력에 기록
        INSERT INTO registration_history (registration_id, action_type, old_status, new_status, notes, performed_by)
        VALUES (NEW.id, NEW.status, OLD.status, NEW.status, '상태 변경', COALESCE(NEW.processed_by, NEW.user_id));
        
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
    SET @auto_approval = (SELECT auto_approval FROM lectures WHERE id = NEW.lecture_id);
    IF @auto_approval = TRUE THEN
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

-- 6. registration_statistics 뷰 재생성 (기존 필드명 사용)
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

-- 7. 필드 매핑을 위한 뷰 생성 (새 API와 기존 테이블 구조 연결)
CREATE VIEW `registration_api_view` AS
SELECT 
    id,
    lecture_id,
    user_id,
    NULL as company_id,  -- 기존 테이블에는 없음
    participant_name as applicant_name,
    participant_email as applicant_email,
    participant_phone as applicant_phone,
    position as applicant_position,
    company_name,
    NULL as company_size,  -- 기존 테이블에는 없음
    status,
    is_waiting_list,
    waiting_order,
    motivation,
    special_requests,
    how_did_you_know,
    processed_by,
    processed_at,
    admin_notes,
    attendance_checked_at,
    attendance_checked_by,
    registration_date as created_at,
    updated_at
FROM lecture_registrations;

-- 8. 확인 쿼리
SELECT 
    'lecture_registrations 테이블 업데이트 확인' as check_type,
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
AND TABLE_NAME = 'lecture_registrations' 
AND COLUMN_NAME IN ('processed_by', 'processed_at', 'admin_notes', 'motivation', 'is_waiting_list')
ORDER BY ORDINAL_POSITION;

-- 9. 트리거 확인
SELECT 
    TRIGGER_NAME,
    EVENT_MANIPULATION,
    ACTION_TIMING
FROM INFORMATION_SCHEMA.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'topmkt' 
AND EVENT_OBJECT_TABLE = 'lecture_registrations';

-- 10. 완료 메시지
SELECT 
    '기존 lecture_registrations 테이블 업데이트 완료!' as status,
    'API 뷰를 통해 새로운 필드 구조와 호환됩니다' as note;

COMMIT;