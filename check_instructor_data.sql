-- 강사 정보 저장 확인 쿼리
-- 가장 최근 임시저장된 강의의 강사 정보 확인

SELECT 
    id,
    title,
    instructor_name,
    instructor_info,
    instructors_json,
    registration_deadline,
    youtube_video,
    status,
    created_at,
    updated_at
FROM lectures 
WHERE status = 'draft' 
ORDER BY updated_at DESC, created_at DESC 
LIMIT 1;

-- 강사 JSON 데이터가 있는 경우 포맷팅해서 보기
SELECT 
    id,
    title,
    JSON_PRETTY(instructors_json) as formatted_instructors,
    status,
    updated_at
FROM lectures 
WHERE status = 'draft' 
AND instructors_json IS NOT NULL
ORDER BY updated_at DESC 
LIMIT 1;

-- 모든 임시저장된 강의의 강사 정보 요약
SELECT 
    id,
    title,
    CASE 
        WHEN instructor_name IS NOT NULL AND instructor_name != '' THEN '✓'
        ELSE '✗'
    END as has_instructor_name,
    CASE 
        WHEN instructors_json IS NOT NULL THEN '✓'
        ELSE '✗'
    END as has_instructors_json,
    CASE 
        WHEN registration_deadline IS NOT NULL THEN '✓'
        ELSE '✗'
    END as has_deadline,
    status,
    updated_at
FROM lectures 
WHERE status = 'draft'
ORDER BY updated_at DESC;