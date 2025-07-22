<?php
/**
 * 레이지 로딩 헬퍼 클래스
 * 
 * 이미지 레이지 로딩을 위한 유틸리티 함수들을 제공합니다.
 */
class LazyLoadHelper {
    
    /**
     * 레이지 로딩 이미지 태그 생성
     * 
     * @param string $src 이미지 URL
     * @param string $alt 대체 텍스트
     * @param array $attributes 추가 속성들
     * @param string $placeholder 플레이스홀더 이미지 (선택사항)
     * @return string HTML img 태그
     */
    public static function img($src, $alt = '', $attributes = [], $placeholder = null) {
        // 기본 속성 설정
        $attrs = array_merge([
            'loading' => 'lazy',
            'decoding' => 'async',
        ], $attributes);
        
        // 플레이스홀더 이미지 설정
        if ($placeholder) {
            $attrs['data-src'] = htmlspecialchars($src);
            $attrs['src'] = htmlspecialchars($placeholder);
            $attrs['class'] = isset($attrs['class']) ? $attrs['class'] . ' lazy-image' : 'lazy-image';
        } else {
            $attrs['src'] = htmlspecialchars($src);
        }
        
        $attrs['alt'] = htmlspecialchars($alt);
        
        // 속성 문자열 생성
        $attrString = '';
        foreach ($attrs as $key => $value) {
            $attrString .= ' ' . $key . '="' . $value . '"';
        }
        
        return '<img' . $attrString . '>';
    }
    
    /**
     * 백그라운드 이미지용 레이지 로딩 div 생성
     */
    public static function backgroundImage($src, $attributes = [], $placeholder = null) {
        $attrs = array_merge([
            'data-bg' => htmlspecialchars($src),
            'class' => 'lazy-bg'
        ], $attributes);
        
        if ($placeholder) {
            $attrs['style'] = 'background-image: url(' . htmlspecialchars($placeholder) . ')';
        }
        
        // 기존 클래스에 lazy-bg 추가
        if (isset($attributes['class'])) {
            $attrs['class'] = $attributes['class'] . ' lazy-bg';
        }
        
        $attrString = '';
        foreach ($attrs as $key => $value) {
            $attrString .= ' ' . $key . '="' . $value . '"';
        }
        
        return '<div' . $attrString . '></div>';
    }
    
    /**
     * 플레이스홀더 이미지 URL 생성
     */
    public static function getPlaceholder($width = 300, $height = 200, $text = '로딩중...') {
        // SVG 플레이스홀더 생성
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="100%" height="100%" fill="#f3f4f6"/>';
        $svg .= '<text x="50%" y="50%" font-family="Arial" font-size="14" fill="#9ca3af" text-anchor="middle" dy="4">' . $text . '</text>';
        $svg .= '</svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * 레이지 로딩 JavaScript 코드 생성
     */
    public static function getScript() {
        return '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Intersection Observer 지원 확인
            if ("IntersectionObserver" in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            
                            // 일반 이미지 처리
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.removeAttribute("data-src");
                                img.classList.remove("lazy-image");
                                img.classList.add("lazy-loaded");
                            }
                            
                            // 백그라운드 이미지 처리
                            if (img.dataset.bg) {
                                img.style.backgroundImage = `url(${img.dataset.bg})`;
                                img.removeAttribute("data-bg");
                                img.classList.remove("lazy-bg");
                                img.classList.add("lazy-loaded");
                            }
                            
                            observer.unobserve(img);
                        }
                    });
                });
                
                // 레이지 로딩 대상 요소들 관찰 시작
                document.querySelectorAll(".lazy-image, .lazy-bg").forEach(img => {
                    imageObserver.observe(img);
                });
            } else {
                // Intersection Observer 미지원 시 즉시 로드
                document.querySelectorAll(".lazy-image").forEach(img => {
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute("data-src");
                    }
                });
                
                document.querySelectorAll(".lazy-bg").forEach(el => {
                    if (el.dataset.bg) {
                        el.style.backgroundImage = `url(${el.dataset.bg})`;
                        el.removeAttribute("data-bg");
                    }
                });
            }
        });
        </script>';
    }
    
    /**
     * 레이지 로딩 CSS 스타일 생성
     */
    public static function getStyles() {
        return '
        <style>
        .lazy-image {
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }
        
        .lazy-image.lazy-loaded {
            opacity: 1;
        }
        
        .lazy-bg {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: opacity 0.3s ease;
            opacity: 0.5;
        }
        
        .lazy-bg.lazy-loaded {
            opacity: 1;
        }
        
        /* 로딩 중 스켈레톤 효과 */
        .lazy-image:not(.lazy-loaded) {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        </style>';
    }
}
?>