const { chromium } = require('playwright');

async function check403Response() {
    console.log('🔍 HTTP 403 응답 내용 정확히 확인');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        
        const page = await context.newPage();
        
        // 정확한 POST 응답 캡처
        let captured = false;
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && 
                response.request().method() === 'POST' && 
                !captured) {
                
                captured = true;
                console.log(`📡 응답 상태: HTTP ${response.status()}`);
                console.log(`📡 Content-Type: ${response.headers()['content-type']}`);
                
                try {
                    const text = await response.text();
                    console.log(`📡 응답 길이: ${text.length}자`);
                    console.log(`📡 응답 전체 내용:`);
                    console.log(`"${text}"`);
                    
                    // JSON 파싱 시도
                    try {
                        const jsonData = JSON.parse(text);
                        console.log(`✅ JSON 파싱 성공:`);
                        console.log(`   success: ${jsonData.success}`);
                        console.log(`   error: ${jsonData.error}`);
                        
                        // CSRF 관련 에러인지 확인
                        if (jsonData.error && jsonData.error.includes('CSRF')) {
                            console.log(`🚨 CSRF 토큰 문제 확인됨!`);
                        } else {
                            console.log(`🔍 다른 종류의 에러: ${jsonData.error}`);
                        }
                        
                    } catch (parseError) {
                        console.log(`❌ JSON 파싱 실패: ${parseError.message}`);
                    }
                } catch (e) {
                    console.log('❌ 응답 내용 읽기 실패');
                }
            }
        });
        
        console.log('📱 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form');
        
        // 실제 CSRF 토큰 값 확인
        const csrfTokenInfo = await page.evaluate(() => {
            const input = document.querySelector('input[name="csrf_token"]');
            return {
                exists: !!input,
                value: input ? input.value : null,
                length: input ? input.value.length : 0
            };
        });
        
        console.log(`🔐 CSRF 토큰 정보:`);
        console.log(`   존재: ${csrfTokenInfo.exists}`);
        console.log(`   길이: ${csrfTokenInfo.length}자`);
        if (csrfTokenInfo.value) {
            console.log(`   값 앞부분: ${csrfTokenInfo.value.substring(0, 20)}...`);
        }
        
        console.log('📞 테스트 번호 입력...');
        await page.fill('input[name="phone"]', '010-9999-8888');
        
        console.log('🖱️ 폼 제출...');
        await page.click('.submit-button');
        
        // 응답 충분히 대기
        await page.waitForTimeout(10000);
        
        if (!captured) {
            console.log('❌ POST 응답을 캡처하지 못했습니다');
        }
        
    } catch (error) {
        console.error('❌ 403 응답 확인 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
check403Response()
    .then(() => {
        console.log('✅ HTTP 403 응답 확인 완료');
    })
    .catch(error => {
        console.error('💥 403 응답 확인 실행 실패:', error);
        process.exit(1);
    });