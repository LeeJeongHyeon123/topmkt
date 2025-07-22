-- 프로필 페이지를 위한 users 테이블 확장
-- 실행 날짜: 2025-06-11

-- 프로필 관련 필드 추가
ALTER TABLE users 
ADD COLUMN bio TEXT NULL COMMENT '자기소개' AFTER email,
ADD COLUMN birth_date DATE NULL COMMENT '생년월일' AFTER bio,
ADD COLUMN gender ENUM('M','F','OTHER') NULL COMMENT '성별 (M:남성, F:여성, OTHER:기타)' AFTER birth_date,
ADD COLUMN profile_image_original VARCHAR(255) NULL COMMENT '원본 프로필 이미지 경로' AFTER gender,
ADD COLUMN profile_image_profile VARCHAR(255) NULL COMMENT '프로필용 이미지 경로 (200x200)' AFTER profile_image_original,
ADD COLUMN profile_image_thumb VARCHAR(255) NULL COMMENT '썸네일 이미지 경로 (80x80)' AFTER profile_image_profile,
ADD COLUMN website_url VARCHAR(255) NULL COMMENT '개인 웹사이트 URL' AFTER profile_image_thumb,
ADD COLUMN social_links JSON NULL COMMENT '소셜 미디어 링크 (카카오톡, 인스타그램, 페이스북, 유튜브, 틱톡)' AFTER website_url;

-- 프로필 이미지 관련 인덱스 추가 (빠른 조회를 위해)
ALTER TABLE users ADD INDEX idx_profile_images (profile_image_original, profile_image_profile, profile_image_thumb);

-- 성별 필드 인덱스 (통계용)
ALTER TABLE users ADD INDEX idx_gender (gender);

-- 생년월일 인덱스 (나이대별 통계용)
ALTER TABLE users ADD INDEX idx_birth_date (birth_date);

-- 소셜 링크 샘플 데이터 구조 (JSON 예시)
/*
{
  "kakao": "https://open.kakao.com/o/xxxxxxx",
  "website": "https://mywebsite.com",
  "instagram": "https://instagram.com/username",
  "facebook": "https://facebook.com/username",
  "youtube": "https://youtube.com/@channelname",
  "tiktok": "https://tiktok.com/@username"
}
*/