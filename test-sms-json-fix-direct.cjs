const { chromium } = require('playwright');

async function testSmsJsonFixDirect() {
    console.log('🎯 SMS JSON 파싱 수정 직접 검증 테스트');
    
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
        
        // 네트워크 이벤트 캡처
        let networkCaptured = false;
        let responseDetails = null;
        
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && response.request().method() === 'POST') {
                try {
                    const text = await response.text();
                    responseDetails = {
                        status: response.status(),
                        statusText: response.statusText(),
                        headers: response.headers(),
                        body: text,
                        isJson: response.headers()['content-type']?.includes('application/json'),
                        isHtml: text.includes('<!DOCTYPE') || text.includes('<html')
                    };
                    networkCaptured = true;
                    console.log(`📡 POST 응답 캡처: HTTP ${responseDetails.status}`);
                } catch (e) {
                    responseDetails = {
                        status: response.status(),
                        error: 'Response body read failed'
                    };
                }
            }
        });
        
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form', { timeout: 10000 });
        await page.waitForTimeout(2000);
        
        // JavaScript 로딩 확인
        const hasFormHandler = await page.evaluate(() => {
            return typeof window.ForgotPasswordForm !== 'undefined';
        });
        
        console.log(`📋 폼 핸들러 로딩: ${hasFormHandler ? '✅' : '❌'}`);
        
        console.log('🧪 폼 직접 제출 테스트...');
        
        // 전화번호 입력
        await page.fill('input[name="phone"]', '123456789');
        
        // 폼 제출 버튼 클릭 및 네트워크 응답 대기
        const submitPromise = page.click('.submit-button');
        const responsePromise = page.waitForResponse(response => 
            response.url().includes('/auth/forgot-password') && 
            response.request().method() === 'POST',
            { timeout: 15000 }
        );
        
        console.log('⏳ 폼 제출 및 응답 대기 중...');
        
        try {
            await Promise.all([submitPromise, responsePromise]);
            await page.waitForTimeout(3000); // 추가 대기
        } catch (e) {
            console.log(`⚠️ 응답 대기 중 오류: ${e.message}`);
        }
        
        // 결과 분석
        console.log('\n📊 테스트 결과:');
        
        if (networkCaptured && responseDetails) {
            console.log(`HTTP 상태: ${responseDetails.status} ${responseDetails.statusText || ''}`);
            console.log(`Content-Type: ${responseDetails.headers['content-type'] || 'not set'}`);
            console.log(`JSON 응답: ${responseDetails.isJson ? '✅' : '❌'}`);
            console.log(`HTML 응답: ${responseDetails.isHtml ? '❌' : '✅'}`);
            
            if (responseDetails.body) {
                console.log(`응답 내용 (처음 200자):`);
                console.log(`"${responseDetails.body.substring(0, 200)}"`);
                
                // JSON 파싱 테스트
                try {
                    const jsonData = JSON.parse(responseDetails.body);
                    console.log('✅ JSON 파싱 성공');
                    console.log(`에러 메시지: "${jsonData.error || jsonData.message || 'N/A'}"`);
                } catch (parseError) {
                    console.log(`❌ JSON 파싱 실패: ${parseError.message}`);
                }
            }
            
            // 성공 조건 확인
            const isFixed = responseDetails.isJson && 
                           !responseDetails.isHtml && 
                           responseDetails.status !== 302;
            
            console.log(`\n🎯 수정 결과: ${isFixed ? '✅ 완전 해결' : '❌ 추가 작업 필요'}`);
            
            return {
                fixed: isFixed,
                details: responseDetails
            };
            
        } else {
            console.log('❌ 네트워크 요청이 캡처되지 않았습니다.');
            console.log('   - 폼 제출이 발생하지 않았거나');
            console.log('   - JavaScript 오류가 있을 수 있습니다.');
            
            return {
                fixed: false,
                error: 'No network request captured'
            };
        }
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSmsJsonFixDirect()
    .then(results => {
        console.log('\n🏁 최종 결론:');
        if (results.fixed) {
            console.log('🎉 SMS JSON 파싱 오류가 완전히 해결되었습니다!');
            console.log('   ✅ 서버가 JSON 응답 반환');
            console.log('   ✅ HTML 리다이렉트 대신 적절한 HTTP 상태');
            console.log('   ✅ JSON 파싱 에러 해결');
        } else {
            console.log('⚠️ 추가 디버깅이 필요할 수 있습니다.');
            if (results.details) {
                console.log(`   - HTTP 상태: ${results.details.status}`);
                console.log(`   - JSON 응답: ${results.details.isJson ? 'Yes' : 'No'}`);
            }
        }
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });