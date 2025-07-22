-- 행사 ID 122 주소를 반도 아이비밸리로 업데이트

UPDATE lectures SET 
    venue_address = '서울시 금천구 가산디지털1로 204 반도 아이비밸리 6층'
WHERE id = 122 AND content_type = 'event';