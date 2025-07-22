-- 132번 강의의 강사 이미지 문제 해결 스크립트

-- 1. 먼저 현재 132번 강의 데이터 확인
SELECT 
    id,
    title,
    instructor_name,
    instructor_info,
    instructors_json
FROM lectures 
WHERE id = 132;

-- 2. 132번 강의의 강사 정보 업데이트 (예시)
-- 강사 이름과 기본 이미지를 연결하여 JSON 데이터 생성

UPDATE lectures 
SET instructors_json = JSON_ARRAY(
    JSON_OBJECT(
        'name', COALESCE(SUBSTRING_INDEX(instructor_name, ',', 1), '마케팅 전문가'),
        'title', '마케팅 컨설턴트',
        'info', COALESCE(
            SUBSTRING_INDEX(instructor_info, '|||', 1), 
            '다년간의 마케팅 경험과 실무 노하우를 바탕으로 실전에서 바로 적용할 수 있는 전략을 제공합니다. 디지털 마케팅부터 브랜딩까지 다양한 영역에서의 전문성을 보유하고 있습니다.'
        ),
        'image', '/assets/uploads/instructors/instructor-kim.jpg'
    )
)
WHERE id = 132 AND (instructors_json IS NULL OR instructors_json = '' OR instructors_json = 'null');

-- 3. 만약 여러 강사가 있는 경우를 위한 업데이트 (쉼표로 구분된 강사명 처리)
UPDATE lectures 
SET instructors_json = CASE 
    WHEN instructor_name LIKE '%,%' THEN 
        -- 여러 강사인 경우
        JSON_ARRAY(
            JSON_OBJECT(
                'name', TRIM(SUBSTRING_INDEX(instructor_name, ',', 1)),
                'title', '수석 강사',
                'info', COALESCE(
                    SUBSTRING_INDEX(instructor_info, '|||', 1),
                    '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.'
                ),
                'image', '/assets/uploads/instructors/instructor-kim.jpg'
            ),
            JSON_OBJECT(
                'name', TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(instructor_name, ',', 2), ',', -1)),
                'title', '전문 강사',
                'info', COALESCE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(instructor_info, '|||', 2), '|||', -1),
                    '풍부한 실무 경험을 통해 참가자들에게 실질적인 도움이 되는 내용을 제공합니다.'
                ),
                'image', '/assets/uploads/instructors/instructor-lee.jpg'
            )
        )
    ELSE 
        -- 단일 강사인 경우
        JSON_ARRAY(
            JSON_OBJECT(
                'name', TRIM(instructor_name),
                'title', '전문 강사',
                'info', COALESCE(
                    instructor_info,
                    '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.'
                ),
                'image', '/assets/uploads/instructors/instructor-kim.jpg'
            )
        )
END
WHERE id = 132 AND (instructors_json IS NULL OR instructors_json = '' OR instructors_json = 'null');

-- 4. 업데이트 결과 확인
SELECT 
    id,
    title,
    instructor_name,
    instructors_json,
    JSON_PRETTY(instructors_json) as formatted_json
FROM lectures 
WHERE id = 132;

-- 5. 모든 강의에서 강사 이미지가 없는 경우들을 찾아서 기본 이미지 할당
-- (선택사항: 132번뿐만 아니라 다른 강의들도 일괄 처리)

-- 기본 강사 이미지 목록
-- /assets/uploads/instructors/instructor-1.jpg
-- /assets/uploads/instructors/instructor-2.jpg
-- /assets/uploads/instructors/instructor-3.jpg
-- /assets/uploads/instructors/instructor-kim.jpg
-- /assets/uploads/instructors/instructor-lee.jpg
-- /assets/uploads/instructors/instructor-park.jpg

UPDATE lectures 
SET instructors_json = JSON_ARRAY(
    JSON_OBJECT(
        'name', COALESCE(NULLIF(TRIM(instructor_name), ''), '전문 강사'),
        'title', '마케팅 전문가',
        'info', COALESCE(
            NULLIF(TRIM(instructor_info), ''),
            '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.'
        ),
        'image', CASE 
            WHEN id % 6 = 0 THEN '/assets/uploads/instructors/instructor-kim.jpg'
            WHEN id % 6 = 1 THEN '/assets/uploads/instructors/instructor-lee.jpg'
            WHEN id % 6 = 2 THEN '/assets/uploads/instructors/instructor-park.jpg'
            WHEN id % 6 = 3 THEN '/assets/uploads/instructors/instructor-1.jpg'
            WHEN id % 6 = 4 THEN '/assets/uploads/instructors/instructor-2.jpg'
            ELSE '/assets/uploads/instructors/instructor-3.jpg'
        END
    )
)
WHERE (instructors_json IS NULL OR instructors_json = '' OR instructors_json = 'null' OR instructors_json = '[]')
AND instructor_name IS NOT NULL 
AND instructor_name != '';

-- 6. 최종 결과 확인 (132번 강의와 최근 몇 개 강의)
SELECT 
    id,
    title,
    instructor_name,
    JSON_EXTRACT(instructors_json, '$[0].name') as first_instructor_name,
    JSON_EXTRACT(instructors_json, '$[0].image') as first_instructor_image
FROM lectures 
WHERE id IN (132) OR id > 120
ORDER BY id DESC
LIMIT 10;