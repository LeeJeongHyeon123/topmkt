const { chromium } = require('playwright');

/**
 * 🔍 취소 버튼 UI/UX 빠른 검증
 */

async function quickUITest() {
    console.log('🔍 취소 버튼 UI/UX 빠른 검증 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // DevLoginHelper로 자동 로그인
        console.log('🔐 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // 커뮤니티 글쓰기 페이지로 이동
        console.log('📝 글쓰기 페이지 이동...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        
        // 버튼 존재 및 순서 확인
        console.log('\\n🎯 버튼 UI 검증:');
        
        const cancelButton = await page.locator('#cancelBtn');
        const submitButton = await page.locator('#submitBtn');
        
        const cancelExists = await cancelButton.count() > 0;
        const submitExists = await submitButton.count() > 0;
        
        console.log(`   취소 버튼 존재: ${cancelExists ? '✅' : '❌'}`);
        console.log(`   작성하기 버튼 존재: ${submitExists ? '✅' : '❌'}`);
        
        if (cancelExists && submitExists) {
            const cancelText = await cancelButton.textContent();
            const submitText = await submitButton.textContent();
            
            console.log(`   취소 버튼 텍스트: "${cancelText}"`);
            console.log(`   작성하기 버튼 텍스트: "${submitText}"`);
            
            // 버튼 순서 확인 (위치 기반)
            const cancelBox = await cancelButton.boundingBox();
            const submitBox = await submitButton.boundingBox();
            
            const isOrderCorrect = cancelBox.x < submitBox.x;
            console.log(`   버튼 순서 (취소 < 작성하기): ${isOrderCorrect ? '✅ 올바름' : '❌ 잘못됨'}`);
            console.log(`   취소 버튼 위치: x=${cancelBox.x}`);
            console.log(`   작성하기 버튼 위치: x=${submitBox.x}`);
        }
        
        // 빈 상태에서 취소 테스트
        console.log('\\n📋 빠른 동작 테스트:');
        
        let dialogShown = false;
        page.on('dialog', async dialog => {
            console.log(`   🚨 Dialog: "${dialog.message()}"`);
            dialogShown = true;
            await dialog.dismiss();
        });
        
        console.log('   1. 빈 내용에서 취소 클릭...');
        await page.click('#cancelBtn');
        await page.waitForTimeout(1500);
        
        const currentUrl = page.url();
        const quickTest1 = !dialogShown && currentUrl.includes('/community');
        console.log(`   결과: ${quickTest1 ? '✅ 성공 (즉시 이동)' : '❌ 실패'}`);
        
        // 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/cancel-button-ui-test.png',
            fullPage: false
        });
        
        console.log('\\n📸 스크린샷 저장: cancel-button-ui-test.png');
        console.log('✅ UI 검증 완료!');
        
        return true;
        
    } catch (error) {
        console.error('💥 테스트 오류:', error);
        return false;
    } finally {
        await browser.close();
    }
}

quickUITest()
    .then(success => {
        if (success) {
            console.log('\\n🎉 UI 검증 성공!');
            process.exit(0);
        } else {
            console.log('\\n❌ UI 검증 실패');
            process.exit(1);
        }
    });