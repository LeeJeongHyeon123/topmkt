const { chromium } = require('playwright');

async function analyzeChatPageStructure() {
    console.log('🔍 채팅 페이지 구조 분석 시작...\n');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--disable-dev-shm-usage', '--no-sandbox']
    });
    
    const context = await browser.newContext({
        viewport: { width: 375, height: 667 }
    });
    
    const page = await context.newPage();
    
    try {
        console.log('📱 채팅 페이지 접근 중...');
        await page.goto('https://www.topmktx.com/chat', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        // 페이지 제목 확인
        const title = await page.title();
        console.log(`페이지 제목: ${title}`);
        
        // 현재 URL 확인
        const currentUrl = await page.url();
        console.log(`현재 URL: ${currentUrl}`);
        
        // 페이지 HTML 구조 분석
        const bodyContent = await page.evaluate(() => {
            const body = document.body;
            const mainContent = body.querySelector('main, .main, .content, .chat-page, .chat-container');
            
            return {
                bodyClasses: body.className,
                bodyId: body.id,
                hasMain: !!body.querySelector('main'),
                hasHeader: !!body.querySelector('header'),
                hasNav: !!body.querySelector('nav'),
                hasSidebar: !!body.querySelector('.sidebar, .side-menu, aside'),
                hasChatContainer: !!body.querySelector('.chat-container, .chat-wrapper, .chat-main'),
                hasMessageInput: !!body.querySelector('input[type="text"], textarea, .message-input'),
                hasSendButton: !!body.querySelector('button[type="submit"], .send-btn, .btn-send'),
                allInputs: Array.from(body.querySelectorAll('input')).map(input => ({
                    type: input.type,
                    placeholder: input.placeholder,
                    className: input.className,
                    id: input.id
                })),
                allButtons: Array.from(body.querySelectorAll('button')).map(btn => ({
                    text: btn.textContent.trim(),
                    className: btn.className,
                    id: btn.id,
                    type: btn.type
                })),
                mainElementsStructure: mainContent ? mainContent.outerHTML.substring(0, 500) : 'No main content found'
            };
        });
        
        console.log('\n📋 페이지 구조 분석 결과:');
        console.log(`- Body 클래스: ${bodyContent.bodyClasses}`);
        console.log(`- Body ID: ${bodyContent.bodyId}`);
        console.log(`- Header 존재: ${bodyContent.hasHeader ? '✅' : '❌'}`);
        console.log(`- Navigation 존재: ${bodyContent.hasNav ? '✅' : '❌'}`);
        console.log(`- Main 존재: ${bodyContent.hasMain ? '✅' : '❌'}`);
        console.log(`- Sidebar 존재: ${bodyContent.hasSidebar ? '✅' : '❌'}`);
        console.log(`- 채팅 컨테이너 존재: ${bodyContent.hasChatContainer ? '✅' : '❌'}`);
        console.log(`- 메시지 입력창 존재: ${bodyContent.hasMessageInput ? '✅' : '❌'}`);
        console.log(`- 전송 버튼 존재: ${bodyContent.hasSendButton ? '✅' : '❌'}`);
        
        console.log('\n📝 입력 요소들:');
        bodyContent.allInputs.forEach((input, index) => {
            console.log(`  ${index + 1}. Type: ${input.type}, Placeholder: "${input.placeholder}", Class: "${input.className}", ID: "${input.id}"`);
        });
        
        console.log('\n🔘 버튼 요소들:');
        bodyContent.allButtons.forEach((btn, index) => {
            console.log(`  ${index + 1}. Text: "${btn.text}", Class: "${btn.className}", ID: "${btn.id}", Type: "${btn.type}"`);
        });
        
        console.log('\n🏗️ 메인 요소 구조:');
        console.log(bodyContent.mainElementsStructure);
        
        // 스크린샷으로 실제 화면 확인
        await page.screenshot({ 
            path: 'chat_page_analysis.png', 
            fullPage: true,
            type: 'png'
        });
        
        console.log('\n✅ 스크린샷 저장: chat_page_analysis.png');
        
    } catch (error) {
        console.log(`❌ 페이지 분석 실패: ${error.message}`);
    }
    
    await context.close();
    await browser.close();
    console.log('\n🎯 채팅 페이지 구조 분석 완료!');
}

// 메인 실행
analyzeChatPageStructure().catch(console.error);