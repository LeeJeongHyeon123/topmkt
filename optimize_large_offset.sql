-- 대용량 페이지네이션 성능 최적화를 위한 추가 인덱스
-- 실행 명령어: SOURCE /var/www/html/topmkt/optimize_large_offset.sql;

-- 현재 posts 테이블 인덱스 확인
SHOW INDEX FROM posts;

-- 큰 OFFSET 최적화를 위한 복합 인덱스 (created_at + id)
-- 이 인덱스는 ORDER BY created_at DESC 와 커서 기반 페이지네이션에 최적화됨
CREATE INDEX IF NOT EXISTS idx_posts_cursor_pagination 
ON posts (created_at DESC, id DESC, status);

-- 게시글 ID만을 빠르게 조회하기 위한 커버링 인덱스
CREATE INDEX IF NOT EXISTS idx_posts_id_only 
ON posts (status, created_at DESC, id);

-- 검색 쿼리 최적화를 위한 FULLTEXT 인덱스
CREATE FULLTEXT INDEX IF NOT EXISTS idx_posts_fulltext_search 
ON posts (title, content);

-- 게시글 상태별 created_at 정렬 최적화
CREATE INDEX IF NOT EXISTS idx_posts_status_created 
ON posts (status, created_at DESC) 
WHERE status = 'published';

-- 통계 정보 업데이트
ANALYZE TABLE posts;

-- 인덱스 생성 후 확인
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    CARDINALITY,
    INDEX_TYPE,
    COMMENT
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'posts'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 쿼리 성능 테스트 (옵션)
-- EXPLAIN SELECT id FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 20 OFFSET 1000000;

SELECT '대용량 페이지네이션 최적화 완료!' AS message;