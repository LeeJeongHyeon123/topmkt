const { chromium } = require('playwright');

async function testSignupPasswordValidation() {
    console.log('🚀 회원가입 비밀번호 검증 QA 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 회원가입 페이지로 이동
        console.log('📄 회원가입 페이지 접속...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForLoadState('networkidle');
        
        // 페이지 로딩 완료 대기
        await page.waitForSelector('#password', { timeout: 10000 });
        console.log('✅ 페이지 로딩 완료');
        
        // 테스트 결과 저장 배열
        const testResults = [];
        
        // === 테스트 1: 빈 비밀번호 입력 시 상태 확인 ===
        console.log('\n📝 테스트 1: 빈 비밀번호 상태 확인...');
        const passwordInput = page.locator('#password');
        await passwordInput.fill('');
        
        // 요구사항 박스가 숨겨져 있는지 확인
        const requirementsHidden = await page.locator('#password-requirements').isVisible();
        const strengthHidden = await page.locator('#password-strength').isVisible();
        
        testResults.push({
            test: '빈 비밀번호 상태',
            requirements_hidden: !requirementsHidden,
            strength_hidden: !strengthHidden,
            passed: !requirementsHidden && !strengthHidden
        });
        
        // === 테스트 2: 약한 비밀번호 입력 (숫자만) ===
        console.log('\n📝 테스트 2: 약한 비밀번호 (12345678)...');
        await passwordInput.fill('12345678');
        await page.waitForTimeout(500);
        
        // 요구사항 상태 확인
        const req1_length = await page.locator('#req-length.valid').count();
        const req1_letter = await page.locator('#req-letter.valid').count();
        const req1_number = await page.locator('#req-number.valid').count();
        const req1_special = await page.locator('#req-special.valid').count();
        
        // 강도 표시기 확인
        const strength1_visible = await page.locator('#password-strength').isVisible();
        const strength1_text = await page.locator('#strength-text').textContent();
        const strength1_weak = await page.locator('.strength-fill.weak').count();
        
        // 입력 필드 상태 확인
        const input1_invalid = await page.locator('#password.invalid').count();
        const icon1_invalid = await page.locator('#password-status-icon.invalid').count();
        
        testResults.push({
            test: '약한 비밀번호 (12345678)',
            length_valid: req1_length > 0,
            letter_valid: req1_letter > 0,
            number_valid: req1_number > 0,
            special_valid: req1_special > 0,
            strength_visible: strength1_visible,
            strength_text: strength1_text,
            strength_weak: strength1_weak > 0,
            input_invalid: input1_invalid > 0,
            icon_invalid: icon1_invalid > 0,
            passed: req1_length > 0 && req1_letter === 0 && req1_number > 0 && req1_special === 0 && strength1_visible && input1_invalid > 0
        });
        
        // === 테스트 3: 중간 강도 비밀번호 (영문+숫자) ===
        console.log('\n📝 테스트 3: 중간 강도 비밀번호 (password123)...');
        await passwordInput.fill('password123');
        await page.waitForTimeout(500);
        
        const req2_length = await page.locator('#req-length.valid').count();
        const req2_letter = await page.locator('#req-letter.valid').count();
        const req2_number = await page.locator('#req-number.valid').count();
        const req2_special = await page.locator('#req-special.valid').count();
        
        const strength2_text = await page.locator('#strength-text').textContent();
        const strength2_fair = await page.locator('.strength-fill.fair').count();
        const input2_invalid = await page.locator('#password.invalid').count();
        
        testResults.push({
            test: '중간 강도 비밀번호 (password123)',
            length_valid: req2_length > 0,
            letter_valid: req2_letter > 0,
            number_valid: req2_number > 0,
            special_valid: req2_special > 0,
            strength_text: strength2_text,
            strength_fair: strength2_fair > 0,
            input_invalid: input2_invalid > 0,
            passed: req2_length > 0 && req2_letter > 0 && req2_number > 0 && req2_special === 0 && input2_invalid > 0
        });
        
        // === 테스트 4: 강한 비밀번호 (영문+숫자+특수문자) ===
        console.log('\n📝 테스트 4: 강한 비밀번호 (Password123!)...');
        await passwordInput.fill('Password123!');
        await page.waitForTimeout(500);
        
        const req3_length = await page.locator('#req-length.valid').count();
        const req3_letter = await page.locator('#req-letter.valid').count();
        const req3_number = await page.locator('#req-number.valid').count();
        const req3_special = await page.locator('#req-special.valid').count();
        
        const strength3_text = await page.locator('#strength-text').textContent();
        const strength3_strong = await page.locator('.strength-fill.strong').count();
        const input3_valid = await page.locator('#password.valid').count();
        const icon3_valid = await page.locator('#password-status-icon.valid').count();
        
        testResults.push({
            test: '강한 비밀번호 (Password123!)',
            length_valid: req3_length > 0,
            letter_valid: req3_letter > 0,
            number_valid: req3_number > 0,
            special_valid: req3_special > 0,
            strength_text: strength3_text,
            strength_strong: strength3_strong > 0,
            input_valid: input3_valid > 0,
            icon_valid: icon3_valid > 0,
            passed: req3_length > 0 && req3_letter > 0 && req3_number > 0 && req3_special > 0 && input3_valid > 0
        });
        
        // === 테스트 5: 비밀번호 확인 불일치 ===
        console.log('\n📝 테스트 5: 비밀번호 확인 불일치...');
        const passwordConfirmInput = page.locator('#password_confirm');
        await passwordConfirmInput.fill('DifferentPassword!');
        await page.waitForTimeout(500);
        
        const match_status_visible = await page.locator('#password-match-status').isVisible();
        const match_indicator_invalid = await page.locator('#match-indicator:not(.valid)').count();
        const match_text = await page.locator('#match-text').textContent();
        const confirm_invalid = await page.locator('#password_confirm.invalid').count();
        const confirm_icon_invalid = await page.locator('#password-confirm-status-icon.invalid').count();
        
        testResults.push({
            test: '비밀번호 확인 불일치',
            match_status_visible: match_status_visible,
            match_indicator_invalid: match_indicator_invalid > 0,
            match_text: match_text,
            confirm_invalid: confirm_invalid > 0,
            confirm_icon_invalid: confirm_icon_invalid > 0,
            passed: match_status_visible && match_indicator_invalid > 0 && confirm_invalid > 0
        });
        
        // === 테스트 6: 비밀번호 확인 일치 ===
        console.log('\n📝 테스트 6: 비밀번호 확인 일치...');
        await passwordConfirmInput.fill('Password123!');
        await page.waitForTimeout(500);
        
        const match_indicator_valid = await page.locator('#match-indicator.valid').count();
        const match_text_valid = await page.locator('#match-text').textContent();
        const confirm_valid = await page.locator('#password_confirm.valid').count();
        const confirm_icon_valid = await page.locator('#password-confirm-status-icon.valid').count();
        
        testResults.push({
            test: '비밀번호 확인 일치',
            match_indicator_valid: match_indicator_valid > 0,
            match_text_valid: match_text_valid,
            confirm_valid: confirm_valid > 0,
            confirm_icon_valid: confirm_icon_valid > 0,
            passed: match_indicator_valid > 0 && confirm_valid > 0 && confirm_icon_valid > 0
        });
        
        // === 스크린샷 촬영 ===
        console.log('\n📸 최종 상태 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/signup-password-validation-desktop.png',
            fullPage: true 
        });
        
        // === 결과 출력 ===
        console.log('\n🎯 === QA 테스트 결과 (데스크톱) ===');
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
        console.error('❌ 테스트 실행 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSignupPasswordValidation()
    .then(() => {
        console.log('✅ 데스크톱 QA 테스트 완료!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });
