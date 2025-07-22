-- 탑마케팅 기업회원 신청 확인 쿼리
-- 실행일: 2025-01-16
-- 용도: 기업회원 신청이 정상적으로 처리되었는지 확인

-- 데이터베이스 선택
USE TOPMKT;

-- 1. 최근 기업회원 신청한 사용자 목록 확인 (최근 7일)
SELECT 
    id,
    nickname,
    phone,
    email,
    corp_status,
    role,
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as 가입일시,
    DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') as 최근수정일시
FROM users 
WHERE corp_status IN ('pending', 'approved', 'rejected')
   OR updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY updated_at DESC 
LIMIT 10;

-- 2. 최근 기업회원 신청 내역 확인 (company_profiles)
SELECT 
    cp.id,
    cp.user_id,
    u.nickname as 신청자_닉네임,
    cp.company_name as 회사명,
    cp.business_number as 사업자번호,
    cp.representative_name as 대표자명,
    cp.representative_phone as 대표자_연락처,
    cp.company_address as 회사주소,
    cp.business_registration_file as 사업자등록증_파일,
    cp.is_overseas as 해외기업여부,
    cp.status as 신청상태,
    cp.admin_notes as 관리자_메모,
    DATE_FORMAT(cp.created_at, '%Y-%m-%d %H:%i:%s') as 신청일시,
    DATE_FORMAT(cp.updated_at, '%Y-%m-%d %H:%i:%s') as 최근수정일시
FROM company_profiles cp
LEFT JOIN users u ON cp.user_id = u.id
ORDER BY cp.created_at DESC 
LIMIT 10;

-- 3. 오늘 신청한 기업회원 상세 정보
SELECT 
    cp.*,
    u.nickname,
    u.phone,
    u.email
FROM company_profiles cp
JOIN users u ON cp.user_id = u.id
WHERE DATE(cp.created_at) = CURDATE()
ORDER BY cp.created_at DESC;

-- 4. 기업회원 신청 이력 확인 (최근 20건)
SELECT 
    h.id,
    h.user_id,
    u.nickname as 신청자,
    h.action_type as 액션타입,
    h.admin_notes as 관리자메모,
    h.created_by as 처리자ID,
    DATE_FORMAT(h.created_at, '%Y-%m-%d %H:%i:%s') as 처리일시
FROM company_application_history h
LEFT JOIN users u ON h.user_id = u.id
ORDER BY h.created_at DESC 
LIMIT 20;

-- 5. 특정 전화번호로 사용자 찾기 (전화번호를 입력하세요)
-- SELECT * FROM users WHERE phone = '010-XXXX-XXXX';

-- 6. 특정 사용자의 기업회원 신청 상태 확인 (user_id를 입력하세요)
-- SELECT 
--     u.id,
--     u.nickname,
--     u.phone,
--     u.corp_status,
--     cp.*
-- FROM users u
-- LEFT JOIN company_profiles cp ON u.id = cp.user_id
-- WHERE u.id = [USER_ID];

-- 7. 기업회원 신청 통계
SELECT 
    '전체 기업회원 신청' as 구분,
    COUNT(*) as 건수
FROM company_profiles
UNION ALL
SELECT 
    CONCAT('상태: ', status) as 구분,
    COUNT(*) as 건수
FROM company_profiles
GROUP BY status
UNION ALL
SELECT 
    '오늘 신청' as 구분,
    COUNT(*) as 건수
FROM company_profiles
WHERE DATE(created_at) = CURDATE();

-- 8. 최근 1시간 내 신청 확인
SELECT 
    cp.id,
    cp.user_id,
    u.nickname,
    cp.company_name,
    cp.status,
    cp.business_registration_file,
    DATE_FORMAT(cp.created_at, '%Y-%m-%d %H:%i:%s') as 신청시각
FROM company_profiles cp
JOIN users u ON cp.user_id = u.id
WHERE cp.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY cp.created_at DESC;