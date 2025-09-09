const { chromium } = require('playwright');

async function testSmsFinalVerification() {
    console.log('🎯 SMS JSON 파싱 오류 수정 최종 검증');
    
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
        
        // 콘솔 에러 모니터링
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
                if (msg.text().includes('SMS') || msg.text().includes('JSON')) {
                    console.log(`🔍 관련 에러: ${msg.text()}`);
                }
            }
        });
        
        // 네트워크 모니터링 - POST 요청/응답 캡처
        let postRequestDetails = null;
        let postResponseDetails = null;
        
        page.on('request', request => {
            if (request.url().includes('/auth/forgot-password') && request.method() === 'POST') {
                postRequestDetails = {
                    url: request.url(),
                    method: request.method(),
                    headers: request.headers(),
                    hasXRequestedWith: !!request.headers()['x-requested-with'],
                    acceptsJson: request.headers()['accept']?.includes('application/json') || false
                };
                console.log('📤 POST 요청 감지');
                console.log(`   X-Requested-With: ${postRequestDetails.hasXRequestedWith ? '✅' : '❌'}`);
                console.log(`   Accept JSON: ${postRequestDetails.acceptsJson ? '✅' : '❌'}`);
            }
        });
        
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && response.request().method() === 'POST') {
                try {
                    const text = await response.text();
                    postResponseDetails = {
                        status: response.status(),
                        statusText: response.statusText(),
                        contentType: response.headers()['content-type'] || '',
                        body: text,
                        isJson: response.headers()['content-type']?.includes('application/json') || false,
                        isHtml: text.includes('<!DOCTYPE') || text.includes('<html>'),
                        isRedirect: response.status() >= 300 && response.status() < 400
                    };
                    console.log('📥 POST 응답 수신');
                    console.log(`   HTTP ${postResponseDetails.status} ${postResponseDetails.statusText}`);
                    console.log(`   Content-Type: ${postResponseDetails.contentType}`);
                    console.log(`   JSON 응답: ${postResponseDetails.isJson ? '✅' : '❌'}`);
                    console.log(`   HTML 응답: ${postResponseDetails.isHtml ? '❌' : '✅'}`);
                    console.log(`   리다이렉트: ${postResponseDetails.isRedirect ? '❌' : '✅'}`);
                } catch (e) {
                    console.log('📥 응답 읽기 실패');
                }
            }
        });
        
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        
        console.log('📝 전화번호 입력 (잘못된 번호로 테스트)...');
        await page.fill('input[name="phone"]', '123456789');
        
        console.log('🖱️ 폼 제출...');
        // 제출 버튼 클릭하고 네트워크 활동 대기
        const [response] = await Promise.all([
            page.waitForResponse(response => 
                response.url().includes('/auth/forgot-password') && 
                response.request().method() === 'POST',
                { timeout: 15000 }
            ),
            page.click('.submit-button')
        ]);
        
        console.log('✅ 네트워크 요청/응답 캡처 완료');
        
        // 추가 대기 (JavaScript 처리 시간)
        await page.waitForTimeout(3000);
        
        // 결과 분석
        console.log('\n📊 상세 분석 결과:');
        
        // 1. 네트워크 요청 분석
        if (postRequestDetails) {
            console.log('📤 요청 분석:');
            console.log(`   ✅ POST 요청 발생`);
            console.log(`   X-Requested-With 헤더: ${postRequestDetails.hasXRequestedWith ? '✅ 있음' : '❌ 없음'}`);
            console.log(`   Accept JSON 헤더: ${postRequestDetails.acceptsJson ? '✅ 있음' : '❌ 없음'}`);
        } else {
            console.log('📤 요청 분석: ❌ POST 요청이 감지되지 않음');
        }
        
        // 2. 네트워크 응답 분석
        if (postResponseDetails) {
            console.log('\n📥 응답 분석:');
            console.log(`   HTTP 상태: ${postResponseDetails.status} ${postResponseDetails.statusText}`);
            console.log(`   Content-Type: ${postResponseDetails.contentType}`);
            console.log(`   JSON 응답: ${postResponseDetails.isJson ? '✅' : '❌'}`);
            console.log(`   HTML 리다이렉트: ${postResponseDetails.isHtml || postResponseDetails.isRedirect ? '❌' : '✅'}`);
            
            if (postResponseDetails.body && postResponseDetails.isJson) {
                try {
                    const jsonData = JSON.parse(postResponseDetails.body);
                    console.log(`   JSON 파싱: ✅ 성공`);
                    console.log(`   응답 메시지: "${jsonData.error || jsonData.message || 'N/A'}"`);
                } catch (parseError) {
                    console.log(`   JSON 파싱: ❌ 실패 - ${parseError.message}`);
                }
            }
            
            if (postResponseDetails.body) {
                console.log(`   응답 내용 (처음 200자): "${postResponseDetails.body.substring(0, 200)}..."`);
            }
        } else {
            console.log('\n📥 응답 분석: ❌ POST 응답이 캡처되지 않음');
        }
        
        // 3. 콘솔 에러 분석
        console.log('\n🔍 콘솔 에러 분석:');
        console.log(`   총 콘솔 에러: ${consoleErrors.length}개`);
        
        const jsonParsingErrors = consoleErrors.filter(error => 
            error.includes('SyntaxError') && error.includes('Unexpected token')
        );
        
        console.log(`   JSON 파싱 에러: ${jsonParsingErrors.length}개`);
        
        if (jsonParsingErrors.length > 0) {
            console.log('   ❌ JSON 파싱 에러 발견:');
            jsonParsingErrors.forEach((error, idx) => {
                console.log(`      ${idx + 1}. ${error}`);
            });
        } else {
            console.log('   ✅ JSON 파싱 에러 없음');
        }
        
        // 4. 최종 결론
        const isFixed = postResponseDetails && 
                       postResponseDetails.isJson && 
                       !postResponseDetails.isRedirect && 
                       jsonParsingErrors.length === 0;
        
        console.log('\n🎯 최종 결론:');
        console.log(`SMS JSON 파싱 오류 수정: ${isFixed ? '✅ 완전 해결' : '❌ 추가 작업 필요'}`);
        
        if (isFixed) {
            console.log('🎉 성공적으로 수정되었습니다!');
            console.log('   ✅ 서버가 JSON 응답을 반환합니다');
            console.log('   ✅ HTML 리다이렉트가 발생하지 않습니다');
            console.log('   ✅ JSON 파싱 에러가 없습니다');
            console.log('   ✅ isJsonRequest() 메서드 수정이 올바르게 작동합니다');
        } else {
            console.log('⚠️ 아직 완전히 해결되지 않았습니다:');
            if (!postResponseDetails?.isJson) {
                console.log('   - 서버가 여전히 JSON이 아닌 응답을 반환합니다');
            }
            if (postResponseDetails?.isRedirect) {
                console.log('   - 서버가 여전히 리다이렉트를 수행합니다');
            }
            if (jsonParsingErrors.length > 0) {
                console.log('   - 클라이언트에서 JSON 파싱 에러가 발생합니다');
            }
        }
        
        return {
            fixed: isFixed,
            request: postRequestDetails,
            response: postResponseDetails,
            consoleErrors: consoleErrors.length,
            jsonParsingErrors: jsonParsingErrors.length
        };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSmsFinalVerification()
    .then(results => {
        console.log('\n📈 최종 테스트 완료:');
        console.log(`해결 여부: ${results.fixed ? '✅ 해결됨' : '❌ 미해결'}`);
        console.log(`콘솔 에러: ${results.consoleErrors}개`);
        console.log(`JSON 파싱 에러: ${results.jsonParsingErrors}개`);
        
        if (results.fixed) {
            console.log('\n🚀 축하합니다! SMS JSON 파싱 오류가 완전히 해결되었습니다!');
            console.log('   - AuthController.isJsonRequest() 메서드 수정 성공');
            console.log('   - 서버가 올바른 JSON 응답 반환');
            console.log('   - 클라이언트 측 파싱 에러 해결');
        } else {
            console.log('\n🔧 추가 디버깅이 필요합니다.');
        }
    })
    .catch(error => {
        console.error('💥 최종 테스트 실행 실패:', error);
        process.exit(1);
    });