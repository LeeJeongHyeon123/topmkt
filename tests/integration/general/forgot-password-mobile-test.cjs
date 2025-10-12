const { chromium } = require('playwright');

async function testForgotPasswordMobile() {
    console.log('📱 모바일 비밀번호 재설정 페이지 폼 배경 검증 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const page = await browser.newPage();
        await page.setViewportSize({ width: 375, height: 667 }); // iPhone SE
        
        console.log('📄 모바일 페이지 로딩 중...');
        await page.goto('https://www.topmktx.com/auth/forgot-password', { 
            waitUntil: 'networkidle' 
        });
        
        await page.waitForTimeout(2000);
        
        console.log('📸 모바일 스크린샷 촬영 중...');
        await page.screenshot({ 
            path: 'forgot-password-mobile-verification.png',
            fullPage: true 
        });
        
        console.log('🎨 모바일 폼 배경 스타일 분석 중...');
        const formStyles = await page.evaluate(() => {
            const formContainer = document.querySelector('.form-container');
            
            if (formContainer) {
                const styles = window.getComputedStyle(formContainer);
                return {
                    selector: '.form-container',
                    backgroundColor: styles.backgroundColor,
                    background: styles.background,
                    backgroundImage: styles.backgroundImage,
                    opacity: styles.opacity,
                    width: styles.width,
                    height: styles.height,
                    padding: styles.padding,
                    margin: styles.margin
                };
            }
            return null;
        });
        
        console.log('📊 모바일 폼 배경 분석 결과:');
        if (formStyles) {
            console.log(`   선택자: ${formStyles.selector}`);
            console.log(`   배경색: ${formStyles.backgroundColor}`);
            console.log(`   배경: ${formStyles.background}`);
            console.log(`   배경이미지: ${formStyles.backgroundImage}`);
            console.log(`   투명도: ${formStyles.opacity}`);
            console.log(`   너비: ${formStyles.width}`);
            console.log(`   높이: ${formStyles.height}`);
            console.log(`   패딩: ${formStyles.padding}`);
            console.log(`   마진: ${formStyles.margin}`);
        }
        
        const hasWhiteBackground = formStyles && 
            (formStyles.backgroundColor.includes('rgb(255, 255, 255)') || 
             formStyles.backgroundColor.includes('white'));
        
        console.log(`\n✅ 모바일 흰색 배경 적용 여부: ${hasWhiteBackground ? '예' : '아니오'}`);
        
        return {
            screenshot: 'forgot-password-mobile-verification.png',
            formStyles,
            hasWhiteBackground
        };
        
    } catch (error) {
        console.error('❌ 오류 발생:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testForgotPasswordMobile()
    .then(result => {
        console.log('\n🎉 모바일 검증 완료!');
        console.log(`📸 모바일 스크린샷: ${result.screenshot}`);
        console.log(`🎨 모바일 흰색 배경: ${result.hasWhiteBackground ? '✅ 적용됨' : '❌ 미적용'}`);
    })
    .catch(console.error);