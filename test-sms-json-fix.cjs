const { chromium } = require('playwright');

async function testSmsJsonFix() {
    console.log('🔍 SMS JSON 파싱 오류 수정 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 콘솔 로그 캡처
        const consoleMessages = [];
        page.on('console', msg => {
            consoleMessages.push({
                type: msg.type(),
                text: msg.text(),
                timestamp: new Date().toISOString()
            });
        });
        
        // 네트워크 요청 캡처
        const networkRequests = [];
        page.on('request', request => {
            if (request.url().includes('/auth/forgot-password')) {
                networkRequests.push({
                    url: request.url(),
                    method: request.method(),
                    headers: request.headers(),
                    timestamp: new Date().toISOString()
                });
            }
        });
        
        // 네트워크 응답 캡처
        const networkResponses = [];
        page.on('response', response => {
            if (response.url().includes('/auth/forgot-password')) {
                networkResponses.push({
                    url: response.url(),
                    status: response.status(),
                    contentType: response.headers()['content-type'] || '',
                    timestamp: new Date().toISOString()
                });
            }
        });
        
        // 1. 비밀번호 찾기 페이지 로딩
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form', { timeout: 10000 });
        await page.waitForTimeout(2000);
        
        // 2. 폼 요소 확인
        const formExists = await page.locator('form').count() > 0;
        const phoneInputExists = await page.locator('input[name="phone"]').count() > 0;
        const submitButtonExists = await page.locator('.submit-button').count() > 0;
        
        console.log('📊 폼 요소 확인:');
        console.log(`폼 존재: ${formExists ? '✅' : '❌'}`);
        console.log(`전화번호 입력: ${phoneInputExists ? '✅' : '❌'}`);
        console.log(`제출 버튼: ${submitButtonExists ? '✅' : '❌'}`);
        
        if (!formExists || !phoneInputExists || !submitButtonExists) {
            throw new Error('필수 폼 요소를 찾을 수 없습니다');
        }
        
        // 3. 유효하지 않은 전화번호로 테스트 (에러 응답 확인)
        console.log('🧪 유효하지 않은 전화번호로 에러 응답 테스트...');
        
        await page.fill('input[name="phone"]', '123456789'); // 잘못된 형식
        await page.click('.submit-button');
        
        // 응답 대기
        await page.waitForTimeout(3000);
        
        // 4. 네트워크 응답 분석
        console.log('📊 네트워크 응답 분석:');
        console.log(`요청 수: ${networkRequests.length}`);
        console.log(`응답 수: ${networkResponses.length}`);
        
        if (networkResponses.length > 0) {
            const response = networkResponses[0];
            console.log(`응답 상태: ${response.status}`);
            console.log(`Content-Type: ${response.contentType}`);
            console.log(`응답 URL: ${response.url}`);
            
            // JSON 응답인지 확인
            const isJsonResponse = response.contentType.includes('application/json');
            console.log(`JSON 응답 여부: ${isJsonResponse ? '✅ JSON' : '❌ Not JSON'}`);
            
            if (!isJsonResponse) {
                console.log('⚠️ 응답이 JSON이 아닙니다. HTML이 반환된 것으로 보입니다.');
            }
        }
        
        // 5. 콘솔 에러 확인
        console.log('🐛 콘솔 메시지 분석:');
        const errorMessages = consoleMessages.filter(msg => msg.type === 'error');
        const warningMessages = consoleMessages.filter(msg => msg.type === 'warning');
        
        console.log(`에러 메시지 수: ${errorMessages.length}`);
        console.log(`경고 메시지 수: ${warningMessages.length}`);
        
        if (errorMessages.length > 0) {
            console.log('❌ 발견된 에러들:');
            errorMessages.forEach((msg, idx) => {
                console.log(`${idx + 1}. ${msg.text}`);
            });
        }
        
        // 6. "SyntaxError: Unexpected token" 에러 확인
        const jsonParseErrors = errorMessages.filter(msg => 
            msg.text.includes('SyntaxError: Unexpected token') ||
            msg.text.includes('not valid JSON') ||
            msg.text.includes('DOCTYPE')
        );
        
        console.log(`JSON 파싱 에러 수: ${jsonParseErrors.length}`);
        
        if (jsonParseErrors.length > 0) {
            console.log('❌ JSON 파싱 에러가 여전히 발생합니다:');
            jsonParseErrors.forEach((msg, idx) => {
                console.log(`${idx + 1}. ${msg.text}`);
            });
        } else {
            console.log('✅ JSON 파싱 에러가 해결되었습니다!');
        }
        
        // 7. 스크린샷 촬영
        await page.screenshot({ 
            path: 'sms-json-fix-test-result.png',
            fullPage: true
        });
        
        console.log('✅ SMS JSON 수정 테스트 완료');
        
        return {
            formElementsOk: formExists && phoneInputExists && submitButtonExists,
            networkResponses,
            errorCount: errorMessages.length,
            jsonParseErrors: jsonParseErrors.length,
            isFixed: jsonParseErrors.length === 0 && networkResponses.some(r => r.contentType.includes('application/json'))
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSmsJsonFix()
    .then(results => {
        console.log('\n📊 최종 SMS JSON 수정 테스트 결과:');
        console.log(`폼 요소: ${results.formElementsOk ? '✅ 정상' : '❌ 문제'}`);
        console.log(`네트워크 응답: ${results.networkResponses.length}개`);
        console.log(`에러 메시지: ${results.errorCount}개`);
        console.log(`JSON 파싱 에러: ${results.jsonParseErrors}개`);
        console.log(`수정 완료 여부: ${results.isFixed ? '✅ 수정됨' : '❌ 아직 문제 있음'}`);
        
        if (results.isFixed) {
            console.log('🎉 SMS 발송 JSON 파싱 오류가 성공적으로 해결되었습니다!');
        } else {
            console.log('⚠️ 추가 수정이 필요할 수 있습니다.');
        }
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });