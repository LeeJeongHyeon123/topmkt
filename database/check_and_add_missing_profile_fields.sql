-- 프로필 필드 존재 여부 확인 및 누락된 필드만 추가
-- 실행 날짜: 2025-06-11

-- 기존 필드 확인을 위한 쿼리
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME IN ('bio', 'birth_date', 'gender', 'profile_image_original', 'profile_image_profile', 'profile_image_thumb', 'website_url', 'social_links');

-- 조건부 필드 추가 (이미 존재하면 에러 무시)
-- MySQL 8.0+ 에서는 IF NOT EXISTS 사용 가능하지만, 하위 버전 호환을 위해 개별 실행

-- birth_date 필드 추가 (bio 다음에)
ALTER TABLE users ADD COLUMN birth_date DATE NULL COMMENT '생년월일' AFTER bio;

-- gender 필드 추가
ALTER TABLE users ADD COLUMN gender ENUM('M','F','OTHER') NULL COMMENT '성별 (M:남성, F:여성, OTHER:기타)' AFTER birth_date;

-- profile_image_original 필드 추가
ALTER TABLE users ADD COLUMN profile_image_original VARCHAR(255) NULL COMMENT '원본 프로필 이미지 경로' AFTER gender;

-- profile_image_profile 필드 추가
ALTER TABLE users ADD COLUMN profile_image_profile VARCHAR(255) NULL COMMENT '프로필용 이미지 경로 (200x200)' AFTER profile_image_original;

-- profile_image_thumb 필드 추가
ALTER TABLE users ADD COLUMN profile_image_thumb VARCHAR(255) NULL COMMENT '썸네일 이미지 경로 (80x80)' AFTER profile_image_profile;

-- website_url 필드 추가
ALTER TABLE users ADD COLUMN website_url VARCHAR(255) NULL COMMENT '개인 웹사이트 URL' AFTER profile_image_thumb;

-- social_links 필드 추가
ALTER TABLE users ADD COLUMN social_links JSON NULL COMMENT '소셜 미디어 링크 (카카오톡, 인스타그램, 페이스북, 유튜브, 틱톡)' AFTER website_url;

-- 인덱스 추가 (중복 방지)
ALTER TABLE users ADD INDEX idx_profile_images (profile_image_original, profile_image_profile, profile_image_thumb);
ALTER TABLE users ADD INDEX idx_gender (gender);
ALTER TABLE users ADD INDEX idx_birth_date (birth_date);