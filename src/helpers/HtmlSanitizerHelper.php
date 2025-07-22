<?php
/**
 * HTML 콘텐츠 보안 처리 헬퍼
 * 리치 텍스트 에디터 콘텐츠를 안전하게 렌더링
 */

class HtmlSanitizerHelper {
    
    /**
     * 허용된 HTML 태그 목록
     * Quill.js 에디터에서 생성되는 안전한 태그들만 포함
     */
    private static $allowedTags = [
        // 텍스트 서식
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        
        // 헤더
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        
        // 리스트
        'ul', 'ol', 'li',
        
        // 링크와 이미지
        'a', 'img',
        
        // 인용과 코드
        'blockquote', 'code', 'pre',
        
        // 기타 허용 태그
        'span', 'div'
    ];
    
    /**
     * 허용된 HTML 속성 목록
     */
    private static $allowedAttributes = [
        'href', 'src', 'alt', 'title', 'class', 'style',
        'target', 'rel', 'width', 'height'
    ];
    
    /**
     * 리치 텍스트 에디터 콘텐츠를 안전하게 정리
     *
     * @param string $html 원본 HTML 콘텐츠
     * @return string 정리된 안전한 HTML
     */
    public static function sanitizeRichText($html) {
        if (empty($html)) {
            return '';
        }
        
        // 1. 기본 HTML 정리
        $html = self::cleanHtml($html);
        
        // 2. 허용된 태그만 유지
        $html = self::filterAllowedTags($html);
        
        // 3. 위험한 속성 제거
        $html = self::sanitizeAttributes($html);
        
        // 4. 스크립트 및 위험한 콘텐츠 제거
        $html = self::removeScripts($html);
        
        // 5. 이미지 경로 검증
        $html = self::validateImagePaths($html);
        
        return $html;
    }
    
    /**
     * 기본 HTML 정리
     */
    private static function cleanHtml($html) {
        // 불필요한 공백 제거
        $html = trim($html);
        
        // 빈 p 태그 제거
        $html = preg_replace('/<p[^>]*>\s*<\/p>/i', '', $html);
        $html = preg_replace('/<p[^>]*>\s*<br[^>]*>\s*<\/p>/i', '', $html);
        
        // 연속된 br 태그 정리
        $html = preg_replace('/(<br[^>]*>\s*){3,}/i', '<br><br>', $html);
        
        return $html;
    }
    
    /**
     * 허용된 태그만 유지
     */
    private static function filterAllowedTags($html) {
        $allowedTagsString = '<' . implode('><', self::$allowedTags) . '>';
        return strip_tags($html, $allowedTagsString);
    }
    
    /**
     * 위험한 속성 제거
     */
    private static function sanitizeAttributes($html) {
        // DOM 파서 사용 (더 안전한 방법)
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//*[@*]');
        
        foreach ($nodes as $node) {
            $attributesToRemove = [];
            
            // 모든 속성 검사
            foreach ($node->attributes as $attribute) {
                $attrName = strtolower($attribute->name);
                $attrValue = $attribute->value;
                
                // 허용되지 않은 속성 제거
                if (!in_array($attrName, self::$allowedAttributes)) {
                    $attributesToRemove[] = $attrName;
                    continue;
                }
                
                // 위험한 속성 값 검사
                if (self::isDangerousAttributeValue($attrValue)) {
                    $attributesToRemove[] = $attrName;
                    continue;
                }
                
                // href 속성 특별 검증
                if ($attrName === 'href') {
                    if (!self::isValidUrl($attrValue)) {
                        $attributesToRemove[] = $attrName;
                    }
                }
                
                // src 속성 특별 검증 (이미지)
                if ($attrName === 'src') {
                    if (!self::isValidImageSrc($attrValue)) {
                        $attributesToRemove[] = $attrName;
                    }
                }
            }
            
            // 위험한 속성들 제거
            foreach ($attributesToRemove as $attr) {
                $node->removeAttribute($attr);
            }
        }
        
        // HTML 반환 (XML 선언 제거)
        $result = $dom->saveHTML();
        return preg_replace('/^<!DOCTYPE.+?>/', '', str_replace('<?xml encoding="UTF-8">', '', $result));
    }
    
    /**
     * 위험한 속성 값 검사
     */
    private static function isDangerousAttributeValue($value) {
        $dangerousPatterns = [
            '/javascript:/i',
            '/vbscript:/i',
            '/data:/i',
            '/on\w+\s*=/i', // onclick, onload 등
            '/<script/i',
            '/expression\s*\(/i', // CSS expression
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 유효한 URL 검증
     */
    private static function isValidUrl($url) {
        // 상대 경로 허용
        if (strpos($url, '/') === 0) {
            return true;
        }
        
        // HTTP/HTTPS만 허용
        if (preg_match('/^https?:\/\//i', $url)) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }
        
        return false;
    }
    
    /**
     * 유효한 이미지 소스 검증
     */
    private static function isValidImageSrc($src) {
        // 로컬 업로드 경로 허용
        if (strpos($src, '/assets/uploads/') === 0) {
            return true;
        }
        
        // 외부 HTTPS 이미지 허용 (선택적)
        if (preg_match('/^https:\/\//i', $src)) {
            return filter_var($src, FILTER_VALIDATE_URL) !== false;
        }
        
        return false;
    }
    
    /**
     * 스크립트 및 위험한 콘텐츠 제거
     */
    private static function removeScripts($html) {
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi',
            '/javascript:/i',
            '/vbscript:/i',
        ];
        
        foreach ($patterns as $pattern) {
            $html = preg_replace($pattern, '', $html);
        }
        
        return $html;
    }
    
    /**
     * 이미지 경로 검증 및 정리
     */
    private static function validateImagePaths($html) {
        return preg_replace_callback('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', function($matches) {
            $imgTag = $matches[0];
            $src = $matches[1];
            
            // 업로드된 이미지만 허용
            if (self::isValidImageSrc($src)) {
                return $imgTag;
            }
            
            // 유효하지 않은 이미지는 제거
            return '';
        }, $html);
    }
    
    /**
     * 일반 텍스트로 변환 (미리보기용)
     */
    public static function htmlToPlainText($html, $maxLength = 200) {
        // HTML 태그 제거
        $text = strip_tags($html);
        
        // 공백 정리
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // 길이 제한
        if (mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength) . '...';
        }
        
        return $text;
    }
}