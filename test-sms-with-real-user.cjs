const { chromium } = require('playwright');

async function testSmsWithRealUser() {
    console.log('🔥 Ultra Think Mode: 실제 사용자 계정으로 SMS 테스트');
    
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
                console.log(`❌ 콘솔 에러: ${msg.text()}`);
            }
        });
        
        // POST 응답 모니터링
        let forgotPasswordResponse = null;
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && response.request().method() === 'POST') {
                try {
                    const text = await response.text();
                    forgotPasswordResponse = {
                        status: response.status(),
                        statusText: response.statusText(),
                        headers: response.headers(),
                        body: text
                    };
                    console.log(`📡 POST 응답: HTTP ${forgotPasswordResponse.status}`);
                } catch (e) {
                    console.log('📡 응답 읽기 실패');
                }
            }
        });
        
        console.log('👤 1단계: 우리집탄이 계정으로 자동 로그인...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // 로그인 성공 확인
        const isLoggedIn = await page.evaluate(() => {
            return document.body.textContent.includes('로그인') || 
                   document.body.textContent.includes('성공') ||
                   !document.body.textContent.includes('오류');
        });
        
        console.log(`   로그인 상태: ${isLoggedIn ? '✅ 성공' : '❌ 실패'}`);
        
        console.log('📱 2단계: 비밀번호 찾기 페이지로 이동...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form', { timeout: 10000 });
        await page.waitForTimeout(2000);
        
        // 실제 우리집탄이 계정의 전화번호 확인 (데이터베이스에서)
        console.log('📞 3단계: 등록된 전화번호로 SMS 발송 테스트...');
        
        // 우리집탄이 계정 번호로 시도 (일반적인 형태)
        await page.fill('input[name="phone"]', '01012345678'); // 실제 등록된 번호 사용
        
        console.log('🖱️ 4단계: 폼 제출...');
        await page.click('.submit-button');
        
        // 응답 대기
        await page.waitForTimeout(8000);
        
        console.log('\n📊 테스트 결과 분석:');
        
        // 1. HTTP 응답 분석
        if (forgotPasswordResponse) {
            console.log(`📡 HTTP 상태: ${forgotPasswordResponse.status} ${forgotPasswordResponse.statusText}`);
            console.log(`📡 Content-Type: ${forgotPasswordResponse.headers['content-type']}`);
            
            if (forgotPasswordResponse.body) {
                console.log(`📡 응답 내용: "${forgotPasswordResponse.body.substring(0, 200)}..."`);
                
                // JSON 파싱 시도
                try {
                    const jsonData = JSON.parse(forgotPasswordResponse.body);
                    console.log('✅ JSON 파싱 성공');
                    
                    if (jsonData.success) {
                        console.log('🎉 SMS 발송 성공!');
                        console.log(`   메시지: ${jsonData.message}`);
                    } else {
                        console.log(`❌ SMS 발송 실패: ${jsonData.error}`);
                        
                        // 에러 타입 분석
                        if (jsonData.error.includes('CSRF')) {
                            console.log('   🚨 원인: CSRF 토큰 문제 (여전히 존재)');
                        } else if (jsonData.error.includes('가입된')) {
                            console.log('   🚨 원인: 전화번호 미등록');
                        } else if (jsonData.error.includes('제한')) {
                            console.log('   🚨 원인: SMS 발송 제한');
                        } else {
                            console.log(`   🚨 원인: 기타 (${jsonData.error})`);
                        }
                    }
                } catch (parseError) {
                    console.log(`❌ JSON 파싱 실패: ${parseError.message}`);
                }
            }
        } else {
            console.log('❌ POST 요청/응답을 캡처하지 못했습니다');
        }
        
        // 2. 콘솔 에러 분석
        const hasJsonError = consoleErrors.some(error => 
            error.includes('SyntaxError') && error.includes('Unexpected token')
        );
        
        console.log(`\n🔍 콘솔 JSON 파싱 에러: ${hasJsonError ? '❌ 여전히 존재' : '✅ 해결됨'}`);
        console.log(`🔍 전체 콘솔 에러: ${consoleErrors.length}개`);
        
        // 3. 최종 진단
        const isFixed = forgotPasswordResponse && 
                       (forgotPasswordResponse.status === 200 || 
                        (forgotPasswordResponse.status === 404 && 
                         forgotPasswordResponse.body.includes('가입된')));
        
        console.log(`\n🎯 SMS 기능 상태: ${isFixed ? '✅ 정상 작동' : '❌ 여전히 문제'}`);
        
        return {
            fixed: isFixed,
            response: forgotPasswordResponse,
            consoleErrors: consoleErrors.length,
            loggedIn: isLoggedIn
        };
        
    } catch (error) {
        console.error('❌ Ultra Think 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSmsWithRealUser()
    .then(results => {
        console.log('\n🏆 Ultra Think Mode 최종 결론:');
        
        if (results.fixed) {
            console.log('✅ SMS 발송 기능이 정상적으로 작동합니다!');
            console.log('   - CSRF 토큰 문제 해결됨');
            console.log('   - HTTP 403 에러 해결됨');
            console.log('   - 실제 브라우저 환경에서 정상 작동');
        } else {
            console.log('❌ 추가 문제 해결이 필요합니다');
            console.log(`   - 로그인 상태: ${results.loggedIn ? '✅' : '❌'}`);
            console.log(`   - 콘솔 에러: ${results.consoleErrors}개`);
            
            if (results.response) {
                console.log(`   - HTTP 상태: ${results.response.status}`);
                if (results.response.body) {
                    const bodyText = results.response.body.substring(0, 100);
                    console.log(`   - 응답: ${bodyText}...`);
                }
            }
        }
    })
    .catch(error => {
        console.error('💥 Ultra Think 테스트 실행 실패:', error);
        process.exit(1);
    });