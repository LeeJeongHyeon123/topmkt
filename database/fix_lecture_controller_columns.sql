-- Fix LectureController missing columns
-- 실행일: 2025-07-08
-- LectureController::store() 에러 해결을 위한 누락된 컬럼 추가

USE `topmkt`;

-- 1. user_id 컬럼 추가 (created_by와 별도로)
-- LectureController에서 user_id를 사용하고 있음
ALTER TABLE `lectures` 
ADD COLUMN IF NOT EXISTS `user_id` INT(11) NULL 
COMMENT '강의 등록자 ID (LectureController 호환)' 
AFTER `created_by`;

-- 2. lecture_images 컬럼 추가 (JSON 타입)
-- 강의 이미지 정보를 JSON으로 저장
ALTER TABLE `lectures` 
ADD COLUMN IF NOT EXISTS `lecture_images` LONGTEXT NULL 
COMMENT '강의 이미지 정보 (JSON)' 
AFTER `additional_info`;

-- 3. instructors_json 컬럼 추가 (JSON 타입)
-- 강사 정보를 JSON으로 저장
ALTER TABLE `lectures` 
ADD COLUMN IF NOT EXISTS `instructors_json` LONGTEXT NULL 
COMMENT '강사 정보 (JSON)' 
AFTER `lecture_images`;

-- 4. registration_deadline 컬럼 추가
-- 등록 마감일
ALTER TABLE `lectures` 
ADD COLUMN IF NOT EXISTS `registration_deadline` DATETIME NULL 
COMMENT '등록 마감일' 
AFTER `instructors_json`;

-- 5. status 컬럼 수정 - draft, published 값 추가
-- 기존 enum에 draft, published 추가
ALTER TABLE `lectures` 
MODIFY COLUMN `status` ENUM('draft', 'published', 'upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'draft';

-- 6. 기존 데이터의 user_id를 created_by 값으로 업데이트
UPDATE `lectures` SET `user_id` = `created_by` WHERE `user_id` IS NULL;

-- 7. 기존 데이터의 status를 published로 업데이트 (draft가 아닌 것들)
UPDATE `lectures` SET `status` = 'published' 
WHERE `status` NOT IN ('draft', 'published') AND `status` IS NOT NULL;

-- 8. 인덱스 추가
ALTER TABLE `lectures` ADD INDEX IF NOT EXISTS `idx_user_id` (`user_id`);
ALTER TABLE `lectures` ADD INDEX IF NOT EXISTS `idx_status_user` (`status`, `user_id`);
ALTER TABLE `lectures` ADD INDEX IF NOT EXISTS `idx_registration_deadline` (`registration_deadline`);

-- 9. 확인 쿼리
SELECT 
    'lectures 테이블 컬럼 추가 완료' as status,
    COUNT(*) as total_lectures,
    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_count,
    COUNT(CASE WHEN status = 'published' THEN 1 END) as published_count,
    COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) as user_id_set_count
FROM lectures;

-- 10. 컬럼 존재 확인
SHOW COLUMNS FROM lectures LIKE 'user_id';
SHOW COLUMNS FROM lectures LIKE 'lecture_images';
SHOW COLUMNS FROM lectures LIKE 'instructors_json';
SHOW COLUMNS FROM lectures LIKE 'registration_deadline';