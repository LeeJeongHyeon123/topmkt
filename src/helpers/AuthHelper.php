<?php
/**
 * AuthHelper 클래스
 * 인증 관련 공통 기능을 제공합니다.
 */

require_once SRC_PATH . '/middlewares/AuthMiddleware.php';

class AuthHelper {

    /**
     * 로그인 필요 응답
     */
    public static function requireLogin($redirectUrl = null) {
        if (!AuthMiddleware::isLoggedIn()) {
            $redirect = $redirectUrl ?: $_SERVER['REQUEST_URI'];
            header('Location: /auth/login?redirect=' . urlencode($redirect));
            exit;
        }
    }

    /**
     * 기업회원 권한 확인
     */
    public static function requireCorporatePermission($redirectUrl = null) {
        self::requireLogin($redirectUrl);

        $userRole = AuthMiddleware::getUserRole();
        if ($userRole !== 'ROLE_CORPORATE') {
            header('HTTP/1.1 403 Forbidden');
            include SRC_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * 관리자 권한 확인
     */
    public static function requireAdminPermission($redirectUrl = null) {
        self::requireLogin($redirectUrl);

        if (!AuthMiddleware::isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            include SRC_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * 로그인된 사용자 ID 반환
     */
    public static function getCurrentUserId() {
        return AuthMiddleware::getCurrentUserId();
    }

    /**
     * 로그인된 사용자 정보 반환
     */
    public static function getCurrentUser() {
        return AuthMiddleware::getCurrentUser();
    }

    /**
     * 사용자 역할 반환
     */
    public static function getUserRole() {
        return AuthMiddleware::getUserRole();
    }
}
?>
