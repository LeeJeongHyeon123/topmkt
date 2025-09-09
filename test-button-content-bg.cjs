const { chromium } = require('playwright');

async function testButtonContentBackground() {
    console.log('🚀 비밀번호 찾기 버튼 백그라운드 문제 확인');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 1. 비밀번호 찾기 페이지 로딩
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('#forgotPasswordForm', { timeout: 10000 });
        
        // 2. button-content 요소의 스타일 확인
        console.log('🔍 button-content 스타일 확인...');
        const buttonContentStyles = await page.locator('.button-content').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                backgroundColor: styles.backgroundColor,
                background: styles.background,
                backgroundImage: styles.backgroundImage,
                color: styles.color,
                display: styles.display
            };
        });
        
        console.log('📊 button-content 현재 스타일:', buttonContentStyles);
        
        // 3. submit-button 전체의 스타일 확인
        const submitButtonStyles = await page.locator('.submit-button').evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                backgroundColor: styles.backgroundColor,
                background: styles.background,
                backgroundImage: styles.backgroundImage,
                color: styles.color
            };
        });
        
        console.log('📊 submit-button 현재 스타일:', submitButtonStyles);
        
        // 4. 스크린샷 촬영 (문제 상황 기록)
        console.log('📸 문제 상황 스크린샷 촬영...');
        await page.screenshot({ 
            path: 'forgot-password-button-before-fix.png',
            fullPage: true 
        });
        
        // 5. 버튼에 포커스하여 상태 변화 확인
        await page.focus('#submitButton');
        await page.screenshot({ 
            path: 'forgot-password-button-focused.png',
            fullPage: true 
        });
        
        console.log('✅ 테스트 완료 - 스크린샷들 저장됨');
        
        return {
            buttonContentStyles,
            submitButtonStyles,
            hasBgIssue: buttonContentStyles.background.includes('gradient') || 
                       buttonContentStyles.backgroundColor !== 'rgba(0, 0, 0, 0)'
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testButtonContentBackground()
    .then(results => {
        console.log('\n📊 최종 결과:', results);
        if (results.hasBgIssue) {
            console.log('❌ button-content에 불필요한 백그라운드가 적용되어 있음');
        } else {
            console.log('✅ button-content 백그라운드 정상');
        }
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });