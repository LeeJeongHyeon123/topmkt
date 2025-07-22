-- 132번 강의 강사 이미지 수정 쿼리
-- 방법 1: 기존 instructor-kim.jpg 이미지 연결
UPDATE lectures 
SET instructors_json = '[{"name":"김마케팅","info":"디지털 마케팅 전문가로 10년 이상의 경험을 보유하고 있습니다.","title":"디지털 마케팅 컨설턴트","image":"/assets/uploads/instructors/instructor-kim.jpg"}]'
WHERE id = 132;

-- 방법 2: 기존 instructor_name을 사용하여 JSON 생성
UPDATE lectures 
SET instructors_json = CONCAT(
    '[{"name":"', IFNULL(instructor_name, '강사'), 
    '","info":"', IFNULL(instructor_info, '전문적인 경험과 노하우를 바탕으로 실무에 바로 적용할 수 있는 내용을 전달합니다.'),
    '","title":"강사","image":"/assets/uploads/instructors/instructor-kim.jpg"}]'
)
WHERE id = 132;

-- 확인 쿼리
SELECT id, title, instructor_name, instructors_json 
FROM lectures 
WHERE id = 132;