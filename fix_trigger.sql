USE TOPMKT;

-- 기존 트리거 삭제
DROP TRIGGER IF EXISTS update_participant_count_after_registration;

-- 새로운 트리거 생성
DELIMITER ;;
CREATE TRIGGER update_participant_count_after_registration
AFTER UPDATE ON lecture_registrations
FOR EACH ROW
BEGIN
    -- 상태가 변경된 경우에만 처리
    IF OLD.status != NEW.status THEN
        -- action_type 매핑
        SET @action_type = CASE NEW.status
            WHEN 'approved' THEN 'approve'
            WHEN 'rejected' THEN 'reject'
            WHEN 'cancelled' THEN 'cancel'
            WHEN 'attended' THEN 'attend'
            WHEN 'no_show' THEN 'no_show'
            WHEN 'waiting' THEN 'move_to_waiting'
            ELSE 'approve'
        END;
        
        -- 이력 추가
        INSERT INTO registration_history (registration_id, action_type, old_status, new_status, notes, performed_by)
        VALUES (NEW.id, @action_type, OLD.status, NEW.status, '상태 변경', COALESCE(NEW.processed_by, NEW.user_id));
        
        -- 참가자 수 업데이트
        UPDATE lectures 
        SET current_participants = (
            SELECT COUNT(*) 
            FROM lecture_registrations 
            WHERE lecture_id = NEW.lecture_id 
            AND status = 'approved'
        )
        WHERE id = NEW.lecture_id;
    END IF;
END;;
DELIMITER ;