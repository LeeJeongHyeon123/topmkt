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
        $cacheKey = "profile_data_" . $userId;
        $cacheFile = ROOT_PATH . '/cache/profile_' . $userId . '.json';
        
        // 캐시 디렉토리가 없으면 생성
        if (!is_dir(ROOT_PATH . '/cache')) {
            mkdir(ROOT_PATH . '/cache', 0755, true);
        }
        
        // 캐시 파일이 있고 10분 미만이면 사용
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 600) {
            $cachedData = json_decode(file_get_contents($cacheFile), true);
            if ($cachedData) {
                error_log("Profile cache hit for user $userId");
                return $cachedData;
            }
        }
        
        // 캐시 미스 또는 만료된 경우 새로 계산
        $profileData = $this->calculateProfileDataOptimized($userId);
        
        // 캐시 저장
        file_put_contents($cacheFile, json_encode($profileData));
        error_log("Profile cache saved for user $userId");
        
        return $profileData;
    }
    
    /**
     * 최적화된 프로필 데이터 계산
     */
    private function calculateProfileDataOptimized($userId) {
        $startTime = microtime(true);
        
        // 1. 기본 사용자 정보 조회
        $sql = "SELECT * FROM users WHERE id = ? AND status = 'active'";
        $user = $this->db->fetch($sql, [$userId]);
        
        if (!$user) {
            return null;
        }
        
        // 2. 통계 정보를 개별 쿼리로 최적화
        $user['stats'] = $this->getOptimizedStats($userId);
        
        // 3. 최근 게시글 조회 - 인덱스 최적화
        $user['recent_posts'] = $this->getRecentPostsOptimized($userId);
        
        // 4. 최근 댓글 조회 - 인덱스 최적화
        $user['recent_comments'] = $this->getRecentCommentsOptimized($userId);
        
        // JSON 데이터 파싱
        if ($user['social_links']) {
            $user['social_links'] = json_decode($user['social_links'], true);
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        error_log("Profile calculation time: {$totalTime}ms for user $userId");
        
        return $user;
    }
    
    /**
     * 최적화된 통계 정보 조회 (캐시 테이블 사용)
     */
    private function getOptimizedStats($userId) {
        // 1. 캐시 테이블에서 먼저 조회
        $sql = "SELECT * FROM user_stats_cache WHERE user_id = ? AND last_updated > DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
        $cached = $this->db->fetch($sql, [$userId]);
        
        if ($cached) {
            return [
                'post_count' => (int)$cached['post_count'],
                'comment_count' => (int)$cached['comment_count'],
                'like_count' => (int)$cached['like_count'],
                'join_days' => (int)$cached['join_days']
            ];
        }
        
        // 2. 캐시 미스면 실시간 계산
        $stats = [];
        
        // 게시글 수 - 인덱스 사용
        $sql = "SELECT COUNT(*) as count FROM posts USE INDEX (idx_posts_user_created) 
                WHERE user_id = ? AND status = 'published'";
        $result = $this->db->fetch($sql, [$userId]);
        $stats['post_count'] = (int)($result['count'] ?? 0);
        
        // 댓글 수 - 인덱스 사용
        $sql = "SELECT COUNT(*) as count FROM comments USE INDEX (idx_comments_user_performance) 
                WHERE user_id = ? AND status = 'active'";
        $result = $this->db->fetch($sql, [$userId]);
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
     * 최적화된 최근 댓글 조회
     */
    private function getRecentCommentsOptimized($userId) {
        $sql = "SELECT c.id, LEFT(c.content, 100) as content, c.created_at, 
                      p.title as post_title, c.post_id
                FROM comments c USE INDEX (idx_comments_user_performance)
                JOIN posts p ON c.post_id = p.id
                WHERE c.user_id = ? AND c.status = 'active'
                ORDER BY c.created_at DESC 
                LIMIT 5";
        
        return $this->db->fetchAll($sql, [$userId]);
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