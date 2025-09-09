const { chromium } = require('playwright');

async function testSmsWithoutCsrf() {
    console.log('🧪 CSRF 우회 후 SMS 테스트');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        
        const page = await context.newPage();
        
        // SMS 관련 응답만 캡처
        let smsResponse = null;
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && response.request().method() === 'POST') {
                try {
                    const text = await response.text();
                    smsResponse = {
                        status: response.status(),
                        body: text
                    };
                    console.log(`📡 응답: HTTP ${smsResponse.status}`);
                    console.log(`📡 내용: ${smsResponse.body.substring(0, 200)}...`);
                } catch (e) {
                    console.log('응답 읽기 실패');
                }
            }
        });
        
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form');
        
        console.log('📞 존재하지 않는 번호로 테스트...');
        await page.fill('input[name="phone"]', '010-9999-9999');
        await page.click('.submit-button');
        
        await page.waitForTimeout(8000);
        
        if (smsResponse) {
            try {
                const jsonData = JSON.parse(smsResponse.body);
                
                if (smsResponse.status === 403) {
                    console.log('❌ 여전히 403 에러 - CSRF 우회 실패');
                } else if (smsResponse.status === 404 && jsonData.error?.includes('가입된')) {
                    console.log('✅ CSRF 우회 성공! 404는 정상 (미등록 번호)');
                    console.log(`   에러: ${jsonData.error}`);
                    return { success: true, bypassWorked: true };
                } else if (smsResponse.status === 400 && jsonData.error?.includes('휴대폰')) {
                    console.log('✅ CSRF 우회 성공! 400은 정상 (번호 형식 오류)');
                    console.log(`   에러: ${jsonData.error}`);
                    return { success: true, bypassWorked: true };
                } else if (smsResponse.status === 200 && jsonData.success) {
                    console.log('🎉 완전 성공! SMS 발송됨');
                    console.log(`   메시지: ${jsonData.message}`);
                    return { success: true, bypassWorked: true, smsWorked: true };
                } else {
                    console.log(`⚠️ 예상치 못한 응답: ${smsResponse.status}`);
                    console.log(`   내용: ${JSON.stringify(jsonData)}`);
                }
            } catch (parseError) {
                console.log(`❌ JSON 파싱 실패: ${parseError.message}`);
            }
        }
        
        return { success: false };
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testSmsWithoutCsrf()
    .then(results => {
        console.log('\n🎯 CSRF 우회 테스트 결과:');
        if (results.bypassWorked) {
            console.log('✅ CSRF 우회 성공 - SMS 로직은 정상 작동');
            console.log('🔧 이제 CSRF 토큰 문제만 해결하면 됨');
            
            if (results.smsWorked) {
                console.log('🎉 SMS 발송까지 성공!');
            }
        } else {
            console.log('❌ CSRF 우회 후에도 문제 존재');
        }
    })
    .catch(error => {
        console.error('💥 테스트 실행 실패:', error);
        process.exit(1);
    });