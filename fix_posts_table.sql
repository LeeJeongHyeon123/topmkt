-- posts 테이블에 status 컬럼 추가
ALTER TABLE posts ADD COLUMN IF NOT EXISTS status ENUM('draft', 'published', 'deleted') DEFAULT 'published' AFTER content;

-- 기존 게시글들을 모두 published 상태로 설정
UPDATE posts SET status = 'published' WHERE status IS NULL;

-- 인덱스 추가
ALTER TABLE posts ADD INDEX IF NOT EXISTS idx_status (status);
ALTER TABLE posts ADD INDEX IF NOT EXISTS idx_posts_list_performance (status, created_at DESC);

-- 확인
SELECT COUNT(*) as total_posts FROM posts WHERE status = 'published';