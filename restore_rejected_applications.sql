-- 거절 처리된 기업인증 신청을 대기 중으로 되돌리는 SQL
-- 실행 전에 반드시 데이터 백업을 권장합니다.

-- ===== 실행 방법 =====
-- 1. SSH로 서버 접속
-- 2. 다음 명령어로 MySQL 접속:
--    mysql -u [사용자명] -p [데이터베이스명]
-- 3. 이 파일 실행:
--    source /workspace/restore_rejected_applications.sql;
-- 
-- 또는 한 번에 실행:
--    mysql -u [사용자명] -p [데이터베이스명] < /workspace/restore_rejected_applications.sql

-- 1. company_profiles 테이블에서 거절된 신청을 대기 중으로 변경
UPDATE company_profiles 
SET 
    status = 'pending',
    admin_notes = NULL,
    processed_by = NULL,
    processed_at = NULL
WHERE status = 'rejected';

-- 2. users 테이블에서 기업회원 상태를 pending으로 변경
UPDATE users 
SET 
    corp_status = 'pending',
    corp_approved_at = NULL
WHERE corp_status = 'rejected';

-- 3. 변경된 레코드 확인
SELECT 
    cp.id,
    cp.company_name,
    cp.status as profile_status,
    u.nickname,
    u.corp_status as user_status,
    cp.created_at,
    cp.processed_at
FROM company_profiles cp
JOIN users u ON cp.user_id = u.id
WHERE cp.status = 'pending'
ORDER BY cp.created_at DESC;