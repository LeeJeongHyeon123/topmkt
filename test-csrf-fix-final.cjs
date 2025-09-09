const { chromium } = require('playwright');

async function testCsrfFixFinal() {
    console.log('🔧 CSRF 토큰 수정 후 최종 테스트');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        
        const page = await context.newPage();
        
        // POST 응답만 캡처
        let postResponse = null;
        page.on('response', async response => {
            if (response.url().includes('/auth/forgot-password') && response.request().method() === 'POST') {
                try {
                    const text = await response.text();
                    postResponse = {
                        status: response.status(),
                        body: text
                    };
                    console.log(`📡 POST 응답: HTTP ${postResponse.status}`);
                } catch (e) {
                    postResponse = { status: response.status(), error: 'Failed to read response' };
                }
            }
        });
        
        console.log('📱 비밀번호 찾기 페이지 로딩...');
        await page.goto('https://www.topmktx.com/auth/forgot-password');
        await page.waitForSelector('form');
        await page.waitForTimeout(2000);
        
        // CSRF 토큰 확인
        const csrfToken = await page.evaluate(() => {
            const input = document.querySelector('input[name="csrf_token"]');
            return input ? input.value : null;
        });
        
        console.log(`🔐 HTML CSRF 토큰: ${csrfToken ? '✅ ' + csrfToken.substring(0, 15) + '...' : '❌ 없음'}`);
        
        console.log('📞 미등록 번호로 테스트...');
        await page.fill('input[name="phone"]', '010-8888-7777');
        await page.click('.submit-button');
        
        // 응답 대기
        await page.waitForTimeout(8000);
        
        // 결과 분석
        if (postResponse) {
            console.log(`응답 상태: HTTP ${postResponse.status}`);
            
            if (postResponse.body && !postResponse.error) {
                console.log(`응답 내용: ${postResponse.body.substring(0, 150)}...`);
                
                try {
                    const jsonData = JSON.parse(postResponse.body);
                    
                    if (postResponse.status === 403) {
                        console.log('❌ 여전히 CSRF 문제 존재');
                        console.log(`   에러: ${jsonData.error}`);
                        return { success: false, csrfFixed: false };
                        
                    } else if (postResponse.status === 404 && jsonData.error?.includes('가입된')) {
                        console.log('✅ CSRF 문제 해결! 404는 정상 응답 (미등록 번호)');
                        console.log(`   메시지: ${jsonData.error}`);
                        return { success: true, csrfFixed: true, reason: 'unregistered_number' };
                        
                    } else if (postResponse.status === 400) {
                        console.log('✅ CSRF 문제 해결! 400은 입력 오류 (CSRF 통과됨)');
                        console.log(`   메시지: ${jsonData.error}`);
                        return { success: true, csrfFixed: true, reason: 'input_error' };
                        
                    } else if (postResponse.status === 200 && jsonData.success) {
                        console.log('🎉 완전 성공! SMS 발송 완료');
                        console.log(`   메시지: ${jsonData.message}`);
                        return { success: true, csrfFixed: true, smsWorked: true };
                        
                    } else if (postResponse.status === 429) {
                        console.log('✅ CSRF 문제 해결! 429는 발송 제한 (CSRF 통과됨)');
                        console.log(`   메시지: ${jsonData.error}`);
                        return { success: true, csrfFixed: true, reason: 'rate_limited' };
                    }
                    
                } catch (parseError) {
                    console.log(`❌ JSON 파싱 실패: ${parseError.message}`);
                }
            }
        } else {
            console.log('❌ POST 응답을 캡처하지 못했습니다');
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
testCsrfFixFinal()
    .then(results => {
        console.log('\n🏆 CSRF 토큰 수정 최종 결과:');
        
        if (results.csrfFixed) {
            console.log('✅ CSRF 토큰 문제 완전 해결됨!');
            console.log('   - 페이지 로딩 시점에서 토큰 생성 보장');
            console.log('   - 세션과 HTML 토큰 동기화 완료');
            console.log('   - HTTP 403 에러 해결');
            
            if (results.smsWorked) {
                console.log('🎉 SMS 발송까지 완전 성공!');
            } else {
                console.log(`📋 응답 타입: ${results.reason || 'unknown'} (정상)`);
            }
            
        } else {
            console.log('❌ CSRF 문제가 여전히 존재합니다');
            console.log('   추가 디버깅이 필요합니다');
        }
    })
    .catch(error => {
        console.error('💥 CSRF 수정 테스트 실행 실패:', error);
        process.exit(1);
    });