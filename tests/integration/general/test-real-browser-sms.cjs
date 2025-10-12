const { chromium } = require('playwright');

async function testRealBrowserSms() {
    console.log('🌐 실제 브라우저 SMS 폼 제출 테스트');
    
    const browser = await chromium.launch({ 
        headless: false,  // 헤드리스 끄고 실제 브라우저로 테스트
        slowMo: 1000
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1366, height: 768 }
        });
        
        const page = await context.newPage();
        
        // 콘솔 에러만 캡처
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
                console.log(`❌ 콘솔 에러: ${msg.text()}`);
            }
        });
        
        // POST 응답만 캡처
        let postResponse = null;
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && response.request().method() === 'POST') {
                try {
                    const text = await response.text();
                    postResponse = {
                        status: response.status(),
                        contentType: response.headers()['content-type'],
                        body: text
                    };
                    console.log(`📡 POST 응답: HTTP ${postResponse.status}, Content-Type: ${postResponse.contentType}`);
                } catch (e) {
                    console.log('📡 POST 응답 읽기 실패');
                }
            }
        });
        
        console.log('📱 비밀번호 찾기 페이지로 이동...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForLoadState('networkidle');
        
        console.log('📝 잘못된 전화번호 입력...');
        await page.fill('input[name="phone"]', '123456789');
        
        console.log('🖱️ 제출 버튼 클릭...');
        await page.click('.submit-button');
        
        console.log('⏳ 응답 대기 중...');
        await page.waitForTimeout(5000);
        
        // 결과 분석
        console.log('\n📊 테스트 결과:');
        console.log(`콘솔 에러 수: ${consoleErrors.length}`);
        
        if (postResponse) {
            console.log('✅ POST 요청 성공적으로 캡처됨');
            console.log(`   HTTP 상태: ${postResponse.status}`);
            console.log(`   Content-Type: ${postResponse.contentType}`);
            
            const isJson = postResponse.contentType?.includes('application/json');
            const isHtml = postResponse.body?.includes('<!DOCTYPE');
            
            console.log(`   JSON 응답: ${isJson ? '✅' : '❌'}`);
            console.log(`   HTML 응답: ${isHtml ? '❌' : '✅'}`);
            
            if (postResponse.body) {
                console.log(`   응답 내용: "${postResponse.body.substring(0, 150)}..."`);
                
                if (isJson) {
                    try {
                        const jsonData = JSON.parse(postResponse.body);
                        console.log(`   JSON 파싱: ✅ 성공`);
                        console.log(`   에러 메시지: "${jsonData.error || jsonData.message || 'N/A'}"`);
                    } catch (parseError) {
                        console.log(`   JSON 파싱: ❌ 실패`);
                    }
                }
            }
        } else {
            console.log('❌ POST 요청이 캡처되지 않았습니다');
        }
        
        // JSON 파싱 에러 확인
        const hasJsonError = consoleErrors.some(error => 
            error.includes('SyntaxError') && error.includes('Unexpected token')
        );
        
        console.log(`\n🎯 SMS JSON 파싱 오류: ${hasJsonError ? '❌ 여전히 존재' : '✅ 해결됨'}`);
        
        const success = postResponse && 
                       postResponse.contentType?.includes('application/json') && 
                       !hasJsonError;
        
        console.log(`\n🏆 전체 수정 결과: ${success ? '✅ 완전 해결' : '❌ 추가 작업 필요'}`);
        
        return { success, postResponse, consoleErrors: consoleErrors.length };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testRealBrowserSms()
    .then(results => {
        console.log('\n🎉 최종 결론:');
        if (results.success) {
            console.log('✅ SMS JSON 파싱 오류가 완전히 해결되었습니다!');
            console.log('   - 서버가 올바른 JSON 응답을 반환합니다');
            console.log('   - 콘솔에 JSON 파싱 에러가 없습니다');
            console.log('   - isJsonRequest() 수정이 성공적으로 작동합니다');
        } else {
            console.log('❌ 아직 완전히 해결되지 않았습니다');
            console.log(`   - 콘솔 에러: ${results.consoleErrors}개`);
            if (results.postResponse) {
                console.log(`   - HTTP 상태: ${results.postResponse.status}`);
                console.log(`   - Content-Type: ${results.postResponse.contentType}`);
            }
        }
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });