const { chromium } = require('playwright');

async function analyzeLoginReadability() {
    console.log('🔍 로그인 페이지 가독성 분석 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        console.log('📄 로그인 페이지 접속...');
        await page.goto('https://www.topmktx.com/auth/login');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('.auth-side-info', { timeout: 10000 });
        
        // 색상 대비 분석
        const contrastAnalysis = await page.evaluate(() => {
            const sideInfo = document.querySelector('.auth-side-info');
            const elements = {
                mainTitle: sideInfo?.querySelector('h2'),
                mainDescription: sideInfo?.querySelector('p'),
                securityFeatures: sideInfo?.querySelectorAll('.security-feature span'),
                benefitsTitle: sideInfo?.querySelector('.login-benefits h3'),
                benefitsList: sideInfo?.querySelectorAll('.login-benefits li')
            };
            
            const getStyles = (element) => {
                if (!element) return null;
                const styles = window.getComputedStyle(element);
                return {
                    color: styles.color,
                    backgroundColor: styles.backgroundColor,
                    fontSize: styles.fontSize,
                    fontWeight: styles.fontWeight,
                    textContent: element.textContent?.trim().substring(0, 50) + '...'
                };
            };
            
            return {
                mainTitle: getStyles(elements.mainTitle),
                mainDescription: getStyles(elements.mainDescription),
                securityFeatures: Array.from(elements.securityFeatures || []).map(el => getStyles(el)),
                benefitsTitle: getStyles(elements.benefitsTitle),
                benefitsList: Array.from(elements.benefitsList || []).map(el => getStyles(el)),
                sideInfoBackground: sideInfo ? window.getComputedStyle(sideInfo).backgroundColor : null
            };
        });
        
        console.log('\n🎨 현재 색상 분석 결과:');
        console.log(JSON.stringify(contrastAnalysis, null, 2));
        
        // 스크린샷 촬영
        await page.screenshot({ 
            path: '/var/www/html/topmkt/login-readability-before.png',
            fullPage: true 
        });
        
        console.log('📸 스크린샷 촬영 완료: login-readability-before.png');
        
        return contrastAnalysis;
        
    } catch (error) {
        console.error('❌ 로그인 페이지 분석 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
analyzeLoginReadability()
    .then((result) => {
        console.log('✅ 로그인 페이지 가독성 분석 완료!');
        console.log('🔍 다음 단계: 개선된 색상 적용');
    })
    .catch((error) => {
        console.error('💥 분석 실패:', error);
        process.exit(1);
    });