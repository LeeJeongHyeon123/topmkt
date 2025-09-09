const { chromium } = require('playwright');

async function testForgotPasswordBackground() {
    console.log('🔍 비밀번호 재설정 페이지 폼 배경 검증 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const page = await browser.newPage();
        await page.setViewportSize({ width: 1920, height: 1080 });
        
        console.log('📄 페이지 로딩 중...');
        await page.goto('https://www.topmktx.com/auth/forgot-password', { 
            waitUntil: 'networkidle' 
        });
        
        // 페이지가 완전히 로드될 때까지 잠시 대기
        await page.waitForTimeout(2000);
        
        console.log('📸 스크린샷 촬영 중...');
        await page.screenshot({ 
            path: 'forgot-password-background-verification.png',
            fullPage: true 
        });
        
        console.log('🎨 폼 배경 스타일 분석 중...');
        // 폼 컨테이너의 배경 스타일 확인
        const formStyles = await page.evaluate(() => {
            const formContainer = document.querySelector('.form-container');
            const authForm = document.querySelector('.auth-form');
            const forgotPasswordForm = document.querySelector('#forgot-password-form');
            
            let results = [];
            
            if (formContainer) {
                const styles = window.getComputedStyle(formContainer);
                results.push({
                    selector: '.form-container',
                    backgroundColor: styles.backgroundColor,
                    background: styles.background,
                    backgroundImage: styles.backgroundImage,
                    opacity: styles.opacity
                });
            }
            
            if (authForm) {
                const styles = window.getComputedStyle(authForm);
                results.push({
                    selector: '.auth-form',
                    backgroundColor: styles.backgroundColor,
                    background: styles.background,
                    backgroundImage: styles.backgroundImage,
                    opacity: styles.opacity
                });
            }
            
            if (forgotPasswordForm) {
                const styles = window.getComputedStyle(forgotPasswordForm);
                results.push({
                    selector: '#forgot-password-form',
                    backgroundColor: styles.backgroundColor,
                    background: styles.background,
                    backgroundImage: styles.backgroundImage,
                    opacity: styles.opacity
                });
            }
            
            // 전체 페이지의 폼 관련 요소들 검색
            const allForms = document.querySelectorAll('form, .form, [class*="form"]');
            allForms.forEach((element, index) => {
                const styles = window.getComputedStyle(element);
                if (styles.backgroundColor !== 'rgba(0, 0, 0, 0)' || styles.background !== 'rgba(0, 0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box') {
                    results.push({
                        selector: `Form element ${index + 1} (${element.tagName}.${element.className})`,
                        backgroundColor: styles.backgroundColor,
                        background: styles.background,
                        backgroundImage: styles.backgroundImage,
                        opacity: styles.opacity
                    });
                }
            });
            
            return results;
        });
        
        console.log('📊 폼 배경 분석 결과:');
        formStyles.forEach((style, index) => {
            console.log(`\n${index + 1}. ${style.selector}:`);
            console.log(`   배경색: ${style.backgroundColor}`);
            console.log(`   배경: ${style.background}`);
            console.log(`   배경이미지: ${style.backgroundImage}`);
            console.log(`   투명도: ${style.opacity}`);
        });
        
        // 흰색 배경 확인
        const hasWhiteBackground = formStyles.some(style => 
            style.backgroundColor.includes('rgb(255, 255, 255)') || 
            style.backgroundColor.includes('white')
        );
        
        console.log(`\n✅ 흰색 배경 적용 여부: ${hasWhiteBackground ? '예' : '아니오'}`);
        
        // 페이지 title과 기본 정보 확인
        const pageInfo = await page.evaluate(() => ({
            title: document.title,
            url: window.location.href,
            hasFormElements: document.querySelectorAll('form').length > 0
        }));
        
        console.log(`\n📋 페이지 정보:`);
        console.log(`   제목: ${pageInfo.title}`);
        console.log(`   URL: ${pageInfo.url}`);
        console.log(`   폼 요소 존재: ${pageInfo.hasFormElements ? '예' : '아니오'}`);
        
        return {
            screenshot: 'forgot-password-background-verification.png',
            formStyles,
            hasWhiteBackground,
            pageInfo
        };
        
    } catch (error) {
        console.error('❌ 오류 발생:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testForgotPasswordBackground()
    .then(result => {
        console.log('\n🎉 검증 완료!');
        console.log(`📸 스크린샷: ${result.screenshot}`);
        console.log(`🎨 흰색 배경: ${result.hasWhiteBackground ? '✅ 적용됨' : '❌ 미적용'}`);
    })
    .catch(console.error);