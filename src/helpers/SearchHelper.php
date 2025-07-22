<?php
/**
 * 검색 관련 헬퍼 클래스
 */

class SearchHelper {
    
    /**
     * 검색어 하이라이트 처리
     *
     * @param string $text 원본 텍스트
     * @param string $search 검색어
     * @param string $highlightClass 하이라이트 CSS 클래스
     * @return string 하이라이트 처리된 텍스트
     */
    public static function highlightSearchTerm($text, $search, $highlightClass = 'search-highlight') {
        if (empty($search) || empty($text)) {
            return $text;
        }
        
        // 검색어를 공백으로 분리
        $terms = array_filter(explode(' ', $search));
        
        foreach ($terms as $term) {
            if (strlen($term) >= 2) {
                // 대소문자 구분 없이 하이라이트
                $text = preg_replace(
                    '/(' . preg_quote($term, '/') . ')/iu',
                    '<mark class="' . $highlightClass . '">$1</mark>',
                    $text
                );
            }
        }
        
        return $text;
    }
    
    /**
     * 검색어 유효성 검증
     *
     * @param string $search 검색어
     * @return array 검증 결과 [valid => boolean, message => string, cleaned => string]
     */
    public static function validateSearchTerm($search) {
        $result = [
            'valid' => false,
            'message' => '',
            'cleaned' => ''
        ];
        
        if (empty($search)) {
            $result['message'] = '검색어를 입력해주세요.';
            return $result;
        }
        
        // 길이 제한 (100자)
        if (strlen($search) > 100) {
            $search = substr($search, 0, 100);
        }
        
        // 최소 길이 검증 (2자 이상)
        if (strlen($search) < 2) {
            $result['message'] = '검색어는 2자 이상 입력해주세요.';
            return $result;
        }
        
        // XSS 방지
        $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
        
        // 특수문자 제거 (FULLTEXT 검색에 적합하게)
        $cleaned = preg_replace('/[^\w\s가-힣ㄱ-ㅎㅏ-ㅣ]/u', ' ', $search);
        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));
        
        // 정리 후 빈 문자열 검증
        if (empty($cleaned)) {
            $result['message'] = '유효한 검색어를 입력해주세요.';
            return $result;
        }
        
        $result['valid'] = true;
        $result['cleaned'] = $cleaned;
        
        return $result;
    }
    
    /**
     * 검색 결과 요약문 생성
     *
     * @param string $content 전체 내용
     * @param string $search 검색어
     * @param int $maxLength 최대 길이
     * @return string 검색어 중심의 요약문
     */
    public static function generateSearchSnippet($content, $search, $maxLength = 200) {
        if (empty($search) || empty($content)) {
            return substr(strip_tags($content), 0, $maxLength) . '...';
        }
        
        // HTML 태그 제거
        $content = strip_tags($content);
        
        // 검색어 첫 번째 등장 위치 찾기
        $searchPos = stripos($content, $search);
        
        if ($searchPos === false) {
            // 검색어가 없으면 앞부분 반환
            return substr($content, 0, $maxLength) . '...';
        }
        
        // 검색어 중심으로 텍스트 추출
        $start = max(0, $searchPos - intval($maxLength / 2));
        $snippet = substr($content, $start, $maxLength);
        
        // 시작이 중간이면 '...' 추가
        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        
        // 끝이 중간이면 '...' 추가
        if (strlen($content) > $start + $maxLength) {
            $snippet = $snippet . '...';
        }
        
        return $snippet;
    }
    
    /**
     * 검색 로그 기록
     *
     * @param string $search 검색어
     * @param int $resultCount 검색 결과 수
     * @param float $executionTime 실행 시간 (초)
     * @return void
     */
    public static function logSearch($search, $resultCount, $executionTime) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'search_term' => $search,
            'result_count' => $resultCount,
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        error_log('🔍 검색 로그: ' . json_encode($logEntry, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 불린 검색어 생성 (고급 검색용)
     *
     * @param string $search 일반 검색어
     * @return string 불린 검색어
     */
    public static function toBooleanSearch($search) {
        if (empty($search)) {
            return '';
        }
        
        // 공백으로 분리된 각 단어를 AND 조건으로 변환
        $terms = array_filter(explode(' ', trim($search)));
        
        if (count($terms) <= 1) {
            return $search;
        }
        
        // 각 단어 앞에 '+' 추가 (필수 조건)
        $booleanTerms = array_map(function($term) {
            return '+' . $term;
        }, $terms);
        
        return implode(' ', $booleanTerms);
    }
}