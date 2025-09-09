/**
 * 간단한 수정 페이지 버튼 확인 테스트
 */

const { chromium } = require('playwright');

async function simpleEditTest() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('🔐 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        console.log('📝 게시글 1000027 수정 페이지로 직접 이동...');
        await page.goto('https://www.topmktx.com/community/edit/1000027');
        await page.waitForTimeout(5000);
        
        // 현재 URL 확인
        console.log(`현재 URL: ${page.url()}`);
        
        // 페이지 제목 확인
        try {
            const title = await page.textContent('h1', { timeout: 3000 });
            console.log(`페이지 제목: ${title}`);
        } catch (e) {
            console.log('페이지 제목을 찾을 수 없음');
        }
        
        // 버튼들이 있는지 확인
        try {
            const formButtons = await page.$('.form-buttons');
            if (formButtons) {
                console.log('✅ form-buttons 요소를 찾았습니다.');
                
                const buttons = await page.$$('.form-buttons button');
                console.log(`버튼 개수: ${buttons.length}`);
                
                for (let i = 0; i < buttons.length; i++) {
                    const text = await buttons[i].textContent();
                    const id = await buttons[i].getAttribute('id');
                    console.log(`  버튼 ${i+1}: "${text.trim()}" (ID: ${id})`);
                }
            } else {
                console.log('❌ form-buttons 요소를 찾을 수 없습니다.');
            }
        } catch (e) {
            console.log('❌ 버튼 확인 중 오류:', e.message);
        }
        
        // 스크린샷 촬영
        await page.screenshot({ 
            path: `/var/www/html/topmkt/simple-edit-test-screenshot.png`,
            fullPage: true
        });
        
        console.log('스크린샷 저장: simple-edit-test-screenshot.png');
        
        // 5초 대기 후 종료 (수동 확인용)
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
    } finally {
        await browser.close();
    }
}

simpleEditTest();