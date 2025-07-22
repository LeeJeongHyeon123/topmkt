-- lecture_registrations 테이블 구조 수정
-- 생성일: 2025-07-01
-- 설명: 누락된 필드 추가 및 트리거 재생성

USE `topmkt`;

-- 1. lecture_registrations 테이블에 누락된 필드들 추가
ALTER TABLE `lecture_registrations` 
ADD COLUMN `processed_by` INT(11) NULL COMMENT '처리한 관리자/기업 사용자 ID' AFTER `admin_notes`,
ADD COLUMN `processed_at` TIMESTAMP NULL COMMENT '처리 일시' AFTER `processed_by`,
ADD COLUMN `attendance_checked_at` TIMESTAMP NULL COMMENT '출석 체크 시간' AFTER `processed_at`,
ADD COLUMN `attendance_checked_by` INT(11) NULL COMMENT '출석 체크한 사용자 ID' AFTER `attendance_checked_at`;

-- 2. 외래키 제약조건 추가
ALTER TABLE `lecture_registrations` 
ADD FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`attendance_checked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- 3. 인덱스 추가
ALTER TABLE `lecture_registrations` 
ADD INDEX `idx_processed` (`processed_by`, `processed_at`);

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

-- 5. 확인 쿼리
SELECT 
    'lecture_registrations 테이블 구조 확인' as check_type,
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
AND TABLE_NAME = 'lecture_registrations' 
ORDER BY ORDINAL_POSITION;

-- 6. 트리거 확인
SELECT 
    TRIGGER_NAME,
    EVENT_MANIPULATION,
    ACTION_TIMING,
    EVENT_OBJECT_TABLE
FROM INFORMATION_SCHEMA.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'topmkt' 
AND EVENT_OBJECT_TABLE = 'lecture_registrations';

-- 7. 완료 메시지
SELECT 
    'lecture_registrations 테이블 수정 완료!' as status,
    COUNT(*) as total_registrations
FROM lecture_registrations;

COMMIT;