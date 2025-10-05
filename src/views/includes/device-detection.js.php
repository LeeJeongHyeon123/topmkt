<script>
/**
 * 디바이스 감지 및 화면 크기 쿠키 저장 시스템
 *
 * 모든 페이지에서 자동으로 로드되어 사용자의 화면 크기를 감지하고
 * 쿠키에 저장하여 서버사이드에서 정확한 모바일 감지가 가능하게 합니다.
 */

// 전역 디바이스 감지 객체
window.DeviceDetection = {
    // 설정값
    config: {
        cookieName: 'screen_width',
        cookieExpireDays: 7,
        breakpoints: {
            mobile: 480,    // 480px 이하: 모바일
            tablet: 768,    // 481-768px: 태블릿
            desktop: 769    // 769px 이상: 데스크톱
        },
        debug: false // 개발 시에만 true로 설정
    },
    
    // 현재 디바이스 정보
    current: {
        width: 0,
        height: 0,
        type: 'unknown',
        isMobile: false,
        isTablet: false,
        isDesktop: false
    },
    
    /**
     * 디바이스 감지 초기화
     */
    init: function() {
        this.updateScreenInfo();
        this.setScreenSizeCookie();
        this.bindResizeEvent();
        
        if (this.config.debug) {
            this.logDebugInfo();
        }
    },
    
    /**
     * 화면 크기 정보 업데이트
     */
    updateScreenInfo: function() {
        this.current.width = window.innerWidth;
        this.current.height = window.innerHeight;
        this.current.type = this.getDeviceType();
        this.current.isMobile = this.current.type === 'mobile';
        this.current.isTablet = this.current.type === 'tablet';
        this.current.isDesktop = this.current.type === 'desktop';
    },
    
    /**
     * 디바이스 타입 결정
     */
    getDeviceType: function() {
        const width = this.current.width;
        
        if (width <= this.config.breakpoints.mobile) {
            return 'mobile';
        } else if (width <= this.config.breakpoints.tablet) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    },
    
    /**
     * 화면 크기를 쿠키에 저장
     */
    setScreenSizeCookie: function() {
        const expires = new Date();
        expires.setTime(expires.getTime() + (this.config.cookieExpireDays * 24 * 60 * 60 * 1000));
        
        const cookieValue = `${this.config.cookieName}=${this.current.width}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
        document.cookie = cookieValue;
        
        if (this.config.debug) {
            console.log(`🍪 쿠키 저장: ${this.current.width}px`);
        }
    },
    
    /**
     * 리사이즈 이벤트 바인딩
     */
    bindResizeEvent: function() {
        let resizeTimeout;
        
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const oldType = this.current.type;
                this.updateScreenInfo();
                this.setScreenSizeCookie();
                
                // 디바이스 타입이 변경된 경우 페이지 새로고침 (옵션)
                if (oldType !== this.current.type && this.shouldReloadOnDeviceChange()) {
                    if (this.config.debug) {
                        console.log(`📱 디바이스 타입 변경: ${oldType} → ${this.current.type}, 페이지 새로고침`);
                    }
                    window.location.reload();
                }
                
                if (this.config.debug) {
                    this.logDebugInfo();
                }
            }, 250); // 250ms 디바운싱
        });
    },
    
    /**
     * 디바이스 변경 시 페이지 새로고침 여부 결정
     */
    shouldReloadOnDeviceChange: function() {
        // 강의/이벤트 페이지에서 뷰 파라미터가 없는 경우에만 새로고침
        const currentUrl = window.location.href;
        const isLectureOrEvent = currentUrl.includes('/lectures') || currentUrl.includes('/events');
        const hasViewParam = new URLSearchParams(window.location.search).has('view');
        
        return isLectureOrEvent && !hasViewParam;
    },
    
    /**
     * 모바일에서 자동 뷰 전환 (강의/이벤트 페이지용)
     */
    autoSwitchView: function() {
        const currentUrl = window.location.href;
        const isLectureOrEvent = currentUrl.includes('/lectures') || currentUrl.includes('/events');
        
        if (!isLectureOrEvent) return;
        
        const urlParams = new URLSearchParams(window.location.search);
        const isFirstVisit = !urlParams.has('view');
        const currentView = urlParams.get('view') || 'calendar';
        
        // 첫 방문이고 모바일에서 캘린더 뷰인 경우 목록형으로 전환
        if (isFirstVisit && this.current.isMobile && currentView === 'calendar') {
            urlParams.set('view', 'list');
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            
            if (this.config.debug) {
                console.log('📱 모바일 감지: 목록형 뷰로 자동 전환');
            }
            
            window.location.href = newUrl;
        }
    },
    
    /**
     * CSS 클래스 추가 (body에 디바이스 클래스 적용)
     */
    addDeviceClasses: function() {
        const body = document.body;
        
        // 기존 디바이스 클래스 제거
        body.classList.remove('device-mobile', 'device-tablet', 'device-desktop');
        
        // 현재 디바이스 클래스 추가
        body.classList.add(`device-${this.current.type}`);
        
        // 화면 크기 클래스 추가 (CSS에서 활용 가능)
        body.classList.remove('screen-small', 'screen-medium', 'screen-large');
        if (this.current.width <= 480) {
            body.classList.add('screen-small');
        } else if (this.current.width <= 768) {
            body.classList.add('screen-medium');
        } else {
            body.classList.add('screen-large');
        }
    },
    
    /**
     * 디버그 정보 출력
     */
    logDebugInfo: function() {
        console.group('📱 Device Detection Debug');
        console.log('화면 크기:', `${this.current.width}x${this.current.height}`);
        console.log('디바이스 타입:', this.current.type);
        console.log('분류:', {
            모바일: this.current.isMobile,
            태블릿: this.current.isTablet,
            데스크톱: this.current.isDesktop
        });
        console.log('경계값:', this.config.breakpoints);
        console.groupEnd();
    },
    
    /**
     * 외부에서 호출 가능한 유틸리티 메서드들
     */
    utils: {
        isMobile: function() {
            return window.DeviceDetection.current.isMobile;
        },
        
        isTablet: function() {
            return window.DeviceDetection.current.isTablet;
        },
        
        isDesktop: function() {
            return window.DeviceDetection.current.isDesktop;
        },
        
        getDeviceType: function() {
            return window.DeviceDetection.current.type;
        },
        
        getScreenWidth: function() {
            return window.DeviceDetection.current.width;
        },
        
        getScreenHeight: function() {
            return window.DeviceDetection.current.height;
        }
    }
};

// DOM 로드 완료 후 자동 초기화
document.addEventListener('DOMContentLoaded', function() {
    window.DeviceDetection.init();
    window.DeviceDetection.addDeviceClasses();
    window.DeviceDetection.autoSwitchView();
});

// 즉시 화면 크기 쿠키 설정 (서버사이드에서 즉시 사용 가능)
if (typeof window !== 'undefined') {
    const width = window.innerWidth;
    const expires = new Date();
    expires.setTime(expires.getTime() + (7 * 24 * 60 * 60 * 1000)); // 7일
    document.cookie = `screen_width=${width}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
}
</script>