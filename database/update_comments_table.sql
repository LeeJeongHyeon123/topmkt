-- comments 테이블 status 컬럼 추가 (없는 경우)
-- 실행 방법: MariaDB 콘솔에서 다음 명령어 실행
-- mysql -u root -p
-- USE TOPMKT;
-- SOURCE /var/www/html/topmkt/database/update_comments_table.sql;

-- status 컬럼이 없으면 추가
ALTER TABLE comments 
ADD COLUMN IF NOT EXISTS status ENUM('active', 'deleted') DEFAULT 'active' 
COMMENT '댓글 상태' 
AFTER content;

-- 기존 댓글들의 status를 'active'로 설정
UPDATE comments SET status = 'active' WHERE status IS NULL;

-- 인덱스 추가
ALTER TABLE comments ADD INDEX IF NOT EXISTS idx_status (status);

-- 완료 메시지
SELECT 'comments 테이블 업데이트 완료!' as message;