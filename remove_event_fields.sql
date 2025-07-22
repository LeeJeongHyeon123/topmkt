-- 행사 테이블에서 불필요한 필드들 제거
-- sponsor_info, dress_code, parking_info 필드 삭제

USE TOPMKT;

-- 1. sponsor_info 필드 제거
ALTER TABLE lectures DROP COLUMN IF EXISTS sponsor_info;

-- 2. dress_code 필드 제거 
ALTER TABLE lectures DROP COLUMN IF EXISTS dress_code;

-- 3. parking_info 필드 제거
ALTER TABLE lectures DROP COLUMN IF EXISTS parking_info;

-- 테이블 구조 확인
DESCRIBE lectures;