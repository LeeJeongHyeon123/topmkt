const { chromium } = require('playwright');

(async () => {
  console.log('🎭 Playwright 채팅 헤더 테스트 시작');
  
  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 }
  });
  
  const page = await context.newPage();
  
  try {
    // 1. 자동 로그인
    console.log('📝 자동 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    // 2. 채팅 페이지 접속
    console.log('💬 채팅 페이지 접속...');
    await page.goto('https://www.topmktx.com/chat');
    await page.waitForTimeout(3000);
    
    // 채팅 페이지 스크린샷
    await page.screenshot({ 
      path: 'chat_page_header.png',
      fullPage: true
    });
    console.log('📸 채팅 페이지 스크린샷 저장: chat_page_header.png');
    
    // 3. 커뮤니티 페이지 접속
    console.log('👥 커뮤니티 페이지 접속...');
    await page.goto('https://www.topmktx.com/community');
    await page.waitForTimeout(3000);
    
    // 커뮤니티 페이지 스크린샷
    await page.screenshot({ 
      path: 'community_page_header.png',
      fullPage: true
    });
    console.log('📸 커뮤니티 페이지 스크린샷 저장: community_page_header.png');
    
    // 4. 헤더 간격 측정
    console.log('📏 헤더 간격 측정 중...');
    
    // 채팅 페이지로 돌아가서 측정
    await page.goto('https://www.topmktx.com/chat');
    await page.waitForTimeout(2000);
    
    const chatHeaderSpacing = await page.evaluate(() => {
      // 공통 헤더 (상단 네비게이션)
      const commonHeader = document.querySelector('header.header, .header, nav');
      // 채팅 헤더
      const chatHeader = document.querySelector('.chat-header, .page-header, .content-header');
      
      if (commonHeader && chatHeader) {
        const commonRect = commonHeader.getBoundingClientRect();
        const chatRect = chatHeader.getBoundingClientRect();
        const spacing = chatRect.top - commonRect.bottom;
        
        return {
          commonHeaderBottom: commonRect.bottom,
          chatHeaderTop: chatRect.top,
          spacing: spacing,
          found: true
        };
      }
      
      // 헤더를 찾지 못한 경우 전체 페이지 요소 확인
      const allHeaders = Array.from(document.querySelectorAll('*')).filter(el => {
        const computedStyle = window.getComputedStyle(el);
        return computedStyle.position === 'fixed' || 
               computedStyle.position === 'sticky' ||
               el.tagName.toLowerCase() === 'header' ||
               el.className.includes('header') ||
               el.className.includes('nav');
      });
      
      return {
        found: false,
        allHeadersCount: allHeaders.length,
        headerElements: allHeaders.map(el => ({
          tag: el.tagName,
          className: el.className,
          position: window.getComputedStyle(el).position,
          top: el.getBoundingClientRect().top,
          bottom: el.getBoundingClientRect().bottom
        }))
      };
    });
    
    console.log('채팅 페이지 헤더 분석:', JSON.stringify(chatHeaderSpacing, null, 2));
    
    // 커뮤니티 페이지로 이동해서 측정
    await page.goto('https://www.topmktx.com/community');
    await page.waitForTimeout(2000);
    
    const communityHeaderSpacing = await page.evaluate(() => {
      const commonHeader = document.querySelector('header.header, .header, nav');
      const contentHeader = document.querySelector('.community-header, .page-header, .content-header');
      
      if (commonHeader && contentHeader) {
        const commonRect = commonHeader.getBoundingClientRect();
        const contentRect = contentHeader.getBoundingClientRect();
        const spacing = contentRect.top - commonRect.bottom;
        
        return {
          commonHeaderBottom: commonRect.bottom,
          contentHeaderTop: contentRect.top,
          spacing: spacing,
          found: true
        };
      }
      
      const allHeaders = Array.from(document.querySelectorAll('*')).filter(el => {
        const computedStyle = window.getComputedStyle(el);
        return computedStyle.position === 'fixed' || 
               computedStyle.position === 'sticky' ||
               el.tagName.toLowerCase() === 'header' ||
               el.className.includes('header') ||
               el.className.includes('nav');
      });
      
      return {
        found: false,
        allHeadersCount: allHeaders.length,
        headerElements: allHeaders.map(el => ({
          tag: el.tagName,
          className: el.className,
          position: window.getComputedStyle(el).position,
          top: el.getBoundingClientRect().top,
          bottom: el.getBoundingClientRect().bottom
        }))
      };
    });
    
    console.log('커뮤니티 페이지 헤더 분석:', JSON.stringify(communityHeaderSpacing, null, 2));
    
    // 5. 비교 결과 출력
    if (chatHeaderSpacing.found && communityHeaderSpacing.found) {
      const difference = chatHeaderSpacing.spacing - communityHeaderSpacing.spacing;
      console.log('\n📊 헤더 간격 비교 결과:');
      console.log(`채팅 페이지 간격: ${chatHeaderSpacing.spacing}px`);
      console.log(`커뮤니티 페이지 간격: ${communityHeaderSpacing.spacing}px`);
      console.log(`차이: ${difference}px ${difference < 0 ? '(채팅이 더 좁음)' : '(채팅이 더 넓음)'}`);
    } else {
      console.log('\n⚠️ 헤더 요소를 정확히 찾지 못했습니다.');
      console.log('스크린샷으로 시각적 확인이 필요합니다.');
    }
    
  } catch (error) {
    console.error('❌ 오류 발생:', error);
  } finally {
    await browser.close();
    console.log('✅ 테스트 완료');
  }
})();
