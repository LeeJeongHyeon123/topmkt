-- 최근 업데이트된 강의들 확인
SELECT 
    id,
    title,
    registration_deadline,
    youtube_video,
    status,
    created_at,
    updated_at,
    TIMESTAMPDIFF(MINUTE, updated_at, NOW()) as minutes_ago
FROM lectures 
ORDER BY updated_at DESC 
LIMIT 10;

-- 오늘 생성된 강의들 확인
SELECT 
    id,
    title,
    registration_deadline,
    youtube_video,
    status,
    created_at,
    updated_at
FROM lectures 
WHERE DATE(created_at) = CURDATE()
ORDER BY created_at DESC;