const { chromium } = require('playwright');

(async () => {
  console.log('🎭 탑마케팅 통합 모바일 메뉴 포괄적 테스트 시작');
  
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 }, // iPhone 12/13 Pro 크기
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1'
  });
  
  const page = await context.newPage();
  
  try {
    console.log('📱 1단계: DevLogin Helper로 사용자 ID 4 로그인');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    console.log('🏠 2단계: 메인 페이지로 이동');
    await page.goto('https://www.topmktx.com');
    await page.waitForTimeout(3000);
    
    // 현재 상태 스크린샷
    await page.screenshot({ path: 'mobile_main_initial.png', fullPage: true });
    console.log('📸 메인 페이지 초기 상태 스크린샷 저장: mobile_main_initial.png');
    
    console.log('👤 3단계: 프로필 이미지 클릭하여 모바일 메뉴 열기');
    
    // 모바일 메뉴 버튼 찾기 (여러 선택자 시도)
    const menuSelectors = [
      'button[onclick*="toggleMobileMenu"]',
      '.mobile-menu-trigger',
      '.profile-avatar.mobile-only',
      'img[onclick*="toggleMobileMenu"]',
      '[data-mobile-menu-trigger]',
      '.mobile-profile-avatar'
    ];
    
    let menuButton = null;
    for (const selector of menuSelectors) {
      menuButton = await page.$(selector);
      if (menuButton) {
        console.log(`✅ 모바일 메뉴 버튼 발견: ${selector}`);
        break;
      }
    }
    
    if (!menuButton) {
      console.log('❌ 모바일 메뉴 버튼을 찾을 수 없음. 페이지 소스 확인...');
      const bodyText = await page.locator('body').innerHTML();
      console.log('페이지 헤더 영역:', bodyText.substring(0, 1000));
      
      // 프로필 관련 요소들 찾기
      const profileElements = await page.$$eval('*', elements => 
        elements.filter(el => 
          el.textContent?.includes('프로필') || 
          el.classList?.contains('profile') ||
          el.onclick?.toString().includes('toggle') ||
          el.onclick?.toString().includes('menu')
        ).map(el => ({
          tagName: el.tagName,
          className: el.className,
          onclick: el.onclick?.toString() || 'none',
          textContent: el.textContent?.substring(0, 50)
        }))
      );
      console.log('프로필 관련 요소들:', profileElements);
    } else {
      await menuButton.click();
      await page.waitForTimeout(1000);
      
      // 메뉴 열린 후 스크린샷
      await page.screenshot({ path: 'mobile_menu_opened.png', fullPage: true });
      console.log('📸 모바일 메뉴 열린 상태 스크린샷 저장: mobile_menu_opened.png');
      
      console.log('🔍 4단계: 모바일 메뉴 구조 분석');
      
      // 메뉴 구조 확인
      const menuContainer = await page.$('.mobile-menu, #mobile-menu, [data-mobile-menu]');
      if (menuContainer) {
        const menuHTML = await menuContainer.innerHTML();
        console.log('📋 모바일 메뉴 HTML 구조:', menuHTML.substring(0, 500));
        
        // 각 섹션 확인
        const sections = [
          { name: '사용자 정보 헤더', selector: '.user-info, .menu-header, .profile-header' },
          { name: '메인 메뉴 섹션', selector: '.main-menu, .menu-section:first-of-type' },
          { name: '개인 메뉴 섹션', selector: '.personal-menu, .menu-section:nth-of-type(2)' },
          { name: '시스템 메뉴', selector: '.system-menu, .menu-section:last-of-type' }
        ];
        
        for (const section of sections) {
          const element = await menuContainer.$(section.selector);
          if (element) {
            const text = await element.textContent();
            console.log(`✅ ${section.name}: ${text?.substring(0, 100)}`);
          } else {
            console.log(`❌ ${section.name}: 찾을 수 없음`);
          }
        }
        
        // 메뉴 아이템들 확인
        const menuItems = await menuContainer.$$eval('a, button', items => 
          items.map(item => ({
            text: item.textContent?.trim(),
            href: item.href || 'no-href',
            className: item.className
          }))
        );
        console.log('📱 메뉴 아이템들:', menuItems);
      } else {
        console.log('❌ 모바일 메뉴 컨테이너를 찾을 수 없음');
      }
      
      console.log('🔄 5단계: 커뮤니티 페이지 이동 테스트');
      
      // 커뮤니티 링크 클릭
      const communityLink = await page.$('a[href*="community"], a:has-text("커뮤니티")');
      if (communityLink) {
        await communityLink.click();
        await page.waitForTimeout(3000);
        
        // 페이지 이동 확인
        const currentURL = page.url();
        console.log(`📍 현재 URL: ${currentURL}`);
        
        await page.screenshot({ path: 'community_page_mobile.png', fullPage: true });
        console.log('📸 커뮤니티 페이지 모바일 뷰 스크린샷 저장: community_page_mobile.png');
        
        // 다시 모바일 메뉴 열어서 active 상태 확인
        const menuButtonCommunity = await page.$('button[onclick*="toggleMobileMenu"], .mobile-menu-trigger');
        if (menuButtonCommunity) {
          await menuButtonCommunity.click();
          await page.waitForTimeout(1000);
          
          await page.screenshot({ path: 'mobile_menu_community_active.png', fullPage: true });
          console.log('📸 커뮤니티 페이지에서 모바일 메뉴 active 상태 스크린샷 저장: mobile_menu_community_active.png');
          
          // active 상태 확인
          const activeItems = await page.$$eval('.active, .current, [data-active="true"]', items => 
            items.map(item => ({
              text: item.textContent?.trim(),
              className: item.className
            }))
          );
          console.log('🎯 Active 상태 메뉴 아이템들:', activeItems);
        }
      } else {
        console.log('❌ 커뮤니티 링크를 찾을 수 없음');
      }
    }
    
    console.log('✅ 모바일 메뉴 테스트 완료');
    
  } catch (error) {
    console.error('❌ 테스트 중 오류 발생:', error);
    await page.screenshot({ path: 'mobile_menu_error.png', fullPage: true });
    console.log('📸 오류 상태 스크린샷 저장: mobile_menu_error.png');
  } finally {
    await browser.close();
  }
})();