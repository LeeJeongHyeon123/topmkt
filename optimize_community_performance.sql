-- 커뮤니티 게시판 성능 최적화 SQL
-- 실행: mysql -u root -pDnlszkem1! topmkt < optimize_community_performance.sql

USE TOPMKT;

-- 현재 상태 확인
SELECT '=== 최적화 전 인덱스 상태 ===' as info;
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    CARDINALITY
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'TOPMKT' 
  AND TABLE_NAME IN ('posts', 'users')
  AND INDEX_NAME != 'PRIMARY'
ORDER BY TABLE_NAME, INDEX_NAME;

-- 1. 커뮤니티 게시판 최적화 인덱스 추가
SELECT '=== 인덱스 생성 중 ===' as info;

-- 게시글 목록 조회 최적화 (status + created_at + user_id 복합 인덱스)
CREATE INDEX IF NOT EXISTS idx_posts_community_list 
ON posts (status, created_at DESC, user_id);

-- 사용자 프로필 이미지 조회 최적화 
CREATE INDEX IF NOT EXISTS idx_users_profile_optimized 
ON users (id, nickname, profile_image_thumb, profile_image_profile);

-- 게시글-사용자 JOIN 최적화
CREATE INDEX IF NOT EXISTS idx_posts_user_join 
ON posts (user_id, status, created_at DESC);

-- 검색 성능 최적화 (제목 검색)
CREATE INDEX IF NOT EXISTS idx_posts_title_search 
ON posts (title(100), status);

-- 댓글 조회 최적화
CREATE INDEX IF NOT EXISTS idx_comments_post_optimized
ON comments (post_id, status, created_at DESC);

-- 2. 테이블 통계 업데이트 (옵티마이저 개선)
SELECT '=== 테이블 통계 업데이트 중 ===' as info;
ANALYZE TABLE posts;
ANALYZE TABLE users;
ANALYZE TABLE comments;

-- 3. 생성된 인덱스 확인
SELECT '=== 최적화 후 인덱스 상태 ===' as info;
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    CARDINALITY,
    INDEX_TYPE
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'TOPMKT'
  AND TABLE_NAME IN ('posts', 'users', 'comments')
  AND INDEX_NAME LIKE 'idx_%'
ORDER BY TABLE_NAME, INDEX_NAME;

-- 4. 성능 개선 확인용 쿼리 (실행 계획 확인)
SELECT '=== 쿼리 실행 계획 분석 ===' as info;
EXPLAIN SELECT 
    p.id,
    p.user_id,
    p.title,
    LEFT(p.content, 200) as content_preview,
    p.view_count,
    p.like_count,
    p.comment_count,
    p.status,
    p.created_at,
    u.nickname as author_name,
    COALESCE(u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image
FROM posts p
JOIN users u ON p.user_id = u.id
WHERE p.status = 'published'
ORDER BY p.created_at DESC 
LIMIT 20 OFFSET 0;

-- 5. 성능 테스트
SELECT '=== 성능 테스트 시작 ===' as info;

-- 게시글 총 개수 (인덱스 효과 확인)
SELECT 'posts 총 개수:' as metric, COUNT(*) as value 
FROM posts WHERE status = 'published';

-- 프로필 이미지 보유 사용자 수
SELECT '프로필 이미지 보유 사용자:' as metric, COUNT(*) as value 
FROM users WHERE profile_image_thumb IS NOT NULL;

-- 최근 게시글 조회 테스트 (성능 측정)
SELECT 'top 20 posts 조회 완료' as test_result, COUNT(*) as count
FROM (
    SELECT p.id
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC 
    LIMIT 20
) test;

SELECT '=== 최적화 완료! ===' as info;
SELECT 'Community performance optimization completed successfully!' as result; 