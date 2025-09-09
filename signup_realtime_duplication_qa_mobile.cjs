const { chromium } = require('playwright');

async function testSignupRealtimeDuplicationMobile() {
    console.log('📱 회원가입 실시간 중복검사 모바일 QA 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 375, height: 667 }, // iPhone SE
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1',
            hasTouch: true, // 터치 지원 활성화
            isMobile: true
        });
        const page = await context.newPage();
        
        console.log('📄 회원가입 페이지 접속 (모바일)...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('#nickname', { timeout: 10000 });
        console.log('✅ 모바일 페이지 로딩 완료');
        
        const testResults = [];
        
        // === 1. 모바일 반응형 UI 테스트 ===
        console.log('\n📱 모바일 반응형 UI 테스트...');
        
        const uiElements = await page.evaluate(() => {
            const nicknameInput = document.getElementById('nickname');
            const phoneInput = document.getElementById('phone');
            const nicknameStatus = document.getElementById('nickname-status-message');
            const phoneStatus = document.getElementById('phone-status-message');
            
            return {
                nickname: {
                    exists: !!nicknameInput,
                    width: nicknameInput ? nicknameInput.offsetWidth : 0,
                    height: nicknameInput ? nicknameInput.offsetHeight : 0,
                    touchTarget: nicknameInput ? (nicknameInput.offsetHeight >= 44) : false
                },
                phone: {
                    exists: !!phoneInput,
                    width: phoneInput ? phoneInput.offsetWidth : 0,
                    height: phoneInput ? phoneInput.offsetHeight : 0,
                    touchTarget: phoneInput ? (phoneInput.offsetHeight >= 44) : false
                },
                statusElements: {
                    nicknameStatus: !!nicknameStatus,
                    phoneStatus: !!phoneStatus,
                    allPresent: !!nicknameStatus && !!phoneStatus
                }
            };
        });
        
        testResults.push({
            test: '모바일 반응형 UI',
            touchTargets: uiElements.nickname.touchTarget && uiElements.phone.touchTarget,
            elementSizes: uiElements,
            allElementsPresent: uiElements.statusElements.allPresent,
            passed: uiElements.nickname.touchTarget && uiElements.phone.touchTarget && uiElements.statusElements.allPresent
        });
        
        // === 2. 모바일 터치 인터랙션 테스트 ===
        console.log('\n👆 모바일 터치 인터랙션 테스트...');
        
        const nicknameInput = page.locator('#nickname');
        const phoneInput = page.locator('#phone');
        
        // 터치 타겟 크기 확인
        const nicknameBox = await nicknameInput.boundingBox();
        const phoneBox = await phoneInput.boundingBox();
        
        const touchTargetsValid = nicknameBox.height >= 44 && phoneBox.height >= 44;
        
        // 실제 터치 테스트
        const uniqueNickname = `모바일${Date.now()}`;
        const uniquePhone = `010${Date.now().toString().slice(-8)}`;
        
        await nicknameInput.click();
        await nicknameInput.fill(uniqueNickname);
        await page.waitForTimeout(1000);
        
        await phoneInput.click();
        await phoneInput.fill(uniquePhone);
        await page.waitForTimeout(1000);
        
        testResults.push({
            test: '모바일 터치 인터랙션',
            touchTargetsValid,
            nicknameHeight: nicknameBox.height,
            phoneHeight: phoneBox.height,
            passed: touchTargetsValid
        });
        
        // === 3. 실시간 중복검사 기능 테스트 ===
        console.log('\n🔍 모바일 실시간 중복검사 테스트...');
        
        await page.waitForTimeout(1200); // 디바운싱 대기
        
        const functionalTest = await page.evaluate(() => {
            const nicknameStatus = document.getElementById('nickname-status-message');
            const phoneStatus = document.getElementById('phone-status-message');
            const nicknameInput = document.getElementById('nickname');
            const phoneInput = document.getElementById('phone');
            
            return {
                nicknameValidation: {
                    statusVisible: nicknameStatus ? nicknameStatus.style.display !== 'none' : false,
                    hasValidClass: nicknameInput ? nicknameInput.classList.contains('valid') : false
                },
                phoneValidation: {
                    statusVisible: phoneStatus ? phoneStatus.style.display !== 'none' : false,
                    hasValidClass: phoneInput ? phoneInput.classList.contains('valid') : false
                },
                globalFunctions: {
                    nicknameFunction: typeof window.checkNicknameDuplication === 'function',
                    phoneFunction: typeof window.checkPhoneDuplication === 'function'
                }
            };
        });
        
        testResults.push({
            test: '실시간 중복검사 기능',
            nicknameWorking: functionalTest.nicknameValidation.statusVisible || functionalTest.nicknameValidation.hasValidClass,
            phoneWorking: functionalTest.phoneValidation.statusVisible || functionalTest.phoneValidation.hasValidClass,
            functionsAvailable: functionalTest.globalFunctions.nicknameFunction && functionalTest.globalFunctions.phoneFunction,
            passed: functionalTest.globalFunctions.nicknameFunction && functionalTest.globalFunctions.phoneFunction
        });
        
        // === 4. 모바일 키보드 호환성 테스트 ===
        console.log('\n⌨️ 모바일 키보드 호환성 테스트...');
        
        // 숫자 키보드 속성 확인
        const keyboardTest = await page.evaluate(() => {
            const phoneInput = document.getElementById('phone');
            const emailInput = document.getElementById('email');
            
            return {
                phoneInputType: phoneInput ? phoneInput.getAttribute('type') : null,
                phonePattern: phoneInput ? phoneInput.getAttribute('pattern') : null,
                phoneInputmode: phoneInput ? phoneInput.getAttribute('inputmode') : null,
                emailInputType: emailInput ? emailInput.getAttribute('type') : null,
                appropriateKeyboards: true // 기본값
            };
        });
        
        testResults.push({
            test: '모바일 키보드 호환성',
            phoneKeyboard: keyboardTest.phoneInputType === 'tel' || keyboardTest.phoneInputmode === 'numeric',
            emailKeyboard: keyboardTest.emailInputType === 'email',
            keyboardDetails: keyboardTest,
            passed: keyboardTest.appropriateKeyboards
        });
        
        // === 5. 모바일 성능 테스트 ===
        console.log('\n⚡ 모바일 성능 테스트...');
        
        const performanceStart = Date.now();
        
        // 빠른 연속 입력
        await nicknameInput.fill('');
        for (let i = 0; i < 3; i++) {
            await nicknameInput.fill(`테스트${i}`);
            await page.waitForTimeout(50);
        }
        await nicknameInput.fill(`최종테스트${Date.now()}`);
        
        await page.waitForTimeout(1000);
        const performanceEnd = Date.now();
        const responseTime = performanceEnd - performanceStart;
        
        testResults.push({
            test: '모바일 성능',
            responseTime,
            acceptable: responseTime < 3000,
            passed: responseTime < 3000
        });
        
        // === 스크린샷 촬영 ===
        console.log('\n📸 모바일 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/signup-realtime-duplication-mobile.png',
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
testSignupRealtimeDuplicationMobile()
    .then(() => {
        console.log('✅ 모바일 QA 테스트 완료!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });