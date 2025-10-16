<?php
/**
 * CSRF 보호 미들웨어 클래스
 */
namespace App\Middlewares;

class CsrfMiddleware {
    /**
     * CSRF 토큰 생성
     * 
     * @return string 생성된 CSRF 토큰
     */
    public static function generateToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * CSRF 토큰 확인
     * 
     * @param string $token 확인할 토큰
     * @return bool 토큰 유효성
     */
    public static function validateToken($token) {
        if (!isset($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * POST 요청 검증
     * CSRF 토큰이 유효하지 않으면 403 오류 반환
     * 
     * @return bool 유효성
     */
    public static function verifyPost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
            
            if (!self::validateToken($token)) {
                header('HTTP/1.1 403 Forbidden');
                include SRC_PATH . '/views/templates/403.php';
                exit;
            }
        }
        
        return true;
    }
    
    /**
     * API 요청 검증
     * CSRF 토큰이 유효하지 않으면 JSON 오류 반환
     * 
     * @return bool 유효성
     */
    public static function verifyApi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $headers = getallheaders();
            $token = $headers['X-CSRF-Token'] ?? '';
            
            if (!self::validateToken($token)) {
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['error' => 'CSRF 토큰이 유효하지 않습니다.']);
                exit;
            }
        }
        
        return true;
    }
    
    /**
     * CSRF 토큰 HTML 입력 필드 생성
     * 
     * @return string HTML 입력 필드
     */
    public static function tokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
    }
    
    /**
     * CSRF 토큰 값만 반환
     * 
     * @return string 토큰 값
     */
    public static function getToken() {
        return self::generateToken();
    }
} 