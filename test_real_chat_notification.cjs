const { chromium } = require('playwright');

(async () => {
    console.log('🔍 실제 채팅 페이지에서 프로필 이미지 테스트...\n');

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();

    try {
        // 콘솔 로그 캡처
        page.on('console', msg => {
            console.log(`[브라우저] ${msg.text()}`);
        });

        // 1. 로그인 (우리집탄이 계정)
        console.log('✅ Step 1: 로그인...');
        await page.goto('https://www.topmktx.com/login');
        await page.fill('input[name="username"]', '우리집탄이');
        await page.fill('input[name="password"]', 'password123!@#');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        // 2. 채팅 페이지 이동
        console.log('✅ Step 2: 채팅 페이지 이동...');
        await page.goto('https://www.topmktx.com/chat');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);

        // 3. JavaScript 로드 확인
        const jsCheck = await page.evaluate(() => {
            return {
                showChatNotification존재: typeof showChatNotification === 'function',
                testChatNotification존재: typeof testChatNotification === 'function',
                testChatNotificationNoImage존재: typeof testChatNotificationNoImage === 'function'
            };
        });
        console.log('\n📦 JavaScript 함수 로드 확인:');
        console.log(JSON.stringify(jsCheck, null, 2));

        if (jsCheck.testChatNotification존재) {
            // 4. 프로필 이미지 있는 테스트
            console.log('\n✅ Step 3: 프로필 이미지 테스트...');
            const result1 = await page.evaluate(() => {
                testChatNotification();
                
                return new Promise(resolve => {
                    setTimeout(() => {
                        const alert = document.querySelector('.alert.chat-notification');
                        const img = alert?.querySelector('.chat-profile-image img');
                        const icon = alert?.querySelector('.chat-profile-image i');
                        
                        resolve({
                            알림존재: !!alert,
                            이미지태그: !!img,
                            이미지src: img?.src || 'N/A',
                            아이콘태그: !!icon,
                            HTML일부: alert?.outerHTML?.substring(0, 300) || 'N/A'
                        });
                    }, 1000);
                });
            });
            
            console.log('\n📊 테스트 결과 (이미지 있음):');
            console.log(JSON.stringify(result1, null, 2));
            
            await page.screenshot({ path: 'chat-page-with-image.png' });
            console.log('📸 스크린샷: chat-page-with-image.png');

            // 5. 프로필 이미지 없는 테스트
            console.log('\n✅ Step 4: 프로필 이미지 없는 테스트...');
            const result2 = await page.evaluate(() => {
                // 기존 알림 제거
                const existing = document.querySelector('.alert.chat-notification');
                if (existing) existing.remove();
                
                testChatNotificationNoImage();
                
                return new Promise(resolve => {
                    setTimeout(() => {
                        const alert = document.querySelector('.alert.chat-notification');
                        const img = alert?.querySelector('.chat-profile-image img');
                        const icon = alert?.querySelector('.chat-profile-image i');
                        
                        resolve({
                            알림존재: !!alert,
                            이미지태그: !!img,
                            아이콘태그: !!icon,
                            아이콘클래스: icon?.className || 'N/A',
                            HTML일부: alert?.outerHTML?.substring(0, 300) || 'N/A'
                        });
                    }, 1000);
                });
            });
            
            console.log('\n📊 테스트 결과 (이미지 없음):');
            console.log(JSON.stringify(result2, null, 2));
            
            await page.screenshot({ path: 'chat-page-without-image.png' });
            console.log('📸 스크린샷: chat-page-without-image.png');
        }

    } catch (error) {
        console.error('❌ 오류:', error.message);
    } finally {
        await browser.close();
    }
})();
