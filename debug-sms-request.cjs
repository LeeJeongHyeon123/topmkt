const { chromium } = require('playwright');

async function debugSmsRequest() {
    console.log('🔍 SMS 요청 응답 상세 분석');
    
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
        
        // 네트워크 요청과 응답 캡처
        const requests = [];
        const responses = [];
        
        page.on('request', request => {
            if (request.url().includes('/auth/forgot-password')) {
                requests.push({
                    url: request.url(),
                    method: request.method(),
                    headers: request.headers(),
                    postData: request.postData()
                });
            }
        });
        
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password')) {
                try {
                    const text = await response.text();
                    responses.push({
                        url: response.url(),
                        status: response.status(),
                        statusText: response.statusText(),
                        headers: response.headers(),
                        body: text.substring(0, 1000) // 처음 1000자만
                    });
                } catch (e) {
                    responses.push({
                        url: response.url(),
                        status: response.status(),
                        statusText: response.statusText(),
                        headers: response.headers(),
                        body: 'Failed to read response body'
                    });
                }
            }
        });
        
        // 1. 비밀번호 찾기 페이지 로딩
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form', { timeout: 10000 });
        await page.waitForTimeout(2000);
        
        // 2. CSRF 토큰 확인
        const csrfToken = await page.evaluate(() => {
            const input = document.querySelector('input[name="csrf_token"]');
            return input ? input.value : null;
        });
        
        console.log(`🔐 CSRF 토큰: ${csrfToken ? csrfToken.substring(0, 10) + '...' : 'NOT FOUND'}`);
        
        // 3. 잘못된 전화번호로 테스트
        console.log('🧪 잘못된 전화번호로 테스트 요청...');
        
        await page.fill('input[name="phone"]', '123456789');
        await page.click('.submit-button');
        
        // 응답 대기
        await page.waitForTimeout(5000);
        
        // 4. 요청/응답 분석
        console.log('\n📊 네트워크 분석 결과:');
        console.log(`요청 수: ${requests.length}`);
        console.log(`응답 수: ${responses.length}`);
        
        if (requests.length > 0) {
            console.log('\n📤 요청 상세:');
            requests.forEach((req, idx) => {
                console.log(`${idx + 1}. ${req.method} ${req.url}`);
                console.log(`   Content-Type: ${req.headers['content-type'] || 'not set'}`);
                console.log(`   Accept: ${req.headers['accept'] || 'not set'}`);
                console.log(`   X-Requested-With: ${req.headers['x-requested-with'] || 'not set'}`);
                if (req.postData) {
                    console.log(`   POST Data: ${req.postData.substring(0, 200)}...`);
                }
            });
        }
        
        if (responses.length > 0) {
            console.log('\n📥 응답 상세:');
            responses.forEach((res, idx) => {
                console.log(`${idx + 1}. HTTP ${res.status} ${res.statusText}`);
                console.log(`   Content-Type: ${res.headers['content-type'] || 'not set'}`);
                console.log(`   Content-Length: ${res.headers['content-length'] || 'not set'}`);
                console.log(`   응답 본문 시작 (처음 200자):`);
                console.log(`   ${res.body.substring(0, 200)}...`);
                
                // HTML 응답인지 JSON 응답인지 확인
                const isHtml = res.body.trim().startsWith('<!DOCTYPE') || res.body.trim().startsWith('<html');
                const isJson = res.body.trim().startsWith('{') || res.body.trim().startsWith('[');
                
                console.log(`   응답 타입: ${isHtml ? 'HTML' : isJson ? 'JSON' : 'UNKNOWN'}`);
                
                if (isHtml) {
                    console.log('   ❌ HTML 응답이 반환되었습니다. 이것이 문제의 원인입니다.');
                    
                    // 에러 메시지가 있는지 확인
                    const errorMatch = res.body.match(/<title>(.*?)<\/title>/);
                    if (errorMatch) {
                        console.log(`   HTML 제목: ${errorMatch[1]}`);
                    }
                }
            });
        }
        
        console.log('\n✅ SMS 요청 디버그 분석 완료');
        
        return {
            requests: requests.length,
            responses: responses.length,
            hasHtmlResponse: responses.some(r => r.body.trim().startsWith('<!DOCTYPE')),
            hasJsonResponse: responses.some(r => r.body.trim().startsWith('{')),
            responseDetails: responses
        };
        
    } catch (error) {
        console.error('❌ 디버그 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
debugSmsRequest()
    .then(results => {
        console.log('\n📊 최종 분석 결과:');
        console.log(`요청 수: ${results.requests}`);
        console.log(`응답 수: ${results.responses}`);
        console.log(`HTML 응답: ${results.hasHtmlResponse ? '✅ 발견됨' : '❌ 없음'}`);
        console.log(`JSON 응답: ${results.hasJsonResponse ? '✅ 발견됨' : '❌ 없음'}`);
        
        if (results.hasHtmlResponse) {
            console.log('\n🚨 문제 분석: 서버가 JSON 대신 HTML을 반환하고 있습니다.');
            console.log('이것이 "SyntaxError: Unexpected token" 오류의 원인입니다.');
            console.log('서버 측 PHP 코드나 라우팅에 문제가 있을 가능성이 높습니다.');
        }
    })
    .catch(error => {
        console.error('💥 디버그 실행 실패:', error);
        process.exit(1);
    });