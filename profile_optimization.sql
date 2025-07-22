-- 프로필 성능 최적화 SQL
-- 실행 시간: 2024-XX-XX

-- 1. posts 테이블 최적화 - 사용자별 게시글 조회 최적화
CREATE INDEX IF NOT EXISTS idx_posts_user_stats ON posts (user_id, status, like_count, created_at);

-- 2. posts 테이블 최적화 - 좋아요 수 계산 최적화 (like_count > 0인 경우만)
CREATE INDEX IF NOT EXISTS idx_posts_likes_optimization ON posts (user_id, status, like_count) WHERE like_count > 0;

-- 3. comments 테이블 최적화 - 사용자별 댓글 조회 최적화
CREATE INDEX IF NOT EXISTS idx_comments_user_stats ON comments (user_id, status, created_at);

-- 4. 통계 정보 저장을 위한 캐시 테이블 생성
CREATE TABLE IF NOT EXISTS user_stats_cache (
    user_id INT PRIMARY KEY,
    post_count INT DEFAULT 0,
    comment_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    join_days INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_last_updated (last_updated)
);

-- 5. 대용량 데이터 처리를 위한 파티셔닝 고려사항
-- posts 테이블이 너무 크면 파티셔닝 검토
-- ALTER TABLE posts PARTITION BY RANGE (YEAR(created_at)) (
--     PARTITION p2023 VALUES LESS THAN (2024),
--     PARTITION p2024 VALUES LESS THAN (2025),
--     PARTITION p2025 VALUES LESS THAN (2026),
--     PARTITION p_future VALUES LESS THAN MAXVALUE
-- );

-- 6. 불필요한 인덱스 정리 (중복 인덱스 제거)
-- 기존 인덱스 중 중복되는 것들 확인 후 제거
-- DROP INDEX IF EXISTS idx_posts_stats ON posts;

-- 7. 쿼리 최적화를 위한 분석 테이블 업데이트
ANALYZE TABLE posts;
ANALYZE TABLE comments;
ANALYZE TABLE users;

-- 8. 사용자 통계 미리 계산 (배치 작업)
INSERT INTO user_stats_cache (user_id, post_count, comment_count, like_count, join_days)
SELECT 
    u.id,
    (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id AND p.status = 'published'),
    (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id AND c.status = 'active'),
    (SELECT COALESCE(SUM(p.like_count), 0) FROM posts p WHERE p.user_id = u.id AND p.status = 'published'),
    DATEDIFF(NOW(), u.created_at)
FROM users u
WHERE u.status = 'active'
ON DUPLICATE KEY UPDATE
    post_count = VALUES(post_count),
    comment_count = VALUES(comment_count),
    like_count = VALUES(like_count),
    join_days = VALUES(join_days);

-- 9. 성능 모니터링을 위한 slow query log 활성화
-- SET GLOBAL slow_query_log = 'ON';
-- SET GLOBAL long_query_time = 1;

-- 10. 캐시 무효화 트리거 생성
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_user_stats_on_post_insert
    AFTER INSERT ON posts
    FOR EACH ROW
BEGIN
    INSERT INTO user_stats_cache (user_id, post_count, last_updated)
    VALUES (NEW.user_id, 1, NOW())
    ON DUPLICATE KEY UPDATE
        post_count = post_count + 1,
        last_updated = NOW();
END//

CREATE TRIGGER IF NOT EXISTS update_user_stats_on_post_update
    AFTER UPDATE ON posts
    FOR EACH ROW
BEGIN
    IF OLD.like_count != NEW.like_count THEN
        UPDATE user_stats_cache 
        SET like_count = like_count + (NEW.like_count - OLD.like_count),
            last_updated = NOW()
        WHERE user_id = NEW.user_id;
    END IF;
END//

CREATE TRIGGER IF NOT EXISTS update_user_stats_on_comment_insert
    AFTER INSERT ON comments
    FOR EACH ROW
BEGIN
    INSERT INTO user_stats_cache (user_id, comment_count, last_updated)
    VALUES (NEW.user_id, 1, NOW())
    ON DUPLICATE KEY UPDATE
        comment_count = comment_count + 1,
        last_updated = NOW();
END//
DELIMITER ;