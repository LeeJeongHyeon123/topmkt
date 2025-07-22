-- user_sessions 테이블 구조 확인
USE TOPMKT;

-- 테이블 구조 확인
DESCRIBE user_sessions;

-- 존재하는 컬럼 확인
SHOW COLUMNS FROM user_sessions;

-- 샘플 데이터 확인
SELECT * FROM user_sessions LIMIT 5;