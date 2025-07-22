-- 커뮤니티 게시판 성능 최적화를 위한 인덱스 생성
-- 실행 명령어: SOURCE /var/www/html/topmkt/optimize_database_indexes.sql;

-- 현재 인덱스 상태 확인
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    INDEX_TYPE
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME IN ('posts', 'users', 'comments')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- posts 테이블 최적화 인덱스
-- 1. 게시글 목록 조회 최적화 (created_at DESC + status)
CREATE INDEX IF NOT EXISTS idx_posts_list_performance 
ON posts (status, created_at DESC);

-- 2. 검색 기능 최적화 (title, content 검색)
CREATE INDEX IF NOT EXISTS idx_posts_search_title 
ON posts (title);

CREATE INDEX IF NOT EXISTS idx_posts_search_content 
ON posts (content(100)); -- content의 첫 100자만 인덱싱

-- 3. 작성자별 게시글 조회 최적화
CREATE INDEX IF NOT EXISTS idx_posts_user_created 
ON posts (user_id, created_at DESC);

-- 4. 통계 조회 최적화
CREATE INDEX IF NOT EXISTS idx_posts_stats 
ON posts (view_count, like_count, comment_count);

-- users 테이블 최적화 인덱스
-- 1. 닉네임 검색 최적화
CREATE INDEX IF NOT EXISTS idx_users_nickname 
ON users (nickname);

-- 2. 프로필 이미지 조회 최적화
CREATE INDEX IF NOT EXISTS idx_users_profile 
ON users (id, nickname, profile_image);

-- comments 테이블 최적화 인덱스
-- 1. 게시글별 댓글 조회 최적화
CREATE INDEX IF NOT EXISTS idx_comments_post_performance 
ON comments (post_id, status, created_at DESC);

-- 2. 대댓글 조회 최적화
CREATE INDEX IF NOT EXISTS idx_comments_parent_performance 
ON comments (parent_id, status, created_at);

-- 3. 사용자별 댓글 조회 최적화
CREATE INDEX IF NOT EXISTS idx_comments_user_performance 
ON comments (user_id, status, created_at DESC);

-- 복합 인덱스로 JOIN 성능 향상
-- posts와 users 조인 최적화
CREATE INDEX IF NOT EXISTS idx_posts_join_optimization 
ON posts (user_id, status, created_at DESC, id);

-- 인덱스 생성 완료 후 통계 업데이트
ANALYZE TABLE posts;
ANALYZE TABLE users; 
ANALYZE TABLE comments;

-- 최종 인덱스 확인
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    CARDINALITY,
    INDEX_TYPE
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME IN ('posts', 'users', 'comments')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

SELECT '데이터베이스 인덱스 최적화 완료!' AS message;