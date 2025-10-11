<?php
/**
 * SessionHelper 클래스
 * 세션 관리 관련 공통 기능을 제공합니다.
 */

class SessionHelper {

    /**
     * 세션 시작
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * 세션 값 설정
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * 세션 값 조회
     */
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * 세션 값 확인
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * 세션 값 제거
     */
    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * 모든 세션 제거
     */
    public static function destroy() {
        self::start();
        session_destroy();
    }

    /**
     * 플래시 메시지 설정 (일회성 메시지)
     */
    public static function setFlash($key, $value) {
        self::set('flash_' . $key, $value);
    }

    /**
     * 플래시 메시지 조회 및 제거
     */
    public static function getFlash($key, $default = null) {
        $value = self::get('flash_' . $key, $default);
        if ($value !== $default) {
            self::remove('flash_' . $key);
        }
        return $value;
    }

    /**
     * 성공 메시지 설정
     */
    public static function setSuccess($message) {
        self::setFlash('success', $message);
    }

    /**
     * 오류 메시지 설정
     */
    public static function setError($message) {
        self::setFlash('error', $message);
    }

    /**
     * 성공 메시지 조회
     */
    public static function getSuccess() {
        return self::getFlash('success');
    }

    /**
     * 오류 메시지 조회
     */
    public static function getError() {
        return self::getFlash('error');
    }

    /**
     * 세션 ID 재생성
     */
    public static function regenerateId() {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * 사용자 에이전트 검증
     */
    public static function validateUserAgent() {
        $storedAgent = self::get('user_agent');
        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ($storedAgent && $storedAgent !== $currentAgent) {
            self::destroy();
            return false;
        }

        self::set('user_agent', $currentAgent);
        return true;
    }

    /**
     * IP 주소 검증
     */
    public static function validateIp() {
        $storedIp = self::get('user_ip');
        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';

        if ($storedIp && $storedIp !== $currentIp) {
            self::destroy();
            return false;
        }

        self::set('user_ip', $currentIp);
        return true;
    }
}
?>
