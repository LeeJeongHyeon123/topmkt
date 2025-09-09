const { chromium } = require('playwright');

async function testSignupRealtimeDuplicationTablet() {
    console.log('📱 회원가입 실시간 중복검사 태블릿 QA 테스트 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 768, height: 1024 }, // iPad
            userAgent: 'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1',
            hasTouch: true,
            isMobile: true
        });
        const page = await context.newPage();
        
        console.log('📄 회원가입 페이지 접속 (태블릿)...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('#nickname', { timeout: 10000 });
        console.log('✅ 태블릿 페이지 로딩 완료');
        
        const testResults = [];
        
        // === 1. 태블릿 레이아웃 테스트 ===
        console.log('\n📱 태블릿 레이아웃 테스트...');
        
        const layoutTest = await page.evaluate(() => {
            const container = document.querySelector('.signup-container, .form-container, form');
            const nicknameInput = document.getElementById('nickname');
            const phoneInput = document.getElementById('phone');
            const nicknameStatus = document.getElementById('nickname-status-message');
            const phoneStatus = document.getElementById('phone-status-message');
            
            return {
                container: {
                    exists: !!container,
                    width: container ? container.offsetWidth : 0,
                    centered: container ? (container.offsetWidth < window.innerWidth) : false
                },
                inputs: {
                    nickname: {
                        width: nicknameInput ? nicknameInput.offsetWidth : 0,
                        height: nicknameInput ? nicknameInput.offsetHeight : 0,
                        touchTarget: nicknameInput ? (nicknameInput.offsetHeight >= 44) : false
                    },
                    phone: {
                        width: phoneInput ? phoneInput.offsetWidth : 0,
                        height: phoneInput ? phoneInput.offsetHeight : 0,
                        touchTarget: phoneInput ? (phoneInput.offsetHeight >= 44) : false
                    }
                },
                statusMessages: {
                    nickname: !!nicknameStatus,
                    phone: !!phoneStatus,
                    allPresent: !!nicknameStatus && !!phoneStatus
                },
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };
        });
        
        testResults.push({
            test: '태블릿 레이아웃',
            containerCentered: layoutTest.container.centered,
            touchTargets: layoutTest.inputs.nickname.touchTarget && layoutTest.inputs.phone.touchTarget,
            statusElementsPresent: layoutTest.statusMessages.allPresent,
            layoutDetails: layoutTest,
            passed: layoutTest.container.centered && layoutTest.statusMessages.allPresent
        });
        
        // === 2. 태블릿 실시간 중복검사 테스트 ===
        console.log('\n🔍 태블릿 실시간 중복검사 테스트...');
        
        const nicknameInput = page.locator('#nickname');
        const phoneInput = page.locator('#phone');
        
        const uniqueNickname = `태블릿${Date.now()}`;
        const uniquePhone = `010${Date.now().toString().slice(-8)}`;
        
        // 닉네임 테스트
        console.log(`📝 고유 닉네임 입력: ${uniqueNickname}`);
        await nicknameInput.click();
        await nicknameInput.fill(uniqueNickname);
        await page.waitForTimeout(1200); // 디바운싱 대기
        
        const nicknameResult = await page.evaluate(() => {
            const statusMessage = document.getElementById('nickname-status-message');
            const statusIcon = document.getElementById('nickname-status-icon');
            const input = document.getElementById('nickname');
            
            return {
                statusVisible: statusMessage ? statusMessage.style.display !== 'none' : false,
                iconClass: statusIcon ? statusIcon.className : '',
                inputClass: input ? input.className : '',
                hasValidClass: input ? input.classList.contains('valid') : false
            };
        });
        
        // 휴대폰 테스트
        console.log(`📱 고유 휴대폰 입력: ${uniquePhone}`);
        await phoneInput.click();
        await phoneInput.fill(uniquePhone);
        await page.waitForTimeout(1200);
        
        const phoneResult = await page.evaluate(() => {
            const statusMessage = document.getElementById('phone-status-message');
            const statusIcon = document.getElementById('phone-status-icon');
            const input = document.getElementById('phone');
            
            return {
                statusVisible: statusMessage ? statusMessage.style.display !== 'none' : false,
                iconClass: statusIcon ? statusIcon.className : '',
                inputClass: input ? input.className : '',
                hasValidClass: input ? input.classList.contains('valid') : false
            };
        });
        
        testResults.push({
            test: '태블릿 실시간 중복검사',
            nicknameWorking: nicknameResult.statusVisible || nicknameResult.hasValidClass,
            phoneWorking: phoneResult.statusVisible || phoneResult.hasValidClass,
            nicknameDetails: nicknameResult,
            phoneDetails: phoneResult,
            passed: (nicknameResult.statusVisible || nicknameResult.hasValidClass) && 
                   (phoneResult.statusVisible || phoneResult.hasValidClass)
        });
        
        // === 3. 태블릿 사용성 테스트 ===
        console.log('\n👆 태블릿 사용성 테스트...');
        
        // 더블 탭 줌 테스트 (메타 태그 확인)
        const zoomTest = await page.evaluate(() => {
            const viewportMeta = document.querySelector('meta[name="viewport"]');
            const viewportContent = viewportMeta ? viewportMeta.getAttribute('content') : '';
            
            return {
                hasViewportMeta: !!viewportMeta,
                viewportContent: viewportContent,
                scaleDisabled: viewportContent.includes('user-scalable=no') || 
                              viewportContent.includes('maximum-scale=1'),
                appropriate: viewportContent.includes('width=device-width')
            };
        });
        
        testResults.push({
            test: '태블릿 사용성',
            viewportMetaPresent: zoomTest.hasViewportMeta,
            appropriateViewport: zoomTest.appropriate,
            zoomHandling: zoomTest.scaleDisabled,
            viewportDetails: zoomTest,
            passed: zoomTest.hasViewportMeta && zoomTest.appropriate
        });
        
        // === 4. 태블릿 성능 및 응답성 테스트 ===
        console.log('\n⚡ 태블릿 성능 테스트...');
        
        const performanceStart = Date.now();
        
        // 연속 입력 테스트
        await nicknameInput.fill('');
        const testValues = [`테스트1`, `테스트2`, `태블릿테스트${Date.now()}`];
        
        for (const value of testValues) {
            await nicknameInput.fill(value);
            await page.waitForTimeout(100);
        }
        
        await page.waitForTimeout(1200); // 최종 디바운싱
        const performanceEnd = Date.now();
        
        const finalState = await page.evaluate(() => {
            const nicknameStatus = document.getElementById('nickname-status-message');
            const phoneStatus = document.getElementById('phone-status-message');
            
            return {
                nicknameStatus: nicknameStatus ? nicknameStatus.style.display !== 'none' : false,
                phoneStatus: phoneStatus ? phoneStatus.style.display !== 'none' : false,
                functionsAvailable: typeof window.checkNicknameDuplication === 'function' && 
                                   typeof window.checkPhoneDuplication === 'function'
            };
        });
        
        const responseTime = performanceEnd - performanceStart;
        
        testResults.push({
            test: '태블릿 성능',
            responseTime,
            acceptable: responseTime < 4000,
            finalState,
            functionsWorking: finalState.functionsAvailable,
            passed: responseTime < 4000 && finalState.functionsAvailable
        });
        
        // === 5. 태블릿 랜드스케이프/포트레이트 테스트 ===
        console.log('\n📐 태블릿 화면 회전 테스트...');
        
        // 랜드스케이프 모드로 변경
        await page.setViewportSize({ width: 1024, height: 768 });
        await page.waitForTimeout(500);
        
        const landscapeTest = await page.evaluate(() => {
            const container = document.querySelector('.signup-container, .form-container, form');
            const nicknameInput = document.getElementById('nickname');
            
            return {
                containerVisible: container ? (container.offsetWidth > 0 && container.offsetHeight > 0) : false,
                inputsResponsive: nicknameInput ? (nicknameInput.offsetWidth > 200) : false,
                layoutStable: true // 기본값
            };
        });
        
        // 다시 포트레이트 모드로 복원
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.waitForTimeout(500);
        
        testResults.push({
            test: '태블릿 화면 회전',
            landscapeWorking: landscapeTest.containerVisible && landscapeTest.inputsResponsive,
            layoutStable: landscapeTest.layoutStable,
            landscapeDetails: landscapeTest,
            passed: landscapeTest.containerVisible && landscapeTest.inputsResponsive
        });
        
        // === 스크린샷 촬영 ===
        console.log('\n📸 태블릿 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/signup-realtime-duplication-tablet.png',
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
testSignupRealtimeDuplicationTablet()
    .then(() => {
        console.log('✅ 태블릿 QA 테스트 완료!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });