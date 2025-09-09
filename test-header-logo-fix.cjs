const { chromium } = require('playwright');

async function testHeaderLogoFix() {
    console.log('🚀 비밀번호 찾기 페이지 헤더 로고 수정 검증 시작');
    
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
        
        // 1. 비밀번호 찾기 페이지에서 헤더 로고 확인
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('.main-header', { timeout: 10000 });
        
        console.log('🔍 헤더 로고 스타일 분석...');
        
        // 로고 링크 스타일 확인
        const logoLinkStyles = await page.locator('.logo-link').first().evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                textDecoration: styles.textDecoration,
                transition: styles.transition
            };
        });
        
        // 로고 텍스트 스타일 확인
        const logoTextStyles = await page.locator('.logo-text').first().evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                fontWeight: styles.fontWeight,
                opacity: styles.opacity
            };
        });
        
        // 헤더 로켓 아이콘 스타일 확인
        const headerRocketStyles = await page.locator('.header-rocket').first().evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                animation: styles.animation,
                transform: styles.transform,
                transition: styles.transition
            };
        });
        
        console.log('📊 헤더 로고 현재 스타일:');
        console.log('로고 링크:', logoLinkStyles);
        console.log('로고 텍스트:', logoTextStyles);
        console.log('헤더 로켓:', headerRocketStyles);
        
        // 2. 메인 페이지의 헤더 로고와 비교
        console.log('🏠 메인 페이지와 비교...');
        await page.goto('https://www.topmktx.com/');
        await page.waitForSelector('.main-header', { timeout: 10000 });
        
        // 메인 페이지 로고 스타일 확인
        const mainPageLogoLinkStyles = await page.locator('.logo-link').first().evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                textDecoration: styles.textDecoration
            };
        });
        
        const mainPageLogoTextStyles = await page.locator('.logo-text').first().evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                fontWeight: styles.fontWeight
            };
        });
        
        const mainPageHeaderRocketStyles = await page.locator('.header-rocket').first().evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                animation: styles.animation
            };
        });
        
        console.log('📊 메인 페이지 헤더 로고 스타일:');
        console.log('로고 링크:', mainPageLogoLinkStyles);
        console.log('로고 텍스트:', mainPageLogoTextStyles);
        console.log('헤더 로켓:', mainPageHeaderRocketStyles);
        
        // 3. 스타일 일관성 검증
        const isColorConsistent = logoLinkStyles.color === mainPageLogoLinkStyles.color &&
                                 logoTextStyles.color === mainPageLogoTextStyles.color &&
                                 headerRocketStyles.color === mainPageHeaderRocketStyles.color;
        
        // 4. 스크린샷 촬영
        console.log('📸 비교 스크린샷 촬영...');
        
        // 비밀번호 찾기 페이지 헤더
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('.main-header', { timeout: 5000 });
        await page.screenshot({ 
            path: 'forgot-password-header-after-fix.png',
            fullPage: false,
            clip: { x: 0, y: 0, width: 1920, height: 120 }
        });
        
        // 메인 페이지 헤더 (비교용)
        await page.goto('https://www.topmktx.com/');
        await page.waitForSelector('.main-header', { timeout: 5000 });
        await page.screenshot({ 
            path: 'main-page-header-reference.png',
            fullPage: false,
            clip: { x: 0, y: 0, width: 1920, height: 120 }
        });
        
        console.log('✅ 테스트 완료 - 스크린샷들 저장됨');
        
        return {
            forgotPassword: {
                logoLink: logoLinkStyles,
                logoText: logoTextStyles,
                headerRocket: headerRocketStyles
            },
            mainPage: {
                logoLink: mainPageLogoLinkStyles,
                logoText: mainPageLogoTextStyles,
                headerRocket: mainPageHeaderRocketStyles
            },
            isConsistent: isColorConsistent,
            summary: isColorConsistent ? 
                '✅ 헤더 로고 색상 일관성 복구 성공!' : 
                '❌ 헤더 로고 색상 불일치 여전히 존재'
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testHeaderLogoFix()
    .then(results => {
        console.log('\n📊 최종 헤더 로고 검증 결과:');
        console.log(`${results.summary}`);
        console.log(`🎨 색상 일관성: ${results.isConsistent ? '일치' : '불일치'}`);
        console.log('\n🔍 상세 비교:');
        console.log('비밀번호 찾기 페이지 - 로고 링크 색상:', results.forgotPassword.logoLink.color);
        console.log('메인 페이지 - 로고 링크 색상:', results.mainPage.logoLink.color);
        console.log('비밀번호 찾기 페이지 - 로켓 색상:', results.forgotPassword.headerRocket.color);
        console.log('메인 페이지 - 로켓 색상:', results.mainPage.headerRocket.color);
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });