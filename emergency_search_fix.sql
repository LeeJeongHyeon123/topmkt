-- 긴급 검색 성능 수정 SQL
-- 100만 건 데이터에서 FULLTEXT 검색 최적화

-- 1. 기존 FULLTEXT 인덱스 제거 (중복 및 성능 문제)
DROP INDEX IF EXISTS idx_posts_fulltext_search ON posts;
DROP INDEX IF EXISTS idx_title_content ON posts;

-- 2. FULLTEXT 검색을 위한 MyISAM 임시 테이블 방식 또는 InnoDB 최적화
-- InnoDB FULLTEXT 설정 최적화
SET GLOBAL innodb_ft_min_token_size = 2;
SET GLOBAL innodb_ft_max_token_size = 84;
SET GLOBAL innodb_ft_enable_stopword = OFF;

-- 3. 새로운 최적화된 FULLTEXT 인덱스 생성
CREATE FULLTEXT INDEX idx_posts_fulltext_optimized ON posts (title, content) 
WITH PARSER ngram;

-- 4. 인덱스 통계 갱신
ANALYZE TABLE posts;

-- 5. 검색 성능 테스트 쿼리
-- 테스트용 - 실행하지 말고 확인만
-- SELECT COUNT(*) FROM posts 
-- WHERE MATCH(title, content) AGAINST('마케팅' IN NATURAL LANGUAGE MODE);

-- 6. 대안: 제한된 FULLTEXT 검색 (최근 1만 건만)
-- 성능이 여전히 느리면 이 방식 사용
/*
CREATE OR REPLACE VIEW posts_recent AS
SELECT * FROM posts 
WHERE status = 'published' 
ORDER BY created_at DESC 
LIMIT 10000;

CREATE FULLTEXT INDEX idx_posts_recent_fulltext ON posts_recent (title, content);
*/

SELECT 'FULLTEXT 인덱스 최적화 완료!' AS status;