-- 강의 128의 소유자 정보 확인 (수정된 버전)
SELECT 
    l.id,
    l.title,
    l.user_id as lecture_user_id,
    l.status,
    l.created_at,
    l.updated_at
FROM lectures l
WHERE l.id = 128;

-- users 테이블 구조 확인
DESCRIBE users;