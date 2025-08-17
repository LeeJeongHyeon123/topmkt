const { chromium } = require('playwright');

async function testChatMobileFixed() {
    console.log('🔥 채팅 페이지 모바일 반응형 테스트 (수정된 버전) 시작...\n');
    
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
            // 직접 채팅 페이지로 이동 (리다이렉트 처리)
            console.log('  📱 채팅 페이지 접근...');
            await page.goto('https://www.topmktx.com/chat', { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            
            // 페이지 로딩 대기
            await page.waitForTimeout(3000);
            
            // 현재 URL 확인
            const currentUrl = await page.url();
            console.log(`  - 현재 URL: ${currentUrl}`);
            
            // 로그인 페이지로 리다이렉트된 경우 간단히 로그인 시도
            if (currentUrl.includes('/auth/login')) {
                console.log('  🔑 로그인 시도...');
                
                // 로그인 폼이 로드될 때까지 대기
                await page.waitForSelector('#phone', { timeout: 5000 }).catch(() => {});
                
                // 테스트 계정으로 로그인 시도
                const phoneInput = await page.$('#phone');
                const passwordInput = await page.$('#password');
                
                if (phoneInput && passwordInput) {
                    await page.fill('#phone', '010-1234-5678');
                    await page.fill('#password', 'test123');
                    await page.click('button[type="submit"]');
                    
                    // 로그인 후 채팅 페이지로 리다이렉트 대기
                    await page.waitForTimeout(3000);
                }
            }
            
            // 채팅 페이지 분석
            const chatAnalysis = await page.evaluate(() => {
                // 주요 채팅 요소들 찾기
                const chatContainer = document.querySelector('.chat-container');
                const chatInput = document.querySelector('#chatInput, .chat-input');
                const sendButton = document.querySelector('#chatSendBtn, .chat-send-btn');
                const newChatBtn = document.querySelector('.new-chat-btn');
                const sidebar = document.querySelector('.chat-sidebar');
                const chatLayout = document.querySelector('.chat-layout');
                
                // 폰트 크기 및 터치 타겟 측정
                const measurements = {
                    chatInput: null,
                    sendButton: null,
                    newChatBtn: null
                };
                
                if (chatInput) {
                    const inputRect = chatInput.getBoundingClientRect();
                    const inputStyle = window.getComputedStyle(chatInput);
                    measurements.chatInput = {
                        width: inputRect.width,
                        height: inputRect.height,
                        fontSize: inputStyle.fontSize,
                        visible: inputStyle.display !== 'none'
                    };
                }
                
                if (sendButton) {
                    const btnRect = sendButton.getBoundingClientRect();
                    const btnStyle = window.getComputedStyle(sendButton);
                    measurements.sendButton = {
                        width: btnRect.width,
                        height: btnRect.height,
                        fontSize: btnStyle.fontSize,
                        touchTarget: Math.min(btnRect.width, btnRect.height),
                        visible: btnStyle.display !== 'none'
                    };
                }
                
                if (newChatBtn) {
                    const newBtnRect = newChatBtn.getBoundingClientRect();
                    const newBtnStyle = window.getComputedStyle(newChatBtn);
                    measurements.newChatBtn = {
                        width: newBtnRect.width,
                        height: newBtnRect.height,
                        fontSize: newBtnStyle.fontSize,
                        touchTarget: Math.min(newBtnRect.width, newBtnRect.height),
                        visible: newBtnStyle.display !== 'none'
                    };
                }
                
                return {
                    hasContainer: !!chatContainer,
                    hasInput: !!chatInput,
                    hasSendBtn: !!sendButton,
                    hasNewChatBtn: !!newChatBtn,
                    hasSidebar: !!sidebar,
                    hasChatLayout: !!chatLayout,
                    sidebarVisible: sidebar ? window.getComputedStyle(sidebar).display !== 'none' : false,
                    hasHorizontalScroll: document.documentElement.scrollWidth > document.documentElement.clientWidth,
                    viewportWidth: window.innerWidth,
                    measurements: measurements,
                    pageTitle: document.title,
                    bodyClasses: document.body.className
                };
            });
            
            console.log(`  - 페이지 제목: ${chatAnalysis.pageTitle}`);
            console.log(`  - Body 클래스: ${chatAnalysis.bodyClasses}`);
            console.log(`  - 뷰포트 너비: ${chatAnalysis.viewportWidth}px`);
            console.log(`  - 채팅 컨테이너: ${chatAnalysis.hasContainer ? '✅' : '❌'}`);
            console.log(`  - 채팅 입력창: ${chatAnalysis.hasInput ? '✅' : '❌'}`);
            console.log(`  - 전송 버튼: ${chatAnalysis.hasSendBtn ? '✅' : '❌'}`);
            console.log(`  - 새 채팅 버튼: ${chatAnalysis.hasNewChatBtn ? '✅' : '❌'}`);
            console.log(`  - 사이드바: ${chatAnalysis.hasSidebar ? '✅' : '❌'}`);
            console.log(`  - 사이드바 표시: ${chatAnalysis.sidebarVisible ? '✅ 표시됨' : '❌ 숨겨짐'}`);
            console.log(`  - 수평 스크롤: ${chatAnalysis.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
            
            // 각 요소별 세부 분석
            if (chatAnalysis.measurements.chatInput) {
                const input = chatAnalysis.measurements.chatInput;
                const fontSizeNum = parseInt(input.fontSize) || 0;
                console.log(`  - 입력창 크기: ${input.width.toFixed(1)}x${input.height.toFixed(1)}px`);
                console.log(`  - 입력창 폰트: ${input.fontSize} ${fontSizeNum >= 16 ? '✅' : '❌ (16px 미만)'}`);
                console.log(`  - 입력창 표시: ${input.visible ? '✅' : '❌'}`);
            }
            
            if (chatAnalysis.measurements.sendButton) {
                const btn = chatAnalysis.measurements.sendButton;
                console.log(`  - 전송 버튼 크기: ${btn.width.toFixed(1)}x${btn.height.toFixed(1)}px`);
                console.log(`  - 전송 버튼 터치 타겟: ${btn.touchTarget.toFixed(1)}px ${btn.touchTarget >= 44 ? '✅' : '❌ (44px 미만)'}`);
                console.log(`  - 전송 버튼 표시: ${btn.visible ? '✅' : '❌'}`);
            }
            
            if (chatAnalysis.measurements.newChatBtn) {
                const newBtn = chatAnalysis.measurements.newChatBtn;
                console.log(`  - 새 채팅 버튼 크기: ${newBtn.width.toFixed(1)}x${newBtn.height.toFixed(1)}px`);
                console.log(`  - 새 채팅 버튼 터치 타겟: ${newBtn.touchTarget.toFixed(1)}px ${newBtn.touchTarget >= 44 ? '✅' : '❌ (44px 미만)'}`);
                console.log(`  - 새 채팅 버튼 표시: ${newBtn.visible ? '✅' : '❌'}`);
            }
            
            // 모바일 반응형 특성 체크
            if (viewport.width <= 768) {
                console.log(`  🔍 모바일 특성 체크:`);
                console.log(`    - 사이드바 숨김: ${!chatAnalysis.sidebarVisible ? '✅' : '❌ (데스크톱 레이아웃)'}`);
                console.log(`    - 단일 컬럼 레이아웃: ${chatAnalysis.viewportWidth <= 768 ? '✅' : '❌'}`);
            }
            
            // 스크린샷 촬영
            await page.screenshot({ 
                path: viewport.file, 
                fullPage: true,
                type: 'png'
            });
            
            console.log(`  ✅ 스크린샷 저장: ${viewport.file}\n`);
            
        } catch (error) {
            console.log(`  ❌ ${viewport.name} 테스트 실패: ${error.message}`);
            
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
testChatMobileFixed().catch(console.error);