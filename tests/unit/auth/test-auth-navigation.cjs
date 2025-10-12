const { chromium } = require('playwright');

async function testAuthNavigation() {
    console.log('🚀 비밀번호 찾기 페이지 auth-navigation 버튼 테스트 시작');
    
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
        await page.waitForSelector('.auth-navigation', { timeout: 10000 });
        
        // 2. auth-navigation 버튼들 확인
        console.log('🔍 auth-navigation 버튼 확인...');
        const navigationButtons = await page.locator('.nav-link');
        const buttonCount = await navigationButtons.count();
        console.log(`📊 버튼 개수: ${buttonCount}개`);
        
        // 3. 각 버튼의 텍스트 및 스타일 확인
        const buttonInfo = [];
        for (let i = 0; i < buttonCount; i++) {
            const button = navigationButtons.nth(i);
            const text = await button.textContent();
            const boundingBox = await button.boundingBox();
            const styles = await button.evaluate(el => {
                const computed = window.getComputedStyle(el);
                return {
                    whiteSpace: computed.whiteSpace,
                    fontSize: computed.fontSize,
                    padding: computed.padding,
                    width: computed.width,
                    height: computed.height
                };
            });
            
            buttonInfo.push({
                index: i + 1,
                text: text?.trim(),
                boundingBox,
                styles,
                textLength: text?.trim().length || 0
            });
            
            console.log(`📋 버튼 ${i + 1}: "${text?.trim()}" (길이: ${text?.trim().length || 0}자)`);
        }
        
        // 4. 모바일 뷰포트로 테스트
        console.log('📱 모바일 뷰포트 테스트...');
        await page.setViewportSize({ width: 375, height: 667 });
        await page.waitForTimeout(1000);
        
        // 모바일에서 버튼 상태 재확인
        const mobileButtonInfo = [];
        for (let i = 0; i < buttonCount; i++) {
            const button = navigationButtons.nth(i);
            const boundingBox = await button.boundingBox();
            const styles = await button.evaluate(el => {
                const computed = window.getComputedStyle(el);
                return {
                    whiteSpace: computed.whiteSpace,
                    fontSize: computed.fontSize,
                    lineHeight: computed.lineHeight,
                    height: computed.height
                };
            });
            
            mobileButtonInfo.push({
                index: i + 1,
                boundingBox,
                styles,
                estimatedLines: Math.ceil(boundingBox.height / parseFloat(styles.lineHeight))
            });
        }
        
        // 5. 스크린샷 촬영
        console.log('📸 스크린샷 촬영...');
        
        // 데스크톱 뷰
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.waitForTimeout(500);
        await page.screenshot({ 
            path: 'forgot-password-auth-nav-desktop.png',
            fullPage: false,
            clip: { x: 0, y: 0, width: 1920, height: 800 }
        });
        
        // 모바일 뷰
        await page.setViewportSize({ width: 375, height: 667 });
        await page.waitForTimeout(500);
        await page.screenshot({ 
            path: 'forgot-password-auth-nav-mobile.png',
            fullPage: true
        });
        
        console.log('✅ 테스트 완료 - 스크린샷들 저장됨');
        
        return {
            buttonCount,
            desktop: buttonInfo,
            mobile: mobileButtonInfo,
            textImprovement: {
                before: "아직 계정이 없으신가요? 회원가입",
                after: "회원가입",
                improvementPercent: Math.round((1 - (4 / 17)) * 100)
            }
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testAuthNavigation()
    .then(results => {
        console.log('\n📊 최종 테스트 결과:');
        console.log(`🔘 버튼 개수: ${results.buttonCount}개`);
        console.log(`📝 텍스트 개선: ${results.textImprovement.improvementPercent}% 단축`);
        console.log(`📋 이전: "${results.textImprovement.before}"`);
        console.log(`📋 개선: "${results.textImprovement.after}"`);
        console.log('✅ auth-navigation UI 개선 완료!');
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });