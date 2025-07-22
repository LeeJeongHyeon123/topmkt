-- 기업회원 일시정지 상태 추가 SQL
-- company_profiles 테이블의 status enum에 'suspended' 추가

-- ===== 실행 방법 =====
-- 1. SSH로 서버 접속
-- 2. 다음 명령어로 MySQL 접속:
--    mysql -u [사용자명] -p [데이터베이스명]
-- 3. 이 파일 실행:
--    source /workspace/add_suspended_status.sql;

USE TOPMKT;

-- company_profiles 테이블의 status enum에 'suspended' 상태 추가
ALTER TABLE company_profiles 
MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending' COMMENT '심사 상태';

-- users 테이블의 corp_status enum에도 'suspended' 추가
ALTER TABLE users 
MODIFY COLUMN corp_status ENUM('none', 'pending', 'approved', 'rejected', 'suspended') DEFAULT 'none';

-- company_application_history 테이블의 action_type enum에 'reapprove', 'suspend' 추가
ALTER TABLE company_application_history 
MODIFY COLUMN action_type ENUM('apply', 'reapply', 'modify', 'approve', 'reject', 'reapprove', 'suspend') NOT NULL COMMENT '액션 타입';

-- 변경 확인
SELECT 
    'company_profiles status enum updated' as message,
    COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
  AND TABLE_NAME = 'company_profiles' 
  AND COLUMN_NAME = 'status'

UNION ALL

SELECT 
    'users corp_status enum updated' as message,
    COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'corp_status'

UNION ALL

SELECT 
    'company_application_history action_type enum updated' as message,
    COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
  AND TABLE_NAME = 'company_application_history' 
  AND COLUMN_NAME = 'action_type';