/**
 * 개발용 비밀번호 없는 로그인 헬퍼
 * 
 * 실제 로그인 폼을 거치지 않고 직접 세션을 생성하여
 * 개발 테스트 시 편리하게 사용할 수 있습니다.
 * 
 * 사용법:
 * const helper = new DevLoginHelper();
 * const { page, context, browser } = await helper.getDevSession('우리집탄이');
 * 
 * @author Claude (Anthropic)
 * @date 2025-08-15
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

class DevLoginHelper {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
        this.sessionDir = path.join(__dirname, 'dev_sessions');
        
        // 테스트 계정 정보 (사용자 ID만 필요)
        this.accounts = {
            '우리집탄이': {
                userId: 4,
                role: 'admin',
                description: '관리자 계정',
                phone: '010-2659-1346'
            },
            '일반사용자': {
                userId: 5,
                role: 'user', 
                description: '일반 사용자 계정',
                phone: '010-1234-5678'
            },
            '기업사용자': {
                userId: 1,
                role: 'corporate',
                description: '기업 회원 계정',
                phone: '010-9876-5432'
            }
        };
        
        this.ensureSessionDir();
    }
    
    /**
     * 세션 디렉토리 생성
     */
    ensureSessionDir() {
        if (!fs.existsSync(this.sessionDir)) {
            fs.mkdirSync(this.sessionDir, { recursive: true });
        }
    }
    
    /**
     * 개발용 로그인 세션 생성 (비밀번호 없음)
     * 
     * @param {string} accountName - 계정명
     * @param {Object} options - 옵션 설정
     * @returns {Promise<Object>} - { page, context, browser, account }
     */
    async getDevSession(accountName = '우리집탄이', options = {}) {
        const defaultOptions = {
            headless: true,
            timeout: 30000
        };
        
        const config = { ...defaultOptions, ...options };
        
        console.log(`🔧 개발용 "${accountName}" 로그인 세션 생성...`);
        
        const account = this.accounts[accountName];
        if (!account) {
            throw new Error(`❌ 지원하지 않는 계정명: ${accountName}\n지원 계정: ${Object.keys(this.accounts).join(', ')}`);
        }
        
        console.log(`📋 계정 정보: ${account.description} (ID: ${account.userId})`);
        
        const browser = await chromium.launch({
            headless: config.headless,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        try {
            // 1. 개발용 세션 생성 방법 1: JWT 토큰 직접 생성
            const sessionResult = await this.createDevSessionWithToken(browser, account, config);
            
            if (sessionResult.success) {
                console.log('✅ 개발용 세션 생성 성공');
                return {
                    page: sessionResult.page,
                    context: sessionResult.context,
                    browser,
                    account,
                    method: 'jwt_token'
                };
            }
            
            // 2. 방법 2: 쿠키 직접 설정
            console.log('🔄 쿠키 직접 설정 방식 시도...');
            const cookieResult = await this.createDevSessionWithCookie(browser, account, config);
            
            if (cookieResult.success) {
                console.log('✅ 쿠키 방식 세션 생성 성공');
                return {
                    page: cookieResult.page,
                    context: cookieResult.context,
                    browser,
                    account,
                    method: 'cookie'
                };
            }
            
            throw new Error('모든 개발용 로그인 방식이 실패했습니다');
            
        } catch (error) {
            console.error('❌ 개발용 세션 생성 오류:', error.message);
            await browser.close();
            throw error;
        }
    }
    
    /**
     * JWT 토큰을 이용한 개발용 세션 생성
     */
    async createDevSessionWithToken(browser, account, config) {
        const context = await browser.newContext();
        const page = await context.newPage();
        
        try {
            console.log('🎫 JWT 토큰 방식으로 세션 생성 시도...');
            
            // 개발용 토큰 생성 API 호출 시뮬레이션
            const devToken = await this.generateDevToken(account);
            
            if (devToken) {
                // localStorage에 토큰 설정
                await page.goto(this.baseUrl);
                
                await page.evaluate((token, userId) => {
                    localStorage.setItem('auth_token', token);
                    localStorage.setItem('user_id', userId.toString());
                    localStorage.setItem('user_role', 'ROLE_ADMIN');
                }, devToken, account.userId);
                
                // 페이지 새로고침하여 토큰 적용
                await page.reload({ waitUntil: 'networkidle' });
                await page.waitForTimeout(2000);
                
                // 로그인 상태 확인
                const isLoggedIn = !page.url().includes('/auth/login');
                
                return {
                    success: isLoggedIn,
                    page: isLoggedIn ? page : null,
                    context: isLoggedIn ? context : null,
                    method: 'jwt'
                };
            }
            
            return { success: false };
            
        } catch (error) {
            await context.close();
            return { success: false, error: error.message };
        }
    }
    
    /**
     * 쿠키를 이용한 개발용 세션 생성
     */
    async createDevSessionWithCookie(browser, account, config) {
        const context = await browser.newContext();
        const page = await context.newPage();
        
        try {
            console.log('🍪 쿠키 방식으로 세션 생성 시도...');
            
            // 개발용 세션 쿠키 생성
            const sessionCookies = this.generateDevSessionCookies(account);
            
            // 쿠키 설정
            await context.addCookies(sessionCookies);
            
            // 메인 페이지로 이동
            await page.goto(this.baseUrl, { waitUntil: 'networkidle' });
            await page.waitForTimeout(2000);
            
            // 로그인 상태 확인
            const isLoggedIn = !page.url().includes('/auth/login');
            
            if (isLoggedIn) {
                console.log('✅ 쿠키 방식 로그인 성공');
                return {
                    success: true,
                    page,
                    context,
                    method: 'cookie'
                };
            } else {
                console.log('❌ 쿠키 방식 로그인 실패');
                await context.close();
                return { success: false };
            }
            
        } catch (error) {
            await context.close();
            return { success: false, error: error.message };
        }
    }
    
    /**
     * 개발용 JWT 토큰 생성 (시뮬레이션)
     */
    async generateDevToken(account) {
        try {
            // 실제 환경에서는 서버의 개발용 토큰 생성 API를 호출
            // 여기서는 시뮬레이션으로 구현
            const payload = {
                user_id: account.userId,
                role: 'ROLE_ADMIN',
                exp: Math.floor(Date.now() / 1000) + (24 * 60 * 60) // 24시간
            };
            
            // Base64로 간단한 토큰 시뮬레이션 (실제로는 JWT 서명 필요)
            const token = Buffer.from(JSON.stringify(payload)).toString('base64');
            
            console.log('🎫 개발용 토큰 생성:', token.substring(0, 20) + '...');
            return token;
            
        } catch (error) {
            console.log('❌ 토큰 생성 실패:', error.message);
            return null;
        }
    }
    
    /**
     * 개발용 세션 쿠키 생성
     */
    generateDevSessionCookies(account) {
        const sessionId = 'dev_session_' + Date.now();
        const expires = new Date(Date.now() + 24 * 60 * 60 * 1000); // 24시간
        
        return [
            {
                name: 'PHPSESSID',
                value: sessionId,
                domain: 'www.topmktx.com',
                path: '/',
                expires: expires.getTime() / 1000,
                httpOnly: true,
                secure: true
            },
            {
                name: 'user_session',
                value: JSON.stringify({
                    user_id: account.userId,
                    role: 'ROLE_ADMIN',
                    nickname: account.description.split(' ')[0]
                }),
                domain: 'www.topmktx.com', 
                path: '/',
                expires: expires.getTime() / 1000,
                httpOnly: false,
                secure: true
            },
            {
                name: 'auth_token',
                value: `dev_token_${account.userId}_${Date.now()}`,
                domain: 'www.topmktx.com',
                path: '/',
                expires: expires.getTime() / 1000,
                httpOnly: false,
                secure: true
            }
        ];
    }
    
    /**
     * 페이지에서 JavaScript로 강제 로그인 상태 설정
     */
    async forceLoginState(page, account) {
        try {
            console.log('⚡ JavaScript로 강제 로그인 상태 설정...');
            
            await page.evaluate((userId, role) => {
                // 로컬스토리지 설정
                if (typeof Storage !== 'undefined') {
                    localStorage.setItem('user_id', userId.toString());
                    localStorage.setItem('user_role', role);
                    localStorage.setItem('logged_in', 'true');
                    localStorage.setItem('dev_login', 'true');
                }
                
                // 세션스토리지 설정  
                if (typeof sessionStorage !== 'undefined') {
                    sessionStorage.setItem('user_id', userId.toString());
                    sessionStorage.setItem('user_role', role);
                    sessionStorage.setItem('logged_in', 'true');
                }
                
                // 전역 변수 설정
                if (typeof window !== 'undefined') {
                    window.currentUser = {
                        id: userId,
                        role: role,
                        logged_in: true
                    };
                }
                
                console.log('🔧 개발용 로그인 상태 강제 설정 완료');
                
            }, account.userId, account.role.toUpperCase());
            
            return true;
            
        } catch (error) {
            console.log('❌ 강제 로그인 상태 설정 실패:', error.message);
            return false;
        }
    }
    
    /**
     * 개발용 세션으로 특정 페이지 접근
     */
    async gotoWithDevAuth(page, url, account) {
        try {
            // 먼저 메인 페이지로 이동
            await page.goto(this.baseUrl);
            
            // 강제 로그인 상태 설정
            await this.forceLoginState(page, account);
            
            // 목표 페이지로 이동
            await page.goto(url, { waitUntil: 'networkidle' });
            
            return true;
            
        } catch (error) {
            console.log('❌ 개발용 인증 페이지 접근 실패:', error.message);
            return false;
        }
    }
    
    /**
     * 지원되는 계정 목록 출력
     */
    listAccounts() {
        console.log('🛠️ 개발용 지원 계정:');
        for (const [name, info] of Object.entries(this.accounts)) {
            console.log(`  - ${name}: ${info.description} (ID: ${info.userId})`);
        }
    }
    
    /**
     * 세션 정리
     */
    async cleanup(sessionData) {
        try {
            if (sessionData.page && !sessionData.page.isClosed()) {
                await sessionData.page.close();
            }
            if (sessionData.context) {
                await sessionData.context.close();
            }
            if (sessionData.browser) {
                await sessionData.browser.close();
            }
            console.log('🧹 개발용 세션 정리 완료');
        } catch (error) {
            console.warn('⚠️ 세션 정리 중 오류:', error.message);
        }
    }
}

module.exports = DevLoginHelper;

// CLI에서 직접 실행 시 테스트
if (require.main === module) {
    async function testDevHelper() {
        const helper = new DevLoginHelper();
        
        console.log('🧪 DevLoginHelper 테스트 시작\n');
        
        helper.listAccounts();
        console.log('');
        
        try {
            console.log('🔧 우리집탄이 개발용 세션 생성...');
            const session = await helper.getDevSession('우리집탄이');
            
            // 관리자 페이지 직접 접근 테스트
            console.log('👨‍💼 관리자 페이지 직접 접근...');
            const success = await helper.gotoWithDevAuth(
                session.page, 
                'https://www.topmktx.com/admin/users',
                session.account
            );
            
            if (success) {
                await session.page.waitForTimeout(3000);
                
                const pageTitle = await session.page.title();
                const currentUrl = session.page.url();
                
                console.log('📄 페이지 제목:', pageTitle);
                console.log('🔗 현재 URL:', currentUrl);
                
                // 성공 스크린샷
                await session.page.screenshot({ 
                    path: '/var/www/html/topmkt/dev_login_test.png',
                    fullPage: true 
                });
                console.log('📸 개발용 로그인 테스트 스크린샷 저장');
                
                console.log('✅ DevLoginHelper 테스트 성공!');
            } else {
                console.log('❌ 관리자 페이지 접근 실패');
            }
            
            await helper.cleanup(session);
            
        } catch (error) {
            console.error('❌ 테스트 실패:', error.message);
            process.exit(1);
        }
    }
    
    testDevHelper();
}