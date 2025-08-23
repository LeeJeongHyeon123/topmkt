const { chromium } = require('playwright');

/**
 * 🔍 Ultra Think: 커뮤니티 작성 후 리다이렉트 테스트
 */

async function testCommunityWriteRedirect() {
    console.log('🔍 커뮤니티 작성 후 리다이렉트 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 콘솔 로그 캡처
        page.on('console', msg => {
            console.log(`🌐 [페이지 콘솔] ${msg.type()}: ${msg.text()}`);
        });
        
        // 에러 캡처
        page.on('pageerror', error => {
            console.error(`💥 [페이지 에러] ${error.message}`);
        });
        
        // 로그인
        console.log('🔐 DevLoginHelper로 로그인...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // 커뮤니티 작성 페이지로 이동
        console.log('📝 커뮤니티 작성 페이지로 이동...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(3000);
        
        // 테스트용 게시글 작성
        const testTitle = '[자동테스트] 리다이렉트 테스트 ' + new Date().toLocaleString();
        const testContent = '리다이렉트 기능 테스트용 게시글입니다.';
        
        console.log('✍️ 테스트 게시글 작성 중...');
        await page.fill('#title', testTitle);
        
        // Quill 에디터에 내용 입력
        await page.waitForSelector('.ql-editor', { timeout: 10000 });
        await page.locator('.ql-editor').click();
        await page.locator('.ql-editor').fill(testContent);
        
        // 네트워크 응답 모니터링
        let apiResponse = null;
        page.on('response', async (response) => {
            if (response.url().includes('/community/posts') && response.request().method() === 'POST') {
                console.log('🔄 API 응답 캡처:', response.url(), response.status());
                try {
                    apiResponse = await response.json();
                    console.log('📦 API 응답 데이터:', JSON.stringify(apiResponse, null, 2));
                } catch (e) {
                    console.error('❌ API 응답 파싱 실패:', e.message);
                }
            }
        });
        
        // 현재 URL 기록
        const beforeSubmitUrl = page.url();
        console.log('📍 제출 전 URL:', beforeSubmitUrl);
        
        // 폼 제출
        console.log('📤 폼 제출...');
        await page.click('button[type="submit"]');
        
        // Alert 처리 및 리다이렉트 대기
        let alertMessage = '';
        page.on('dialog', async (dialog) => {
            alertMessage = dialog.message();
            console.log('🚨 Alert 메시지:', alertMessage);
            await dialog.accept();
        });
        
        // 최대 10초 동안 리다이렉트 대기
        console.log('⏳ 리다이렉트 대기 중...');
        let currentUrl = beforeSubmitUrl;
        let redirected = false;
        
        for (let i = 0; i < 50; i++) { // 10초간 0.2초마다 확인
            await page.waitForTimeout(200);
            currentUrl = page.url();
            
            if (currentUrl !== beforeSubmitUrl) {
                redirected = true;
                console.log('✅ 리다이렉트 성공!');
                console.log('📍 리다이렉트된 URL:', currentUrl);
                break;
            }
        }
        
        // 결과 분석
        console.log('\\n📊 === 테스트 결과 ===');
        console.log('Alert 메시지:', alertMessage);
        console.log('API 응답:', apiResponse ? '성공' : '없음');
        console.log('리다이렉트 여부:', redirected ? '✅ 성공' : '❌ 실패');
        console.log('최종 URL:', currentUrl);
        
        if (apiResponse) {
            console.log('API 응답 상세:');
            console.log('- success:', apiResponse.success);
            console.log('- message:', apiResponse.message);
            console.log('- redirectUrl:', apiResponse.data?.redirectUrl);
        }
        
        // 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/test-community-write-redirect.png',
            fullPage: true 
        });
        
        console.log('📸 스크린샷 저장: test-community-write-redirect.png');
        
        return {
            alertMessage,
            apiResponse,
            redirected,
            finalUrl: currentUrl
        };
        
    } catch (error) {
        console.error('💥 테스트 실행 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testCommunityWriteRedirect()
    .then((result) => {
        console.log('\\n🏁 === 테스트 완료 ===');
        if (result.redirected) {
            console.log('🎉 리다이렉트 기능이 정상 작동합니다!');
        } else {
            console.log('⚠️ 리다이렉트 기능에 문제가 있습니다.');
            console.log('🔧 추가 디버깅이 필요합니다.');
        }
        process.exit(result.redirected ? 0 : 1);
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });