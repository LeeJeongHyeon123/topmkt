-- 100만 건 데이터용 폴백 검색 최적화
-- FULLTEXT가 너무 느릴 경우 사용

-- 1. 제목 우선 검색 인덱스 최적화
CREATE INDEX IF NOT EXISTS idx_posts_title_search ON posts (title(100), status, created_at DESC);

-- 2. 내용 검색을 위한 부분 인덱스
CREATE INDEX IF NOT EXISTS idx_posts_content_prefix ON posts (content(200), status);

-- 3. 검색 성능을 위한 전용 테이블 생성 (검색어 캐시용)
CREATE TABLE IF NOT EXISTS search_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(255) NOT NULL,
    search_hash VARCHAR(64) NOT NULL UNIQUE,
    result_count INT NOT NULL,
    result_ids TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 1 HOUR),
    INDEX idx_search_hash (search_hash),
    INDEX idx_expires (expires_at)
);

-- 4. 인기 검색어를 위한 미리 계산된 결과
CREATE TABLE IF NOT EXISTS popular_searches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(255) NOT NULL UNIQUE,
    search_count INT DEFAULT 1,
    last_searched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_search_count (search_count DESC),
    INDEX idx_last_searched (last_searched DESC)
);

-- 5. 제목만 빠른 검색을 위한 뷰 (대안)
CREATE OR REPLACE VIEW posts_searchable AS
SELECT 
    id,
    user_id,
    title,
    LEFT(content, 300) as content_preview,
    view_count,
    like_count,
    comment_count,
    status,
    created_at
FROM posts 
WHERE status = 'published'
AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR); -- 최근 1년 데이터만

-- 6. 통계 정보 갱신
ANALYZE TABLE posts;

SELECT '폴백 검색 최적화 완료!' AS status;