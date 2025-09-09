const { chromium } = require('playwright');

async function debug403Error() {
    console.log('🔍 HTTP 403 에러 상세 분석');
    
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
        
        // POST 요청/응답 상세 캡처
        let postResponse = null;
        let requestDetails = null;
        
        page.on('request', request => {
            if (request.url().includes('/auth/forgot-password') && request.method() === 'POST') {
                requestDetails = {
                    url: request.url(),
                    method: request.method(),
                    headers: request.headers(),
                    postData: request.postData()
                };
                console.log('📤 POST 요청 상세:');
                console.log(`   URL: ${requestDetails.url}`);
                console.log(`   Content-Type: ${requestDetails.headers['content-type']}`);
                console.log(`   X-Requested-With: ${requestDetails.headers['x-requested-with']}`);
                console.log(`   Accept: ${requestDetails.headers['accept']}`);
                if (requestDetails.postData) {
                    console.log(`   POST Data: ${requestDetails.postData.substring(0, 200)}...`);
                }
            }
        });
        
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && response.request().method() === 'POST') {
                try {
                    const text = await response.text();
                    postResponse = {
                        status: response.status(),
                        statusText: response.statusText(),
                        headers: response.headers(),
                        body: text
                    };
                    
                    console.log('\n📥 POST 응답 상세:');
                    console.log(`   HTTP ${postResponse.status} ${postResponse.statusText}`);
                    console.log(`   Content-Type: ${postResponse.headers['content-type']}`);
                    console.log(`   Content-Length: ${postResponse.headers['content-length']}`);
                    console.log(`   응답 본문: "${postResponse.body}"`);
                    
                    // JSON 파싱 시도
                    try {
                        const jsonData = JSON.parse(postResponse.body);
                        console.log('✅ JSON 파싱 성공:');
                        console.log(`   성공: ${jsonData.success}`);
                        console.log(`   에러: ${jsonData.error}`);
                        console.log(`   메시지: ${jsonData.message}`);
                    } catch (parseError) {
                        console.log(`❌ JSON 파싱 실패: ${parseError.message}`);
                    }
                    
                } catch (e) {
                    console.log('📥 응답 읽기 실패');
                }
            }
        });
        
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form', { timeout: 10000 });
        
        // CSRF 토큰 확인
        const csrfToken = await page.evaluate(() => {
            const input = document.querySelector('input[name="csrf_token"]');
            return input ? input.value : null;
        });
        
        console.log(`🔐 CSRF 토큰: ${csrfToken ? csrfToken.substring(0, 10) + '...' : 'NOT FOUND'}`);
        
        // 실제 가입된 번호로 테스트 (대신 일반적인 한국 번호 형식)
        console.log('📝 한국 전화번호 형식으로 테스트...');
        await page.fill('input[name="phone"]', '010-1234-5678'); // 표준 형식
        
        console.log('🖱️ 폼 제출...');
        await page.click('.submit-button');
        
        // 응답 대기
        await page.waitForTimeout(5000);
        
        return {
            request: requestDetails,
            response: postResponse,
            csrfToken: !!csrfToken
        };
        
    } catch (error) {
        console.error('❌ 디버깅 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
debug403Error()
    .then(results => {
        console.log('\n📊 HTTP 403 에러 분석 완료:');
        
        if (results.response) {
            console.log(`HTTP 상태: ${results.response.status}`);
            console.log(`CSRF 토큰 존재: ${results.csrfToken ? '✅' : '❌'}`);
            
            // 403 에러 원인 추정
            if (results.response.body) {
                const body = results.response.body;
                
                if (body.includes('CSRF')) {
                    console.log('🚨 원인: CSRF 토큰 문제');
                } else if (body.includes('휴대폰') || body.includes('전화번호')) {
                    console.log('🚨 원인: 전화번호 관련 문제');
                } else if (body.includes('가입된') || body.includes('계정')) {
                    console.log('🚨 원인: 가입되지 않은 계정');
                } else if (body.includes('요청') || body.includes('제한')) {
                    console.log('🚨 원인: 요청 제한');
                } else {
                    console.log('🚨 원인: 기타 서버 오류');
                }
            }
        } else {
            console.log('❌ 응답을 캡처하지 못했습니다.');
        }
    })
    .catch(error => {
        console.error('💥 디버깅 실행 실패:', error);
        process.exit(1);
    });