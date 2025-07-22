<?php
/**
 * JWT (JSON Web Token) 헬퍼 클래스
 * 
 * PHP 네이티브 구현으로 JWT 토큰 생성, 검증, 디코딩 기능 제공
 * Firebase JWT 라이브러리 없이 독립적으로 작동
 * 
 * @author Claude Code
 * @version 1.0
 */

class JWTHelper {
    
    // JWT 비밀키 (실제 운영에서는 환경변수나 설정 파일에서 관리)
    private static $secret_key = null;
    
    // 기본 토큰 만료 시간 (30일)
    private static $default_expiry = 2592000; // 30 * 24 * 60 * 60
    
    // 짧은 토큰 만료 시간 (1시간) - 액세스 토큰용
    private static $short_expiry = 3600; // 60 * 60
    
    /**
     * 비밀키 초기화
     */
    private static function initSecretKey() {
        if (self::$secret_key === null) {
            // 설정 파일에서 키 로드하거나 기본값 사용
            if (defined('JWT_SECRET_KEY')) {
                self::$secret_key = JWT_SECRET_KEY;
            } else {
                // 기본 비밀키 (운영환경에서는 반드시 변경)
                self::$secret_key = 'topmkt_jwt_secret_key_2025_' . hash('sha256', 'topmktx.com');
            }
        }
    }
    
    /**
     * JWT 토큰 생성
     * 
     * @param array $payload 토큰에 포함할 데이터
     * @param int $expiry 만료 시간 (초, 선택사항)
     * @return string JWT 토큰
     */
    public static function createToken($payload, $expiry = null) {
        self::initSecretKey();
        
        if ($expiry === null) {
            $expiry = self::$default_expiry;
        }
        
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        
        $current_time = time();
        $token_payload = array_merge($payload, [
            'iat' => $current_time,           // 발급 시간
            'exp' => $current_time + $expiry, // 만료 시간
            'iss' => 'topmktx.com',          // 발급자
            'aud' => 'topmkt-users'           // 대상
        ]);
        
        // Base64 URL 인코딩
        $header_encoded = self::base64UrlEncode(json_encode($header));
        $payload_encoded = self::base64UrlEncode(json_encode($token_payload));
        
        // 서명 생성
        $signature = self::sign($header_encoded . '.' . $payload_encoded);
        
        return $header_encoded . '.' . $payload_encoded . '.' . $signature;
    }
    
    /**
     * 액세스 토큰 생성 (짧은 만료시간)
     * 
     * @param array $payload 토큰 데이터
     * @return string JWT 토큰
     */
    public static function createAccessToken($payload) {
        return self::createToken($payload, self::$short_expiry);
    }
    
    /**
     * 리프레시 토큰 생성 (긴 만료시간)
     * 
     * @param array $payload 토큰 데이터  
     * @return string JWT 토큰
     */
    public static function createRefreshToken($payload) {
        // 리프레시 토큰은 최소 정보만 포함
        $refresh_payload = [
            'user_id' => $payload['user_id'],
            'type' => 'refresh'
        ];
        return self::createToken($refresh_payload, self::$default_expiry);
    }
    
    /**
     * JWT 토큰 검증 및 디코딩
     * 
     * @param string $token JWT 토큰
     * @return array|false 성공시 payload 배열, 실패시 false
     */
    public static function validateToken($token) {
        self::initSecretKey();
        
        if (empty($token)) {
            return false;
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header_encoded, $payload_encoded, $signature) = $parts;
        
        // 서명 검증
        $expected_signature = self::sign($header_encoded . '.' . $payload_encoded);
        if (!hash_equals($signature, $expected_signature)) {
            error_log('JWT 서명 검증 실패');
            return false;
        }
        
        // 헤더 디코딩
        $header = json_decode(self::base64UrlDecode($header_encoded), true);
        if (!$header || $header['alg'] !== 'HS256') {
            error_log('JWT 헤더 검증 실패');
            return false;
        }
        
        // 페이로드 디코딩
        $payload = json_decode(self::base64UrlDecode($payload_encoded), true);
        if (!$payload) {
            error_log('JWT 페이로드 디코딩 실패');
            return false;
        }
        
        // 만료 시간 검증
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            error_log('JWT 토큰 만료: exp=' . $payload['exp'] . ', now=' . time());
            return false;
        }
        
        // 발급 시간 검증 (미래 토큰 방지)
        if (isset($payload['iat']) && $payload['iat'] > time() + 60) {
            error_log('JWT 발급 시간 오류: iat=' . $payload['iat'] . ', now=' . time());
            return false;
        }
        
        return $payload;
    }
    
    /**
     * 토큰에서 사용자 정보 추출
     * 
     * @param string $token JWT 토큰
     * @return array|false 사용자 정보 또는 false
     */
    public static function getUserFromToken($token) {
        $payload = self::validateToken($token);
        if (!$payload) {
            return false;
        }
        
        // 필수 사용자 정보 확인
        if (!isset($payload['user_id'])) {
            return false;
        }
        
        return [
            'user_id' => $payload['user_id'],
            'username' => $payload['username'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'user_role' => $payload['user_role'] ?? 'GENERAL',
            'exp' => $payload['exp'] ?? null,
            'iat' => $payload['iat'] ?? null
        ];
    }
    
    /**
     * 토큰 만료 여부 확인
     * 
     * @param string $token JWT 토큰
     * @return bool true면 만료됨
     */
    public static function isTokenExpired($token) {
        $payload = json_decode(self::base64UrlDecode(explode('.', $token)[1] ?? ''), true);
        if (!$payload || !isset($payload['exp'])) {
            return true;
        }
        return $payload['exp'] < time();
    }
    
    /**
     * 토큰 남은 시간 (초)
     * 
     * @param string $token JWT 토큰
     * @return int 남은 시간 (초), 만료되었으면 0
     */
    public static function getTokenTimeLeft($token) {
        $payload = json_decode(self::base64UrlDecode(explode('.', $token)[1] ?? ''), true);
        if (!$payload || !isset($payload['exp'])) {
            return 0;
        }
        return max(0, $payload['exp'] - time());
    }
    
    /**
     * Base64 URL 인코딩
     * 
     * @param string $data 인코딩할 데이터
     * @return string 인코딩된 문자열
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL 디코딩
     * 
     * @param string $data 디코딩할 문자열
     * @return string 디코딩된 데이터
     */
    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    /**
     * HMAC SHA256 서명 생성
     * 
     * @param string $data 서명할 데이터
     * @return string Base64 URL 인코딩된 서명
     */
    private static function sign($data) {
        return self::base64UrlEncode(hash_hmac('sha256', $data, self::$secret_key, true));
    }
    
    /**
     * 사용자용 토큰 쌍 생성 (액세스 + 리프레시)
     * 
     * @param array $user_data 사용자 정보
     * @return array ['access_token' => string, 'refresh_token' => string]
     */
    public static function createTokenPair($user_data) {
        $payload = [
            'user_id' => $user_data['id'],
            'username' => $user_data['nickname'] ?? $user_data['username'] ?? '',
            'phone' => $user_data['phone'] ?? '',
            'user_role' => $user_data['role'] ?? 'GENERAL'
        ];
        
        return [
            'access_token' => self::createAccessToken($payload),
            'refresh_token' => self::createRefreshToken($payload)
        ];
    }
    
    /**
     * 디버그 정보 출력 (개발용)
     * 
     * @param string $token JWT 토큰
     * @return array 토큰 분석 정보
     */
    public static function debugToken($token) {
        if (empty($token)) {
            return ['error' => '토큰이 없습니다'];
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return ['error' => '토큰 형식이 올바르지 않습니다'];
        }
        
        $header = json_decode(self::base64UrlDecode($parts[0]), true);
        $payload = json_decode(self::base64UrlDecode($parts[1]), true);
        
        return [
            'header' => $header,
            'payload' => $payload,
            'signature_length' => strlen($parts[2]),
            'is_expired' => self::isTokenExpired($token),
            'time_left' => self::getTokenTimeLeft($token),
            'time_left_formatted' => self::formatTimeLeft(self::getTokenTimeLeft($token))
        ];
    }
    
    /**
     * 남은 시간을 사람이 읽기 쉬운 형태로 변환
     * 
     * @param int $seconds 초
     * @return string 포맷된 시간
     */
    private static function formatTimeLeft($seconds) {
        if ($seconds <= 0) {
            return '만료됨';
        }
        
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($days > 0) {
            return "{$days}일 {$hours}시간";
        } elseif ($hours > 0) {
            return "{$hours}시간 {$minutes}분";
        } else {
            return "{$minutes}분";
        }
    }
}