const { chromium } = require('playwright');

async function testMainPageLogo() {
    console.log('🚀 메인 페이지 헤더 로고 색상 확인');
    
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
        
        // 1. 메인 페이지 로딩
        console.log('📱 메인 페이지 로딩...');
        await page.goto('https://www.topmktx.com/');
        await page.waitForSelector('.main-header', { timeout: 10000 });
        
        console.log('🔍 메인 페이지 헤더 로고 스타일 분석...');
        
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
        
        console.log('📊 메인 페이지 헤더 로고 스타일:');
        console.log('로고 링크:', logoLinkStyles);
        console.log('로고 텍스트:', logoTextStyles);
        console.log('헤더 로켓:', headerRocketStyles);
        
        // 2. 다른 페이지 (커뮤니티)와 비교
        console.log('📋 커뮤니티 페이지와 비교...');
        await page.goto('https://www.topmktx.com/community');
        await page.waitForSelector('.main-header', { timeout: 10000 });
        
        // 커뮤니티 페이지 로고 스타일 확인
        const communityLogoLinkStyles = await page.locator('.logo-link').first().evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                textDecoration: styles.textDecoration
            };
        });
        
        const communityLogoTextStyles = await page.locator('.logo-text').first().evaluate(el => {
            const styles = window.getComputedStyle(el);
            return {
                color: styles.color,
                fontWeight: styles.fontWeight
            };
        });
        
        console.log('📊 커뮤니티 페이지 헤더 로고 스타일:');
        console.log('로고 링크:', communityLogoLinkStyles);
        console.log('로고 텍스트:', communityLogoTextStyles);
        
        // 3. 스크린샷 촬영
        console.log('📸 스크린샷 촬영...');
        
        // 메인 페이지 헤더
        await page.goto('https://www.topmktx.com/');
        await page.waitForSelector('.main-header', { timeout: 5000 });
        await page.screenshot({ 
            path: 'main-page-header-logo-test.png',
            fullPage: false,
            clip: { x: 0, y: 0, width: 1920, height: 120 }
        });
        
        // 커뮤니티 페이지 헤더 (비교용)
        await page.goto('https://www.topmktx.com/community');
        await page.waitForSelector('.main-header', { timeout: 5000 });
        await page.screenshot({ 
            path: 'community-page-header-logo-test.png',
            fullPage: false,
            clip: { x: 0, y: 0, width: 1920, height: 120 }
        });
        
        console.log('✅ 테스트 완료 - 스크린샷들 저장됨');
        
        return {
            mainPage: {
                logoLink: logoLinkStyles,
                logoText: logoTextStyles,
                headerRocket: headerRocketStyles
            },
            communityPage: {
                logoLink: communityLogoLinkStyles,
                logoText: communityLogoTextStyles
            },
            isConsistent: logoLinkStyles.color === communityLogoLinkStyles.color &&
                          logoTextStyles.color === communityLogoTextStyles.color
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testMainPageLogo()
    .then(results => {
        console.log('\n📊 메인 페이지 로고 색상 분석 결과:');
        console.log(`🎨 색상 일관성: ${results.isConsistent ? '일치' : '불일치'}`);
        console.log('\n🔍 상세 분석:');
        console.log('메인 페이지 - 로고 링크 색상:', results.mainPage.logoLink.color);
        console.log('커뮤니티 페이지 - 로고 링크 색상:', results.communityPage.logoLink.color);
        console.log('메인 페이지 - 로고 텍스트 색상:', results.mainPage.logoText.color);
        console.log('커뮤니티 페이지 - 로고 텍스트 색상:', results.communityPage.logoText.color);
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });