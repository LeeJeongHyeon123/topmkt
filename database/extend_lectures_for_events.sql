-- lectures 테이블을 행사 일정도 지원하도록 확장
-- 2025-01-06: 행사 일정 시스템 구현을 위한 테이블 확장

-- 1. content_type 컬럼 추가 (강의/행사 구분)
ALTER TABLE lectures 
ADD COLUMN content_type ENUM('lecture', 'event') DEFAULT 'lecture' 
COMMENT '콘텐츠 타입 (강의/행사)' 
AFTER category;

-- 2. 행사 전용 필드 추가
ALTER TABLE lectures 
ADD COLUMN event_scale ENUM('small', 'medium', 'large') NULL 
COMMENT '행사 규모 (소규모/중규모/대규모)' 
AFTER content_type;

ALTER TABLE lectures 
ADD COLUMN has_networking BOOLEAN DEFAULT FALSE 
COMMENT '네트워킹 시간 포함 여부' 
AFTER event_scale;

ALTER TABLE lectures 
ADD COLUMN sponsor_info TEXT NULL 
COMMENT '협찬사/파트너 정보' 
AFTER has_networking;

ALTER TABLE lectures 
ADD COLUMN dress_code ENUM('casual', 'business_casual', 'business', 'formal') NULL 
COMMENT '드레스 코드' 
AFTER sponsor_info;

ALTER TABLE lectures 
ADD COLUMN parking_info TEXT NULL 
COMMENT '주차 정보' 
AFTER dress_code;

-- 3. 기존 데이터를 모두 'lecture'로 설정
UPDATE lectures SET content_type = 'lecture' WHERE content_type IS NULL;

-- 4. 인덱스 추가 (성능 최적화)
CREATE INDEX idx_lectures_content_type ON lectures(content_type);
CREATE INDEX idx_lectures_start_date_type ON lectures(start_date, content_type);

-- 5. 확인용 쿼리
SELECT 
    'lectures 테이블 확장 완료' as status,
    COUNT(*) as total_count,
    COUNT(CASE WHEN content_type = 'lecture' THEN 1 END) as lecture_count,
    COUNT(CASE WHEN content_type = 'event' THEN 1 END) as event_count
FROM lectures;