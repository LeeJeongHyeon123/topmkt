const { chromium } = require('playwright');

async function debugHeaderLogoStyles() {
    console.log('🔍 헤더 로고 스타일 심화 분석 시작');
    
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
        await page.waitForSelector('.main-header', { timeout: 10000 });
        await page.waitForTimeout(3000); // 애니메이션 완료 대기
        
        // 2. 헤더 로고 요소들의 실제 스타일 분석
        const headerLogoAnalysis = await page.evaluate(() => {
            const logoLink = document.querySelector('.main-header .logo-link');
            const logoText = document.querySelector('.main-header .logo-text');
            const headerRocket = document.querySelector('.main-header .header-rocket');
            
            if (!logoLink || !logoText || !headerRocket) {
                return { error: '헤더 로고 요소를 찾을 수 없습니다' };
            }
            
            const logoLinkStyles = window.getComputedStyle(logoLink);
            const logoTextStyles = window.getComputedStyle(logoText);
            const rocketStyles = window.getComputedStyle(headerRocket);
            
            return {
                logoLink: {
                    color: logoLinkStyles.color,
                    backgroundColor: logoLinkStyles.backgroundColor,
                    textDecoration: logoLinkStyles.textDecoration,
                    fontWeight: logoLinkStyles.fontWeight,
                    fontSize: logoLinkStyles.fontSize,
                    display: logoLinkStyles.display,
                    visibility: logoLinkStyles.visibility,
                    opacity: logoLinkStyles.opacity
                },
                logoText: {
                    color: logoTextStyles.color,
                    backgroundColor: logoTextStyles.backgroundColor,
                    fontWeight: logoTextStyles.fontWeight,
                    fontSize: logoTextStyles.fontSize,
                    display: logoTextStyles.display,
                    visibility: logoTextStyles.visibility,
                    opacity: logoTextStyles.opacity
                },
                rocket: {
                    color: rocketStyles.color,
                    backgroundColor: rocketStyles.backgroundColor,
                    fontSize: rocketStyles.fontSize,
                    display: rocketStyles.display,
                    visibility: rocketStyles.visibility,
                    opacity: rocketStyles.opacity,
                    animation: rocketStyles.animation
                },
                // 추가: CSS 변수 값들 확인
                cssVariables: {
                    primaryColor: logoLinkStyles.getPropertyValue('--primary-color') || 'not set',
                    textColorPrimary: logoLinkStyles.getPropertyValue('--text-color-primary') || 'not set',
                    logoColor: logoLinkStyles.getPropertyValue('--logo-color') || 'not set'
                }
            };
        });
        
        console.log('📊 헤더 로고 상세 스타일 분석:');
        console.log('==== 로고 링크 스타일 ====');
        console.log('색상:', headerLogoAnalysis.logoLink.color);
        console.log('배경색:', headerLogoAnalysis.logoLink.backgroundColor);
        console.log('폰트 굵기:', headerLogoAnalysis.logoLink.fontWeight);
        console.log('가시성:', headerLogoAnalysis.logoLink.visibility);
        console.log('불투명도:', headerLogoAnalysis.logoLink.opacity);
        
        console.log('==== 로고 텍스트 스타일 ====');
        console.log('색상:', headerLogoAnalysis.logoText.color);
        console.log('배경색:', headerLogoAnalysis.logoText.backgroundColor);
        console.log('폰트 굵기:', headerLogoAnalysis.logoText.fontWeight);
        console.log('가시성:', headerLogoAnalysis.logoText.visibility);
        console.log('불투명도:', headerLogoAnalysis.logoText.opacity);
        
        console.log('==== 로켓 아이콘 스타일 ====');
        console.log('색상:', headerLogoAnalysis.rocket.color);
        console.log('애니메이션:', headerLogoAnalysis.rocket.animation);
        console.log('가시성:', headerLogoAnalysis.rocket.visibility);
        
        console.log('==== CSS 변수들 ====');
        console.log('Primary Color:', headerLogoAnalysis.cssVariables.primaryColor);
        console.log('Text Color Primary:', headerLogoAnalysis.cssVariables.textColorPrimary);
        console.log('Logo Color:', headerLogoAnalysis.cssVariables.logoColor);
        
        // 3. 다른 페이지와 비교 (메인 페이지)
        console.log('\n🏠 메인 페이지와 비교 중...');
        await page.goto('https://www.topmktx.com/');
        await page.waitForSelector('.main-header', { timeout: 5000 });
        await page.waitForTimeout(2000);
        
        const mainPageLogoAnalysis = await page.evaluate(() => {
            const logoLink = document.querySelector('.main-header .logo-link');
            const logoText = document.querySelector('.main-header .logo-text');
            const headerRocket = document.querySelector('.main-header .header-rocket');
            
            if (!logoLink || !logoText || !headerRocket) {
                return { error: '메인 페이지 헤더 로고 요소를 찾을 수 없습니다' };
            }
            
            const logoLinkStyles = window.getComputedStyle(logoLink);
            const logoTextStyles = window.getComputedStyle(logoText);
            const rocketStyles = window.getComputedStyle(headerRocket);
            
            return {
                logoLinkColor: logoLinkStyles.color,
                logoTextColor: logoTextStyles.color,
                rocketColor: rocketStyles.color
            };
        });
        
        console.log('📊 메인 페이지 헤더 로고 스타일:');
        console.log('로고 링크 색상:', mainPageLogoAnalysis.logoLinkColor);
        console.log('로고 텍스트 색상:', mainPageLogoAnalysis.logoTextColor);
        console.log('로켓 색상:', mainPageLogoAnalysis.rocketColor);
        
        // 4. 색상 비교 분석
        console.log('\n🎨 색상 비교 분석:');
        const forgotPasswordLogoColor = headerLogoAnalysis.logoText.color;
        const mainPageLogoColor = mainPageLogoAnalysis.logoTextColor;
        
        console.log(`비밀번호 찾기 페이지 로고 색상: ${forgotPasswordLogoColor}`);
        console.log(`메인 페이지 로고 색상: ${mainPageLogoColor}`);
        console.log(`색상 일치 여부: ${forgotPasswordLogoColor === mainPageLogoColor ? '✅ 일치' : '❌ 불일치'}`);
        
        // 5. 스크린샷 촬영
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('.main-header', { timeout: 5000 });
        await page.waitForTimeout(2000);
        
        await page.screenshot({ 
            path: 'header-logo-debug-screenshot.png',
            fullPage: false,
            clip: { x: 0, y: 0, width: 1920, height: 150 }
        });
        
        console.log('✅ 헤더 로고 디버그 분석 완료');
        
        return {
            forgotPasswordPage: headerLogoAnalysis,
            mainPage: mainPageLogoAnalysis,
            colorsMatch: forgotPasswordLogoColor === mainPageLogoColor
        };
        
    } catch (error) {
        console.error('❌ 헤더 로고 분석 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
debugHeaderLogoStyles()
    .then(results => {
        console.log('\n📊 최종 헤더 로고 분석 결과:');
        if (results.colorsMatch) {
            console.log('✅ 헤더 로고 색상이 메인 페이지와 일치합니다!');
        } else {
            console.log('❌ 헤더 로고 색상이 메인 페이지와 다릅니다!');
            console.log('🔧 CSS 오버라이드나 스타일 충돌 문제가 있을 수 있습니다.');
        }
    })
    .catch(error => {
        console.error('💥 분석 실행 실패:', error);
        process.exit(1);
    });