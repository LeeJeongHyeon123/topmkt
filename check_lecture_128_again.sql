-- 강의 ID 128의 최신 상태 확인
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
WHERE id = 128;

-- 가장 최근에 업데이트된 강의들 확인
SELECT 
    id,
    title,
    registration_deadline,
    youtube_video,
    status,
    updated_at
FROM lectures 
ORDER BY updated_at DESC 
LIMIT 3;