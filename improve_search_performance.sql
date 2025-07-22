-- 커뮤니티 검색 성능 최적화 SQL
-- 실행 명령어: SOURCE /var/www/html/topmkt/improve_search_performance.sql;

-- 1. 현재 FULLTEXT 인덱스 상태 확인
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    INDEX_TYPE,
    COLUMN_NAME
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'posts'
  AND INDEX_TYPE = 'FULLTEXT'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 2. 기존 비효율적인 부분 인덱스 제거 후 재생성
DROP INDEX IF EXISTS idx_posts_search_content ON posts;

-- 3. FULLTEXT 검색을 위한 최적화된 인덱스 생성
-- (이미 존재하지만 확실히 하기 위해)
CREATE FULLTEXT INDEX IF NOT EXISTS idx_posts_fulltext_search 
ON posts (title, content);

-- 4. 검색 결과 정렬을 위한 복합 인덱스 최적화
CREATE INDEX IF NOT EXISTS idx_posts_search_performance 
ON posts (status, created_at DESC, view_count DESC);

-- 5. 제목만 검색할 때를 위한 개선된 인덱스
CREATE INDEX IF NOT EXISTS idx_posts_title_optimized 
ON posts (title(50), status, created_at DESC);

-- 6. MyISAM의 FULLTEXT 성능을 위한 설정 (MariaDB/MySQL 버전에 따라)
-- ft_min_word_len = 2 (2글자 이상 검색어)
-- ft_boolean_syntax = '+ -><()~*:""&|' (불린 검색 지원)

-- 7. 통계 정보 갱신
ANALYZE TABLE posts;

-- 8. 검색 성능 테스트 쿼리들
-- FULLTEXT 자연어 검색 테스트
-- EXPLAIN SELECT * FROM posts 
-- WHERE MATCH(title, content) AGAINST('마케팅' IN NATURAL LANGUAGE MODE) 
-- AND status = 'published'
-- ORDER BY created_at DESC 
-- LIMIT 20;

-- FULLTEXT 불린 검색 테스트 (AND 조건)
-- EXPLAIN SELECT * FROM posts 
-- WHERE MATCH(title, content) AGAINST('+마케팅 +네트워크' IN BOOLEAN MODE) 
-- AND status = 'published'
-- ORDER BY created_at DESC 
-- LIMIT 20;

-- 인덱스 사용률 확인
SELECT 
    INDEX_NAME,
    CARDINALITY,
    SUB_PART,
    INDEX_TYPE
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'posts'
  AND INDEX_NAME IN ('idx_posts_fulltext_search', 'idx_posts_search_performance', 'idx_posts_title_optimized')
ORDER BY INDEX_NAME;

SELECT '검색 성능 최적화 인덱스 생성 완료!' AS message;