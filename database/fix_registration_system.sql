-- 강의/행사 신청 관리 시스템 오류 수정
-- 생성일: 2025-07-01
-- 설명: 누락된 필드 추가 및 오류 수정

USE `topmkt`;

-- 1. lectures 테이블에 누락된 필드만 추가 (이미 존재하는 필드는 건너뛰기)
SET @sql = 'ALTER TABLE lectures ADD COLUMN current_participants INT DEFAULT 0 COMMENT ''현재 참가자 수''';
SET @field_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'lectures' AND COLUMN_NAME = 'current_participants');
SET @sql = IF(@field_exists = 0, @sql, 'SELECT ''current_participants already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'ALTER TABLE lectures ADD COLUMN auto_approval BOOLEAN DEFAULT FALSE COMMENT ''자동 승인 여부''';
SET @field_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'lectures' AND COLUMN_NAME = 'auto_approval');
SET @sql = IF(@field_exists = 0, @sql, 'SELECT ''auto_approval already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'ALTER TABLE lectures ADD COLUMN registration_start_date DATETIME NULL COMMENT ''신청 시작일시''';
SET @field_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'lectures' AND COLUMN_NAME = 'registration_start_date');
SET @sql = IF(@field_exists = 0, @sql, 'SELECT ''registration_start_date already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'ALTER TABLE lectures ADD COLUMN registration_end_date DATETIME NULL COMMENT ''신청 마감일시''';
SET @field_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'lectures' AND COLUMN_NAME = 'registration_end_date');
SET @sql = IF(@field_exists = 0, @sql, 'SELECT ''registration_end_date already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'ALTER TABLE lectures ADD COLUMN allow_waiting_list BOOLEAN DEFAULT FALSE COMMENT ''대기자 명단 허용 여부''';
SET @field_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'lectures' AND COLUMN_NAME = 'allow_waiting_list');
SET @sql = IF(@field_exists = 0, @sql, 'SELECT ''allow_waiting_list already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. 기존 강의들의 current_participants 초기화 (필드가 존재하는 경우에만)
SET @field_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'lectures' AND COLUMN_NAME = 'current_participants');
SET @sql = IF(@field_exists > 0, 'UPDATE lectures SET current_participants = 0 WHERE current_participants IS NULL', 'SELECT ''current_participants field not found'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. registration_statistics 뷰 다시 생성 (기존 뷰 삭제 후 재생성)
DROP VIEW IF EXISTS `registration_statistics`;

CREATE VIEW `registration_statistics` AS
SELECT 
    l.id as lecture_id,
    l.title as lecture_title,
    l.content_type,
    l.max_participants,
    COALESCE(l.current_participants, 0) as current_participants,
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

-- 4. 기존 트리거 삭제 후 재생성
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
        
        -- current_participants 업데이트 (필드가 존재하는 경우에만)
        SET @field_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'lectures' AND COLUMN_NAME = 'current_participants');
        IF @field_exists > 0 THEN
            UPDATE lectures 
            SET current_participants = (
                SELECT COUNT(*) 
                FROM lecture_registrations 
                WHERE lecture_id = NEW.lecture_id 
                AND status = 'approved'
            )
            WHERE id = NEW.lecture_id;
        END IF;
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
    
    -- current_participants 업데이트 (필드가 존재하는 경우에만)
    SET @field_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'lectures' AND COLUMN_NAME = 'current_participants');
    IF @field_exists > 0 THEN
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

DELIMITER ;

-- 5. 확인 쿼리
SELECT 
    'lectures 테이블 필드 확인' as check_type,
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
AND TABLE_NAME = 'lectures' 
AND COLUMN_NAME IN ('max_participants', 'current_participants', 'auto_approval', 'registration_start_date', 'registration_end_date', 'allow_waiting_list')
ORDER BY ORDINAL_POSITION;

-- 6. 완료 메시지
SELECT 
    '강의/행사 신청 관리 시스템 수정 완료!' as status,
    COUNT(*) as total_lectures,
    SUM(CASE WHEN max_participants IS NOT NULL THEN 1 ELSE 0 END) as lectures_with_limit
FROM lectures;

COMMIT;