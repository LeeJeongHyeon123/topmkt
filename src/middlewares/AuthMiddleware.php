<?php
/**
 * 인증 미들웨어 클래스 (JWT 기반)
 */

require_once SRC_PATH . '/helpers/JWTHelper.php';

class AuthMiddleware {
    /**
     * 인증 확인 (JWT 기반)
     * 인증되지 않은 사용자는 로그인 페이지로 리다이렉트
     *
     * @return bool 인증 여부
     */
    public static function isAuthenticated() {
        // JWT 토큰으로 인증 확인
        $user = self::authenticateWithJWT();
        
        if (!$user) {
            header('Location: /auth/login');
            exit;
        }
        
        // 호환성을 위해 세션에 사용자 정보 설정
        self::setSessionFromUser($user);
        
        // 인증된 사용자의 활동 시간 갱신
        self::updateLastActivity();
        
        return true;
    }
    
    /**
     * 특정 역할 확인 (JWT 기반)
     * 인증되지 않았거나 권한이 없는 사용자는 적절한 페이지로 리다이렉트
     *
     * @param string|array $role 필요한 역할 (단일 문자열 또는 배열)
     * @return bool 권한 여부
     */
    public static function hasRole($role) {
        // JWT 토큰으로 인증 확인
        $user = self::authenticateWithJWT();
        
        if (!$user) {
            header('Location: /auth/login');
            exit;
        }
        
        // 호환성을 위해 세션에 사용자 정보 설정
        self::setSessionFromUser($user);
        
        $userRole = $user['user_role'] ?? 'GENERAL';
        
        // 관리자는 모든 권한 허용
        if ($userRole === 'ADMIN' || $userRole === 'SUPER_ADMIN' || $userRole === 'ROLE_ADMIN') {
            return true;
        }
        
        // 단일 역할 또는 여러 역할 확인
        if (is_array($role)) {
            if (!in_array($userRole, $role)) {
                header('HTTP/1.1 403 Forbidden');
                include SRC_PATH . '/views/templates/403.php';
                exit;
            }
        } else {
            if ($userRole !== $role) {
                header('HTTP/1.1 403 Forbidden');
                include SRC_PATH . '/views/templates/403.php';
                exit;
            }
        }
        
        return true;
    }
    
    // 구 버전 API 메서드들은 JWT 기반으로 교체됨 - 하단의 JWT 기반 메서드들 참조
    
    // 세션 기반 메서드들은 JWT 기반으로 교체됨 - 하단의 JWT 기반 메서드들 참조
    
    /**
     * 현재 사용자 프로필 이미지 경로 반환
     * 
     * @return string|null 프로필 이미지 경로 또는 null
     */
    public static function getCurrentUserProfileImage() {
        // 세션에 저장된 값 우선 사용
        if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])) {
            return $_SESSION['profile_image'];
        }
        
        // 세션에 없으면 데이터베이스에서 조회
        $currentUserId = self::getCurrentUserId();
        if (!$currentUserId) {
            return null;
        }
        
        try {
            require_once SRC_PATH . '/config/database.php';
            $db = Database::getInstance();
            
            $result = $db->fetch("
                SELECT profile_image_thumb 
                FROM users 
                WHERE id = ? AND status = 'active'
            ", [$currentUserId]);
            
            $profileImage = $result ? $result['profile_image_thumb'] : null;
            
            // 세션에 저장하여 다음 요청에서 재사용
            if ($profileImage) {
                $_SESSION['profile_image'] = $profileImage;
            }
            
            return $profileImage;
        } catch (Exception $e) {
            error_log('프로필 이미지 조회 오류: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 관리자 권한 확인
     * 
     * @return bool 관리자 여부
     */
    public static function isAdmin() {
        $role = self::getCurrentUserRole();
        return $role === 'ADMIN' || $role === 'SUPER_ADMIN' || $role === 'ROLE_ADMIN';
    }
    
    /**
     * 소유자 또는 관리자 권한 확인
     * 
     * @param int $ownerId 소유자 ID
     * @return bool 권한 여부
     */
    public static function isOwnerOrAdmin($ownerId) {
        $currentUserId = self::getCurrentUserId();
        return ($currentUserId && $currentUserId == $ownerId) || self::isAdmin();
    }
    
    /**
     * 세션 활동 시간 갱신
     * Remember Token이 있는 사용자의 세션을 자동으로 연장
     */
    public static function updateLastActivity() {
        // 로그인된 사용자만 처리
        if (!isset($_SESSION['user_id'])) {
            return;
        }
        
        $currentTime = time();
        $lastActivity = $_SESSION['last_activity'] ?? $currentTime;
        
        // 5분 이상 지난 경우에만 갱신 (너무 자주 갱신하지 않기 위해)
        if (($currentTime - $lastActivity) >= 300) {
            $_SESSION['last_activity'] = $currentTime;
            
            // JWT 기반에서는 토큰 자체가 만료 시간을 관리하므로 별도 세션 연장 불필요
        }
    }
    
    /**
     * JWT 토큰으로 사용자 인증
     * 
     * @return array|false 사용자 정보 또는 false
     */
    private static function authenticateWithJWT() {
        // 액세스 토큰 확인
        $accessToken = $_COOKIE['access_token'] ?? null;
        
        if ($accessToken) {
            $userData = JWTHelper::getUserFromToken($accessToken);
            if ($userData) {
                // 데이터베이스에서 최신 사용자 정보 조회
                return self::getUserFromDatabase($userData['user_id']);
            }
        }
        
        // 액세스 토큰이 없거나 만료된 경우 리프레시 토큰으로 갱신 시도
        $refreshToken = $_COOKIE['refresh_token'] ?? null;
        if ($refreshToken) {
            $payload = JWTHelper::validateToken($refreshToken);
            if ($payload && ($payload['type'] ?? '') === 'refresh') {
                $user = self::getUserFromDatabase($payload['user_id']);
                if ($user) {
                    // 새 액세스 토큰 생성
                    $newTokens = JWTHelper::createTokenPair($user);
                    
                    // 새 액세스 토큰을 쿠키에 저장
                    setcookie(
                        'access_token',
                        $newTokens['access_token'],
                        time() + 3600, // 1시간
                        '/',
                        '',
                        isset($_SERVER['HTTPS']),
                        true
                    );
                    
                    return $user;
                }
            }
        }
        
        // 세션 기반 인증 fallback (기존 세션이 있는 경우)
        if (isset($_SESSION['user_id'])) {
            return self::getUserFromDatabase($_SESSION['user_id']);
        }
        
        return false;
    }
    
    /**
     * 데이터베이스에서 사용자 정보 조회
     * 
     * @param int $userId 사용자 ID
     * @return array|false 사용자 정보 또는 false
     */
    private static function getUserFromDatabase($userId) {
        try {
            require_once SRC_PATH . '/config/database.php';
            $db = Database::getInstance();
            
            $user = $db->fetch("
                SELECT id, nickname, phone, role, profile_image_thumb, 
                       created_at, updated_at, status
                FROM users 
                WHERE id = ? AND status = 'active'
            ", [$userId]);
            
            if (!$user) {
                return false;
            }
            
            // JWT에서 사용하는 형태로 변환
            return [
                'id' => $user['id'],
                'user_id' => $user['id'], // 호환성
                'nickname' => $user['nickname'],
                'username' => $user['nickname'], // 호환성
                'phone' => $user['phone'],
                'user_role' => $user['role'],
                'role' => $user['role'], // 호환성
                'profile_image' => $user['profile_image_thumb'],
                'status' => $user['status'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at']
            ];
            
        } catch (Exception $e) {
            error_log('사용자 정보 조회 오류: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * JWT 사용자 정보를 세션에 설정 (호환성)
     * 
     * @param array $user 사용자 정보
     */
    private static function setSessionFromUser($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nickname'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['user_role'] = $user['role'] ?? 'GENERAL';
        $_SESSION['profile_image'] = $user['profile_image'] ?? null;
        $_SESSION['auth_method'] = 'jwt';
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * 로그인 상태 확인 (JWT 기반, 리다이렉트 없이)
     * 
     * @return bool 로그인 여부
     */
    public static function isLoggedIn() {
        $user = self::authenticateWithJWT();
        
        if ($user) {
            // 호환성을 위해 세션에 사용자 정보 설정
            self::setSessionFromUser($user);
            self::updateLastActivity();
            return true;
        }
        
        return false;
    }
    
    /**
     * 현재 사용자 ID 반환 (JWT 기반)
     * 
     * @return int|null 사용자 ID 또는 null
     */
    public static function getCurrentUserId() {
        $user = self::authenticateWithJWT();
        return $user ? $user['id'] : null;
    }
    
    /**
     * 현재 사용자 역할 반환 (JWT 기반)
     * 
     * @return string|null 사용자 역할 또는 null
     */
    public static function getCurrentUserRole() {
        $user = self::authenticateWithJWT();
        return $user ? ($user['role'] ?? 'GENERAL') : null;
    }
    
    /**
     * 사용자 역할 반환 (호환성을 위한 별칭)
     * 
     * @return string|null 사용자 역할 또는 null
     */
    public static function getUserRole() {
        return self::getCurrentUserRole();
    }
    
    /**
     * 현재 사용자 정보 반환 (JWT 기반)
     * 
     * @return array|null 사용자 정보 배열 또는 null
     */
    public static function getCurrentUser() {
        return self::authenticateWithJWT();
    }
    
    /**
     * API 인증 확인 (JWT 기반)
     * API 요청에 대한 인증 확인
     *
     * @return bool 인증 여부
     */
    public static function apiAuthenticate() {
        $user = self::authenticateWithJWT();
        
        if (!$user) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode(['error' => '인증이 필요합니다.']);
            exit;
        }
        
        // 호환성을 위해 세션에 사용자 정보 설정
        self::setSessionFromUser($user);
        
        return true;
    }
    
    /**
     * API 권한 확인 (JWT 기반)
     * 
     * @param string|array $role 필요한 역할
     * @return bool 권한 여부
     */
    public static function apiHasRole($role) {
        $user = self::authenticateWithJWT();
        
        if (!$user) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode(['error' => '인증이 필요합니다.']);
            exit;
        }
        
        // 호환성을 위해 세션에 사용자 정보 설정
        self::setSessionFromUser($user);
        
        $userRole = $user['role'] ?? 'GENERAL';
        
        // 관리자는 모든 권한 허용
        if ($userRole === 'ADMIN' || $userRole === 'SUPER_ADMIN' || $userRole === 'ROLE_ADMIN') {
            return true;
        }
        
        // 단일 역할 또는 여러 역할 확인
        if (is_array($role)) {
            if (!in_array($userRole, $role)) {
                header('HTTP/1.1 403 Forbidden');
                header('Content-Type: application/json');
                echo json_encode(['error' => '권한이 없습니다.']);
                exit;
            }
        } else {
            if ($userRole !== $role) {
                header('HTTP/1.1 403 Forbidden');
                header('Content-Type: application/json');
                echo json_encode(['error' => '권한이 없습니다.']);
                exit;
            }
        }
        
        return true;
    }
    
} 