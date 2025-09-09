const { chromium } = require('playwright');

async function testCsrfSessionIssue() {
    console.log('🔥 Ultra Think Mode: CSRF 세션 문제 정확한 분석');
    console.log('📝 로그아웃 상태에서 비밀번호 찾기 테스트');
    
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
        
        // 모든 쿠키 모니터링
        let cookiesBefore = [];
        let cookiesAfter = [];
        
        // POST 요청/응답 상세 캡처
        let postRequest = null;
        let postResponse = null;
        
        page.on('request', request => {
            if (request.url().includes('/auth/forgot-password') && request.method() === 'POST') {
                postRequest = {
                    url: request.url(),
                    method: request.method(),
                    headers: request.headers()
                };
                console.log('📤 POST 요청 헤더:');
                Object.entries(postRequest.headers).forEach(([key, value]) => {
                    if (key.includes('cookie') || key.includes('session')) {
                        console.log(`   ${key}: ${value.substring(0, 50)}...`);
                    }
                });
            }
        });
        
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && response.request().method() === 'POST') {
                try {
                    const text = await response.text();
                    postResponse = {
                        status: response.status(),
                        headers: response.headers(),
                        body: text
                    };
                    console.log('📥 POST 응답:');
                    console.log(`   상태: HTTP ${postResponse.status}`);
                    console.log(`   응답: ${postResponse.body}`);
                } catch (e) {
                    console.log('📥 응답 읽기 실패');
                }
            }
        });
        
        console.log('🔓 1단계: 로그아웃 상태 확인 (쿠키 클리어)...');
        await context.clearCookies();
        
        console.log('📱 2단계: 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form', { timeout: 10000 });
        
        // 페이지 로딩 후 쿠키 확인
        cookiesBefore = await context.cookies();
        console.log(`🍪 페이지 로딩 후 쿠키: ${cookiesBefore.length}개`);
        cookiesBefore.forEach((cookie, idx) => {
            console.log(`   ${idx + 1}. ${cookie.name}: ${cookie.value.substring(0, 20)}...`);
        });
        
        // CSRF 토큰 확인
        const csrfInfo = await page.evaluate(() => {
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            return {
                exists: !!csrfInput,
                value: csrfInput ? csrfInput.value : null
            };
        });
        
        console.log(`🔐 HTML CSRF 토큰: ${csrfInfo.exists ? '✅ 존재' : '❌ 없음'}`);
        if (csrfInfo.value) {
            console.log(`   값: ${csrfInfo.value.substring(0, 20)}...`);
        }
        
        await page.waitForTimeout(2000);
        
        console.log('📞 3단계: 전화번호 입력...');
        await page.fill('input[name="phone"]', '010-9999-8888'); // 존재하지 않을 번호
        
        console.log('🖱️ 4단계: 폼 제출 및 세션 추적...');
        
        // 폼 제출 전후 쿠키 비교
        await page.click('.submit-button');
        await page.waitForTimeout(5000);
        
        // 제출 후 쿠키 확인
        cookiesAfter = await context.cookies();
        console.log(`🍪 폼 제출 후 쿠키: ${cookiesAfter.length}개`);
        
        // 쿠키 변화 분석
        const sessionCookieBefore = cookiesBefore.find(c => c.name.includes('session') || c.name === 'PHPSESSID');
        const sessionCookieAfter = cookiesAfter.find(c => c.name.includes('session') || c.name === 'PHPSESSID');
        
        console.log('\n🔍 세션 쿠키 분석:');
        console.log(`   제출 전: ${sessionCookieBefore ? sessionCookieBefore.value.substring(0, 15) + '...' : 'NOT FOUND'}`);
        console.log(`   제출 후: ${sessionCookieAfter ? sessionCookieAfter.value.substring(0, 15) + '...' : 'NOT FOUND'}`);
        console.log(`   쿠키 유지: ${sessionCookieBefore && sessionCookieAfter && sessionCookieBefore.value === sessionCookieAfter.value ? '✅' : '❌'}`);
        
        return {
            csrfExists: csrfInfo.exists,
            postRequest,
            postResponse,
            cookiesChanged: cookiesBefore.length !== cookiesAfter.length,
            sessionMaintained: sessionCookieBefore && sessionCookieAfter && sessionCookieBefore.value === sessionCookieAfter.value
        };
        
    } catch (error) {
        console.error('❌ CSRF 세션 분석 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testCsrfSessionIssue()
    .then(results => {
        console.log('\n📊 Ultra Think 분석 결과:');
        console.log(`CSRF 토큰 존재: ${results.csrfExists ? '✅' : '❌'}`);
        console.log(`세션 유지: ${results.sessionMaintained ? '✅' : '❌'}`);
        console.log(`쿠키 변화: ${results.cookiesChanged ? '⚠️ 있음' : '✅ 없음'}`);
        
        if (results.postResponse) {
            console.log(`HTTP 상태: ${results.postResponse.status}`);
            
            if (results.postResponse.status === 403) {
                console.log('\n🚨 403 에러 원인 분석:');
                if (!results.sessionMaintained) {
                    console.log('   ❌ 세션 쿠키가 유지되지 않음');
                    console.log('   → 브라우저는 정상, curl 테스트에서만 문제');
                } else {
                    console.log('   ⚠️ 다른 원인으로 CSRF 검증 실패');
                    console.log('   → 서버 측 CSRF 로직 문제 가능성');
                }
            } else if (results.postResponse.status === 404) {
                console.log('✅ CSRF 통과, 전화번호 미등록 (정상 응답)');
            } else if (results.postResponse.status === 200) {
                console.log('🎉 완전 성공! SMS 발송 완료');
            }
        }
        
        console.log('\n🎯 최종 진단:');
        if (results.sessionMaintained && results.postResponse?.status !== 403) {
            console.log('✅ 실제 브라우저에서는 CSRF 문제 없음');
            console.log('   curl 테스트의 세션 쿠키 처리 문제였음');
        } else {
            console.log('❌ 브라우저에서도 CSRF 문제 존재');
            console.log('   서버 측 수정 필요');
        }
    })
    .catch(error => {
        console.error('💥 Ultra Think 분석 실행 실패:', error);
        process.exit(1);
    });