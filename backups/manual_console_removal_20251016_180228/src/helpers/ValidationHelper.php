<?php
/**
 * 유효성 검증 관련 헬퍼 함수
 */
namespace App\Helpers;

class ValidationHelper {
    /**
     * 이메일 형식 검증
     * 
     * @param string $email 검증할 이메일
     * @return bool 유효성 여부
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * 문자열 최소/최대 길이 검증
     * 
     * @param string $str 검증할 문자열
     * @param int $min 최소 길이
     * @param int $max 최대 길이
     * @return bool 유효성 여부
     */
    public static function isValidLength($str, $min, $max) {
        $length = mb_strlen($str, 'UTF-8');
        return $length >= $min && $length <= $max;
    }
    
    /**
     * 비밀번호 강도 검증
     * 영문, 숫자, 특수문자 조합으로 8자 이상
     * 
     * @param string $password 검증할 비밀번호
     * @return bool 유효성 여부
     */
    public static function isStrongPassword($password) {
        // 최소 8자 이상
        if (strlen($password) < 8) {
            return false;
        }
        
        // 영문, 숫자, 특수문자 각각 1개 이상 포함
        $hasLetter = preg_match('/[a-zA-Z]/', $password);
        $hasDigit = preg_match('/\d/', $password);
        $hasSpecial = preg_match('/[^a-zA-Z\d]/', $password);
        
        return $hasLetter && $hasDigit && $hasSpecial;
    }
    
    /**
     * 필수 필드 검증
     * 
     * @param array $data 검증할 데이터
     * @param array $fields 필수 필드 목록
     * @return array 오류 메시지 배열 (비어있으면 오류 없음)
     */
    public static function validateRequired($data, $fields) {
        $errors = [];
        
        foreach ($fields as $field => $label) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = $label . '은(는) 필수 입력 항목입니다.';
            }
        }
        
        return $errors;
    }
    
    /**
     * 입력 데이터 필터링
     * 
     * @param array $data 필터링할 데이터
     * @param array $filters 필드별 필터 유형 (FILTER_* 상수)
     * @return array 필터링된 데이터
     */
    public static function filterInput($data, $filters) {
        $filtered = [];
        
        foreach ($filters as $field => $filter) {
            if (isset($data[$field])) {
                $filtered[$field] = filter_var($data[$field], $filter);
            }
        }
        
        return $filtered;
    }
    
    /**
     * XSS 방지를 위한 HTML 특수 문자 이스케이프
     * 
     * @param string $str 이스케이프할 문자열
     * @return string 이스케이프된 문자열
     */
    public static function escapeHtml($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * 날짜 형식 검증 (YYYY-MM-DD)
     * 
     * @param string $date 검증할 날짜
     * @return bool 유효성 여부
     */
    public static function isValidDate($date) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $parts = explode('-', $date);
            return checkdate($parts[1], $parts[2], $parts[0]);
        }
        
        return false;
    }
    
    /**
     * 전화번호 형식 검증
     * 
     * @param string $phone 검증할 전화번호
     * @return bool 유효성 여부
     */
    public static function isValidPhone($phone) {
        // 숫자만 추출
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // 10-11자리 전화번호 검증
        return preg_match('/^01[0-9]{8,9}$/', $phone) === 1;
    }
} 