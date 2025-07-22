-- 행사 배지 기능 제거 (event_scale, has_networking 필드 삭제)
-- 생성일: 2025-07-02
-- 설명: event_scale과 has_networking 필드를 lectures 테이블에서 완전히 제거

USE `topmkt`;

-- 1. lectures 테이블에서 배지 관련 필드 제거
ALTER TABLE `lectures` 
DROP COLUMN IF EXISTS `event_scale`,
DROP COLUMN IF EXISTS `has_networking`;

-- 2. 완료 메시지
SELECT 'event_scale과 has_networking 필드가 성공적으로 제거되었습니다.' as status;

-- 3. 테이블 구조 확인
DESCRIBE lectures;

-- 4. 변경 로그 기록
INSERT INTO `user_logs` (`user_id`, `action`, `description`, `ip_address`, `user_agent`) VALUES
(NULL, 'SYSTEM_MIGRATION', '행사 배지 기능 제거 - event_scale, has_networking 필드 삭제', '127.0.0.1', 'System Migration Script');

COMMIT;