import { chromium } from 'playwright';

(async () => {
  console.log('Playwright 모바일 헤더 테스트 시작...');
  
  // 브라우저 시작 (헤드리스 모드)
  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 }, // iPhone 12 Pro 크기
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
  });
  
  const page = await context.newPage();
  
  try {
    console.log('1. DevLogin Helper로 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', { 
      waitUntil: 'networkidle',
      timeout: 30000 
    });
    
    // 잠시 대기
    await page.waitForTimeout(2000);
    
    console.log('2. 메인 페이지로 이동...');
    await page.goto('https://www.topmktx.com/', { 
      waitUntil: 'networkidle',
      timeout: 30000 
    });
    
    // 페이지 로드 대기
    await page.waitForTimeout(3000);
    
    console.log('3. 헤더 요소 분석 중...');
    
    // 헤더 관련 요소들 찾기
    const headerElements = await page.evaluate(() => {
      const elements = [];
      
      // 로고 찾기
      const logo = document.querySelector('.logo, .navbar-brand, [class*="logo"]');
      if (logo) {
        const rect = logo.getBoundingClientRect();
        elements.push({
          type: 'logo',
          text: logo.textContent?.trim() || logo.alt || 'Logo',
          position: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
          className: logo.className
        });
      }
      
      // 프로필 이미지 찾기
      const profileImg = document.querySelector('.profile-img, .user-avatar, [class*="profile"], img[src*="profile"]');
      if (profileImg) {
        const rect = profileImg.getBoundingClientRect();
        elements.push({
          type: 'profile',
          src: profileImg.src,
          position: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
          className: profileImg.className
        });
      }
      
      // 헤더 컨테이너 찾기
      const header = document.querySelector('header, .header, .navbar, .top-bar');
      if (header) {
        const rect = header.getBoundingClientRect();
        elements.push({
          type: 'header',
          position: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
          className: header.className
        });
      }
      
      // 네비게이션 메뉴 찾기
      const navItems = document.querySelectorAll('nav a, .nav-item, .menu-item');
      navItems.forEach((item, index) => {
        const rect = item.getBoundingClientRect();
        if (rect.y < 150) { // 헤더 영역으로 추정되는 위치
          elements.push({
            type: 'nav-item',
            text: item.textContent?.trim(),
            position: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
            className: item.className
          });
        }
      });
      
      return elements;
    });
    
    console.log('4. 헤더 요소 분석 결과:');
    headerElements.forEach((element, index) => {
      console.log(`   ${index + 1}. ${element.type}:`);
      console.log(`      위치: x=${element.position.x}, y=${element.position.y}`);
      console.log(`      크기: ${element.position.width}x${element.position.height}`);
      if (element.text) console.log(`      텍스트: "${element.text}"`);
      if (element.className) console.log(`      클래스: ${element.className}`);
      console.log('');
    });
    
    console.log('5. 스크린샷 촬영 중...');
    await page.screenshot({ 
      path: '/var/www/html/topmkt/mobile_header_analysis.png',
      fullPage: false,
      clip: { x: 0, y: 0, width: 390, height: 200 } // 헤더 영역만
    });
    
    console.log('6. 전체 페이지 스크린샷 촬영...');
    await page.screenshot({ 
      path: '/var/www/html/topmkt/mobile_full_page.png',
      fullPage: false
    });
    
    console.log('테스트 완료! 스크린샷이 저장되었습니다.');
    
  } catch (error) {
    console.error('오류 발생:', error.message);
    
    // 오류 발생 시에도 스크린샷 촬영
    try {
      await page.screenshot({ 
        path: '/var/www/html/topmkt/mobile_error_screenshot.png',
        fullPage: false
      });
      console.log('오류 상황 스크린샷 저장됨');
    } catch (screenshotError) {
      console.error('스크린샷 촬영 실패:', screenshotError.message);
    }
  } finally {
    await browser.close();
    console.log('브라우저 종료됨');
  }
})();