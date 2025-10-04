const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🚀 빠른 네비게이션 테스트...');

    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.goto('https://www.topmktx.com/events');

    await page.screenshot({ path: 'final-nav-test.png', clip: { x: 0, y: 0, width: 1400, height: 300 } });

    const navStatus = await page.evaluate(() => {
      const nav = document.querySelector('.main-nav');
      const menu = document.querySelector('.nav-menu');

      return {
        navExists: !!nav,
        menuExists: !!menu,
        navDisplay: nav ? window.getComputedStyle(nav).display : 'not found',
        menuDisplay: menu ? window.getComputedStyle(menu).display : 'not found',
        navVisible: nav ? window.getComputedStyle(nav).visibility : 'not found',
        menuVisible: menu ? window.getComputedStyle(menu).visibility : 'not found',
        menuItems: menu ? menu.querySelectorAll('li').length : 0
      };
    });

    console.log('📊 최종 네비게이션 상태:', navStatus);

  } catch (error) {
    console.error('❌ 오류:', error.message);
  } finally {
    await browser.close();
    console.log('✅ 빠른 테스트 완료: final-nav-test.png');
  }
})();