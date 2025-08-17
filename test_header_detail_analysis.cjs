/**
 * 탑마케팅 햄버거 메뉴 헤더 영역 상세 분석 (v3.16.0)
 * 목표: 헤더 영역의 햄버거 버튼과 숨겨진 프로필 요소 확인
 */

const { chromium } = require('playwright');

async function analyzeHeaderDetails() {
    console.log('🔍 탑마케팅 헤더 영역 상세 분석 시작');
    
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 390, height: 844 },
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15'
    });
    
    const page = await context.newPage();
    
    try {
        // DevLogin 로그인
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        // 메인 페이지 이동
        await page.goto('https://www.topmktx.com/');
        await page.waitForSelector('header', { timeout: 10000 });
        await page.waitForTimeout(2000);
        
        // 헤더 영역만 스크린샷
        const header = await page.$('header, .header, .navbar');
        if (header) {
            await header.screenshot({ path: 'header_detail_mobile.png' });
            console.log('📸 헤더 영역 스크린샷 저장: header_detail_mobile.png');
        }
        
        // 햄버거 버튼 상세 분석
        console.log('\n🔍 햄버거 버튼 상세 분석:');
        const hamburgerSelectors = [
            '.mobile-menu-toggle',
            '.hamburger-menu',
            '.menu-toggle',
            '.navbar-toggler',
            '[aria-label*="menu"]'
        ];
        
        for (const selector of hamburgerSelectors) {
            const element = await page.$(selector);
            if (element) {
                const isVisible = await element.isVisible();
                const boundingBox = await element.boundingBox();
                const styles = await element.evaluate(el => {
                    const computedStyle = window.getComputedStyle(el);
                    return {
                        display: computedStyle.display,
                        visibility: computedStyle.visibility,
                        opacity: computedStyle.opacity,
                        fontSize: computedStyle.fontSize,
                        color: computedStyle.color,
                        backgroundColor: computedStyle.backgroundColor
                    };
                });
                
                console.log(`✅ ${selector}:`);
                console.log(`   - 가시성: ${isVisible}`);
                console.log(`   - 위치: x=${boundingBox?.x}, y=${boundingBox?.y}`);
                console.log(`   - 크기: ${boundingBox?.width}×${boundingBox?.height}`);
                console.log(`   - 스타일:`, styles);
                break;
            }
        }
        
        // 숨겨진 프로필 요소 확인
        console.log('\n👤 프로필 요소 숨김 상태 확인:');
        const profileSelectors = [
            '.profile-image',
            '.user-profile',
            '.profile-avatar',
            '.user-avatar',
            '.profile-dropdown',
            '.user-dropdown'
        ];
        
        for (const selector of profileSelectors) {
            const elements = await page.$$(selector);
            for (let i = 0; i < elements.length; i++) {
                const element = elements[i];
                const isVisible = await element.isVisible();
                const styles = await element.evaluate(el => {
                    const computedStyle = window.getComputedStyle(el);
                    return {
                        display: computedStyle.display,
                        visibility: computedStyle.visibility,
                        opacity: computedStyle.opacity
                    };
                });
                
                console.log(`🔍 ${selector}[${i}]:`);
                console.log(`   - 가시성: ${isVisible}`);
                console.log(`   - 스타일:`, styles);
            }
        }
        
        // 모바일 뷰에서 숨겨져야 할 요소들 확인
        console.log('\n📱 모바일 전용 요소 확인:');
        const mobileElements = await page.$$('.d-md-none, .mobile-only, .d-block.d-md-none');
        console.log(`   - 모바일 전용 요소: ${mobileElements.length}개`);
        
        const desktopElements = await page.$$('.d-none.d-md-block, .desktop-only');
        console.log(`   - 데스크톱 전용 요소: ${desktopElements.length}개`);
        
        // 반응형 브레이크포인트 확인
        console.log('\n📏 반응형 브레이크포인트 테스트:');
        
        // 768px 이상 (데스크톱)
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.waitForTimeout(1000);
        
        const hamburgerDesktop = await page.$('.mobile-menu-toggle');
        const hamburgerVisibleDesktop = hamburgerDesktop ? await hamburgerDesktop.isVisible() : false;
        console.log(`   - 768px에서 햄버거 버튼: ${hamburgerVisibleDesktop ? '표시됨' : '숨겨짐'}`);
        
        await page.screenshot({ path: 'header_desktop_768px.png' });
        
        // 다시 모바일로 변경
        await page.setViewportSize({ width: 390, height: 844 });
        await page.waitForTimeout(1000);
        
        const hamburgerMobile = await page.$('.mobile-menu-toggle');
        const hamburgerVisibleMobile = hamburgerMobile ? await hamburgerMobile.isVisible() : false;
        console.log(`   - 390px에서 햄버거 버튼: ${hamburgerVisibleMobile ? '표시됨' : '숨겨짐'}`);
        
        await page.screenshot({ path: 'header_mobile_390px.png' });
        
    } catch (error) {
        console.error('❌ 헤더 분석 중 오류:', error.message);
    }
    
    await browser.close();
    console.log('✅ 헤더 상세 분석 완료');
}

analyzeHeaderDetails().catch(console.error);