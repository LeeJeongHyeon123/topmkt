-- lectures 테이블 구조 확인
DESCRIBE lectures;

-- 특히 registration_deadline과 youtube_video 필드 확인
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
  AND TABLE_NAME = 'lectures' 
  AND COLUMN_NAME IN ('registration_deadline', 'youtube_video');