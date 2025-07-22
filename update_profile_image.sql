-- 우리집탄이 사용자의 프로필 이미지 업데이트
-- 실행 방법: MariaDB 콘솔에서 다음 명령어 실행
-- mysql -u root -p
-- USE TOPMKT;
-- SOURCE /var/www/html/topmkt/update_profile_image.sql;

UPDATE users 
SET profile_image = '/assets/images/user-profile-urijibtani.jpg'
WHERE nickname = '우리집탄이';

-- 업데이트 확인
SELECT id, nickname, profile_image 
FROM users 
WHERE nickname = '우리집탄이';

-- 완료 메시지
SELECT '우리집탄이 프로필 이미지 업데이트 완료!' as message;