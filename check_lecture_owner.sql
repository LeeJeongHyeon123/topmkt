-- 강의 128의 소유자 정보 확인
SELECT 
    l.id,
    l.title,
    l.user_id as lecture_user_id,
    u.username,
    u.name as user_name,
    l.status,
    l.created_at,
    l.updated_at
FROM lectures l
LEFT JOIN users u ON l.user_id = u.id 
WHERE l.id = 128;