const { chromium } = require('playwright');

(async () => {
  console.log('🚀 모바일 헤더 UI 테스트 시작...');
  
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 },
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15'
  });
  
  const page = await context.newPage();
  
  try {
    // 1. DevLogin Helper로 사용자 ID 4로 로그인
    console.log('📱 1단계: DevLogin Helper 접속...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    console.log('📱 2단계: 메인 페이지로 이동...');
    await page.goto('https://www.topmktx.com');
    await page.waitForTimeout(3000);
    
    // 메인 페이지 스크린샷
    await page.screenshot({ path: 'mobile_main_page.png', fullPage: true });
    console.log('✅ 메인 페이지 스크린샷 촬영 완료');
    
    // 3. 프로필 이미지 찾기 및 클릭
    console.log('📱 3단계: 프로필 이미지 찾기...');
    
    // 다양한 프로필 이미지 셀렉터 시도
    const profileSelectors = [
      '.profile-image',
      '.header-profile img',
      '.user-profile img',
      'img[alt*="프로필"]',
      'img[src*="profile"]',
      '.profile-modal-trigger',
      '.navbar img'
    ];
    
    let profileElement = null;
    for (const selector of profileSelectors) {
      try {
        profileElement = await page.$(selector);
        if (profileElement) {
          console.log(`✅ 프로필 이미지 발견: ${selector}`);
          break;
        }
      } catch (e) {
        continue;
      }
    }
    
    if (!profileElement) {
      console.log('⚠️ 프로필 이미지를 찾을 수 없습니다. 페이지 내용 확인...');
      
      // 모든 img 태그 찾기
      const allImages = await page.$$eval('img', imgs => 
        imgs.map(img => ({
          src: img.src,
          alt: img.alt,
          className: img.className,
          id: img.id
        }))
      );
      console.log('페이지 내 모든 이미지:', JSON.stringify(allImages, null, 2));
      
      // 헤더 영역 확인
      const headerContent = await page.evaluate(() => {
        const header = document.querySelector('header, .header, .navbar, .top-header');
        return header ? header.innerHTML : '헤더를 찾을 수 없음';
      });
      console.log('헤더 내용 일부:', headerContent.substring(0, 500));
      
    } else {
      console.log('📱 4단계: 프로필 이미지 클릭...');
      await profileElement.click();
      await page.waitForTimeout(1000);
      
      // 모달 확인
      console.log('📱 5단계: 모달 UI 확인...');
      
      // 모달이 나타났는지 확인
      const modalState = await page.evaluate(() => {
        const modals = document.querySelectorAll('.modal, [class*="modal"], [id*="modal"]');
        const overlays = document.querySelectorAll('.overlay, [class*="overlay"]');
        const profileModals = document.querySelectorAll('[class*="profile"][class*="modal"]');
        
        return {
          modalCount: modals.length,
          overlayCount: overlays.length,
          profileModalCount: profileModals.length,
          visibleModals: Array.from(modals).map(m => ({
            className: m.className,
            id: m.id,
            display: getComputedStyle(m).display,
            visibility: getComputedStyle(m).visibility,
            opacity: getComputedStyle(m).opacity
          }))
        };
      });
      
      console.log('모달 상태:', JSON.stringify(modalState, null, 2));
      
      // 모달 상태 스크린샷
      await page.screenshot({ path: 'mobile_profile_modal.png', fullPage: true });
      console.log('✅ 프로필 모달 스크린샷 촬영 완료');
      
      // X 버튼 확인
      const closeButtons = await page.$$eval('[class*="close"], .modal-close, [onclick*="close"]', buttons =>
        buttons.map(btn => ({
          text: btn.textContent,
          className: btn.className,
          onclick: btn.onclick ? btn.onclick.toString() : null
        }))
      );
      console.log('닫기 버튼들:', JSON.stringify(closeButtons, null, 2));
    }
    
  } catch (error) {
    console.error('❌ 테스트 중 오류 발생:', error.message);
    await page.screenshot({ path: 'mobile_error_state.png', fullPage: true });
  } finally {
    await browser.close();
    console.log('🏁 테스트 완료');
  }
})();