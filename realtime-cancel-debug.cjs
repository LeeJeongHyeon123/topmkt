const { chromium } = require('playwright');

/**
 * 🚨 실시간 취소 버튼 확인 - 사용자 경험과 동일한 테스트
 */

async function realtimeCancelTest() {
    console.log('🚨 실시간 취소 버튼 확인 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 모든 로그 캡처
        page.on('console', msg => {
            const text = msg.text();
            if (text.includes('취소 버튼') || text.includes('v3.14')) {
                console.log(`[페이지] ${text}`);
            }
        });
        
        // Dialog 모니터링
        let dialogShown = false;
        page.on('dialog', async dialog => {
            console.log(`🚨 DIALOG 확인: "${dialog.message()}"`);
            dialogShown = true;
            await dialog.dismiss();
        });
        
        // 로그인
        console.log('🔐 로그인...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // 글쓰기 페이지
        console.log('📝 글쓰기 페이지로...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(3000);
        
        // 페이지 소스에서 실제 JavaScript 확인
        console.log('\\n🔍 실제 JavaScript 코드 확인:');
        const pageSource = await page.content();
        
        // 취소 버튼 관련 코드 찾기
        const cancelBtnCodeMatch = pageSource.match(/취소 버튼 처리.*?addEventListener.*?confirm.*?}/gs);
        if (cancelBtnCodeMatch) {
            console.log('✅ 취소 버튼 JavaScript 코드 발견:');
            console.log(cancelBtnCodeMatch[0].substring(0, 200) + '...');
        } else {
            console.log('❌ 취소 버튼 JavaScript 코드를 찾을 수 없음');
        }
        
        // v3.14.1+ 버전 확인
        const hasV3141 = pageSource.includes('v3.14.1+');
        console.log(`v3.14.1+ 코드 존재: ${hasV3141 ? '✅' : '❌'}`);
        
        // 실제 취소 버튼 HTML 확인
        const cancelBtnInfo = await page.evaluate(() => {
            const btn = document.getElementById('cancelBtn');
            if (!btn) return null;
            
            return {
                tagName: btn.tagName,
                type: btn.type,
                href: btn.href,
                onclick: btn.onclick ? btn.onclick.toString() : 'null',
                hasEventListeners: btn._hasEventListeners || 'unknown',
                outerHTML: btn.outerHTML
            };
        });
        
        console.log('\\n🔍 취소 버튼 실제 상태:');
        console.log(JSON.stringify(cancelBtnInfo, null, 2));
        
        // 사용자와 동일한 테스트: 제목 입력 후 취소
        console.log('\\n🧪 실제 사용자 테스트:');
        await page.fill('#title', '실시간 테스트 - 취소 버튼 확인');
        console.log('1. 제목 입력 완료');
        
        console.log('2. 취소 버튼 클릭...');
        await page.click('#cancelBtn');
        await page.waitForTimeout(2000);
        
        const finalUrl = page.url();
        
        console.log('\\n📊 최종 결과:');
        console.log(`Dialog 표시됨: ${dialogShown ? '✅ 예' : '❌ 아니오'}`);
        console.log(`최종 URL: ${finalUrl}`);
        console.log(`예상: Dialog 표시 후 선택`);
        console.log(`실제: ${dialogShown ? 'Dialog 정상 작동' : '❌ Dialog 없이 바로 이동'}`);
        
        if (!dialogShown && finalUrl.includes('/community') && !finalUrl.includes('/write')) {
            console.log('\\n🔴 문제 확인: 여전히 즉시 이동됨!');
        }
        
        // 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/realtime-cancel-test.png',
            fullPage: false
        });
        
        return { dialogShown, finalUrl, hasV3141 };
        
    } catch (error) {
        console.error('💥 테스트 오류:', error);
        return null;
    } finally {
        await browser.close();
    }
}

realtimeCancelTest()
    .then(result => {
        if (result && result.dialogShown) {
            console.log('\\n🎉 취소 버튼이 정상 작동합니다!');
        } else {
            console.log('\\n🚨 취소 버튼에 여전히 문제가 있습니다!');
            console.log('🔧 즉시 수정이 필요합니다.');
        }
    });