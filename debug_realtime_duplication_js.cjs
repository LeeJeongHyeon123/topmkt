const { chromium } = require('playwright');

async function debugRealtimeDuplicationJS() {
    console.log('🔧 실시간 중복검사 JavaScript 오류 진단 시작...');
    
    const browser = await chromium.launch({ 
        headless: true, // 서버 환경에서는 헤드리스 모드
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 콘솔 로그 캡처
        const consoleLogs = [];
        page.on('console', msg => {
            const type = msg.type();
            const text = msg.text();
            consoleLogs.push(`[${type.toUpperCase()}] ${text}`);
            console.log(`🟡 [${type.toUpperCase()}] ${text}`);
        });
        
        // JavaScript 오류 캡처
        const jsErrors = [];
        page.on('pageerror', error => {
            jsErrors.push(error.message);
            console.log(`🔴 JavaScript Error: ${error.message}`);
        });
        
        console.log('📄 회원가입 페이지 접속...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('#nickname', { timeout: 10000 });
        
        // 페이지 로딩 후 잠시 대기
        await page.waitForTimeout(2000);
        
        console.log('\n🔍 필수 함수들 존재 여부 확인...');
        
        const functionsCheck = await page.evaluate(() => {
            const checks = {};
            
            // 필수 함수들 존재 여부 확인
            checks.checkNicknameDuplication = typeof window.checkNicknameDuplication === 'function';
            checks.checkPhoneDuplication = typeof window.checkPhoneDuplication === 'function';
            
            // 필수 변수들 존재 여부 확인
            checks.nicknameCheckTimeout = typeof window.nicknameCheckTimeout !== 'undefined';
            checks.phoneCheckTimeout = typeof window.phoneCheckTimeout !== 'undefined';
            checks.DEBOUNCE_DELAY = typeof window.DEBOUNCE_DELAY !== 'undefined';
            checks.isNicknameAvailable = typeof window.isNicknameAvailable !== 'undefined';
            checks.isPhoneAvailable = typeof window.isPhoneAvailable !== 'undefined';
            
            // DOM 요소 존재 여부 확인
            checks.nicknameInput = !!document.getElementById('nickname');
            checks.phoneInput = !!document.getElementById('phone');
            checks.nicknameStatusMessage = !!document.getElementById('nickname-status-message');
            checks.phoneStatusMessage = !!document.getElementById('phone-status-message');
            checks.nicknameStatusIcon = !!document.getElementById('nickname-status-icon');
            checks.phoneStatusIcon = !!document.getElementById('phone-status-icon');
            
            return checks;
        });
        
        console.log('\n📊 함수 및 요소 존재 여부:');
        Object.entries(functionsCheck).forEach(([key, value]) => {
            console.log(`  ${value ? '✅' : '❌'} ${key}`);
        });
        
        // API 엔드포인트 테스트
        console.log('\n🌐 API 엔드포인트 테스트...');
        
        try {
            const nicknameResponse = await page.evaluate(async () => {
                const response = await fetch('/auth/check-nickname', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nickname: '테스트' })
                });
                const text = await response.text();
                return { status: response.status, body: text };
            });
            console.log(`✅ 닉네임 API 응답: ${nicknameResponse.status} - ${nicknameResponse.body.substring(0, 100)}`);
        } catch (error) {
            console.log(`❌ 닉네임 API 오류: ${error.message}`);
        }
        
        try {
            const phoneResponse = await page.evaluate(async () => {
                const response = await fetch('/auth/check-phone', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone: '01012345678' })
                });
                const text = await response.text();
                return { status: response.status, body: text };
            });
            console.log(`✅ 휴대폰 API 응답: ${phoneResponse.status} - ${phoneResponse.body.substring(0, 100)}`);
        } catch (error) {
            console.log(`❌ 휴대폰 API 오류: ${error.message}`);
        }
        
        // 수동 함수 실행 테스트
        console.log('\n🧪 수동 함수 실행 테스트...');
        
        if (functionsCheck.checkNicknameDuplication) {
            try {
                await page.evaluate(() => {
                    if (window.checkNicknameDuplication) {
                        console.log('🔍 수동으로 닉네임 중복검사 함수 실행...');
                        window.checkNicknameDuplication('테스트');
                    }
                });
                await page.waitForTimeout(2000);
                console.log('✅ 닉네임 중복검사 함수 실행 완료');
            } catch (error) {
                console.log(`❌ 닉네임 함수 실행 오류: ${error.message}`);
            }
        }
        
        if (functionsCheck.checkPhoneDuplication) {
            try {
                await page.evaluate(() => {
                    if (window.checkPhoneDuplication) {
                        console.log('🔍 수동으로 휴대폰 중복검사 함수 실행...');
                        window.checkPhoneDuplication('01012345678');
                    }
                });
                await page.waitForTimeout(2000);
                console.log('✅ 휴대폰 중복검사 함수 실행 완료');
            } catch (error) {
                console.log(`❌ 휴대폰 함수 실행 오류: ${error.message}`);
            }
        }
        
        // 입력 이벤트 테스트
        console.log('\n⌨️ 입력 이벤트 테스트...');
        
        const nicknameInput = page.locator('#nickname');
        const phoneInput = page.locator('#phone');
        
        console.log('📝 닉네임 입력 테스트...');
        await nicknameInput.fill('');
        await nicknameInput.fill('테스트' + Date.now());
        await page.waitForTimeout(1500); // 디바운싱 대기
        
        console.log('📱 휴대폰 입력 테스트...');
        await phoneInput.fill('');
        await phoneInput.fill('01012345678');
        await page.waitForTimeout(1500); // 디바운싱 대기
        
        // 최종 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/debug-realtime-duplication-js.png',
            fullPage: true 
        });
        
        console.log('\n📋 === 진단 결과 요약 ===');
        console.log(`🟡 콘솔 로그 수: ${consoleLogs.length}`);
        console.log(`🔴 JavaScript 오류 수: ${jsErrors.length}`);
        
        if (jsErrors.length > 0) {
            console.log('\n🔴 JavaScript 오류들:');
            jsErrors.forEach((error, index) => {
                console.log(`  ${index + 1}. ${error}`);
            });
        }
        
        const missingElements = Object.entries(functionsCheck)
            .filter(([key, value]) => !value)
            .map(([key, value]) => key);
            
        if (missingElements.length > 0) {
            console.log('\n❌ 누락된 요소들:');
            missingElements.forEach(element => {
                console.log(`  - ${element}`);
            });
        }
        
        // 헤드리스 모드에서는 대기 불필요
        console.log('\n✅ 진단 완료, 브라우저 종료...');
        
        return {
            functionsCheck,
            consoleLogs,
            jsErrors,
            missingElements
        };
        
    } catch (error) {
        console.error('❌ 진단 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
debugRealtimeDuplicationJS()
    .then((result) => {
        console.log('✅ JavaScript 진단 완료!');
        if (result.jsErrors.length === 0 && result.missingElements.length === 0) {
            console.log('🎉 모든 기능이 정상적으로 구현되어 있습니다!');
        } else {
            console.log('⚠️ 일부 문제가 발견되었습니다. 위의 진단 결과를 확인하세요.');
        }
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 진단 실패:', error);
        process.exit(1);
    });