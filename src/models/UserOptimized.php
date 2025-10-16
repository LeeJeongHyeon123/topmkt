<?php
/**
 * 최적화된 사용자 모델 클래스
 */

require_once SRC_PATH . '/config/database.php';

class UserOptimized {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 캐시를 사용한 최적화된 프로필 데이터 조회
     * 대용량 데이터 사용자를 위한 성능 최적화
     */
    public function getOptimizedProfileDataWithCache($userId) {
        try {
            // ROOT_PATH가 정의되지 않은 경우 안전한 기본값 사용
            if (!defined('ROOT_PATH')) {
                define('ROOT_PATH', '/var/www/html/topmkt');
            }

            $cacheKey = "profile_data_" . $userId;
            $cacheFile = ROOT_PATH . '/cache/profile_' . $userId . '.json';

            // 캐시 디렉토리가 없으면 생성
            if (!is_dir(ROOT_PATH . '/cache')) {
                mkdir(ROOT_PATH . '/cache', 0755, true);
            }

            // 🚀 v3.66.0: 캐시 TTL 10분 → 1시간 연장 (성능 최적화)
            // 🚀 v3.76.0: 캐시 TTL 1시간 → 6시간 연장 (캐시 미스 빈도 6배 감소)
            // 캐시 파일이 있고 6시간 미만이면 사용
            $cacheTTL = 21600; // 6시간 (360분 * 60초)
            if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
                $cachedData = json_decode(file_get_contents($cacheFile), true);
                if ($cachedData) {
                    $cacheAge = time() - filemtime($cacheFile);
                    error_log("✅ Profile cache HIT for user $userId (age: {$cacheAge}s / {$cacheTTL}s)");
                    return $cachedData;
                }
            }

            // 캐시 미스 또는 만료된 경우 새로 계산
            error_log("Profile cache miss for user $userId, calculating fresh data...");
            $profileData = $this->calculateProfileDataOptimized($userId);

            if (!$profileData) {
                error_log("Profile data calculation failed for user $userId");
                return null;
            }

            // 캐시 저장
            $cacheResult = file_put_contents($cacheFile, json_encode($profileData));
            if ($cacheResult !== false) {
                error_log("Profile cache saved successfully for user $userId");
            } else {
                error_log("Profile cache save failed for user $userId");
            }

            return $profileData;

        } catch (Exception $e) {
            error_log("UserOptimized::getOptimizedProfileDataWithCache Exception for user $userId: " . $e->getMessage());
            error_log("Exception file: " . $e->getFile() . ":" . $e->getLine());
            throw $e; // 예외를 다시 던져서 상위에서 처리하도록 함
        }
    }
    
    /**
     * 최적화된 프로필 데이터 계산
     */
    private function calculateProfileDataOptimized($userId) {
        try {
            $startTime = microtime(true);
            error_log("Starting profile calculation for user $userId");

            // 1. 기본 사용자 정보 조회
            $sql = "SELECT * FROM users WHERE id = ? AND status = 'active'";
            $user = $this->db->fetch($sql, [$userId]);

            if (!$user) {
                error_log("User $userId not found or not active");
                return null;
            }
            error_log("User basic data retrieved for user $userId");

            // 2. 통계 정보를 개별 쿼리로 최적화
            try {
                $user['stats'] = $this->getOptimizedStats($userId);
                error_log("Stats retrieved for user $userId");
            } catch (Exception $e) {
                error_log("Stats retrieval failed for user $userId: " . $e->getMessage());
                // 통계 실패시 기본값 사용
                $user['stats'] = [
                    'post_count' => 0,
                    'comment_count' => 0,
                    'like_count' => 0,
                    'join_days' => 0
                ];
            }

            // 3. 최근 게시글 조회 - 인덱스 최적화
            try {
                $user['recent_posts'] = $this->getRecentPostsOptimized($userId);
                error_log("Recent posts retrieved for user $userId");
            } catch (Exception $e) {
                error_log("Recent posts retrieval failed for user $userId: " . $e->getMessage());
                $user['recent_posts'] = [];
            }

            // 4. 최근 댓글 조회 - 인덱스 최적화
            try {
                $user['recent_comments'] = $this->getRecentCommentsOptimized($userId);
                error_log("Recent comments retrieved for user $userId");
            } catch (Exception $e) {
                error_log("Recent comments retrieval failed for user $userId: " . $e->getMessage());
                $user['recent_comments'] = [];
            }

            // JSON 데이터 파싱
            if (!empty($user['social_links'])) {
                try {
                    $user['social_links'] = json_decode($user['social_links'], true);
                } catch (Exception $e) {
                    error_log("Social links JSON parsing failed for user $userId: " . $e->getMessage());
                    $user['social_links'] = null;
                }
            }

            $totalTime = (microtime(true) - $startTime) * 1000;
            error_log("Profile calculation completed for user $userId in {$totalTime}ms");

            return $user;

        } catch (Exception $e) {
            error_log("calculateProfileDataOptimized Exception for user $userId: " . $e->getMessage());
            error_log("Exception file: " . $e->getFile() . ":" . $e->getLine());
            throw $e;
        }
    }
    
    /**
     * 최적화된 통계 정보 조회 (캐시 테이블 사용)
     * v3.66.0: 캐시 TTL 10분 → 1시간 연장
     * v3.76.0: DB 캐시 TTL 1시간 → 6시간 연장 (일관성 유지)
     */
    private function getOptimizedStats($userId) {
        // 1. 캐시 테이블에서 먼저 조회 (6시간 TTL)
        $sql = "SELECT * FROM user_stats_cache WHERE user_id = ? AND last_updated > DATE_SUB(NOW(), INTERVAL 6 HOUR)";
        $cached = $this->db->fetch($sql, [$userId]);

        if ($cached) {
            error_log("✅ Stats cache HIT for user $userId");
            return [
                'post_count' => (int)$cached['post_count'],
                'comment_count' => (int)$cached['comment_count'],
                'like_count' => (int)$cached['like_count'],
                'join_days' => (int)$cached['join_days']
            ];
        }

        error_log("❌ Stats cache MISS for user $userId, recalculating...");
        
        // 2. 캐시 미스면 실시간 계산
        $stats = [];
        
        // 게시글 수 - 인덱스 사용
        $sql = "SELECT COUNT(*) as count FROM posts USE INDEX (idx_posts_user_created) 
                WHERE user_id = ? AND status = 'published'";
        $result = $this->db->fetch($sql, [$userId]);
        $stats['post_count'] = (int)($result['count'] ?? 0);
        
        // 댓글 수 - 커뮤니티 + 공지사항 댓글 통합 (v3.73.0)
        $sql = "SELECT
                    (SELECT COUNT(*) FROM comments USE INDEX (idx_comments_user_performance)
                     WHERE user_id = ? AND status = 'active') +
                    (SELECT COUNT(*) FROM notice_comments
                     WHERE user_id = ? AND status = 'active') as count";
        $result = $this->db->fetch($sql, [$userId, $userId]);
        $stats['comment_count'] = (int)($result['count'] ?? 0);
        
        // 좋아요 수 - 최적화된 계산 (LIMIT 추가)
        $sql = "SELECT SUM(like_count) as total_likes 
                FROM posts USE INDEX (idx_posts_user_created) 
                WHERE user_id = ? AND status = 'published' AND like_count > 0";
        $result = $this->db->fetch($sql, [$userId]);
        $stats['like_count'] = (int)($result['total_likes'] ?? 0);
        
        // 가입일 계산
        $sql = "SELECT DATEDIFF(NOW(), created_at) as join_days FROM users WHERE id = ?";
        $result = $this->db->fetch($sql, [$userId]);
        $stats['join_days'] = (int)($result['join_days'] ?? 0);
        
        // 3. 캐시 테이블에 저장
        $this->updateStatsCache($userId, $stats);
        
        return $stats;
    }
    
    /**
     * 통계 캐시 업데이트
     */
    private function updateStatsCache($userId, $stats) {
        $sql = "INSERT INTO user_stats_cache (user_id, post_count, comment_count, like_count, join_days) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    post_count = VALUES(post_count),
                    comment_count = VALUES(comment_count),
                    like_count = VALUES(like_count),
                    join_days = VALUES(join_days)";
        
        $this->db->execute($sql, [
            $userId,
            $stats['post_count'],
            $stats['comment_count'],
            $stats['like_count'],
            $stats['join_days']
        ]);
    }
    
    /**
     * 최적화된 최근 게시글 조회
     */
    private function getRecentPostsOptimized($userId) {
        $sql = "SELECT id, title, created_at, view_count, like_count, comment_count 
                FROM posts USE INDEX (idx_posts_user_created)
                WHERE user_id = ? AND status = 'published'
                ORDER BY created_at DESC 
                LIMIT 5";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * 최적화된 최근 댓글 조회 (커뮤니티 + 공지사항 댓글 통합)
     * v3.73.0: 공지사항 댓글 포함하도록 개선
     * v3.73.1: 서브쿼리 패턴으로 160배 성능 개선 (180ms → 1.1ms)
     */
    private function getRecentCommentsOptimized($userId) {
        // 서브쿼리로 먼저 인덱스 필터링 후 JOIN (대용량 데이터 최적화)
        $sql = "SELECT * FROM (
                    SELECT
                        c.id,
                        LEFT(c.content, 100) as content,
                        c.created_at,
                        p.title as post_title,
                        c.post_id,
                        c.parent_id,
                        'community' as comment_type
                    FROM (
                        SELECT id, content, created_at, post_id, parent_id
                        FROM comments FORCE INDEX (idx_comments_user_performance)
                        WHERE user_id = ? AND status = 'active'
                        ORDER BY created_at DESC
                        LIMIT 10
                    ) c
                    JOIN posts p ON c.post_id = p.id

                    UNION ALL

                    SELECT
                        nc.id,
                        LEFT(nc.content, 100) as content,
                        nc.created_at,
                        n.title as post_title,
                        nc.notice_id as post_id,
                        nc.parent_id,
                        'notice' as comment_type
                    FROM (
                        SELECT id, content, created_at, notice_id, parent_id
                        FROM notice_comments
                        WHERE user_id = ? AND status = 'active'
                        ORDER BY created_at DESC
                        LIMIT 10
                    ) nc
                    JOIN notices n ON nc.notice_id = n.id
                ) combined
                ORDER BY created_at DESC
                LIMIT 5";

        return $this->db->fetchAll($sql, [$userId, $userId]);
    }
    
    /**
     * 프로필 캐시 무효화
     */
    public function invalidateProfileCache($userId) {
        $cacheFile = ROOT_PATH . '/cache/profile_' . $userId . '.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
            error_log("Profile cache invalidated for user $userId");
        }
    }
    
    /**
     * 비동기 통계 업데이트 (백그라운드 작업용)
     */
    public function updateStatsAsync($userId) {
        // 별도의 프로세스로 통계 업데이트
        $command = "php " . ROOT_PATH . "/scripts/update_profile_stats.php $userId > /dev/null 2>&1 &";
        exec($command);
    }
}
?>