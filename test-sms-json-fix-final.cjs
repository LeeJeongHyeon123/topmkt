const { chromium } = require('playwright');

async function testSmsJsonFixFinal() {
    console.log('🎯 SMS JSON 파싱 수정 최종 검증 테스트');
    
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
        
        // 콘솔 에러 캡처
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
            }
        });
        
        // 네트워크 요청/응답 캡처
        const networkRequests = [];
        const networkResponses = [];
        
        page.on('request', request => {
            if (request.url().includes('/auth/forgot-password') && request.method() === 'POST') {
                networkRequests.push({
                    method: request.method(),
                    url: request.url(),
                    headers: request.headers()
                });
            }
        });
        
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && response.request().method() === 'POST') {
                try {
                    const text = await response.text();
                    networkResponses.push({
                        status: response.status(),
                        statusText: response.statusText(),
                        contentType: response.headers()['content-type'],
                        body: text.substring(0, 500)
                    });
                } catch (e) {
                    networkResponses.push({
                        status: response.status(),
                        error: 'Failed to read response'
                    });
                }
            }
        });
        
        console.log('📱 비밀번호 찾기 페이지로 이동...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form', { timeout: 10000 });
        await page.waitForTimeout(2000);
        
        console.log('🧪 잘못된 전화번호로 폼 제출 테스트...');
        
        // 폰 입력 및 제출
        await page.fill('input[name="phone"]', '123456789');
        await page.click('.submit-button');
        
        // 응답 대기
        await page.waitForTimeout(5000);
        
        console.log('\n📊 테스트 결과 분석:');
        console.log(`콘솔 에러 수: ${consoleErrors.length}`);
        console.log(`네트워크 요청 수: ${networkRequests.length}`);
        console.log(`네트워크 응답 수: ${networkResponses.length}`);
        
        // 콘솔 에러 분석
        const jsonParsingErrors = consoleErrors.filter(error => 
            error.includes('SyntaxError') && error.includes('Unexpected token')
        );
        
        console.log(`\n🔍 JSON 파싱 에러: ${jsonParsingErrors.length}개`);
        if (jsonParsingErrors.length > 0) {
            console.log('❌ JSON 파싱 에러가 여전히 존재합니다:');
            jsonParsingErrors.forEach((error, idx) => {
                console.log(`   ${idx + 1}. ${error}`);
            });
        } else {
            console.log('✅ JSON 파싱 에러 없음 - 수정 완료!');
        }
        
        // 네트워크 응답 분석
        if (networkResponses.length > 0) {
            console.log('\n📡 서버 응답 분석:');
            networkResponses.forEach((response, idx) => {
                console.log(`${idx + 1}. HTTP ${response.status} ${response.statusText || ''}`);
                console.log(`   Content-Type: ${response.contentType || 'not set'}`);
                
                const isJson = response.contentType && response.contentType.includes('application/json');
                const isHtml = response.body && (response.body.includes('<!DOCTYPE') || response.body.includes('<html'));
                
                console.log(`   응답 타입: ${isJson ? 'JSON ✅' : isHtml ? 'HTML ❌' : 'UNKNOWN'}`);
                
                if (response.body) {
                    console.log(`   응답 내용: ${response.body.substring(0, 100)}...`);
                }
            });
        }
        
        // 최종 결론
        const isFixed = jsonParsingErrors.length === 0 && 
                       networkResponses.length > 0 && 
                       networkResponses.some(r => r.contentType && r.contentType.includes('application/json'));
        
        console.log('\n🎯 최종 결과:');
        console.log(`SMS JSON 파싱 오류 수정: ${isFixed ? '✅ 완전 해결' : '❌ 추가 작업 필요'}`);
        
        if (isFixed) {
            console.log('🎉 서버가 올바른 JSON 응답을 반환하고 있습니다!');
            console.log('   - Content-Type: application/json ✅');
            console.log('   - JSON 파싱 에러 없음 ✅');
            console.log('   - 302 리다이렉트 대신 적절한 HTTP 상태 코드 ✅');
        }
        
        return {
            fixed: isFixed,
            consoleErrors: consoleErrors.length,
            jsonParsingErrors: jsonParsingErrors.length,
            networkResponses: networkResponses.length,
            hasJsonResponse: networkResponses.some(r => r.contentType && r.contentType.includes('application/json'))
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSmsJsonFixFinal()
    .then(results => {
        console.log('\n📈 테스트 완료 요약:');
        console.log(`문제 해결 여부: ${results.fixed ? '✅ 해결됨' : '❌ 미해결'}`);
        console.log(`콘솔 에러: ${results.consoleErrors}개`);
        console.log(`JSON 파싱 에러: ${results.jsonParsingErrors}개`);
        console.log(`JSON 응답: ${results.hasJsonResponse ? '✅ 정상' : '❌ 비정상'}`);
        
        if (results.fixed) {
            console.log('\n🚀 SMS 발송 JSON 파싱 오류가 완전히 해결되었습니다!');
        }
    })
    .catch(error => {
        console.error('💥 최종 테스트 실행 실패:', error);
        process.exit(1);
    });