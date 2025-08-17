<?php
/**
 * 디바이스 감지 및 반응형 처리 헬퍼 클래스
 * 
 * 모바일/태블릿/PC 감지 및 반응형 기본 설정을 제공합니다.
 * 모든 페이지에서 공통으로 사용할 수 있습니다.
 */
class DeviceHelper 
{
    // 화면 크기 기준값 (픽셀)
    const MOBILE_BREAKPOINT = 480;      // 480px 이하: 모바일
    const TABLET_BREAKPOINT = 768;      // 481-768px: 태블릿  
    const DESKTOP_BREAKPOINT = 769;     // 769px 이상: 데스크톱
    
    /**
     * 모바일 디바이스인지 확인
     * 
     * @return bool true: 모바일, false: 태블릿/PC
     */
    public static function isMobile() {
        // 1순위: JavaScript에서 전달된 화면 크기 기반 감지 (가장 정확)
        if (isset($_COOKIE['screen_width'])) {
            $screenWidth = intval($_COOKIE['screen_width']);
            return $screenWidth <= self::MOBILE_BREAKPOINT;
        }
        
        // 2순위: User-Agent 기반 감지
        return self::isMobileByUserAgent();
    }
    
    /**
     * 태블릿 디바이스인지 확인
     * 
     * @return bool true: 태블릿, false: 모바일/PC
     */
    public static function isTablet() {
        // JavaScript 화면 크기 기반
        if (isset($_COOKIE['screen_width'])) {
            $screenWidth = intval($_COOKIE['screen_width']);
            return $screenWidth > self::MOBILE_BREAKPOINT && $screenWidth <= self::TABLET_BREAKPOINT;
        }
        
        // User-Agent 기반 태블릿 감지
        return self::isTabletByUserAgent();
    }
    
    /**
     * 데스크톱 디바이스인지 확인
     * 
     * @return bool true: 데스크톱, false: 모바일/태블릿
     */
    public static function isDesktop() {
        // JavaScript 화면 크기 기반
        if (isset($_COOKIE['screen_width'])) {
            $screenWidth = intval($_COOKIE['screen_width']);
            return $screenWidth > self::TABLET_BREAKPOINT;
        }
        
        // User-Agent 기반 (모바일/태블릿이 아니면 데스크톱)
        return !self::isMobileByUserAgent() && !self::isTabletByUserAgent();
    }
    
    /**
     * 디바이스 타입 문자열 반환
     * 
     * @return string 'mobile', 'tablet', 'desktop'
     */
    public static function getDeviceType() {
        if (self::isMobile()) {
            return 'mobile';
        } elseif (self::isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
    
    /**
     * 강의/이벤트 페이지의 기본 뷰 타입 결정
     * 
     * @param string $requestedView URL 파라미터로 요청된 뷰 (선택사항)
     * @return string 'calendar' 또는 'list'
     */
    public static function getDefaultView($requestedView = null) {
        // 사용자가 명시적으로 뷰를 선택한 경우 우선
        if ($requestedView && in_array($requestedView, ['calendar', 'list'])) {
            return $requestedView;
        }
        
        // 디바이스에 따른 기본 뷰 설정
        if (self::isMobile()) {
            return 'list';      // 모바일: 목록형이 더 편리
        } else {
            return 'calendar';  // 태블릿/PC: 캘린더가 더 직관적
        }
    }
    
    /**
     * 화면 크기 정보 배열 반환
     * 
     * @return array 화면 크기 및 디바이스 정보
     */
    public static function getScreenInfo() {
        $screenWidth = isset($_COOKIE['screen_width']) ? intval($_COOKIE['screen_width']) : null;
        $deviceType = self::getDeviceType();
        
        return [
            'screen_width' => $screenWidth,
            'device_type' => $deviceType,
            'is_mobile' => self::isMobile(),
            'is_tablet' => self::isTablet(),
            'is_desktop' => self::isDesktop(),
            'breakpoints' => [
                'mobile' => self::MOBILE_BREAKPOINT,
                'tablet' => self::TABLET_BREAKPOINT,
                'desktop' => self::DESKTOP_BREAKPOINT
            ]
        ];
    }
    
    /**
     * CSS 클래스명 생성 (반응형 스타일링용)
     * 
     * @return string CSS 클래스명
     */
    public static function getDeviceClass() {
        $deviceType = self::getDeviceType();
        $screenWidth = isset($_COOKIE['screen_width']) ? intval($_COOKIE['screen_width']) : 0;
        
        $classes = ["device-{$deviceType}"];
        
        if ($screenWidth > 0) {
            $classes[] = "screen-{$screenWidth}";
        }
        
        return implode(' ', $classes);
    }
    
    // === Private Methods ===
    
    /**
     * User-Agent 기반 모바일 감지
     */
    private static function isMobileByUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPod', 
            'Windows Phone', 'BlackBerry', 'webOS'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * User-Agent 기반 태블릿 감지
     */
    private static function isTabletByUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $tabletKeywords = [
            'iPad', 'Tablet', 'Tab'
        ];
        
        foreach ($tabletKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 디버그 정보 출력 (개발용)
     * 
     * @return array 상세 디버그 정보
     */
    public static function getDebugInfo() {
        return [
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'screen_width_cookie' => $_COOKIE['screen_width'] ?? 'N/A',
            'device_detection' => [
                'is_mobile_ua' => self::isMobileByUserAgent(),
                'is_tablet_ua' => self::isTabletByUserAgent(),
                'is_mobile_final' => self::isMobile(),
                'is_tablet_final' => self::isTablet(),
                'is_desktop_final' => self::isDesktop()
            ],
            'screen_info' => self::getScreenInfo(),
            'default_view' => self::getDefaultView()
        ];
    }
}
?>