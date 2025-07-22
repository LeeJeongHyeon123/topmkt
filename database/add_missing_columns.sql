-- 누락된 컬럼들을 lectures 테이블에 추가
-- 실행일: 2025-06-21

USE `topmkt`;

-- prerequisites 컬럼 추가 (참가 조건)
ALTER TABLE `lectures` ADD COLUMN `prerequisites` TEXT NULL AFTER `youtube_video`;

-- what_to_bring 컬럼 추가 (준비물)  
ALTER TABLE `lectures` ADD COLUMN `what_to_bring` TEXT NULL AFTER `prerequisites`;

-- benefits 컬럼 추가 (참가자 혜택)
ALTER TABLE `lectures` ADD COLUMN `benefits` TEXT NULL AFTER `what_to_bring`;

-- additional_info 컬럼 추가 (기타 안내사항)
ALTER TABLE `lectures` ADD COLUMN `additional_info` TEXT NULL AFTER `benefits`;