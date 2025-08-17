const { chromium } = require('playwright');

async function testChatWithLogin() {
    console.log('🔥 로그인 후 채팅 페이지 모바일 반응형 테스트 시작...\n');
    
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
            // 로그인 페이지로 이동
            console.log('  📱 로그인 페이지 접근...');
            await page.goto('https://www.topmktx.com/auth/login', { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            // 로그인 정보 입력
            await page.fill('#phone', '010-1234-5678');
            await page.fill('#password', 'test123');
            
            // 로그인 버튼 클릭
            await page.click('button[type="submit"]');
            
            // 로그인 처리 대기
            await page.waitForTimeout(3000);
            
            // 채팅 페이지로 직접 이동
            console.log('  💬 채팅 페이지 접근...');
            await page.goto('https://www.topmktx.com/chat', { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            // 페이지 로딩 대기
            await page.waitForTimeout(2000);
            
            // 현재 URL 확인
            const currentUrl = await page.url();
            console.log(`  - 현재 URL: ${currentUrl}`);
            
            // 채팅 관련 요소들 확인
            const chatElements = await page.evaluate(() => {
                // 다양한 채팅 요소 셀렉터 시도
                const selectors = [
                    '.chat-container', '.chat-wrapper', '.chat-main', '#chat-container',
                    '.chat-room', '.chat-area', '.chat-box', '.messaging-area',
                    'input[type="text"]', 'textarea', '.message-input', 
                    'input[placeholder*="메시지"]', 'input[placeholder*="채팅"]', 'input[placeholder*="입력"]',
                    'button[type="submit"]', '.send-btn', '.btn-send', 'button:has-text("전송")',
                    '.sidebar', '.chat-sidebar', '.user-list', '.channel-list',
                    '.hamburger', '.menu-toggle', '.sidebar-toggle'
                ];
                
                const results = {};
                selectors.forEach(selector => {
                    const element = document.querySelector(selector);
                    if (element) {
                        const rect = element.getBoundingClientRect();
                        const style = window.getComputedStyle(element);
                        results[selector] = {
                            exists: true,
                            visible: style.display !== 'none' && style.visibility !== 'hidden',
                            width: rect.width,
                            height: rect.height,
                            fontSize: style.fontSize,
                            tagName: element.tagName,
                            text: element.textContent ? element.textContent.trim().substring(0, 50) : '',
                            className: element.className
                        };
                    }
                });
                
                return {
                    foundElements: results,
                    totalElements: Object.keys(results).length,
                    pageTitle: document.title,
                    bodyClasses: document.body.className,
                    hasHorizontalScroll: document.documentElement.scrollWidth > document.documentElement.clientWidth
                };
            });
            
            console.log(`  - 페이지 제목: ${chatElements.pageTitle}`);
            console.log(`  - Body 클래스: ${chatElements.bodyClasses}`);
            console.log(`  - 찾은 요소 수: ${chatElements.totalElements}`);
            console.log(`  - 수평 스크롤: ${chatElements.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
            
            // 찾은 요소들 상세 정보
            Object.entries(chatElements.foundElements).forEach(([selector, info]) => {
                const touchTarget = Math.min(info.width, info.height);
                const fontSizeNum = parseInt(info.fontSize) || 0;
                
                console.log(`  - ${selector}:`);
                console.log(`    * 크기: ${info.width.toFixed(1)}x${info.height.toFixed(1)}px`);
                console.log(`    * 터치 타겟: ${touchTarget.toFixed(1)}px ${touchTarget >= 44 ? '✅' : '❌'}`);
                console.log(`    * 폰트 크기: ${info.fontSize} ${fontSizeNum >= 16 ? '✅' : fontSizeNum > 0 ? '❌' : '-'}`);
                console.log(`    * 표시: ${info.visible ? '✅' : '❌'}`);
                if (info.text) console.log(`    * 텍스트: "${info.text}"`);
            });
            
            // 스크린샷 촬영
            await page.screenshot({ 
                path: viewport.file, 
                fullPage: true,
                type: 'png'
            });
            
            console.log(`  ✅ 스크린샷 저장: ${viewport.file}\n`);
            
        } catch (error) {
            console.log(`  ❌ ${viewport.name} 테스트 실패: ${error.message}\n`);
            
            // 에러 발생 시에도 스크린샷 촬영
            try {
                await page.screenshot({ 
                    path: `error_${viewport.file}`, 
                    fullPage: true,
                    type: 'png'
                });
                console.log(`  📸 에러 스크린샷 저장: error_${viewport.file}\n`);
            } catch (screenshotError) {
                console.log(`  ❌ 스크린샷 저장 실패: ${screenshotError.message}\n`);
            }
        }
        
        await context.close();
    }
    
    await browser.close();
    console.log('🎯 채팅 페이지 모바일 반응형 테스트 완료!');
}

// 메인 실행
testChatWithLogin().catch(console.error);