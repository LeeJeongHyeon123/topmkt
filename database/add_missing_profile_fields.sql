-- 누락된 프로필 필드만 추가하는 스크립트
-- 실행 날짜: 2025-06-11
-- 이미 존재하는 필드는 에러가 발생하지만 계속 진행됩니다.

-- birth_date 필드 추가
SET @sql = 'ALTER TABLE users ADD COLUMN birth_date DATE NULL COMMENT ''생년월일'' AFTER bio';
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'birth_date');
SET @sql = IF(@table_exists = 0, @sql, 'SELECT ''birth_date already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- gender 필드 추가
SET @sql = 'ALTER TABLE users ADD COLUMN gender ENUM(''M'',''F'',''OTHER'') NULL COMMENT ''성별 (M:남성, F:여성, OTHER:기타)'' AFTER birth_date';
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'gender');
SET @sql = IF(@table_exists = 0, @sql, 'SELECT ''gender already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- profile_image_original 필드 추가
SET @sql = 'ALTER TABLE users ADD COLUMN profile_image_original VARCHAR(255) NULL COMMENT ''원본 프로필 이미지 경로'' AFTER gender';
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'profile_image_original');
SET @sql = IF(@table_exists = 0, @sql, 'SELECT ''profile_image_original already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- profile_image_profile 필드 추가
SET @sql = 'ALTER TABLE users ADD COLUMN profile_image_profile VARCHAR(255) NULL COMMENT ''프로필용 이미지 경로 (200x200)'' AFTER profile_image_original';
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'profile_image_profile');
SET @sql = IF(@table_exists = 0, @sql, 'SELECT ''profile_image_profile already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- profile_image_thumb 필드 추가
SET @sql = 'ALTER TABLE users ADD COLUMN profile_image_thumb VARCHAR(255) NULL COMMENT ''썸네일 이미지 경로 (80x80)'' AFTER profile_image_profile';
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'profile_image_thumb');
SET @sql = IF(@table_exists = 0, @sql, 'SELECT ''profile_image_thumb already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- website_url 필드 추가
SET @sql = 'ALTER TABLE users ADD COLUMN website_url VARCHAR(255) NULL COMMENT ''개인 웹사이트 URL'' AFTER profile_image_thumb';
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'website_url');
SET @sql = IF(@table_exists = 0, @sql, 'SELECT ''website_url already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- social_links 필드 추가
SET @sql = 'ALTER TABLE users ADD COLUMN social_links JSON NULL COMMENT ''소셜 미디어 링크 (카카오톡, 인스타그램, 페이스북, 유튜브, 틱톡)'' AFTER website_url';
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'social_links');
SET @sql = IF(@table_exists = 0, @sql, 'SELECT ''social_links already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 인덱스 추가 (중복 시 에러 무시)
SET @sql = 'ALTER TABLE users ADD INDEX idx_profile_images (profile_image_original, profile_image_profile, profile_image_thumb)';
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_profile_images');
SET @sql = IF(@index_exists = 0, @sql, 'SELECT ''idx_profile_images already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'ALTER TABLE users ADD INDEX idx_gender (gender)';
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_gender');
SET @sql = IF(@index_exists = 0, @sql, 'SELECT ''idx_gender already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = 'ALTER TABLE users ADD INDEX idx_birth_date (birth_date)';
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = 'TOPMKT' AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_birth_date');
SET @sql = IF(@index_exists = 0, @sql, 'SELECT ''idx_birth_date already exists'' as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 완료 메시지
SELECT 'Profile fields migration completed!' as status;