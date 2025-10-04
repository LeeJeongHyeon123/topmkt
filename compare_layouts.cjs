const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 강의 일정 vs 행사 일정 레이아웃 직접 비교...');

  const testSizes = [
    { width: 1920, height: 1080, name: 'FullHD' },
    { width: 1440, height: 900, name: 'MacBook' }
  ];

  for (const size of testSizes) {
    console.log(`\n📱 ${size.name} (${size.width}x${size.height}) 테스트...`);

    // 강의 일정 테스트
    const lecturesPage = await browser.newPage();
    await lecturesPage.setViewportSize({ width: size.width, height: size.height });
    await lecturesPage.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle', timeout: 10000
    });
    await lecturesPage.waitForTimeout(1000);
    await lecturesPage.goto('https://www.topmktx.com/lectures', {
      waitUntil: 'networkidle', timeout: 10000
    });
    await lecturesPage.waitForTimeout(2000);

    const lecturesAnalysis = await lecturesPage.evaluate(() => {
      const layout = document.querySelector('.lectures-layout');
      const sidebar = document.querySelector('.lectures-sidebar');
      const calendarView = document.querySelector('.calendar-view');

      if (!layout || !sidebar || !calendarView) {
        return { error: '요소를 찾을 수 없음' };
      }

      const layoutStyle = getComputedStyle(layout);
      const sidebarRect = sidebar.getBoundingClientRect();
      const calendarRect = calendarView.getBoundingClientRect();

      return {
        layout: {
          width: layout.offsetWidth,
          maxWidth: layoutStyle.maxWidth,
          gridColumns: layoutStyle.gridTemplateColumns
        },
        sidebar: {
          left: sidebarRect.left,
          right: sidebarRect.right,
          width: sidebarRect.width,
          visible: sidebarRect.right <= window.innerWidth
        },
        calendar: {
          left: calendarRect.left,
          right: calendarRect.right,
          width: calendarRect.width
        },
        hasHorizontalScroll: document.documentElement.scrollWidth > window.innerWidth
      };
    });

    console.log(`   🎓 강의 일정:`);
    console.log(`      레이아웃: ${lecturesAnalysis.layout.width}px (max: ${lecturesAnalysis.layout.maxWidth})`);
    console.log(`      Grid: ${lecturesAnalysis.layout.gridColumns}`);
    console.log(`      사이드바: ${lecturesAnalysis.sidebar.left} ~ ${lecturesAnalysis.sidebar.right} (visible: ${lecturesAnalysis.sidebar.visible})`);
    console.log(`      캘린더: ${lecturesAnalysis.calendar.left} ~ ${lecturesAnalysis.calendar.right} (${lecturesAnalysis.calendar.width}px)`);
    console.log(`      가로스크롤: ${lecturesAnalysis.hasHorizontalScroll}`);

    await lecturesPage.screenshot({
      path: `/var/www/html/topmkt/lectures-layout-${size.name.toLowerCase()}.png`,
      fullPage: false
    });

    // 행사 일정 테스트
    const eventsPage = await browser.newPage();
    await eventsPage.setViewportSize({ width: size.width, height: size.height });
    await eventsPage.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle', timeout: 10000
    });
    await eventsPage.waitForTimeout(1000);
    await eventsPage.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle', timeout: 10000
    });
    await eventsPage.waitForTimeout(2000);

    const eventsAnalysis = await eventsPage.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      const sidebar = document.querySelector('.events-sidebar');
      const calendarView = document.querySelector('.calendar-view');

      if (!layout || !sidebar || !calendarView) {
        return { error: '요소를 찾을 수 없음' };
      }

      const layoutStyle = getComputedStyle(layout);
      const sidebarRect = sidebar.getBoundingClientRect();
      const calendarRect = calendarView.getBoundingClientRect();

      return {
        layout: {
          width: layout.offsetWidth,
          maxWidth: layoutStyle.maxWidth,
          gridColumns: layoutStyle.gridTemplateColumns
        },
        sidebar: {
          left: sidebarRect.left,
          right: sidebarRect.right,
          width: sidebarRect.width,
          visible: sidebarRect.right <= window.innerWidth
        },
        calendar: {
          left: calendarRect.left,
          right: calendarRect.right,
          width: calendarRect.width
        },
        hasHorizontalScroll: document.documentElement.scrollWidth > window.innerWidth
      };
    });

    console.log(`   🎪 행사 일정:`);
    console.log(`      레이아웃: ${eventsAnalysis.layout.width}px (max: ${eventsAnalysis.layout.maxWidth})`);
    console.log(`      Grid: ${eventsAnalysis.layout.gridColumns}`);
    console.log(`      사이드바: ${eventsAnalysis.sidebar.left} ~ ${eventsAnalysis.sidebar.right} (visible: ${eventsAnalysis.sidebar.visible})`);
    console.log(`      캘린더: ${eventsAnalysis.calendar.left} ~ ${eventsAnalysis.calendar.right} (${eventsAnalysis.calendar.width}px)`);
    console.log(`      가로스크롤: ${eventsAnalysis.hasHorizontalScroll}`);

    await eventsPage.screenshot({
      path: `/var/www/html/topmkt/events-layout-${size.name.toLowerCase()}.png`,
      fullPage: false
    });

    // 결과 비교
    const lecturesGood = lecturesAnalysis.sidebar.visible && !lecturesAnalysis.hasHorizontalScroll;
    const eventsGood = eventsAnalysis.sidebar.visible && !eventsAnalysis.hasHorizontalScroll;

    console.log(`   🔍 결과: 강의 ${lecturesGood ? '✅' : '❌'} vs 행사 ${eventsGood ? '✅' : '❌'}`);

    await lecturesPage.close();
    await eventsPage.close();
  }

  await browser.close();
  console.log('\n🏁 비교 완료');
})();