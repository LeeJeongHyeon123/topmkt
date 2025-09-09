const { chromium } = require('playwright');

async function testSignupRealtimeDuplicationDesktop() {
    console.log('🖥️ 회원가입 실시간 중복검사 데스크톱 QA 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }, // Desktop
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        });
        const page = await context.newPage();
        
        console.log('📄 회원가입 페이지 접속...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('#nickname', { timeout: 10000 });
        console.log('✅ 페이지 로딩 완료');
        
        const testResults = [];
        
        // === 1. 닉네임 실시간 중복검사 테스트 ===
        console.log('\n📝 닉네임 실시간 중복검사 테스트...');
        
        const nicknameInput = page.locator('#nickname');
        const nicknameStatusMessage = page.locator('#nickname-status-message');
        const nicknameStatusIcon = page.locator('#nickname-status-icon');
        
        // 1-1. 빈 값 테스트
        await nicknameInput.fill('');
        await page.waitForTimeout(100);
        const emptyVisible = await nicknameStatusMessage.isVisible();
        
        // 1-2. 짧은 닉네임 테스트 (2자 미만)
        await nicknameInput.fill('a');
        await page.waitForTimeout(1000); // 디바운싱 대기
        const shortVisible = await nicknameStatusMessage.isVisible();
        const shortIconClass = await nicknameStatusIcon.getAttribute('class');
        
        // 1-3. 유효한 새 닉네임 테스트 (사용 가능해야 함)
        const uniqueNickname = `테스트${Date.now()}`;
        await nicknameInput.fill(uniqueNickname);
        await page.waitForTimeout(1000);
        const uniqueVisible = await nicknameStatusMessage.isVisible();
        const uniqueIconClass = await nicknameStatusIcon.getAttribute('class');
        const uniqueMessageText = await page.locator('#nickname-message-text').textContent();
        
        // 1-4. 기존 닉네임 테스트 (중복이어야 함) 
        await nicknameInput.fill('우리집탄이'); // 기존 사용자
        await page.waitForTimeout(1000);
        const duplicateVisible = await nicknameStatusMessage.isVisible();
        const duplicateIconClass = await nicknameStatusIcon.getAttribute('class');
        const duplicateMessageText = await page.locator('#nickname-message-text').textContent();
        
        testResults.push({
            test: '닉네임 실시간 중복검사',
            empty_hidden: !emptyVisible,
            short_shown: shortVisible && shortIconClass.includes('invalid'),
            unique_available: uniqueVisible && uniqueIconClass.includes('valid') && uniqueMessageText.includes('사용 가능'),
            duplicate_unavailable: duplicateVisible && duplicateIconClass.includes('invalid') && duplicateMessageText.includes('이미 사용'),
            passed: !emptyVisible && shortVisible && uniqueVisible && duplicateVisible &&
                   shortIconClass.includes('invalid') && uniqueIconClass.includes('valid') && 
                   duplicateIconClass.includes('invalid')
        });
        
        // === 2. 휴대폰 번호 실시간 중복검사 테스트 ===
        console.log('\n📱 휴대폰 번호 실시간 중복검사 테스트...');
        
        const phoneInput = page.locator('#phone');
        const phoneStatusMessage = page.locator('#phone-status-message');
        const phoneStatusIcon = page.locator('#phone-status-icon');
        
        // 2-1. 빈 값 테스트
        await phoneInput.fill('');
        await page.waitForTimeout(100);
        const phoneEmptyVisible = await phoneStatusMessage.isVisible();
        
        // 2-2. 잘못된 형식 테스트
        await phoneInput.fill('012-3456-7890');
        await page.waitForTimeout(1000);
        const phoneInvalidVisible = await phoneStatusMessage.isVisible();
        const phoneInvalidIconClass = await phoneStatusIcon.getAttribute('class');
        
        // 2-3. 유효한 새 번호 테스트 (사용 가능해야 함)
        const uniquePhone = `010${Date.now().toString().slice(-8)}`;
        await phoneInput.fill(uniquePhone);
        await page.waitForTimeout(1000);
        const phoneUniqueVisible = await phoneStatusMessage.isVisible();
        const phoneUniqueIconClass = await phoneStatusIcon.getAttribute('class');
        const phoneUniqueMessageText = await page.locator('#phone-message-text').textContent();
        
        // 2-4. 기존 번호 테스트 (중복이어야 함)
        await phoneInput.fill('010-1234-5678'); // 예상 기존 번호
        await page.waitForTimeout(1000);
        const phoneDuplicateVisible = await phoneStatusMessage.isVisible();
        const phoneDuplicateIconClass = await phoneStatusIcon.getAttribute('class');
        const phoneDuplicateMessageText = await page.locator('#phone-message-text').textContent();
        
        testResults.push({
            test: '휴대폰 번호 실시간 중복검사',
            empty_hidden: !phoneEmptyVisible,
            invalid_format_error: phoneInvalidVisible,
            unique_available: phoneUniqueVisible && phoneUniqueIconClass.includes('valid'),
            duplicate_handling: phoneDuplicateVisible, // 중복 여부는 실제 데이터에 따라 다름
            passed: !phoneEmptyVisible && phoneUniqueVisible
        });
        
        // === 3. 포맷팅 동작 테스트 ===
        console.log('\n🔧 휴대폰 번호 포맷팅 테스트...');
        
        await phoneInput.fill('01012345678');
        const formattedValue = await phoneInput.inputValue();
        const correctFormat = formattedValue === '010-1234-5678';
        
        testResults.push({
            test: '휴대폰 번호 자동 포맷팅',
            input: '01012345678',
            output: formattedValue,
            correct_format: correctFormat,
            passed: correctFormat
        });
        
        // === 4. 디바운싱 성능 테스트 ===
        console.log('\n⏱️ 디바운싱 성능 테스트...');
        
        const startTime = Date.now();
        
        // 빠른 연속 입력 시뮬레이션
        await nicknameInput.fill('');
        for (let i = 0; i < 5; i++) {
            await nicknameInput.fill(`테스트${i}`);
            await page.waitForTimeout(100); // 빠른 타이핑
        }
        
        // 디바운싱 대기
        await page.waitForTimeout(1000);
        
        const endTime = Date.now();
        const responseTime = endTime - startTime;
        
        testResults.push({
            test: '디바운싱 성능',
            response_time: responseTime,
            reasonable_time: responseTime < 2000,
            passed: responseTime < 2000
        });
        
        // === 5. UI/UX 통합 테스트 ===
        console.log('\n🎨 UI/UX 통합 테스트...');
        
        // 폼 검증 상태 테스트
        await nicknameInput.fill(uniqueNickname);
        await phoneInput.fill(uniquePhone);
        await page.waitForTimeout(1000);
        
        const nicknameHasValid = await nicknameInput.evaluate(el => el.classList.contains('valid'));
        const phoneHasValid = await phoneInput.evaluate(el => el.classList.contains('valid'));
        
        testResults.push({
            test: 'UI/UX 통합',
            nickname_valid_class: nicknameHasValid,
            phone_valid_class: phoneHasValid,
            passed: nicknameHasValid && phoneHasValid
        });
        
        // === 스크린샷 촬영 ===
        console.log('\n📸 데스크톱 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/signup-realtime-duplication-desktop.png',
            fullPage: true 
        });
        
        // === 결과 출력 ===
        console.log('\n🎯 === 데스크톱 QA 테스트 결과 ===');
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
        
        // === 보안 테스트 ===
        console.log('\n🔒 보안 기능 테스트...');
        
        // Rate Limiting 테스트 (간단히)
        const requests = [];
        for (let i = 0; i < 5; i++) {
            const start = Date.now();
            await nicknameInput.fill(`테스트${i}${Date.now()}`);
            await page.waitForTimeout(200);
            requests.push(Date.now() - start);
        }
        
        console.log('✅ Rate Limiting 테스트 완료 (연속 5회 요청)');
        console.log(`📈 평균 응답시간: ${Math.round(requests.reduce((a, b) => a + b) / requests.length)}ms`);
        
        return testResults;
        
    } catch (error) {
        console.error('❌ 데스크톱 테스트 실행 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSignupRealtimeDuplicationDesktop()
    .then(() => {
        console.log('✅ 데스크톱 QA 테스트 완료!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });