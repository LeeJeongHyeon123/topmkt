<?php
/**
 * 공지사항 모델 클래스
 * 기존 Post 모델 패턴을 준수하여 개발
 */

require_once SRC_PATH . '/helpers/CacheHelper.php';
require_once SRC_PATH . '/helpers/PerformanceDebugger.php';
require_once SRC_PATH . '/helpers/WebLogger.php';

class Notice {
    private $db;
    
    /**
     * 생성자
     */
    public function __construct() {
        require_once SRC_PATH . '/config/database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * 공지사항 목록 조회
     *
     * @param int $page 페이지 번호
     * @param int $pageSize 페이지당 항목 수
     * @param string|null $search 검색어
     * @param string $filter 검색 필터 (all, title, content, company)
     * @param string|null $companyName 특정 기업명 필터링
     * @return array 공지사항 목록
     */
    public function getList($page = 1, $pageSize = 20, $search = null, $filter = 'all', $companyName = null) {
        // 큰 페이지는 최적화된 방식 사용
        if ($page > 500) {
            return $this->getListOptimized($page, $pageSize, $search, $companyName);
        }
        
        // 작은 페이지는 기존 방식 사용
        return $this->getListWithOffset($page, $pageSize, $search, $filter, $companyName);
    }
    
    /**
     * 커서 기반 공지사항 목록 조회 (큰 페이지 최적화)
     */
    public function getListOptimized($page = 1, $pageSize = 20, $search = null, $companyName = null) {
        // 캐시 키 생성
        $cacheKey = $this->getListCacheKey($page, $pageSize, $search, $companyName) . '_optimized';
        
        return CacheHelper::remember($cacheKey, function() use ($page, $pageSize, $search, $companyName) {
            $params = [];
            
            if ($page <= 500) {
                // 첫 500페이지는 기존 OFFSET 방식
                return $this->getListWithOffset($page, $pageSize, $search, 'all', $companyName);
            }
            
            // 큰 페이지는 커서 방식 사용
            $skipCount = ($page - 1) * $pageSize;
            
            $timeResult = $this->db->fetch("
                SELECT created_at 
                FROM notices 
                WHERE status = 'published'
                ORDER BY created_at DESC 
                LIMIT 1 OFFSET ?
            ", [$skipCount]);
            $startTime = $timeResult ? $timeResult['created_at'] : null;
            
            if (!$startTime) {
                return []; // 해당 페이지에 데이터 없음
            }
            
            // 커서 기반으로 데이터 조회
            $sql = "
                SELECT 
                    n.id,
                    n.user_id,
                    n.company_id,
                    n.title,
                    LEFT(n.content, 200) as content_preview,
                    n.view_count,
                    n.like_count,
                    n.comment_count,
                    n.is_featured,
                    n.status,
                    n.created_at,
                    u.nickname as author_name,
                    COALESCE(u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image,
                    cp.company_name,
                    cp.representative_name
                FROM notices n
                LEFT JOIN users u ON n.user_id = u.id
                JOIN company_profiles cp ON n.company_id = cp.id
                WHERE n.status = 'published'
                AND n.created_at <= :start_time
            ";
            
            $params[':start_time'] = $startTime;
            
            if ($search) {
                $sql .= " AND MATCH(n.title, n.content) AGAINST(:search IN NATURAL LANGUAGE MODE)";
                $params[':search'] = $search;
            }
            
            if ($companyName) {
                $sql .= " AND cp.company_name LIKE :company_name";
                $params[':company_name'] = "%$companyName%";
            }
            
            $sql .= " ORDER BY n.created_at DESC LIMIT :limit";
            
            $executeParams = array_values($params);
            $executeParams[] = $pageSize;
            
            return $this->db->fetchAll($sql, $executeParams);
        }, $page > 1000 ? 1800 : 300); // 큰 페이지는 30분, 작은 페이지는 5분 캐시
    }
    
    /**
     * OFFSET 방식 공지사항 목록 조회
     */
    private function getListWithOffset($page, $pageSize, $search, $filter = 'all', $companyName = null) {
        $offset = ($page - 1) * $pageSize;
        
        PerformanceDebugger::startTimer('notice_list_query');
        
        if ($search) {
            WebLogger::info("🔍 [NOTICE_SEARCH] 검색 시작: '$search' (필터: $filter), 페이지: $page, 오프셋: $offset");
            $searchStartTime = microtime(true);
            
            // 필터에 따른 검색 조건 생성
            $whereCondition = '';
            $params = [];
            
            switch ($filter) {
                case 'title':
                    $whereCondition = 'n.title LIKE ?';
                    $params = ["%$search%"];
                    break;
                case 'content':
                    $whereCondition = 'n.content LIKE ?';
                    $params = ["%$search%"];
                    break;
                case 'company':
                    $whereCondition = 'cp.company_name LIKE ?';
                    $params = ["%$search%"];
                    break;
                case 'all':
                default:
                    $whereCondition = '(n.title LIKE ? OR n.content LIKE ? OR cp.company_name LIKE ?)';
                    $params = ["%$search%", "%$search%", "%$search%"];
                    break;
            }
            
            // 최근 500개 공지사항에서 필터별 검색
            $sql = "
                SELECT 
                    n.id,
                    n.user_id,
                    n.company_id,
                    n.title,
                    LEFT(n.content, 200) as content_preview,
                    n.view_count,
                    n.like_count,
                    n.comment_count,
                    n.is_featured,
                    n.status,
                    n.created_at,
                    u.nickname as author_name,
                    COALESCE(u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image,
                    cp.company_name,
                    cp.representative_name,
                    CASE 
                        WHEN n.title LIKE ? THEN 3
                        WHEN cp.company_name LIKE ? THEN 2
                        ELSE 1
                    END as relevance_score
                FROM (
                    SELECT * FROM notices 
                    WHERE status = 'published' 
                    ORDER BY created_at DESC 
                    LIMIT 500
                ) n
                LEFT JOIN users u ON n.user_id = u.id
                JOIN company_profiles cp ON n.company_id = cp.id
                WHERE $whereCondition
            ";
            
            // 기업명 필터 추가
            if ($companyName) {
                $sql .= " AND cp.company_name LIKE ?";
                $params[] = "%$companyName%";
            }
            
            $sql .= " ORDER BY relevance_score DESC, n.created_at DESC LIMIT ? OFFSET ?";
            
            // 관련도 점수용 파라미터 + 검색 조건 파라미터 + LIMIT/OFFSET
            $executeParams = ["%$search%", "%$search%"]; // 관련도 점수용
            $executeParams = array_merge($executeParams, $params); // 검색 조건
            $executeParams[] = $pageSize;
            $executeParams[] = $offset;
            
            $result = $this->db->fetchAll($sql, $executeParams);
            
            $totalSearchTime = (microtime(true) - $searchStartTime) * 1000;
            WebLogger::info("🔍 [NOTICE_SEARCH] 검색 완료: " . count($result) . "개 결과, " . round($totalSearchTime, 2) . "ms");
        } else {
            // 일반 목록 조회
            $sql = "
                SELECT 
                    n.id,
                    n.user_id,
                    n.company_id,
                    n.title,
                    LEFT(n.content, 200) as content_preview,
                    n.view_count,
                    n.like_count,
                    n.comment_count,
                    n.is_featured,
                    n.status,
                    n.created_at,
                    u.nickname as author_name,
                    COALESCE(u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image,
                    cp.company_name,
                    cp.representative_name
                FROM notices n
                FORCE INDEX (idx_notices_list_performance)
                LEFT JOIN users u ON n.user_id = u.id
                JOIN company_profiles cp ON n.company_id = cp.id
                WHERE n.status = 'published'
            ";
            
            $params = [];
            
            // 기업명 필터 추가
            if ($companyName) {
                $sql .= " AND cp.company_name LIKE ?";
                $params[] = "%$companyName%";
            }
            
            $sql .= " ORDER BY n.created_at DESC LIMIT ? OFFSET ?";
            
            $params[] = $pageSize;
            $params[] = $offset;
            
            $result = PerformanceDebugger::executeQuery($this->db, $sql, $params);
        }
        
        $timerResult = PerformanceDebugger::endTimer('notice_list_query');
        error_log("📊 공지사항 목록 조회 성능: " . json_encode($timerResult, JSON_UNESCAPED_UNICODE));
        
        return $result;
    }
    
    /**
     * 공지사항 총 개수 조회
     */
    public function getTotalCount($search = null, $filter = 'all', $companyName = null) {
        // 캐시 키 생성
        $cacheKey = $this->getCountCacheKey($search . '_' . $filter . '_' . ($companyName ?? 'all'));
        
        return CacheHelper::remember($cacheKey, function() use ($search, $filter, $companyName) {
            if ($search) {
                WebLogger::info("📊 [NOTICE_COUNT] 검색 카운트 시작: '$search' (필터: $filter)");
                
                // 필터에 따른 카운트 조건 생성
                $whereCondition = '';
                $params = [];
                
                switch ($filter) {
                    case 'title':
                        $whereCondition = 'n.title LIKE ?';
                        $params = ["%$search%"];
                        break;
                    case 'content':
                        $whereCondition = 'n.content LIKE ?';
                        $params = ["%$search%"];
                        break;
                    case 'company':
                        $whereCondition = 'cp.company_name LIKE ?';
                        $params = ["%$search%"];
                        break;
                    case 'all':
                    default:
                        $whereCondition = '(n.title LIKE ? OR n.content LIKE ? OR cp.company_name LIKE ?)';
                        $params = ["%$search%", "%$search%", "%$search%"];
                        break;
                }
                
                // 최근 500개에서 필터별 검색 카운트
                $sql = "
                    SELECT COUNT(*) FROM (
                        SELECT n.id, n.title, n.content, n.company_id FROM notices n
                        WHERE n.status = 'published' 
                        ORDER BY n.created_at DESC 
                        LIMIT 500
                    ) n
                    JOIN company_profiles cp ON n.company_id = cp.id
                    WHERE $whereCondition
                ";
                
                // 기업명 필터 추가
                if ($companyName) {
                    $sql .= " AND cp.company_name LIKE ?";
                    $params[] = "%$companyName%";
                }
                
                $result = $this->db->fetch($sql, $params);
                $count = $result ? array_values($result)[0] : 0;
                
                WebLogger::info("📊 [NOTICE_COUNT] 검색 카운트 완료: {$count}개");
                return $count;
            } else {
                // 일반 카운트 (인덱스 활용)
                if ($companyName) {
                    // 기업명 필터가 있을 때는 JOIN 필요
                    $sql = "SELECT COUNT(*) as count FROM notices n
                            JOIN company_profiles cp ON n.company_id = cp.id
                            WHERE n.status = 'published' AND cp.company_name LIKE ?";
                    $params = ["%$companyName%"];
                } else {
                    // 기업명 필터가 없을 때는 단순 카운트
                    $sql = "SELECT COUNT(*) as count FROM notices 
                            FORCE INDEX (idx_notices_list_performance) 
                            WHERE status = 'published'";
                    $params = [];
                }
                
                $result = $this->db->fetch($sql, $params);
                return $result ? $result['count'] : 0;
            }
        }, 600); // 10분 캐시
    }
    
    /**
     * 공지사항 상세 조회
     */
    public function getById($id) {
        $sql = "
            SELECT n.*, 
                   u.nickname as author_name,
                   COALESCE(u.profile_image_thumb, u.profile_image_profile, '/assets/images/default-avatar.png') as profile_image,
                   cp.company_name,
                   cp.representative_name,
                   cp.business_number
            FROM notices n
            JOIN users u ON n.user_id = u.id
            JOIN company_profiles cp ON n.company_id = cp.id
            WHERE n.id = ? AND n.status != 'deleted'
        ";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * 공지사항 생성
     */
    public function create($data) {
        $sql = "
            INSERT INTO notices (user_id, company_id, title, content, image_path, is_featured, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";
        
        $this->db->execute($sql, [
            $data['user_id'],
            $data['company_id'],
            $data['title'],
            $data['content'],
            $data['image_path'] ?? null,
            ($data['is_featured'] ?? false) ? 1 : 0
        ]);
        
        // 새 공지사항 추가 시 관련 캐시 무효화
        $this->clearListCaches();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * 공지사항 수정
     */
    public function update($id, $data) {
        $sql = "
            UPDATE notices 
            SET title = ?, content = ?, image_path = ?, is_featured = ?, updated_at = NOW()
            WHERE id = ? AND status != 'deleted'
        ";
        
        $result = $this->db->execute($sql, [
            $data['title'],
            $data['content'],
            $data['image_path'] ?? null,
            ($data['is_featured'] ?? false) ? 1 : 0,
            $id
        ]);
        
        // 공지사항 수정 시 관련 캐시 무효화
        if ($result) {
            $this->clearListCaches();
        }
        
        return $result > 0;
    }
    
    /**
     * 공지사항 삭제
     */
    public function delete($id) {
        $sql = "UPDATE notices SET status = 'deleted', updated_at = NOW() WHERE id = ?";
        $result = $this->db->execute($sql, [$id]);
        
        // 공지사항 삭제 시 관련 캐시 무효화
        if ($result) {
            $this->clearListCaches();
        }
        
        return $result > 0;
    }
    
    /**
     * 조회수 증가
     */
    public function incrementViewCount($id) {
        $sql = "UPDATE notices SET view_count = view_count + 1 WHERE id = ? AND status = 'published'";
        return $this->db->execute($sql, [$id]) > 0;
    }
    
    /**
     * 사용자별 공지사항 목록 조회
     */
    public function getByUserId($userId, $limit = 5) {
        $sql = "
            SELECT n.*, cp.company_name
            FROM notices n
            JOIN company_profiles cp ON n.company_id = cp.id
            WHERE n.user_id = ? AND n.status != 'deleted'
            ORDER BY n.created_at DESC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
    
    /**
     * 기업별 공지사항 목록 조회
     */
    public function getByCompanyId($companyId, $limit = 10) {
        $sql = "
            SELECT n.*, 
                   u.nickname as author_name,
                   cp.company_name
            FROM notices n
            JOIN users u ON n.user_id = u.id
            JOIN company_profiles cp ON n.company_id = cp.id
            WHERE n.company_id = ? AND n.status = 'published'
            ORDER BY n.created_at DESC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$companyId, $limit]);
    }
    
    /**
     * 추천 공지사항 목록 조회
     */
    public function getFeaturedNotices($limit = 5) {
        $sql = "
            SELECT n.*, 
                   u.nickname as author_name,
                   cp.company_name
            FROM notices n
            JOIN users u ON n.user_id = u.id
            JOIN company_profiles cp ON n.company_id = cp.id
            WHERE n.status = 'published' AND n.is_featured = true
            ORDER BY n.created_at DESC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * 기업 목록 조회 (필터링용)
     */
    public function getCompaniesWithNotices() {
        $sql = "
            SELECT DISTINCT cp.id, cp.company_name, COUNT(n.id) as notice_count
            FROM company_profiles cp
            JOIN notices n ON cp.id = n.company_id
            WHERE n.status = 'published'
            GROUP BY cp.id, cp.company_name
            ORDER BY notice_count DESC, cp.company_name ASC
        ";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * 공지사항 목록 관련 캐시 무효화
     */
    private function clearListCaches() {
        $cacheDir = '/tmp/topmkt_cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*.cache');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($content && (strpos($content, 'notices_list_') !== false || strpos($content, 'notices_count_') !== false)) {
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * 목록 캐시 키 생성
     */
    private function getListCacheKey($page, $pageSize, $search, $companyId) {
        return 'notices_list_' . md5($page . '_' . $pageSize . '_' . ($search ?? '') . '_' . ($companyId ?? 'all'));
    }
    
    /**
     * 카운트 캐시 키 생성
     */
    private function getCountCacheKey($params) {
        return 'notices_count_' . md5($params);
    }
    
    /**
     * 사용자가 공지사항 소유자인지 확인
     */
    public function isOwner($noticeId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM notices WHERE id = ? AND user_id = ? AND status != 'deleted'";
        $result = $this->db->fetch($sql, [$noticeId, $userId]);
        return $result && $result['count'] > 0;
    }
    
    /**
     * 기업 인증 사용자인지 확인
     * ROLE_CORP 또는 ROLE_ADMIN이면서 승인된 기업 프로필이 있는 경우
     */
    public function isCompanyUser($userId) {
        $sql = "
            SELECT COUNT(*) as count 
            FROM users u 
            JOIN company_profiles cp ON u.id = cp.user_id 
            WHERE u.id = ? 
            AND (u.role = 'ROLE_CORP' OR u.role = 'ROLE_ADMIN') 
            AND u.corp_status = 'approved' 
            AND cp.status = 'approved'
        ";
        $result = $this->db->fetch($sql, [$userId]);
        return $result && $result['count'] > 0;
    }
    
    /**
     * 사용자의 기업 ID 조회
     */
    public function getUserCompanyId($userId) {
        $sql = "
            SELECT cp.id 
            FROM company_profiles cp 
            WHERE cp.user_id = ? AND cp.status = 'approved'
        ";
        $result = $this->db->fetch($sql, [$userId]);
        return $result ? $result['id'] : null;
    }
} 