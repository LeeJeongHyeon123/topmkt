const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 행사 일정 레이아웃 수정 후 검증 테스트...');

  // 다양한 화면 크기 테스트 (특히 문제가 있었던 크기들)
  const viewports = [
    { width: 1920, height: 1080, name: 'FullHD' },
    { width: 1440, height: 900, name: 'MacBook' },
    { width: 1366, height: 768, name: 'Laptop' },
    { width: 1200, height: 800, name: 'Tablet-Large' },
    { width: 1100, height: 700, name: 'Problem-1100' },
    { width: 1000, height: 600, name: 'Problem-1000' }
  ];

  for (const viewport of viewports) {
    const page = await browser.newPage();

    try {
      console.log(`\n📱 ${viewport.name} (${viewport.width}x${viewport.height}) 테스트...`);

      await page.setViewportSize({ width: viewport.width, height: viewport.height });

      // 로그인
      await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
        waitUntil: 'networkidle',
        timeout: 10000
      });

      await page.waitForTimeout(1000);

      // 행사 일정 페이지로 이동
      await page.goto('https://www.topmktx.com/events', {
        waitUntil: 'networkidle',
        timeout: 10000
      });

      await page.waitForTimeout(2000);

      // 레이아웃 분석
      const analysis = await page.evaluate(() => {
        const layout = document.querySelector('.events-layout');
        const main = document.querySelector('.events-main');
        const sidebar = document.querySelector('.events-sidebar');
        const calendarView = document.querySelector('.calendar-view');

        if (!layout || !main || !sidebar || !calendarView) {
          return { error: '요소를 찾을 수 없음' };
        }

        const layoutStyle = getComputedStyle(layout);
        const sidebarRect = sidebar.getBoundingClientRect();
        const calendarRect = calendarView.getBoundingClientRect();

        // 겹침 검사
        const isOverlapping = !(sidebarRect.right <= calendarRect.left ||
                                sidebarRect.left >= calendarRect.right ||
                                sidebarRect.bottom <= calendarRect.top ||
                                sidebarRect.top >= calendarRect.bottom);

        // 뷰포트 밖으로 나갔는지 확인
        const isOutsideViewport = sidebarRect.right > window.innerWidth ||
                                  calendarRect.right > window.innerWidth;

        // 가로 스크롤 확인
        const hasHorizontalScroll = document.documentElement.scrollWidth > window.innerWidth;

        return {
          viewport: { width: window.innerWidth, height: window.innerHeight },
          layout: {
            display: layoutStyle.display,
            gridColumns: layoutStyle.gridTemplateColumns,
            width: layout.offsetWidth
          },
          sidebar: {
            left: sidebarRect.left,
            right: sidebarRect.right,
            width: sidebarRect.width
          },
          calendar: {
            left: calendarRect.left,
            right: calendarRect.right,
            width: calendarRect.width
          },
          isOverlapping,
          isOutsideViewport,
          hasHorizontalScroll,
          gap: calendarRect.left > sidebarRect.right ?
               calendarRect.left - sidebarRect.right :
               sidebarRect.left - calendarRect.right
        };
      });

      console.log(`   📊 레이아웃: ${analysis.layout.display}, ${analysis.layout.gridColumns}`);
      console.log(`   📋 사이드바: ${analysis.sidebar.left} ~ ${analysis.sidebar.right} (${analysis.sidebar.width}px)`);
      console.log(`   📅 캘린더: ${analysis.calendar.left} ~ ${analysis.calendar.right} (${analysis.calendar.width}px)`);
      console.log(`   🔄 갭: ${analysis.gap}px`);

      // 결과 판정
      let hasIssue = false;
      const issues = [];

      if (analysis.isOverlapping) {
        issues.push('겹침');
        hasIssue = true;
      }
      if (analysis.isOutsideViewport) {
        issues.push('뷰포트 초과');
        hasIssue = true;
      }
      if (analysis.hasHorizontalScroll) {
        issues.push('가로스크롤');
        hasIssue = true;
      }

      if (hasIssue) {
        console.log(`   ❌ 문제: ${issues.join(', ')}`);
        await page.screenshot({
          path: `/var/www/html/topmkt/events-layout-issue-${viewport.name.toLowerCase()}.png`,
          fullPage: false
        });
        console.log(`   📸 스크린샷: events-layout-issue-${viewport.name.toLowerCase()}.png`);
      } else {
        console.log(`   ✅ 완벽! 가로스크롤 없음, 한 화면 완벽 배치`);
        await page.screenshot({
          path: `/var/www/html/topmkt/events-layout-success-${viewport.name.toLowerCase()}.png`,
          fullPage: false
        });
        console.log(`   📸 성공 스크린샷: events-layout-success-${viewport.name.toLowerCase()}.png`);
      }

    } catch (error) {
      console.error(`   ❌ ${viewport.name} 테스트 실패:`, error.message);
    }

    await page.close();
  }

  await browser.close();
  console.log('\n🏁 행사 일정 레이아웃 수정 검증 완료');
})();