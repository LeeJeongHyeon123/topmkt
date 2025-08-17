/**
 * 개발 테스트용 자동 로그인 세션 헬퍼
 * 
 * 사용법:
 * const helper = new LoginSessionHelper();
 * const { page, context, browser } = await helper.getLoggedInSession('우리집탄이');
 * 
 * 지원 계정:
 * - '우리집탄이': 관리자 계정
 * - '일반사용자': 일반 사용자 계정
 * - '기업사용자': 기업 회원 계정
 * 
 * @author Claude (Anthropic)
 * @date 2025-08-15
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

class LoginSessionHelper {
    constructor() {
        this.baseUrl = 'https://www.topmktx.com';
        this.sessionDir = path.join(__dirname, 'sessions');
        
        // 테스트 계정 정보
        this.accounts = {
            '우리집탄이': {
                phone: '010-2659-1346',
                password: 'dnlszkem1!',
                role: 'admin',
                description: '관리자 계정'
            },
            '일반사용자': {
                phone: '010-1234-5678',
                password: 'test123!',
                role: 'user',
                description: '일반 사용자 계정'
            },
            '기업사용자': {
                phone: '010-9876-5432',
                password: 'corp123!',
                role: 'corporate',
                description: '기업 회원 계정'
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
     * 로그인된 세션 반환 (메인 함수)
     * 
     * @param {string} accountName - 계정명 ('우리집탄이', '일반사용자', '기업사용자')
     * @param {Object} options - 옵션 설정
     * @returns {Promise<Object>} - { page, context, browser, account }
     */
    async getLoggedInSession(accountName = '우리집탄이', options = {}) {
        const defaultOptions = {
            headless: true,
            timeout: 30000,
            saveSession: true,
            loadSavedSession: true
        };
        
        const config = { ...defaultOptions, ...options };
        
        console.log(`🔐 "${accountName}" 계정으로 로그인 세션 준비...`);
        
        const account = this.accounts[accountName];
        if (!account) {
            throw new Error(`❌ 지원하지 않는 계정명: ${accountName}\n지원 계정: ${Object.keys(this.accounts).join(', ')}`);
        }
        
        console.log(`📋 계정 정보: ${account.description} (${account.phone})`);
        
        const browser = await chromium.launch({
            headless: config.headless,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        let context, page;
        
        try {
            // 1. 저장된 세션 로드 시도
            if (config.loadSavedSession) {
                const sessionResult = await this.loadSavedSession(browser, accountName);
                if (sessionResult) {
                    console.log('✅ 저장된 로그인 세션 로드 성공');
                    return {
                        page: sessionResult.page,
                        context: sessionResult.context,
                        browser,
                        account,
                        fromSavedSession: true
                    };
                }
            }
            
            // 2. 새로운 로그인 수행
            console.log('🔄 새로운 로그인 수행...');
            const loginResult = await this.performLogin(browser, account, config);
            
            // 3. 세션 저장
            if (config.saveSession && loginResult.success) {
                await this.saveSession(loginResult.context, accountName);
                console.log('💾 로그인 세션 저장 완료');
            }
            
            if (!loginResult.success) {
                throw new Error(`❌ ${accountName} 계정 로그인 실패: ${loginResult.error}`);
            }
            
            console.log('✅ 새로운 로그인 세션 생성 성공');
            
            return {
                page: loginResult.page,
                context: loginResult.context,
                browser,
                account,
                fromSavedSession: false
            };
            
        } catch (error) {
            console.error('❌ 로그인 세션 생성 오류:', error.message);
            await browser.close();
            throw error;
        }
    }
    
    /**
     * 실제 로그인 수행
     * 
     * @param {Object} browser - Playwright 브라우저 객체
     * @param {Object} account - 계정 정보
     * @param {Object} config - 설정
     * @returns {Promise<Object>} - 로그인 결과
     */
    async performLogin(browser, account, config) {
        const context = await browser.newContext();
        const page = await context.newPage();
        
        try {
            // 로그인 페이지 이동
            console.log('📄 로그인 페이지 접근...');
            await page.goto(`${this.baseUrl}/auth/login`, {
                waitUntil: 'networkidle',
                timeout: config.timeout
            });
            
            // 로그인 폼 입력
            console.log('📝 로그인 정보 입력...');
            await page.fill('#phone', account.phone);
            await page.fill('#password', account.password);
            
            // 로그인 버튼 클릭
            console.log('🔘 로그인 버튼 클릭...');
            await page.click('button[type="submit"]');
            
            // 로그인 결과 대기
            await page.waitForTimeout(3000);
            
            // 로그인 성공 확인
            const currentUrl = page.url();
            const isLoginPage = currentUrl.includes('/auth/login');
            
            if (isLoginPage) {
                // 로그인 실패 메시지 확인
                const errorMessage = await page.textContent('.alert-danger, .error-message').catch(() => null);
                return {
                    success: false,
                    error: errorMessage || '로그인 실패 (로그인 페이지에 남아있음)',
                    page: null,
                    context: null
                };
            }
            
            console.log('✅ 로그인 성공 - 현재 URL:', currentUrl);
            
            return {
                success: true,
                page,
                context,
                redirectUrl: currentUrl
            };
            
        } catch (error) {
            await context.close();
            return {
                success: false,
                error: error.message,
                page: null,
                context: null
            };
        }
    }
    
    /**
     * 세션 저장
     * 
     * @param {Object} context - Playwright 컨텍스트
     * @param {string} accountName - 계정명
     */
    async saveSession(context, accountName) {
        try {
            const sessionFile = path.join(this.sessionDir, `${accountName}_session.json`);
            const cookies = await context.cookies();
            const sessionData = {
                cookies,
                timestamp: Date.now(),
                accountName,
                userAgent: await context.evaluate(() => navigator.userAgent)
            };
            
            fs.writeFileSync(sessionFile, JSON.stringify(sessionData, null, 2));
            console.log(`💾 세션 저장: ${sessionFile}`);
        } catch (error) {
            console.warn('⚠️ 세션 저장 실패:', error.message);
        }
    }
    
    /**
     * 저장된 세션 로드
     * 
     * @param {Object} browser - Playwright 브라우저
     * @param {string} accountName - 계정명
     * @returns {Promise<Object|null>} - 로드된 세션 또는 null
     */
    async loadSavedSession(browser, accountName) {
        try {
            const sessionFile = path.join(this.sessionDir, `${accountName}_session.json`);
            
            if (!fs.existsSync(sessionFile)) {
                console.log('📭 저장된 세션 없음');
                return null;
            }
            
            const sessionData = JSON.parse(fs.readFileSync(sessionFile, 'utf8'));
            
            // 세션이 24시간 이상 지났으면 무효
            const sessionAge = Date.now() - sessionData.timestamp;
            const maxAge = 24 * 60 * 60 * 1000; // 24시간
            
            if (sessionAge > maxAge) {
                console.log('⏰ 저장된 세션이 만료됨 (24시간 초과)');
                fs.unlinkSync(sessionFile);
                return null;
            }
            
            console.log('🔄 저장된 세션 로드 중...');
            
            const context = await browser.newContext();
            await context.addCookies(sessionData.cookies);
            
            const page = await context.newPage();
            
            // 세션 유효성 검증 (메인 페이지 접근)
            await page.goto(this.baseUrl, { waitUntil: 'networkidle', timeout: 10000 });
            await page.waitForTimeout(2000);
            
            // 로그인 상태 확인
            const isLoggedIn = !page.url().includes('/auth/login');
            
            if (!isLoggedIn) {
                console.log('❌ 저장된 세션이 무효함');
                await context.close();
                fs.unlinkSync(sessionFile);
                return null;
            }
            
            console.log('✅ 저장된 세션 유효성 확인 완료');
            
            return { page, context };
            
        } catch (error) {
            console.log('❌ 저장된 세션 로드 실패:', error.message);
            return null;
        }
    }
    
    /**
     * 특정 계정의 저장된 세션 삭제
     * 
     * @param {string} accountName - 계정명
     */
    clearSavedSession(accountName) {
        try {
            const sessionFile = path.join(this.sessionDir, `${accountName}_session.json`);
            if (fs.existsSync(sessionFile)) {
                fs.unlinkSync(sessionFile);
                console.log(`🗑️ ${accountName} 세션 삭제 완료`);
            } else {
                console.log(`📭 ${accountName} 저장된 세션 없음`);
            }
        } catch (error) {
            console.error('❌ 세션 삭제 실패:', error.message);
        }
    }
    
    /**
     * 모든 저장된 세션 삭제
     */
    clearAllSessions() {
        try {
            const files = fs.readdirSync(this.sessionDir);
            let cleared = 0;
            
            for (const file of files) {
                if (file.endsWith('_session.json')) {
                    fs.unlinkSync(path.join(this.sessionDir, file));
                    cleared++;
                }
            }
            
            console.log(`🗑️ ${cleared}개의 저장된 세션 삭제 완료`);
        } catch (error) {
            console.error('❌ 세션 삭제 실패:', error.message);
        }
    }
    
    /**
     * 지원되는 계정 목록 출력
     */
    listAccounts() {
        console.log('📋 지원되는 테스트 계정:');
        for (const [name, info] of Object.entries(this.accounts)) {
            console.log(`  - ${name}: ${info.description} (${info.phone})`);
        }
    }
    
    /**
     * 세션 정리 및 브라우저 종료
     * 
     * @param {Object} sessionData - getLoggedInSession()에서 반환된 객체
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
            console.log('🧹 세션 정리 완료');
        } catch (error) {
            console.warn('⚠️ 세션 정리 중 오류:', error.message);
        }
    }
}

module.exports = LoginSessionHelper;

// CLI에서 직접 실행 시 테스트
if (require.main === module) {
    async function testHelper() {
        const helper = new LoginSessionHelper();
        
        console.log('🧪 LoginSessionHelper 테스트 시작\n');
        
        // 계정 목록 출력
        helper.listAccounts();
        console.log('');
        
        try {
            // 우리집탄이 계정으로 테스트
            console.log('🔐 우리집탄이 계정 로그인 테스트...');
            const session = await helper.getLoggedInSession('우리집탄이');
            
            // 관리자 페이지 접근 테스트
            console.log('👨‍💼 관리자 페이지 접근 테스트...');
            await session.page.goto('https://www.topmktx.com/admin/users');
            await session.page.waitForTimeout(3000);
            
            const pageTitle = await session.page.title();
            console.log('📄 페이지 제목:', pageTitle);
            
            const currentUrl = session.page.url();
            console.log('🔗 현재 URL:', currentUrl);
            
            // 스크린샷 저장
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/login_helper_test.png',
                fullPage: true 
            });
            console.log('📸 테스트 스크린샷 저장: login_helper_test.png');
            
            console.log('✅ LoginSessionHelper 테스트 성공!');
            
            // 정리
            await helper.cleanup(session);
            
        } catch (error) {
            console.error('❌ 테스트 실패:', error.message);
            process.exit(1);
        }
    }
    
    testHelper();
}