import { chromium } from 'playwright';

(async () => {
  console.log('🔍 최종 햄버거 메뉴 확인...');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 390, height: 844 },
      userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1'
    });
    
    const page = await context.newPage();
    
    // DevLogin Helper로 사용자 ID 4 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    // 메인 페이지로 이동
    await page.goto('https://www.topmktx.com/');
    await page.waitForTimeout(3000);
    
    // 전체 화면 스크린샷
    await page.screenshot({ 
      path: '/var/www/html/topmkt/hamburger_menu_fixed.png',
      fullPage: false
    });
    
    // 헤더만 확대 스크린샷
    await page.screenshot({ 
      path: '/var/www/html/topmkt/hamburger_header_only.png',
      fullPage: false,
      clip: { x: 0, y: 0, width: 390, height: 80 }
    });
    
    // 햄버거 메뉴 클릭 테스트
    console.log('🖱️ 햄버거 메뉴 클릭 테스트...');
    
    const hamburgerExists = await page.locator('#mobile-menu-toggle').count();
    console.log(`🍔 햄버거 메뉴 존재: ${hamburgerExists > 0 ? 'YES' : 'NO'}`);
    
    if (hamburgerExists > 0) {
      const isVisible = await page.locator('#mobile-menu-toggle').isVisible();
      console.log(`👁️ 햄버거 메뉴 가시성: ${isVisible ? 'VISIBLE' : 'HIDDEN'}`);
      
      if (isVisible) {
        // 클릭 전 모바일 메뉴 상태
        const menuModalBefore = await page.locator('#mobileMenuModal').getAttribute('class');
        console.log(`📱 클릭 전 모바일 메뉴 상태: ${menuModalBefore}`);
        
        // 햄버거 메뉴 클릭
        await page.click('#mobile-menu-toggle');
        await page.waitForTimeout(1000);
        
        // 클릭 후 상태
        const menuModalAfter = await page.locator('#mobileMenuModal').getAttribute('class');
        console.log(`📱 클릭 후 모바일 메뉴 상태: ${menuModalAfter}`);
        
        // 클릭 후 스크린샷
        await page.screenshot({ 
          path: '/var/www/html/topmkt/hamburger_menu_opened.png',
          fullPage: false
        });
        
        // 성공적으로 메뉴가 열렸는지 확인
        const isMenuOpen = menuModalAfter && menuModalAfter.includes('active');
        console.log(`✅ 햄버거 메뉴 작동: ${isMenuOpen ? 'SUCCESS' : 'FAILED'}`);
      }
    }
    
    console.log('✅ 햄버거 메뉴 최종 확인 완료!');
    console.log('📁 생성된 스크린샷:');
    console.log('  - hamburger_menu_fixed.png (전체 화면)');
    console.log('  - hamburger_header_only.png (헤더만)');
    console.log('  - hamburger_menu_opened.png (메뉴 열린 상태)');
    
  } catch (error) {
    console.error('❌ 오류 발생:', error);
  } finally {
    await browser.close();
  }
})();