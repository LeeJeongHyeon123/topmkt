const { chromium } = require('playwright');

async function testLogoMultipleDevices() {
    console.log('🔍 다양한 디바이스에서 헤더 로고 색상 테스트');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const devices = [
        { name: '데스크톱 (1920x1080)', width: 1920, height: 1080 },
        { name: '노트북 (1366x768)', width: 1366, height: 768 },
        { name: '태블릿 (768x1024)', width: 768, height: 1024 },
        { name: '모바일 (375x667)', width: 375, height: 667 },
        { name: '작은 모바일 (320x568)', width: 320, height: 568 }
    ];
    
    try {
        for (const device of devices) {
            console.log(`\n📱 ${device.name} 테스트 중...`);
            
            const context = await browser.newContext({
                viewport: { width: device.width, height: device.height },
                userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            });
            
            const page = await context.newPage();
            
            // 비밀번호 찾기 페이지 로딩
            await page.goto('https://www.topmktx.com/auth/forgot-password');
            await page.waitForSelector('.main-header', { timeout: 10000 });
            await page.waitForTimeout(2000);
            
            // 로고 스타일 확인
            const logoStyles = await page.evaluate(() => {
                const logoLink = document.querySelector('.main-header .logo-link');
                const logoText = document.querySelector('.main-header .logo-text');
                const headerRocket = document.querySelector('.main-header .header-rocket');
                
                if (!logoLink || !logoText) {
                    return { error: '로고 요소를 찾을 수 없습니다' };
                }
                
                const logoLinkStyles = window.getComputedStyle(logoLink);
                const logoTextStyles = window.getComputedStyle(logoText);
                const rocketStyles = headerRocket ? window.getComputedStyle(headerRocket) : null;
                
                return {
                    logoLinkColor: logoLinkStyles.color,
                    logoTextColor: logoTextStyles.color,
                    rocketColor: rocketStyles ? rocketStyles.color : 'not found',
                    logoLinkVisible: logoLinkStyles.visibility === 'visible' && logoLinkStyles.display !== 'none',
                    logoTextVisible: logoTextStyles.visibility === 'visible' && logoTextStyles.display !== 'none',
                    // 추가: 실제 텍스트 내용 확인
                    logoTextContent: logoText.textContent?.trim() || '',
                    logoLinkHref: logoLink.href || ''
                };
            });
            
            console.log(`   로고 링크 색상: ${logoStyles.logoLinkColor}`);
            console.log(`   로고 텍스트 색상: ${logoStyles.logoTextColor}`);
            console.log(`   로켓 색상: ${logoStyles.rocketColor}`);
            console.log(`   로고 텍스트: "${logoStyles.logoTextContent}"`);
            console.log(`   로고 가시성: ${logoStyles.logoLinkVisible && logoStyles.logoTextVisible ? '✅ 정상' : '❌ 숨김'}`);
            
            // 스크린샷 촬영
            const filename = `header-logo-${device.name.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase()}.png`;
            await page.screenshot({ 
                path: filename,
                fullPage: false,
                clip: { x: 0, y: 0, width: Math.min(device.width, 1200), height: 120 }
            });
            
            await context.close();
        }
        
        console.log('\n✅ 모든 디바이스 테스트 완료');
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testLogoMultipleDevices()
    .then(() => {
        console.log('\n📊 다양한 디바이스 헤더 로고 테스트 완료!');
        console.log('🖼️ 스크린샷들이 생성되었습니다.');
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });