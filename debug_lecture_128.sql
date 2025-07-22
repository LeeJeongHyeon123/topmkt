-- 강의 ID 128 상세 정보 확인
SELECT 
    id,
    title,
    registration_deadline,
    youtube_video,
    status,
    created_at,
    updated_at,
    CHAR_LENGTH(registration_deadline) as deadline_length,
    CHAR_LENGTH(youtube_video) as youtube_length
FROM lectures 
WHERE id = 128;

-- 최근 생성된 강의들의 해당 필드 확인
SELECT 
    id,
    title,
    registration_deadline,
    youtube_video,
    status,
    updated_at
FROM lectures 
WHERE id >= 125
ORDER BY id DESC;