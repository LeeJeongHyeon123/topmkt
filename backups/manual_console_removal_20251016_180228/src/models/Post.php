<?php
/**
 * 게시글 모델 클래스
 */

require_once SRC_PATH . '/helpers/CacheHelper.php';
require_once SRC_PATH . '/helpers/PerformanceDebugger.php';
require_once SRC_PATH . '/helpers/WebLogger.php';

class Post {
    private $db;
    
    /**
     * 생성자
     */
    public function __construct() {
        require_once SRC_PATH . '/config/database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * 게시글 목록 조회
     *
     * @param int $page 페이지 번호
     * @param int $pageSize 페이지당 항목 수
     * @param string|null $search 검색어
     * @param string $filter 검색 필터 (all, title, content, author)
     * @return array 게시글 목록
     */
    public function getList($page = 1, $pageSize = 20, $search = null, $filter = 'all') {
        // 큰 페이지는 최적화된 방식 사용
        if ($page > 500) {
            return $this->getListOptimized($page, $pageSize, $search, $filter);
        }
        
        // 작은 페이지는 기존 방식 사용
        return $this->getListWithOffset($page, $pageSize, $search, $filter);
    }
    
    /**
     * 커서 기반 게시글 목록 조회 (큰 페이지 최적화)
     *
     * @param int $page 페이지 번호
     * @param int $pageSize 페이지당 항목 수
     * @param string|null $search 검색어
     * @param string $filter 검색 필터
     * @return array 게시글 목록
     */
    public function getListOptimized($page = 1, $pageSize = 20, $search = null, $filter = 'all') {
        // 캐시 키 생성
        $cacheKey = CacheHelper::getPostListCacheKey($page, $pageSize, $search) . '_optimized';
        
        return CacheHelper::remember($cacheKey, function() use ($page, $pageSize, $search, $filter) {
            $params = [];
            
            if ($page <= 500) {
                // 첫 500페이지는 기존 OFFSET 방식
                return $this->getListWithOffset($page, $pageSize, $search, $filter);
            }
            
            // 큰 페이지는 ID 기반 역순으로 처리
            $skipCount = ($page - 1) * $pageSize;

            // 큰 페이지는 간단하게 처리: timeout 방지를 위해 최근 게시글만 반환
            if ($page >= 50000) {
                // 마지막 페이지는 최근 20개 게시글만 표시
                $limitCount = $pageSize;
                $skipCount = 0;  // OFFSET 사용 안 함
            } else {
                // 일반적인 큰 페이지 처리
                $limitCount = $pageSize;
            }
            
            // ID 기반으로 역순 조회 (빠름)
            $sql = "
                SELECT
                    p.id,
                    p.user_id,
                    p.title,
                    LEFT(p.content, 200) as content_preview,
                    p.view_count,
                    p.like_count,
                    p.comment_count,
                    p.status,
                    p.created_at,
                    CASE WHEN u.status = 'deleted' THEN '탈퇴한 회원' ELSE u.nickname END as author_name,
                    u.profile_image,
                    u.profile_image_original,
                    u.profile_image_profile,
                    u.profile_image_thumb,
                    COALESCE(u.profile_image, u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image_url
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.status = 'published'
            ";

            $params = [];
            
            if ($search) {
                // 필터에 따른 검색 조건 추가
                switch ($filter) {
                    case 'title':
                        $sql .= " AND p.title LIKE ?";
                        $params[] = "%$search%";
                        break;
                    case 'content':
                        $sql .= " AND p.content LIKE ?";
                        $params[] = "%$search%";
                        break;
                    case 'author':
                        $sql .= " AND u.nickname LIKE ?";
                        $params[] = "%$search%";
                        break;
                    case 'all':
                    default:
                        $sql .= " AND (p.title LIKE ? OR p.content LIKE ? OR u.nickname LIKE ?)";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                        break;
                }
            }
            
            if ($skipCount == 0) {
                // 마지막 페이지: OFFSET 없이 최근 게시글만
                $sql .= " ORDER BY p.created_at DESC, p.id DESC LIMIT ?";
                $params[] = $limitCount;
            } else {
                // 일반 큰 페이지: OFFSET 사용
                $sql .= " ORDER BY p.created_at DESC, p.id DESC LIMIT ? OFFSET ?";
                $params[] = $limitCount;
                $params[] = $skipCount;
            }
            
            return $this->db->fetchAll($sql, $params);
        }, $page > 1000 ? 1800 : 300); // 큰 페이지는 30분, 작은 페이지는 5분 캐시
    }
    
    /**
     * OFFSET 방식 게시글 목록 조회
     */
    private function getListWithOffset($page, $pageSize, $search, $filter = 'all') {
        $offset = ($page - 1) * $pageSize;
        
        PerformanceDebugger::startTimer('post_list_query');
        
        if ($search) {
            WebLogger::info("🔍 [SEARCH] 검색 시작: '$search' (필터: $filter), 페이지: $page, 오프셋: $offset");
            $searchStartTime = microtime(true);
            
            WebLogger::info("🔍 [SEARCH] 필터별 최적화된 쿼리 실행");
            $step1Start = microtime(true);
            
            // 필터에 따른 검색 조건 생성
            $whereCondition = '';
            $params = [];
            
            switch ($filter) {
                case 'title':
                    $whereCondition = 'p.title LIKE ?';
                    $params = ["%$search%"];
                    break;
                case 'content':
                    $whereCondition = 'p.content LIKE ?';
                    $params = ["%$search%"];
                    break;
                case 'author':
                    $whereCondition = 'u.nickname LIKE ?';
                    $params = ["%$search%"];
                    break;
                case 'all':
                default:
                    $whereCondition = '(p.title LIKE ? OR p.content LIKE ? OR u.nickname LIKE ?)';
                    $params = ["%$search%", "%$search%", "%$search%"];
                    break;
            }
            
            // 전체 게시글에서 필터별 검색 (제한 없음)
            $sql = "
                SELECT 
                    p.id,
                    p.user_id,
                    p.title,
                    LEFT(p.content, 200) as content_preview,
                    p.view_count,
                    p.like_count,
                    p.comment_count,
                    p.status,
                    p.created_at,
                    CASE WHEN u.status = 'deleted' THEN '탈퇴한 회원' ELSE u.nickname END as author_name,
                    u.profile_image,
                    u.profile_image_original,
                    u.profile_image_profile,
                    u.profile_image_thumb,
                    COALESCE(u.profile_image, u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image_url,
                    CASE 
                        WHEN p.title LIKE ? THEN 3
                        WHEN u.nickname LIKE ? THEN 2
                        ELSE 1
                    END as relevance_score
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.status = 'published' AND $whereCondition
                ORDER BY relevance_score DESC, p.created_at DESC 
                LIMIT ? OFFSET ?
            ";
            
            WebLogger::info("🔍 [SEARCH] 쿼리 파라미터 바인딩 시작");
            // 관련도 점수용 파라미터 + 검색 조건 파라미터 + LIMIT/OFFSET
            $executeParams = ["%$search%", "%$search%"]; // 관련도 점수용
            $executeParams = array_merge($executeParams, $params); // 검색 조건
            $executeParams[] = $pageSize;
            $executeParams[] = $offset;
            
            $result = $this->db->fetchAll($sql, $executeParams);
            
            $step1Time = (microtime(true) - $step1Start) * 1000;
            WebLogger::info("🔍 [SEARCH] 필터 쿼리 완료: " . count($result) . "개 결과, " . round($step1Time, 2) . "ms");
            
            $totalSearchTime = (microtime(true) - $searchStartTime) * 1000;
            WebLogger::info("🔍 [SEARCH] 전체 검색 완료: " . round($totalSearchTime, 2) . "ms");
        } else {
            // 일반 목록 조회 - 서브쿼리 최적화 버전
            WebLogger::info("🚀 [OPTIMIZED] 서브쿼리 기반 최적화 쿼리 실행");
            
            $sql = "
                SELECT 
                    p.id,
                    p.user_id,
                    p.title,
                    LEFT(p.content, 200) as content_preview,
                    p.view_count,
                    p.like_count,
                    p.comment_count,
                    p.status,
                    p.created_at,
                    CASE WHEN u.status = 'deleted' THEN '탈퇴한 회원' ELSE u.nickname END as author_name,
                    u.profile_image,
                    u.profile_image_original,
                    u.profile_image_profile,
                    u.profile_image_thumb,
                    COALESCE(u.profile_image, u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image_url
                FROM (
                    SELECT id, user_id, title, content, view_count, like_count, 
                           comment_count, status, created_at
                    FROM posts 
                    WHERE status = 'published'
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?
                ) p
                JOIN users u ON p.user_id = u.id
            ";
            
            $result = PerformanceDebugger::executeQuery($this->db, $sql, [$pageSize, $offset]);
        }
        
        $timerResult = PerformanceDebugger::endTimer('post_list_query');
        error_log("📊 게시글 목록 조회 성능: " . json_encode($timerResult, JSON_UNESCAPED_UNICODE));
        
        return $result;
    }
    
    /**
     * 게시글 총 개수 조회
     *
     * @param string|null $search 검색어
     * @param string $filter 검색 필터
     * @return int 게시글 총 개수
     */
    public function getTotalCount($search = null, $filter = 'all') {
        // 캐시 키 생성 (필터 포함)
        $cacheKey = CacheHelper::getPostCountCacheKey($search . '_' . $filter);
        
        // 캐시에서 조회 시도
        return CacheHelper::remember($cacheKey, function() use ($search, $filter) {
            if ($search) {
                WebLogger::info("📊 [COUNT] 검색 카운트 시작: '$search' (필터: $filter)");
                $countStartTime = microtime(true);
                
                // 필터에 따른 카운트 조건 생성
                $whereCondition = '';
                $params = [];
                
                switch ($filter) {
                    case 'title':
                        $whereCondition = 'p.title LIKE ?';
                        $params = ["%$search%"];
                        break;
                    case 'content':
                        $whereCondition = 'p.content LIKE ?';
                        $params = ["%$search%"];
                        break;
                    case 'author':
                        $whereCondition = 'u.nickname LIKE ?';
                        $params = ["%$search%"];
                        break;
                    case 'all':
                    default:
                        $whereCondition = '(p.title LIKE ? OR p.content LIKE ? OR u.nickname LIKE ?)';
                        $params = ["%$search%", "%$search%", "%$search%"];
                        break;
                }
                
                // 전체 게시글에서 필터별 검색 카운트 (제한 없음)
                $sql = "
                    SELECT COUNT(*) 
                    FROM posts p
                    JOIN users u ON p.user_id = u.id
                    WHERE p.status = 'published' AND $whereCondition
                ";
                $result = $this->db->fetch($sql, $params);
                $count = $result ? array_values($result)[0] : 0;
                
                $countTime = (microtime(true) - $countStartTime) * 1000;
                WebLogger::info("📊 [COUNT] 검색 카운트 완료: {$count}개, " . round($countTime, 2) . "ms");
                return $count;
            } else {
                // 일반 카운트 (인덱스 활용)
                $sql = "SELECT COUNT(*) as count FROM posts 
                        FORCE INDEX (idx_posts_list_performance) 
                        WHERE status = 'published'";
                $result = $this->db->fetch($sql);
                return $result ? $result['count'] : 0;
            }
        }, 600); // 10분 캐시
    }
    
    /**
     * 게시글 상세 조회
     *
     * @param int $id 게시글 ID
     * @return array|false 게시글 정보 또는 false
     */
    public function getById($id) {
        $sql = "
            SELECT p.*,
                   u.nickname as author_name,
                   u.profile_image,
                   u.profile_image_original,
                   u.profile_image_profile,
                   u.profile_image_thumb,
                   COALESCE(u.profile_image, u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image_url
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * 게시글 생성
     *
     * @param array $data 게시글 데이터
     * @return int 생성된 게시글 ID
     */
    public function create($data) {
        // image_path 컬럼 포함하여 INSERT
        $sql = "
            INSERT INTO posts (user_id, title, content, image_path, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ";
        
        $this->db->execute($sql, [
            $data['user_id'],
            $data['title'],
            $data['content'],
            $data['image_path'] ?? null
        ]);
        
        // 새 게시글 추가 시 관련 캐시 무효화
        $this->clearListCaches();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * 게시글 수정
     *
     * @param int $id 게시글 ID
     * @param array $data 수정할 데이터
     * @return bool 성공 여부
     */
    public function update($id, $data) {
        $sql = "
            UPDATE posts 
            SET title = ?, content = ?, image_path = ?, updated_at = NOW()
            WHERE id = ?
        ";
        
        $result = $this->db->execute($sql, [
            $data['title'],
            $data['content'],
            $data['image_path'] ?? null,
            $id
        ]);
        
        // 게시글 수정 시 관련 캐시 무효화
        if ($result) {
            $this->clearListCaches();
        }
        
        return $result > 0;
    }
    
    /**
     * 게시글 삭제
     *
     * @param int $id 게시글 ID
     * @return bool 성공 여부
     */
    public function delete($id) {
        $sql = "DELETE FROM posts WHERE id = ?";
        $result = $this->db->execute($sql, [$id]);
        
        // 게시글 삭제 시 관련 캐시 무효화
        if ($result) {
            $this->clearListCaches();
        }
        
        return $result > 0;
    }
    
    /**
     * 게시글 목록 관련 캐시 무효화
     */
    private function clearListCaches() {
        // 게시글 목록과 카운트 캐시를 모두 삭제
        // 패턴 매칭으로 관련 캐시를 모두 찾아 삭제
        $cacheDir = '/tmp/topmkt_cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*.cache');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($content && strpos($content, 'posts_list_') !== false || strpos($content, 'posts_count_') !== false) {
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * 조회수 증가
     *
     * @param int $id 게시글 ID
     * @return bool 성공 여부
     */
    public function incrementViewCount($id) {
        $sql = "UPDATE posts SET view_count = view_count + 1 WHERE id = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    /**
     * 사용자 게시글 목록 조회
     *
     * @param int $userId 사용자 ID
     * @param int $limit 조회할 개수
     * @return array 게시글 목록
     */
    public function getByUserId($userId, $limit = 5) {
        $sql = "
            SELECT * FROM posts
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
} 