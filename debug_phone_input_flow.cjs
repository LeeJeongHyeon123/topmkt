const { chromium } = require('playwright');

async function debugPhoneInputFlow() {
    console.log('📱 휴대폰 입력 핸들러 흐름 상세 진단 시작...');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 콘솔 로그 수집
        const consoleLogs = [];
        page.on('console', msg => {
            const text = msg.text();
            consoleLogs.push(text);
            if (text.includes('휴대폰') || text.includes('중복') || text.includes('삭제')) {
                console.log(`🟡 ${text}`);
            }
        });
        
        // 네트워크 요청 모니터링
        const networkRequests = [];
        page.on('request', request => {
            if (request.url().includes('/auth/check-phone')) {
                networkRequests.push({
                    url: request.url(),
                    method: request.method(),
                    postData: request.postData()
                });
                console.log(`🌐 Phone API Request: ${request.postData()}`);
            }
        });
        
        console.log('📄 회원가입 페이지 접속...');
        await page.goto('https://www.topmktx.com/auth/signup');
        await page.waitForLoadState('networkidle');
        await page.waitForSelector('#phone', { timeout: 10000 });
        
        console.log('\n=== 휴대폰 입력 흐름 단계별 테스트 ===');
        
        const phoneInput = page.locator('#phone');
        
        // 단계별 입력 테스트
        const testPhoneNumber = '01012345678';
        console.log(`\n📱 테스트 번호: ${testPhoneNumber}`);
        
        // 1단계: 필드 초기화
        console.log('\n1️⃣ 필드 초기화...');
        await phoneInput.fill('');
        await page.waitForTimeout(500);
        
        // 2단계: 전체 번호를 한 번에 입력
        console.log('\n2️⃣ 전체 번호 한 번에 입력...');
        await phoneInput.fill(testPhoneNumber);
        await page.waitForTimeout(2000); // 디바운싱 + 처리 시간 대기
        
        // 상태 확인
        const afterFullInput = await page.evaluate(() => {
            const phoneStatus = document.getElementById('phone-status-message');
            const phoneIcon = document.getElementById('phone-status-icon');
            const phoneInput = document.getElementById('phone');
            
            return {
                phoneValue: phoneInput.value,
                statusVisible: phoneStatus ? phoneStatus.style.display !== 'none' : false,
                statusDisplay: phoneStatus ? getComputedStyle(phoneStatus).display : 'none',
                iconClass: phoneIcon ? phoneIcon.className : '',
                inputClass: phoneInput.className,
                isDeleting: window.isDeleting,
                phoneCheckTimeout: window.phoneCheckTimeout,
                isPhoneAvailable: window.isPhoneAvailable
            };
        });
        
        console.log('\n📊 전체 입력 후 상태:');
        console.log(JSON.stringify(afterFullInput, null, 2));
        
        // 3단계: 문자별 순차 입력 테스트
        console.log('\n3️⃣ 문자별 순차 입력 테스트...');
        await phoneInput.fill('');
        await page.waitForTimeout(200);
        
        for (let i = 1; i <= testPhoneNumber.length; i++) {
            const partialNumber = testPhoneNumber.substring(0, i);
            console.log(`   타이핑: "${partialNumber}"`);
            await phoneInput.fill(partialNumber);
            await page.waitForTimeout(100);
        }
        
        // 최종 디바운싱 대기
        await page.waitForTimeout(1200);
        
        const afterSequentialInput = await page.evaluate(() => {
            const phoneStatus = document.getElementById('phone-status-message');
            const phoneIcon = document.getElementById('phone-status-icon');
            const phoneInput = document.getElementById('phone');
            
            return {
                phoneValue: phoneInput.value,
                statusVisible: phoneStatus ? phoneStatus.style.display !== 'none' : false,
                statusDisplay: phoneStatus ? getComputedStyle(phoneStatus).display : 'none',
                iconClass: phoneIcon ? phoneIcon.className : '',
                inputClass: phoneInput.className,
                isDeleting: window.isDeleting,
                isPhoneAvailable: window.isPhoneAvailable
            };
        });
        
        console.log('\n📊 순차 입력 후 상태:');
        console.log(JSON.stringify(afterSequentialInput, null, 2));
        
        // 4단계: 수동 함수 호출 테스트
        console.log('\n4️⃣ 수동 함수 호출 테스트...');
        const cleanPhone = testPhoneNumber;
        console.log(`   수동 호출: checkPhoneDuplication("${cleanPhone}")`);
        
        await page.evaluate((phone) => {
            if (window.checkPhoneDuplication) {
                console.log(`🔍 수동으로 휴대폰 중복검사 호출: ${phone}`);
                window.checkPhoneDuplication(phone);
            } else {
                console.error('❌ checkPhoneDuplication 함수를 찾을 수 없음');
            }
        }, cleanPhone);
        
        await page.waitForTimeout(2000);
        
        const afterManualCall = await page.evaluate(() => {
            const phoneStatus = document.getElementById('phone-status-message');
            const phoneIcon = document.getElementById('phone-status-icon');
            const phoneInput = document.getElementById('phone');
            
            return {
                phoneValue: phoneInput.value,
                statusVisible: phoneStatus ? phoneStatus.style.display !== 'none' : false,
                statusDisplay: phoneStatus ? getComputedStyle(phoneStatus).display : 'none',
                iconClass: phoneIcon ? phoneIcon.className : '',
                inputClass: phoneInput.className,
                isPhoneAvailable: window.isPhoneAvailable
            };
        });
        
        console.log('\n📊 수동 호출 후 상태:');
        console.log(JSON.stringify(afterManualCall, null, 2));
        
        // 최종 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/debug-phone-input-flow.png',
            fullPage: true 
        });
        
        console.log('\n📊 === 진단 결과 요약 ===');
        console.log(`🌐 API 요청 수: ${networkRequests.length}`);
        
        if (networkRequests.length > 0) {
            console.log('📞 API 요청들:');
            networkRequests.forEach((req, index) => {
                console.log(`  ${index + 1}. ${req.postData}`);
            });
        } else {
            console.log('❌ API 요청이 전혀 발생하지 않음');
        }
        
        console.log('\n🔍 관련 콘솔 로그 요약:');
        const relevantLogs = consoleLogs.filter(log => 
            log.includes('휴대폰') || log.includes('중복') || log.includes('삭제') || log.includes('포맷')
        );
        relevantLogs.slice(-10).forEach((log, index) => {
            console.log(`  ${index + 1}. ${log}`);
        });
        
        return {
            afterFullInput,
            afterSequentialInput,
            afterManualCall,
            networkRequests: networkRequests.length,
            relevantLogsCount: relevantLogs.length
        };
        
    } catch (error) {
        console.error('❌ 휴대폰 입력 흐름 진단 중 오류:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
debugPhoneInputFlow()
    .then((result) => {
        console.log('✅ 휴대폰 입력 흐름 진단 완료!');
        if (result.networkRequests > 0) {
            console.log('🎉 API 호출이 발생했습니다!');
        } else {
            console.log('⚠️ API 호출이 발생하지 않았습니다. 추가 디버깅이 필요합니다.');
        }
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 진단 실패:', error);
        process.exit(1);
    });