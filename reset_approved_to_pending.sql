-- 승인된 기업인증을 대기 중 상태로 되돌리는 SQL
-- 실행 전에 반드시 데이터 백업을 권장합니다.

-- ===== 실행 방법 =====
-- 1. SSH로 서버 접속
-- 2. 다음 명령어로 MySQL 접속:
--    mysql -u [사용자명] -p [데이터베이스명]
-- 3. 이 파일 실행:
--    source /workspace/reset_approved_to_pending.sql;
-- 
-- 또는 한 번에 실행:
--    mysql -u [사용자명] -p [데이터베이스명] < /workspace/reset_approved_to_pending.sql

-- ===== 실행 전 현재 상태 확인 =====
SELECT 
    cp.id,
    cp.company_name,
    cp.status as profile_status,
    u.nickname,
    u.corp_status as user_status,
    cp.processed_at,
    u.corp_approved_at
FROM company_profiles cp
JOIN users u ON cp.user_id = u.id
WHERE cp.status = 'approved'
ORDER BY cp.processed_at DESC;

-- ===== 승인된 기업인증을 대기 중으로 변경 =====

-- 1. company_profiles 테이블에서 승인된 신청을 대기 중으로 변경
UPDATE company_profiles 
SET 
    status = 'pending',
    admin_notes = NULL,
    processed_by = NULL,
    processed_at = NULL
WHERE status = 'approved';

-- 2. users 테이블에서 기업회원 상태를 pending으로 변경
UPDATE users 
SET 
    corp_status = 'pending',
    corp_approved_at = NULL
WHERE corp_status = 'approved';

-- ===== 변경 후 결과 확인 =====
SELECT 
    cp.id,
    cp.company_name,
    cp.status as profile_status,
    u.nickname,
    u.corp_status as user_status,
    cp.created_at,
    cp.processed_at,
    u.corp_approved_at
FROM company_profiles cp
JOIN users u ON cp.user_id = u.id
WHERE cp.status = 'pending'
ORDER BY cp.created_at DESC;

-- ===== 변경된 건수 확인 =====
SELECT 
    'company_profiles' as table_name,
    COUNT(*) as pending_count
FROM company_profiles 
WHERE status = 'pending'
UNION ALL
SELECT 
    'users' as table_name,
    COUNT(*) as pending_count
FROM users 
WHERE corp_status = 'pending';