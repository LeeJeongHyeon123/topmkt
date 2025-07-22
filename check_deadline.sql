-- 등록 마감일시 저장 확인
SELECT 
    id,
    title,
    registration_deadline,
    youtube_video,
    status,
    updated_at
FROM lectures 
WHERE status = 'draft' 
ORDER BY updated_at DESC 
LIMIT 1;