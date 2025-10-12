const { chromium } = require('playwright');

async function testChatMobileResponsive() {
    console.log('🔥 채팅 페이지 모바일 반응형 테스트 시작...\n');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--disable-dev-shm-usage', '--no-sandbox']
    });
    
    const viewports = [
        { name: 'mobile', width: 375, height: 667, file: 'chat_mobile_current.png' },
        { name: 'tablet', width: 768, height: 1024, file: 'chat_tablet_current.png' },
        { name: 'small_mobile', width: 320, height: 568, file: 'chat_small_current.png' }
    ];
    
    for (const viewport of viewports) {
        console.log(`📱 ${viewport.name} 테스트 (${viewport.width}x${viewport.height})...`);
        
        const context = await browser.newContext({
            viewport: { width: viewport.width, height: viewport.height },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
        });
        
        const page = await context.newPage();
        
        try {
            // 채팅 페이지로 이동
            await page.goto('https://www.topmktx.com/chat', { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            // 페이지 로딩 대기
            await page.waitForTimeout(2000);
            
            // 기본 요소들이 있는지 확인
            const chatContainer = await page.$('.chat-container, .chat-wrapper, .chat-main, #chat-container');
            const messageInput = await page.$('input[type="text"], textarea, .message-input, input[placeholder*="메시지"], input[placeholder*="채팅"]');
            const sendButton = await page.$('button[type="submit"], .send-btn, .btn-send, button:has-text("전송")');
            
            console.log(`  - 채팅 컨테이너: ${chatContainer ? '✅' : '❌'}`);
            console.log(`  - 메시지 입력창: ${messageInput ? '✅' : '❌'}`);
            console.log(`  - 전송 버튼: ${sendButton ? '✅' : '❌'}`);
            
            // 터치 타겟 크기 검사
            if (sendButton) {
                const buttonBox = await sendButton.boundingBox();
                if (buttonBox) {
                    const touchTarget = Math.min(buttonBox.width, buttonBox.height);
                    console.log(`  - 전송 버튼 터치 타겟: ${touchTarget.toFixed(1)}px ${touchTarget >= 44 ? '✅' : '❌'}`);
                }
            }
            
            // 폰트 크기 검사
            if (messageInput) {
                const fontSize = await page.evaluate((input) => {
                    return window.getComputedStyle(input).fontSize;
                }, messageInput);
                const fontSizeNum = parseInt(fontSize);
                console.log(`  - 입력창 폰트 크기: ${fontSize} ${fontSizeNum >= 16 ? '✅' : '❌'}`);
            }
            
            // 수평 스크롤 검사
            const hasHorizontalScroll = await page.evaluate(() => {
                return document.documentElement.scrollWidth > document.documentElement.clientWidth;
            });
            console.log(`  - 수평 스크롤: ${hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
            
            // 사이드바 토글 버튼 검사
            const sidebarToggle = await page.$('.sidebar-toggle, .menu-toggle, .hamburger, button:has-text("메뉴")');
            console.log(`  - 사이드바 토글: ${sidebarToggle ? '✅' : '❌'}`);
            
            if (sidebarToggle) {
                const toggleBox = await sidebarToggle.boundingBox();
                if (toggleBox) {
                    const touchTarget = Math.min(toggleBox.width, toggleBox.height);
                    console.log(`  - 토글 버튼 터치 타겟: ${touchTarget.toFixed(1)}px ${touchTarget >= 44 ? '✅' : '❌'}`);
                }
            }
            
            // 화면 스크롤해서 더 많은 요소 확인
            await page.evaluate(() => window.scrollTo(0, 100));
            await page.waitForTimeout(500);
            
            // 스크린샷 촬영
            await page.screenshot({ 
                path: viewport.file, 
                fullPage: true,
                type: 'png'
            });
            
            console.log(`  ✅ 스크린샷 저장: ${viewport.file}\n`);
            
        } catch (error) {
            console.log(`  ❌ ${viewport.name} 테스트 실패: ${error.message}\n`);
        }
        
        await context.close();
    }
    
    await browser.close();
    console.log('🎯 채팅 페이지 모바일 반응형 테스트 완료!');
}

// 메인 실행
testChatMobileResponsive().catch(console.error);