-- 관리자 전용 메모 컬럼 추가 SQL
-- admin_notes는 승인/거절 사유로 사용하고, admin_memo는 관리자 전용 메모로 분리

-- ===== 실행 방법 =====
-- 1. SSH로 서버 접속
-- 2. 다음 명령어로 MySQL 접속:
--    mysql -u [사용자명] -p [데이터베이스명]
-- 3. 이 파일 실행:
--    source /workspace/add_admin_memo_column.sql;

USE TOPMKT;

-- company_profiles 테이블에 admin_memo 컬럼 추가
ALTER TABLE company_profiles 
ADD COLUMN admin_memo TEXT NULL COMMENT '관리자 전용 메모' AFTER admin_notes;

-- 변경 확인
SELECT 
    'admin_memo column added' as message,
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
  AND TABLE_NAME = 'company_profiles' 
  AND COLUMN_NAME IN ('admin_notes', 'admin_memo')
ORDER BY ORDINAL_POSITION;