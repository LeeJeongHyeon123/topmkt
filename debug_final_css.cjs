const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 행사 일정 MacBook 크기에서 캘린더 CSS 디버깅...');

  const page = await browser.newPage();
  await page.setViewportSize({ width: 1440, height: 900 });

  await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await page.goto('https://www.topmktx.com/events', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await page.waitForTimeout(2000);

  const cssDebug = await page.evaluate(() => {
    const calendar = document.querySelector('.calendar-view');
    const main = document.querySelector('.events-main');

    if (!calendar || !main) return { error: 'Elements not found' };

    const calendarStyle = getComputedStyle(calendar);
    const mainStyle = getComputedStyle(main);

    return {
      calendar: {
        width: calendarStyle.width,
        maxWidth: calendarStyle.maxWidth,
        minWidth: calendarStyle.minWidth,
        offsetWidth: calendar.offsetWidth,
        clientWidth: calendar.clientWidth,
        className: calendar.className
      },
      main: {
        width: mainStyle.width,
        maxWidth: mainStyle.maxWidth,
        minWidth: mainStyle.minWidth,
        offsetWidth: main.offsetWidth,
        clientWidth: main.clientWidth,
        className: main.className
      },
      viewport: {
        width: window.innerWidth,
        height: window.innerHeight
      }
    };
  });

  console.log('\n📋 MacBook (1440px) CSS 분석:');
  console.log('   뷰포트:', cssDebug.viewport.width, 'x', cssDebug.viewport.height);
  console.log('   Main 요소:');
  console.log('     클래스:', cssDebug.main.className);
  console.log('     width:', cssDebug.main.width);
  console.log('     maxWidth:', cssDebug.main.maxWidth);
  console.log('     실제 너비:', cssDebug.main.offsetWidth);
  console.log('   Calendar 요소:');
  console.log('     클래스:', cssDebug.calendar.className);
  console.log('     width:', cssDebug.calendar.width);
  console.log('     maxWidth:', cssDebug.calendar.maxWidth);
  console.log('     실제 너비:', cssDebug.calendar.offsetWidth);

  // 스크린샷 촬영
  await page.screenshot({
    path: `/var/www/html/topmkt/debug-macbook-events.png`,
    fullPage: false
  });
  console.log('   📸 스크린샷: debug-macbook-events.png');

  await page.close();
  await browser.close();

  console.log('\n🏁 CSS 디버깅 완료');
})();