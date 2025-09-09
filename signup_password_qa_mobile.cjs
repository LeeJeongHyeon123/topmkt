const { chromium } = require('playwright');

async function testSignupPasswordValidationMobile() {
    console.log('📱 회원가입 비밀번호 검증 모바일 QA 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 375, height: 667 }, // iPhone SE
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1'
        });
        const page = await context.newPage();
        
        console.log('📄 회원가입 페이지 접속 (모바일)...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('#password', { timeout: 10000 });
        console.log('✅ 모바일 페이지 로딩 완료');
        
        const testResults = [];
        
        // === 모바일 반응형 UI 테스트 ===
        console.log('\n📱 모바일 반응형 UI 테스트...');
        
        // 요소들의 크기 및 위치 확인
        const passwordInput = page.locator('#password');
        const requirements = page.locator('#password-requirements');
        const strengthBar = page.locator('#password-strength');
        const matchStatus = page.locator('#password-match-status');
        
        // 강한 비밀번호 입력하여 모든 UI 표시
        await passwordInput.fill('Password123!');
        await page.waitForTimeout(500);
        
        const passwordConfirmInput = page.locator('#password_confirm');
        await passwordConfirmInput.fill('Password123!');
        await page.waitForTimeout(500);
        
        // UI 요소들이 모바일에서 제대로 표시되는지 확인
        const requirementsVisible = await requirements.isVisible();
        const strengthVisible = await strengthBar.isVisible();
        const matchVisible = await matchStatus.isVisible();
        
        // 요소들의 크기가 모바일에 적합한지 확인
        const reqBox = await requirements.boundingBox();
        const strengthBox = await strengthBar.boundingBox();
        const matchBox = await matchStatus.boundingBox();
        
        testResults.push({
            test: '모바일 반응형 UI',
            requirements_visible: requirementsVisible,
            strength_visible: strengthVisible,
            match_visible: matchVisible,
            requirements_width: reqBox ? reqBox.width : 0,
            strength_width: strengthBox ? strengthBox.width : 0,
            match_width: matchBox ? matchBox.width : 0,
            passed: requirementsVisible && strengthVisible && matchVisible && 
                   reqBox?.width > 300 && strengthBox?.width > 300 && matchBox?.width > 300
        });
        
        // === 모바일 터치 인터랙션 테스트 ===
        console.log('\n👆 모바일 터치 인터랙션 테스트...');
        
        // 비밀번호 토글 버튼 터치 테스트
        const passwordToggle = page.locator('#password-toggle');
        const passwordToggleBox = await passwordToggle.boundingBox();
        const isToggleClickable = passwordToggleBox && passwordToggleBox.width >= 44 && passwordToggleBox.height >= 44;
        
        await passwordToggle.click();
        const passwordType1 = await passwordInput.getAttribute('type');
        await passwordToggle.click();
        const passwordType2 = await passwordInput.getAttribute('type');
        
        testResults.push({
            test: '모바일 터치 인터랙션',
            toggle_size_ok: isToggleClickable,
            toggle_width: passwordToggleBox?.width || 0,
            toggle_height: passwordToggleBox?.height || 0,
            toggle_works: passwordType1 === 'text' && passwordType2 === 'password',
            passed: isToggleClickable && passwordType1 === 'text' && passwordType2 === 'password'
        });
        
        // === 모바일 텍스트 가독성 테스트 ===
        console.log('\n📖 모바일 텍스트 가독성 테스트...');
        
        const requirementElements = await page.locator('.requirement').all();
        let minFontSize = 16;
        let allReadable = true;
        
        for (const element of requirementElements) {
            const styles = await element.evaluate(el => {
                const computedStyles = window.getComputedStyle(el);
                return {
                    fontSize: computedStyles.fontSize,
                    lineHeight: computedStyles.lineHeight,
                    color: computedStyles.color
                };
            });
            
            const fontSize = parseInt(styles.fontSize);
            if (fontSize < minFontSize) {
                minFontSize = fontSize;
                allReadable = false;
            }
        }
        
        testResults.push({
            test: '모바일 텍스트 가독성',
            min_font_size: minFontSize,
            all_readable: allReadable,
            passed: minFontSize >= 13 // 모바일 최소 권장 크기
        });
        
        // === 스크린샷 촬영 ===
        console.log('\n📸 모바일 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/signup-password-validation-mobile.png',
            fullPage: true 
        });
        
        // === 결과 출력 ===
        console.log('\n🎯 === 모바일 QA 테스트 결과 ===');
        let passedTests = 0;
        testResults.forEach((result, index) => {
            console.log(`\n테스트 ${index + 1}: ${result.test}`);
            if (result.passed) {
                console.log('✅ PASS');
                passedTests++;
            } else {
                console.log('❌ FAIL');
                console.log('상세:', JSON.stringify(result, null, 2));
            }
        });
        
        console.log(`\n📊 총 테스트: ${testResults.length}개, 성공: ${passedTests}개, 성공률: ${Math.round(passedTests/testResults.length*100)}%`);
        
        return testResults;
        
    } catch (error) {
        console.error('❌ 모바일 테스트 실행 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSignupPasswordValidationMobile()
    .then(() => {
        console.log('✅ 모바일 QA 테스트 완료!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });
