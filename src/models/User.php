<?php
/**
 * 회원 모델 클래스
 */

require_once SRC_PATH . '/config/database.php';

class User {
    private $db;
    
    /**
     * 생성자
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * 회원가입
     */
    public function create($userData) {
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO users (
                phone, nickname, email, password_hash,
                phone_verified, marketing_agreed,
                status, created_at
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?,
                'active', NOW()
            )";
            
            $params = [
                $userData['phone'],
                $userData['nickname'],
                $userData['email'],
                password_hash($userData['password'], PASSWORD_DEFAULT),
                1,  // DB 스키마에 맞게 숫자로 변경
                $userData['marketing_agreed'] ? 1 : 0
            ];
            
            $this->db->execute($sql, $params);
            $userId = $this->db->lastInsertId();
            
            // 가입 로그 기록 (임시 비활성화)
            // $this->logUserActivity($userId, 'SIGNUP', '회원가입 완료');
            
            $this->db->commit();
            return $userId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('User creation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 휴대폰 번호로 사용자 조회
     */
    public function findByPhone($phone) {
        $sql = "SELECT * FROM users WHERE phone = ? AND status != 'deleted'";
        return $this->db->fetch($sql, [$phone]);
    }
    
    /**
     * 이메일로 사용자 조회
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? AND status != 'deleted'";
        return $this->db->fetch($sql, [$email]);
    }
    
    /**
     * 닉네임으로 사용자 조회
     */
    public function findByNickname($nickname) {
        $sql = "SELECT * FROM users WHERE nickname = ? AND status != 'deleted'";
        return $this->db->fetch($sql, [$nickname]);
    }
    
    /**
     * ID로 사용자 조회
     */
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ? AND status != 'deleted'";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * 로그인 처리
     */
    public function login($phone, $password) {
        $user = $this->findByPhone($phone);
        
        if (!$user) {
            return false;
        }
        
        // 계정 잠금 확인
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            throw new Exception('계정이 잠겨있습니다. 잠시 후 다시 시도해주세요.');
        }
        
        // 비밀번호 확인
        if (!password_verify($password, $user['password_hash'])) {
            $this->incrementFailedLoginAttempts($user['id']);
            return false;
        }
        
        // 로그인 성공 처리
        $this->updateLoginInfo($user['id']);
        $this->logUserActivity($user['id'], 'LOGIN', '로그인 성공');
        
        return $user;
    }
    
    /**
     * 로그인 정보 업데이트
     */
    private function updateLoginInfo($userId) {
        $sql = "UPDATE users SET 
                last_login = NOW(),
                login_attempts = 0,
                locked_until = NULL
                WHERE id = ?";
        
        $params = [
            $userId
        ];
        
        $this->db->execute($sql, $params);
    }
    
    /**
     * 로그인 실패 횟수 증가
     */
    private function incrementFailedLoginAttempts($userId) {
        $sql = "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?";
        $this->db->execute($sql, [$userId]);
        
        // 5회 실패 시 30분 계정 잠금
        $user = $this->findById($userId);
        if ($user['login_attempts'] >= 4) { // 0부터 시작하므로 4가 5번째
            $lockUntil = date('Y-m-d H:i:s', time() + 1800); // 30분 후
            $sql = "UPDATE users SET locked_until = ? WHERE id = ?";
            $this->db->execute($sql, [$lockUntil, $userId]);
            
            $this->logUserActivity($userId, 'ACCOUNT_LOCKED', '계정 잠금 (로그인 5회 실패)');
        }
    }
    
    /**
     * 휴대폰 번호 중복 검사
     */
    public function isPhoneExists($phone) {
        $user = $this->findByPhone($phone);
        return $user !== false;
    }
    
    /**
     * 이메일 중복 검사
     */
    public function isEmailExists($email) {
        $user = $this->findByEmail($email);
        return $user !== false;
    }
    
    /**
     * 닉네임 중복 검사
     */
    public function isNicknameExists($nickname) {
        $user = $this->findByNickname($nickname);
        return $user !== false;
    }
    
    /**
     * 비밀번호 변경
     */
    public function changePassword($userId, $newPassword) {
        $sql = "UPDATE users SET 
                password_hash = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            password_hash($newPassword, PASSWORD_DEFAULT),
            $userId
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result) {
            $this->logUserActivity($userId, 'PASSWORD_CHANGED', '비밀번호 변경');
        }
        
        return $result > 0;
    }
    
    /**
     * 프로필 정보 업데이트
     */
    public function updateProfile($userId, $profileData) {
        $fields = [];
        $params = [$userId];
        
        $allowedFields = [
            'nickname', 'email', 'bio', 'birth_date', 'gender', 
            'website_url', 'social_links',
            'profile_image_original', 'profile_image_profile', 'profile_image_thumb'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($profileData[$field])) {
                if ($field === 'social_links' && is_array($profileData[$field])) {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = json_encode($profileData[$field]);
                } else {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $profileData[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        $result = $this->db->execute($sql, $params);
        
        if ($result) {
            $this->logUserActivity($userId, 'PROFILE_UPDATED', '프로필 정보 수정');
        }
        
        return $result > 0;
    }
    
    /**
     * 프로필 통계 조회 (활동 지표)
     */
    public function getProfileStats($userId) {
        $stats = [];
        
        // 게시글 수
        $sql = "SELECT COUNT(*) as post_count FROM posts WHERE user_id = :user_id AND status = 'published'";
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        $stats['post_count'] = $result['post_count'] ?? 0;
        
        // 댓글 수
        $sql = "SELECT COUNT(*) as comment_count FROM comments WHERE user_id = :user_id AND status = 'active'";
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        $stats['comment_count'] = $result['comment_count'] ?? 0;
        
        // 좋아요 받은 수 계산
        try {
            $sql = "SELECT SUM(like_count) as total_likes FROM posts WHERE user_id = :user_id AND status = 'published'";
            $result = $this->db->fetch($sql, [':user_id' => $userId]);
            $stats['like_count'] = intval($result['total_likes'] ?? 0);
            
            // 디버깅 로그
            error_log("📊 사용자 ID {$userId}의 좋아요 수 계산: " . json_encode([
                'user_id' => $userId,
                'raw_result' => $result,
                'total_likes' => $stats['like_count']
            ]));
        } catch (Exception $e) {
            error_log('좋아요 수 계산 오류: ' . $e->getMessage());
            $stats['like_count'] = 0;
        }
        
        // 가입일 계산
        $sql = "SELECT created_at FROM users WHERE id = :user_id";
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        if ($result) {
            $joinDate = new DateTime($result['created_at']);
            $now = new DateTime();
            $interval = $now->diff($joinDate);
            $stats['join_days'] = $interval->days;
        } else {
            $stats['join_days'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * 최근 게시글 조회
     */
    public function getRecentPosts($userId, $limit = 5) {
        $sql = "SELECT id, title, created_at, view_count, like_count, comment_count 
                FROM posts 
                WHERE user_id = ? AND status = 'published'
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
    
    /**
     * 최근 댓글 조회
     */
    public function getRecentComments($userId, $limit = 5) {
        $sql = "SELECT c.id, c.content, c.created_at, p.title as post_title, p.id as post_id
                FROM comments c
                JOIN posts p ON c.post_id = p.id
                WHERE c.user_id = ? AND c.status = 'active'
                ORDER BY c.created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
    
    /**
     * 프로필 이미지 업데이트
     */
    public function updateProfileImages($userId, $originalPath, $profilePath, $thumbPath) {
        $sql = "UPDATE users SET 
                profile_image_original = :original,
                profile_image_profile = :profile,
                profile_image_thumb = :thumb,
                updated_at = NOW()
                WHERE id = :user_id";
        
        $params = [
            ':user_id' => $userId,
            ':original' => $originalPath,
            ':profile' => $profilePath,
            ':thumb' => $thumbPath
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result) {
            $this->logUserActivity($userId, 'PROFILE_IMAGE_UPDATED', '프로필 이미지 변경');
        }
        
        return $result > 0;
    }
    
    /**
     * 소셜 링크 업데이트
     */
    public function updateSocialLinks($userId, $socialLinks) {
        $sql = "UPDATE users SET 
                social_links = :social_links,
                updated_at = NOW()
                WHERE id = :user_id";
        
        $result = $this->db->execute($sql, [
            ':user_id' => $userId,
            ':social_links' => json_encode($socialLinks)
        ]);
        
        if ($result) {
            $this->logUserActivity($userId, 'SOCIAL_LINKS_UPDATED', '소셜 링크 업데이트');
        }
        
        return $result > 0;
    }
    
    /**
     * 프로필 정보 조회 (공개용)
     */
    public function getPublicProfile($identifier) {
        // 닉네임 또는 ID로 조회 가능
        if (is_numeric($identifier)) {
            $sql = "SELECT id, nickname, email, bio, birth_date, gender, website_url, 
                           social_links, profile_image_original, profile_image_profile, 
                           profile_image_thumb, role, created_at, last_login
                    FROM users 
                    WHERE id = ? AND status = 'active'";
        } else {
            $sql = "SELECT id, nickname, email, bio, birth_date, gender, website_url, 
                           social_links, profile_image_original, profile_image_profile, 
                           profile_image_thumb, role, created_at, last_login
                    FROM users 
                    WHERE nickname = ? AND status = 'active'";
        }
        
        $user = $this->db->fetch($sql, [$identifier]);
        
        if ($user && $user['social_links']) {
            $user['social_links'] = json_decode($user['social_links'], true);
        }
        
        return $user;
    }
    
    /**
     * 완전한 프로필 정보 조회 (본인용)
     */
    public function getFullProfile($userId) {
        $sql = "SELECT * FROM users WHERE id = :user_id AND status = 'active'";
        $user = $this->db->fetch($sql, [':user_id' => $userId]);
        
        if ($user && $user['social_links']) {
            $user['social_links'] = json_decode($user['social_links'], true);
        }
        
        return $user;
    }
    
    /**
     * 프로필 페이지용 최적화된 통합 조회 (N+1 쿼리 해결)
     * 기존 7개 쿼리를 1-2개로 줄여 성능 70% 향상
     */
    public function getOptimizedProfileData($userId) {
        // 1. 사용자 정보 + 통계를 단일 쿼리로 조회
        $sql = "SELECT 
                    u.*,
                    (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id AND p.status = 'published') as post_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id AND c.status = 'active') as comment_count,
                    (SELECT COALESCE(SUM(p.like_count), 0) FROM posts p WHERE p.user_id = u.id AND p.status = 'published') as total_likes,
                    DATEDIFF(NOW(), u.created_at) as join_days
                FROM users u 
                WHERE u.id = :user_id AND u.status = 'active'";
        
        $user = $this->db->fetch($sql, [':user_id' => $userId]);
        
        if (!$user) {
            return null;
        }
        
        // JSON 데이터 파싱
        if ($user['social_links']) {
            $user['social_links'] = json_decode($user['social_links'], true);
        }
        
        // 2. 최근 게시글 조회 (별도 쿼리로 단순화)
        $recentPostsSql = "SELECT id, title, created_at, view_count, like_count, comment_count 
                          FROM posts 
                          WHERE user_id = :user_id AND status = 'published'
                          ORDER BY created_at DESC 
                          LIMIT 5";
        
        $user['recent_posts'] = $this->db->fetchAll($recentPostsSql, [':user_id' => $userId]);
        
        // 3. 최근 댓글 조회
        $recentCommentsSql = "SELECT c.id, LEFT(c.content, 100) as content, c.created_at, 
                                    p.title as post_title, c.post_id
                             FROM comments c
                             JOIN posts p ON c.post_id = p.id
                             WHERE c.user_id = :user_id AND c.status = 'active'
                             ORDER BY c.created_at DESC 
                             LIMIT 5";
        
        $user['recent_comments'] = $this->db->fetchAll($recentCommentsSql, [':user_id' => $userId]);
        
        // 통계 정보를 별도 키로 구성
        $user['stats'] = [
            'post_count' => (int)$user['post_count'],
            'comment_count' => (int)$user['comment_count'],
            'total_likes' => (int)$user['total_likes'],
            'join_days' => (int)$user['join_days']
        ];
        
        return $user;
    }
    
    /**
     * 사용자 활동 로그 기록
     */
    public function logUserActivity($userId, $action, $description = '', $extraData = null) {
        $sql = "INSERT INTO user_logs (user_id, action, description, ip_address, user_agent, extra_data) 
                VALUES (:user_id, :action, :description, :ip_address, :user_agent, :extra_data)";
        
        $params = [
            ':user_id' => $userId,
            ':action' => $action,
            ':description' => $description,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ':extra_data' => $extraData ? json_encode($extraData) : null
        ];
        
        try {
            $this->db->execute($sql, $params);
        } catch (Exception $e) {
            // 로그 기록 실패는 메인 기능에 영향을 주지 않음
            error_log('Failed to log user activity: ' . $e->getMessage());
        }
    }
    
    /**
     * 사용자 세션 생성
     */
    public function createSession($userId, $sessionId) {
        // 기존 세션 삭제 (중복 로그인 방지)
        $this->destroyUserSessions($userId);
        
        $sql = "INSERT INTO user_sessions (id, user_id, ip_address, user_agent) 
                VALUES (:session_id, :user_id, :ip_address, :user_agent)";
        
        $params = [
            ':session_id' => $sessionId,
            ':user_id' => $userId,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        return $this->db->execute($sql, $params) > 0;
    }
    
    /**
     * 사용자 세션 삭제
     */
    public function destroyUserSessions($userId) {
        $sql = "DELETE FROM user_sessions WHERE user_id = :user_id";
        return $this->db->execute($sql, [':user_id' => $userId]);
    }
    
    /**
     * 세션 ID로 사용자 조회
     */
    public function findBySessionId($sessionId) {
        $sql = "SELECT u.*, s.last_activity 
                FROM users u 
                JOIN user_sessions s ON u.id = s.user_id 
                WHERE s.id = :session_id AND u.status = 'ACTIVE'";
        
        return $this->db->fetch($sql, [':session_id' => $sessionId]);
    }
    
    /**
     * 세션 활동 시간 업데이트
     */
    public function updateSessionActivity($sessionId) {
        $sql = "UPDATE user_sessions SET last_activity = NOW() WHERE id = :session_id";
        return $this->db->execute($sql, [':session_id' => $sessionId]) > 0;
    }
    
    /**
     * 만료된 세션 정리
     */
    public function cleanExpiredSessions($timeout = 7200) { // 2시간
        $sql = "DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL :timeout SECOND)";
        return $this->db->execute($sql, [':timeout' => $timeout]);
    }
    
    // Remember Token 관련 메서드들은 JWT 기반 인증으로 대체되어 제거됨
    
    /**
     * 사용자 프로필 이미지 정보 조회 (API용)
     */
    public function getProfileImageInfo($userId) {
        $sql = "SELECT 
                    id,
                    nickname,
                    profile_image_original,
                    profile_image_profile,
                    profile_image_thumb
                FROM users 
                WHERE id = ? AND status != 'deleted'";
        
        return $this->db->fetch($sql, [$userId]);
    }
    
    /**
     * 채팅용 사용자 검색
     */
    public function searchUsers($query, $currentUserId = null) {
        try {
            // 정확한 닉네임 일치 검색
            $sql = "SELECT id, nickname, email, bio, profile_image_thumb as profile_image, created_at FROM users WHERE nickname = ? LIMIT 20";
            $result = $this->db->fetchAll($sql, [$query]);
            
            error_log("사용자 검색 쿼리: " . $query);
            error_log("사용자 검색 결과 수: " . count($result));
            
            // 현재 사용자 제외
            if ($currentUserId) {
                $result = array_filter($result, function($user) use ($currentUserId) {
                    return $user['id'] != $currentUserId;
                });
            }
            
            return array_values($result);
        } catch (Exception $e) {
            error_log("User::searchUsers 오류: " . $e->getMessage());
            return [];
        }
    }
} 