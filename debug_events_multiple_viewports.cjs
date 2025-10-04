const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 다양한 뷰포트에서 행사 일정 페이지 레이아웃 테스트...');

  // 다양한 뷰포트 크기들
  const viewports = [
    { width: 1920, height: 1080, name: 'FullHD' },
    { width: 1440, height: 900, name: 'MacBook' },
    { width: 1366, height: 768, name: 'Laptop' },
    { width: 1280, height: 720, name: 'HD' },
    { width: 1024, height: 768, name: 'Tablet' },
    { width: 800, height: 600, name: 'Small' }
  ];

  for (const viewport of viewports) {
    const page = await browser.newPage();

    try {
      console.log(`\n📱 ${viewport.name} (${viewport.width}x${viewport.height}) 테스트...`);

      // 뷰포트 설정
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

        return {
          display: layoutStyle.display,
          gridColumns: layoutStyle.gridTemplateColumns,
          containerWidth: layout.offsetWidth,
          sidebarLeft: sidebarRect.left,
          sidebarRight: sidebarRect.right,
          calendarLeft: calendarRect.left,
          calendarRight: calendarRect.right,
          gap: sidebarRect.left - calendarRect.right,
          isOverlapping,
          isGridWorking: layoutStyle.display === 'grid' && layoutStyle.gridTemplateColumns.includes('px')
        };
      });

      console.log(`   📊 분석: ${JSON.stringify(analysis, null, 2)}`);

      // 문제 있는 경우만 스크린샷
      if (analysis.isOverlapping || !analysis.isGridWorking || analysis.gap < 0) {
        await page.screenshot({
          path: `/var/www/html/topmkt/events-debug-${viewport.name.toLowerCase()}.png`,
          fullPage: false
        });
        console.log(`   📸 문제 감지! 스크린샷: events-debug-${viewport.name.toLowerCase()}.png`);
      }

      // 결과 요약
      if (analysis.isOverlapping) {
        console.log('   ❌ 겹침 발생!');
      } else if (analysis.gap < 10) {
        console.log('   ⚠️ 갭이 너무 작음');
      } else {
        console.log('   ✅ 정상');
      }

    } catch (error) {
      console.error(`   ❌ ${viewport.name} 테스트 실패:`, error.message);
    }

    await page.close();
  }

  await browser.close();
  console.log('\n🏁 다중 뷰포트 테스트 완료');
})();