const { chromium } = require('playwright');

async function testSignupPasswordValidationTablet() {
    console.log('📱 회원가입 비밀번호 검증 태블릿 QA 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 768, height: 1024 }, // iPad
            userAgent: 'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1'
        });
        const page = await context.newPage();
        
        console.log('📄 회원가입 페이지 접속 (태블릿)...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('#password', { timeout: 10000 });
        console.log('✅ 태블릿 페이지 로딩 완료');
        
        const testResults = [];
        
        // === 태블릿 반응형 UI 테스트 ===
        console.log('\n📱 태블릿 반응형 UI 테스트...');
        
        const passwordInput = page.locator('#password');
        const passwordConfirmInput = page.locator('#password_confirm');
        
        // 모든 비밀번호 검증 UI 표시를 위한 입력
        await passwordInput.fill('Password123!');
        await page.waitForTimeout(500);
        await passwordConfirmInput.fill('Password123!');
        await page.waitForTimeout(500);
        
        // UI 요소들이 태블릿에서 적절히 표시되는지 확인
        const requirements = page.locator('#password-requirements');
        const strengthBar = page.locator('#password-strength');
        const matchStatus = page.locator('#password-match-status');
        
        const requirementsVisible = await requirements.isVisible();
        const strengthVisible = await strengthBar.isVisible();
        const matchVisible = await matchStatus.isVisible();
        
        // 요소들의 크기가 태블릿에 적합한지 확인
        const reqBox = await requirements.boundingBox();
        const strengthBox = await strengthBar.boundingBox();
        const matchBox = await matchStatus.boundingBox();
        
        testResults.push({
            test: '태블릿 반응형 UI',
            requirements_visible: requirementsVisible,
            strength_visible: strengthVisible,
            match_visible: matchVisible,
            requirements_width: reqBox ? reqBox.width : 0,
            strength_width: strengthBox ? strengthBox.width : 0,
            match_width: matchBox ? matchBox.width : 0,
            passed: requirementsVisible && strengthVisible && matchVisible && 
                   reqBox?.width > 400 && strengthBox?.width > 400 && matchBox?.width > 400
        });
        
        // === 태블릿 터치 인터랙션 테스트 ===
        console.log('\n👆 태블릿 터치 인터랙션 테스트...');
        
        const passwordToggle = page.locator('#password-toggle');
        const passwordConfirmToggle = page.locator('#password-confirm-toggle');
        
        const toggleBox = await passwordToggle.boundingBox();
        const confirmToggleBox = await passwordConfirmToggle.boundingBox();
        
        const isToggleClickable = toggleBox && toggleBox.width >= 44 && toggleBox.height >= 44;
        const isConfirmToggleClickable = confirmToggleBox && confirmToggleBox.width >= 44 && confirmToggleBox.height >= 44;
        
        // 터치 동작 테스트
        await passwordToggle.click();
        const passwordType1 = await passwordInput.getAttribute('type');
        await passwordToggle.click();
        const passwordType2 = await passwordInput.getAttribute('type');
        
        testResults.push({
            test: '태블릿 터치 인터랙션',
            toggle_size_ok: isToggleClickable,
            confirm_toggle_size_ok: isConfirmToggleClickable,
            toggle_width: toggleBox?.width || 0,
            toggle_height: toggleBox?.height || 0,
            toggle_works: passwordType1 === 'text' && passwordType2 === 'password',
            passed: isToggleClickable && isConfirmToggleClickable && passwordType1 === 'text' && passwordType2 === 'password'
        });
        
        // === 다양한 비밀번호 시나리오 테스트 ===
        console.log('\n🔒 다양한 비밀번호 시나리오 테스트...');
        
        const scenarios = [
            { input: '', name: '빈 비밀번호', expectedStrength: '', shouldHideUI: true },
            { input: '1234', name: '짧은 비밀번호', expectedStrength: '약함', shouldHideUI: false },
            { input: 'password', name: '영문만', expectedStrength: '약함', shouldHideUI: false },
            { input: 'password123', name: '영문+숫자', expectedStrength: '보통', shouldHideUI: false },
            { input: 'Password123!', name: '강한 비밀번호', expectedStrength: '매우 강함', shouldHideUI: false }
        ];
        
        let scenarioPassed = 0;
        for (const scenario of scenarios) {
            await passwordInput.fill(scenario.input);
            await page.waitForTimeout(300);
            
            const strengthText = scenario.input.length > 0 ? await page.locator('#strength-text').textContent() : '';
            const strengthVisible = await page.locator('#password-strength').isVisible();
            const requirementsVisible = await page.locator('#password-requirements').isVisible();
            
            const passed = scenario.shouldHideUI ? 
                (!strengthVisible && !requirementsVisible) : 
                (strengthVisible && requirementsVisible && strengthText.includes(scenario.expectedStrength));
                
            if (passed) scenarioPassed++;
            
            console.log(`  - ${scenario.name}: ${passed ? '✅' : '❌'} (강도: ${strengthText})`);
        }
        
        testResults.push({
            test: '다양한 비밀번호 시나리오',
            scenarios_passed: scenarioPassed,
            total_scenarios: scenarios.length,
            passed: scenarioPassed === scenarios.length
        });
        
        // === 스크린샷 촬영 ===
        console.log('\n📸 태블릿 스크린샷 촬영...');
        await passwordInput.fill('Password123!');
        await passwordConfirmInput.fill('Password123!');
        await page.waitForTimeout(500);
        
        await page.screenshot({ 
            path: '/var/www/html/topmkt/signup-password-validation-tablet.png',
            fullPage: true 
        });
        
        // === 결과 출력 ===
        console.log('\n🎯 === 태블릿 QA 테스트 결과 ===');
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
        console.error('❌ 태블릿 테스트 실행 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSignupPasswordValidationTablet()
    .then(() => {
        console.log('✅ 태블릿 QA 테스트 완료!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });
