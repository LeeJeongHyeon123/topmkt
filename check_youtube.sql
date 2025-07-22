-- YouTube 링크 저장 확인
SELECT 
    id,
    title,
    youtube_video,
    CHAR_LENGTH(youtube_video) as youtube_length,
    registration_deadline,
    updated_at
FROM lectures 
WHERE status = 'draft' 
ORDER BY updated_at DESC 
LIMIT 1;