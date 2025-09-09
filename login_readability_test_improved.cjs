const { chromium } = require('playwright');

async function testImprovedLoginReadability() {
    console.log('🔍 개선된 로그인 페이지 가독성 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const testResults = [];
        
        // 1. 데스크톱 테스트
        console.log('\n💻 데스크톱 가독성 테스트...');
        const desktopContext = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const desktopPage = await desktopContext.newPage();
        
        await desktopPage.goto('https://www.topmktx.com/auth/login');
        await desktopPage.waitForLoadState('networkidle');
        await desktopPage.waitForSelector('.auth-side-info', { timeout: 10000 });
        
        const desktopAnalysis = await desktopPage.evaluate(() => {
            const elements = {
                mainTitle: document.querySelector('.auth-side-info h2'),
                mainDescription: document.querySelector('.auth-side-info > .side-info-content > p'),
                securityFeatures: document.querySelectorAll('.security-feature span'),
                benefitsTitle: document.querySelector('.login-benefits h3'),
                benefitsList: document.querySelectorAll('.login-benefits li')
            };
            
            const getContrastData = (element) => {
                if (!element) return null;
                const styles = window.getComputedStyle(element);
                const rect = element.getBoundingClientRect();
                return {
                    color: styles.color,
                    fontSize: styles.fontSize,
                    fontWeight: styles.fontWeight,
                    isVisible: rect.width > 0 && rect.height > 0,
                    textContent: element.textContent?.trim()
                };
            };
            
            return {
                mainTitle: getContrastData(elements.mainTitle),
                mainDescription: getContrastData(elements.mainDescription),
                securityFeatures: Array.from(elements.securityFeatures).map(el => getContrastData(el)),
                benefitsTitle: getContrastData(elements.benefitsTitle),
                benefitsList: Array.from(elements.benefitsList).map(el => getContrastData(el))
            };
        });
        
        await desktopPage.screenshot({ 
            path: '/var/www/html/topmkt/login-readability-desktop-improved.png',
            fullPage: true 
        });
        
        testResults.push({
            device: 'Desktop (1920x1080)',
            analysis: desktopAnalysis,
            screenshot: 'login-readability-desktop-improved.png'
        });
        
        await desktopContext.close();
        
        // 2. 모바일 테스트
        console.log('\n📱 모바일 가독성 테스트...');
        const mobileContext = await browser.newContext({
            viewport: { width: 375, height: 667 },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1',
            hasTouch: true,
            isMobile: true
        });
        const mobilePage = await mobileContext.newPage();
        
        await mobilePage.goto('https://www.topmktx.com/auth/login');
        await mobilePage.waitForLoadState('networkidle');
        await mobilePage.waitForSelector('.auth-side-info', { timeout: 10000 });
        
        const mobileAnalysis = await mobilePage.evaluate(() => {
            const elements = {
                securityFeatures: document.querySelectorAll('.security-feature span'),
                benefitsTitle: document.querySelector('.login-benefits h3'),
                benefitsList: document.querySelectorAll('.login-benefits li')
            };
            
            const getContrastData = (element) => {
                if (!element) return null;
                const styles = window.getComputedStyle(element);
                const rect = element.getBoundingClientRect();
                return {
                    color: styles.color,
                    fontSize: styles.fontSize,
                    fontWeight: styles.fontWeight,
                    isVisible: rect.width > 0 && rect.height > 0,
                    textContent: element.textContent?.trim()
                };
            };
            
            return {
                securityFeatures: Array.from(elements.securityFeatures).map(el => getContrastData(el)),
                benefitsTitle: getContrastData(elements.benefitsTitle),
                benefitsList: Array.from(elements.benefitsList).map(el => getContrastData(el))
            };
        });
        
        await mobilePage.screenshot({ 
            path: '/var/www/html/topmkt/login-readability-mobile-improved.png',
            fullPage: true 
        });
        
        testResults.push({
            device: 'Mobile (375x667)',
            analysis: mobileAnalysis,
            screenshot: 'login-readability-mobile-improved.png'
        });
        
        await mobileContext.close();
        
        // 3. 태블릿 테스트
        console.log('\n📱 태블릿 가독성 테스트...');
        const tabletContext = await browser.newContext({
            viewport: { width: 768, height: 1024 },
            userAgent: 'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1',
            hasTouch: true,
            isMobile: true
        });
        const tabletPage = await tabletContext.newPage();
        
        await tabletPage.goto('https://www.topmktx.com/auth/login');
        await tabletPage.waitForLoadState('networkidle');
        await tabletPage.waitForSelector('.auth-side-info', { timeout: 10000 });
        
        await tabletPage.screenshot({ 
            path: '/var/www/html/topmkt/login-readability-tablet-improved.png',
            fullPage: true 
        });
        
        await tabletContext.close();
        
        // 결과 분석
        console.log('\n🎯 === 개선된 가독성 테스트 결과 ===');
        testResults.forEach((result, index) => {
            console.log(`\n테스트 ${index + 1}: ${result.device}`);
            console.log(`스크린샷: ${result.screenshot}`);
            
            if (result.analysis) {
                const securityColors = result.analysis.securityFeatures?.map(f => f?.color).filter(Boolean);
                const benefitsColors = result.analysis.benefitsList?.map(f => f?.color).filter(Boolean);
                
                console.log(`보안 기능 텍스트 색상: ${[...new Set(securityColors)].join(', ')}`);
                console.log(`혜택 리스트 텍스트 색상: ${[...new Set(benefitsColors)].join(', ')}`);
                console.log(`혜택 제목 색상: ${result.analysis.benefitsTitle?.color || 'N/A'}`);
            }
        });
        
        return testResults;
        
    } catch (error) {
        console.error('❌ 개선된 가독성 테스트 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testImprovedLoginReadability()
    .then((results) => {
        console.log('\n✅ 개선된 로그인 페이지 가독성 테스트 완료!');
        console.log(`📊 총 ${results.length}개 디바이스 테스트 완료`);
        console.log('🎨 색상 개선 사항:');
        console.log('  - 보안 기능: #64748b → #e2e8f0 (더 밝은 회색)');
        console.log('  - 혜택 리스트: #64748b → #cbd5e1 (더 밝은 회색)');
        console.log('  - 혜택 제목: #1e293b → #f1f5f9 (거의 흰색)');
        console.log('  - 메인 제목/설명: 더 강한 대비와 그림자 효과 추가');
        console.log('  - 모바일: 추가 대비 강화 (#f8fafc, #ffffff)');
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });