/**
 * JWT 인증 관리 클라이언트 사이드 스크립트
 * 
 * 기능:
 * - 자동 토큰 갱신
 * - 토큰 만료 감지
 * - 로그인 상태 모니터링
 * - 백그라운드 하트비트
 * 
 * @author Claude Code
 * @version 1.0
 */

class JWTAuth {
    constructor() {
        this.refreshInterval = null;
        this.heartbeatInterval = null;
        this.tokenExpiryWarning = false;
        
        // 설정
        this.config = {
            refreshBeforeExpiry: 5 * 60 * 1000, // 5분 전에 갱신
            heartbeatInterval: 5 * 60 * 1000,   // 5분마다 하트비트
            warningBeforeExpiry: 10 * 60 * 1000 // 10분 전에 경고
        };
        
        this.init();
    }
    
    /**
     * 초기화
     */
    init() {
        // 페이지 로드 시 토큰 상태 확인
        this.checkTokenStatus();
        
        // 자동 갱신 시작
        this.startTokenRefresh();
        
        // 하트비트 시작
        this.startHeartbeat();
        
        // 페이지 가시성 변경 이벤트 리스너
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // 페이지가 다시 활성화되면 토큰 상태 확인
                this.checkTokenStatus();
            }
        });
        
        // 윈도우 포커스 이벤트
        window.addEventListener('focus', () => {
            this.checkTokenStatus();
        });
        
        console.log('JWT Auth initialized');
    }
    
    /**
     * JWT 토큰 존재 여부 확인
     */
    hasJWTToken() {
        // 쿠키에서 access_token 또는 refresh_token 확인
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name] = cookie.trim().split('=');
            if (name === 'access_token' || name === 'refresh_token') {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 현재 토큰 상태 확인
     */
    async checkTokenStatus() {
        // JWT 토큰이 있는지 먼저 확인
        if (!this.hasJWTToken()) {
            return false;
        }
        
        try {
            const response = await fetch('/auth/me', {
                method: 'GET',
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.token_info) {
                    const expiresIn = data.token_info.expires_in * 1000; // 밀리초로 변환
                    
                    // 만료 임박 경고
                    if (expiresIn <= this.config.warningBeforeExpiry && !this.tokenExpiryWarning) {
                        this.showTokenExpiryWarning(expiresIn);
                        this.tokenExpiryWarning = true;
                    }
                    
                    // 토큰 갱신이 필요한 경우
                    if (expiresIn <= this.config.refreshBeforeExpiry) {
                        await this.refreshToken();
                    }
                    
                    return true;
                }
            }
            
            // 토큰이 유효하지 않은 경우
            if (response.status === 401) {
                await this.handleTokenExpiry();
                return false;
            }
            
        } catch (error) {
            console.error('Token status check failed:', error);
        }
        
        return false;
    }
    
    /**
     * 토큰 갱신
     */
    async refreshToken() {
        try {
            const response = await fetch('/auth/refresh', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    console.log('Token refreshed successfully');
                    this.tokenExpiryWarning = false; // 경고 리셋
                    
                    // 토큰 갱신 성공 이벤트 발생
                    this.dispatchEvent('tokenRefreshed', data);
                    
                    return true;
                }
            }
            
            // 리프레시 실패
            if (response.status === 401) {
                await this.handleTokenExpiry();
            }
            
        } catch (error) {
            console.error('Token refresh failed:', error);
        }
        
        return false;
    }
    
    /**
     * 토큰 만료 처리
     */
    async handleTokenExpiry() {
        console.log('Token expired or not found');
        
        // 갱신 및 하트비트 중지
        this.stopTokenRefresh();
        this.stopHeartbeat();
        
        // 토큰 만료 이벤트 발생
        this.dispatchEvent('tokenExpired');
        
        // 현재 페이지가 로그인 관련 페이지가 아닌 경우에만 리다이렉트
        const currentUrl = window.location.pathname + window.location.search;
        const isAuthPage = currentUrl.startsWith('/auth/');
        
        if (!isAuthPage) {
            console.log('Redirecting to login');
            localStorage.setItem('jwt_redirect_after_login', currentUrl);
            window.location.href = '/auth/login';
        }
    }
    
    /**
     * 토큰 만료 경고 표시 (콘솔 로그만)
     */
    showTokenExpiryWarning(expiresIn) {
        const minutes = Math.floor(expiresIn / (60 * 1000));
        console.warn(`Token expires in ${minutes} minutes`);
    }
    
    /**
     * 자동 토큰 갱신 시작
     */
    startTokenRefresh() {
        // 기존 인터벌 클리어
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        
        // 1분마다 토큰 상태 확인
        this.refreshInterval = setInterval(() => {
            if (!document.hidden) { // 페이지가 활성화된 경우에만
                this.checkTokenStatus();
            }
        }, 60 * 1000); // 1분
    }
    
    /**
     * 자동 토큰 갱신 중지
     */
    stopTokenRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
    
    /**
     * 하트비트 시작 (서버에 활동 신호 전송)
     */
    startHeartbeat() {
        // 기존 인터벌 클리어
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }
        
        // 5분마다 하트비트 전송
        this.heartbeatInterval = setInterval(() => {
            if (!document.hidden) { // 페이지가 활성화된 경우에만
                this.sendHeartbeat();
            }
        }, this.config.heartbeatInterval);
    }
    
    /**
     * 하트비트 중지
     */
    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    }
    
    /**
     * 서버에 하트비트 전송
     */
    async sendHeartbeat() {
        // JWT 토큰이 있는 경우에만 하트비트 전송
        if (!this.hasJWTToken()) {
            return;
        }
        
        try {
            const response = await fetch('/auth/me', {
                method: 'GET',
                credentials: 'include'
            });
            
            if (response.ok) {
                console.log('Heartbeat sent successfully');
            } else if (response.status === 401) {
                // 인증 실패시 토큰 만료 처리
                await this.handleTokenExpiry();
            }
        } catch (error) {
            console.error('Heartbeat failed:', error);
        }
    }
    
    /**
     * 로그인 성공 시 호출
     */
    onLoginSuccess() {
        console.log('Login successful, starting JWT auth');
        
        // 토큰 관리 시작
        this.startTokenRefresh();
        this.startHeartbeat();
        
        // 저장된 리다이렉트 URL 확인
        const redirectUrl = localStorage.getItem('jwt_redirect_after_login');
        if (redirectUrl) {
            localStorage.removeItem('jwt_redirect_after_login');
            window.location.href = redirectUrl;
        }
    }
    
    /**
     * 로그아웃 시 호출
     */
    onLogout() {
        console.log('Logout, stopping JWT auth');
        
        // 모든 인터벌 중지
        this.stopTokenRefresh();
        this.stopHeartbeat();
        
        // 로컬 스토리지 정리
        localStorage.removeItem('jwt_redirect_after_login');
    }
    
    /**
     * 커스텀 이벤트 발생
     */
    dispatchEvent(eventName, data = null) {
        const event = new CustomEvent(`jwt:${eventName}`, {
            detail: data
        });
        document.dispatchEvent(event);
    }
    
    // 브라우저 알림 기능 제거됨
    
    /**
     * 디버그 정보 출력
     */
    debug() {
        return {
            refreshInterval: !!this.refreshInterval,
            heartbeatInterval: !!this.heartbeatInterval,
            tokenExpiryWarning: this.tokenExpiryWarning,
            config: this.config,
            documentHidden: document.hidden
        };
    }
}

// 전역 인스턴스 생성
let jwtAuth = null;

// DOM 로드 완료 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // JWT 인증 시스템 초기화
    jwtAuth = new JWTAuth();
    
    // 전역 함수로 노출
    window.jwtAuth = jwtAuth;
    
    // JWT 이벤트 리스너 등록
    document.addEventListener('jwt:tokenRefreshed', function(event) {
        console.log('JWT token refreshed:', event.detail);
    });
    
    document.addEventListener('jwt:tokenExpired', function(event) {
        console.log('JWT token expired');
        // 필요시 추가 처리
    });
});

// 페이지 언로드 시 정리
window.addEventListener('beforeunload', function() {
    if (jwtAuth) {
        jwtAuth.stopTokenRefresh();
        jwtAuth.stopHeartbeat();
    }
});

// 로그인 폼 제출 시 JWT 인증 시작
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // 폼 제출 후 성공 시 JWT 인증 시작
            setTimeout(() => {
                if (jwtAuth && window.location.pathname !== '/auth/login') {
                    jwtAuth.onLoginSuccess();
                }
            }, 1000);
        });
    }
});

console.log('JWT Auth script loaded');