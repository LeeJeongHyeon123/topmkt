const { chromium } = require('playwright');

(async () => {
  console.log('🔍 모바일 메뉴 구조 상세 분석');
  
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 }
  });
  
  const page = await context.newPage();
  
  try {
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    await page.goto('https://www.topmktx.com');
    await page.waitForTimeout(3000);
    
    console.log('📱 헤더 전체 구조 분석');
    
    // 헤더 전체 HTML 가져오기
    const headerHTML = await page.$eval('header', header => header.innerHTML);
    console.log('📋 전체 헤더 HTML:');
    console.log(headerHTML);
    
    console.log('\n🔍 특정 요소들 검색');
    
    // 모든 클릭 가능한 요소들 찾기
    const clickableElements = await page.$$eval('header button, header [onclick], header .profile, header .user', elements => 
      elements.map(el => ({
        tagName: el.tagName,
        className: el.className,
        id: el.id,
        onclick: el.onclick?.toString() || 'none',
        textContent: el.textContent?.trim().substring(0, 50),
        outerHTML: el.outerHTML.substring(0, 200)
      }))
    );
    console.log('👆 클릭 가능한 요소들:', clickableElements);
    
    // 이미지 요소들 확인
    const images = await page.$$eval('header img', imgs => 
      imgs.map(img => ({
        src: img.src,
        alt: img.alt,
        className: img.className,
        onclick: img.onclick?.toString() || 'none',
        style: img.style.cssText
      }))
    );
    console.log('🖼️ 헤더 이미지들:', images);
    
    // CSS 클래스로 검색
    const mobileElements = await page.$$eval('*[class*="mobile"], *[class*="menu"], *[class*="toggle"]', elements => 
      elements.map(el => ({
        tagName: el.tagName,
        className: el.className,
        id: el.id,
        textContent: el.textContent?.trim().substring(0, 100)
      }))
    );
    console.log('📱 모바일 관련 요소들:', mobileElements);
    
    // JavaScript 함수들 확인
    const jsContext = await page.evaluate(() => {
      return {
        toggleMobileMenu: typeof toggleMobileMenu !== 'undefined',
        toggleMobileUserModal: typeof toggleMobileUserModal !== 'undefined',
        openProfileModal: typeof openProfileModal !== 'undefined',
        globalFunctions: Object.keys(window).filter(key => 
          typeof window[key] === 'function' && 
          (key.includes('mobile') || key.includes('menu') || key.includes('toggle') || key.includes('profile'))
        )
      };
    });
    console.log('⚙️ JavaScript 함수들:', jsContext);
    
    // 모바일 메뉴 모달 확인
    const mobileModal = await page.$('.mobile-user-modal');
    if (mobileModal) {
      const modalHTML = await mobileModal.innerHTML();
      console.log('📱 모바일 모달 구조:');
      console.log(modalHTML);
      
      // 모달이 보이는지 확인
      const isVisible = await mobileModal.isVisible();
      console.log('👁️ 모바일 모달 가시성:', isVisible);
    }
    
    // 현재 CSS 미디어 쿼리 상태 확인
    const mobileState = await page.evaluate(() => {
      return {
        viewportWidth: window.innerWidth,
        viewportHeight: window.innerHeight,
        isMobileWidth: window.innerWidth <= 768,
        computedStyles: {
          mobileModal: window.getComputedStyle(document.querySelector('.mobile-user-modal') || document.body).display,
          mainNav: window.getComputedStyle(document.querySelector('.main-nav') || document.body).display
        }
      };
    });
    console.log('📱 모바일 상태:', mobileState);
    
  } catch (error) {
    console.error('❌ 분석 중 오류:', error);
  } finally {
    await browser.close();
  }
})();