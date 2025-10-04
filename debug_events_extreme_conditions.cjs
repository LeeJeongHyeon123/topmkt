const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 극한 조건에서 행사 일정 레이아웃 테스트...');

  // 극한 조건들
  const conditions = [
    { width: 1200, height: 800, zoom: 1.25, name: 'Zoom125' },
    { width: 1200, height: 800, zoom: 1.5, name: 'Zoom150' },
    { width: 1400, height: 900, zoom: 1.1, name: 'SlightZoom' },
    { width: 1100, height: 700, zoom: 1.0, name: 'Narrow' },
    { width: 1050, height: 600, zoom: 1.0, name: 'VeryNarrow' },
    { width: 1000, height: 600, zoom: 1.0, name: 'Critical' }
  ];

  for (const condition of conditions) {
    const page = await browser.newPage();

    try {
      console.log(`\n🔬 ${condition.name} (${condition.width}x${condition.height}, zoom: ${condition.zoom}) 테스트...`);

      // 뷰포트 설정
      await page.setViewportSize({ width: condition.width, height: condition.height });

      // 줌 설정
      if (condition.zoom !== 1.0) {
        await page.addStyleTag({
          content: `body { transform: scale(${condition.zoom}); transform-origin: 0 0; }`
        });
      }

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

      // 상세 분석
      const analysis = await page.evaluate(() => {
        const container = document.querySelector('.events-container');
        const layout = document.querySelector('.events-layout');
        const main = document.querySelector('.events-main');
        const sidebar = document.querySelector('.events-sidebar');
        const calendarView = document.querySelector('.calendar-view');

        if (!layout || !main || !sidebar || !calendarView) {
          return { error: '요소를 찾을 수 없음' };
        }

        const containerRect = container.getBoundingClientRect();
        const layoutRect = layout.getBoundingClientRect();
        const sidebarRect = sidebar.getBoundingClientRect();
        const calendarRect = calendarView.getBoundingClientRect();

        const layoutStyle = getComputedStyle(layout);
        const sidebarStyle = getComputedStyle(sidebar);

        // 겹침 검사
        const isOverlapping = !(sidebarRect.right <= calendarRect.left ||
                                sidebarRect.left >= calendarRect.right ||
                                sidebarRect.bottom <= calendarRect.top ||
                                sidebarRect.top >= calendarRect.bottom);

        // 뷰포트 밖으로 나갔는지 확인
        const isOutsideViewport = sidebarRect.right > window.innerWidth ||
                                  sidebarRect.left < 0;

        // 컨테이너 밖으로 나갔는지 확인
        const isOutsideContainer = sidebarRect.right > containerRect.right ||
                                   sidebarRect.left < containerRect.left;

        return {
          viewport: { width: window.innerWidth, height: window.innerHeight },
          container: {
            width: containerRect.width,
            left: containerRect.left,
            right: containerRect.right
          },
          layout: {
            display: layoutStyle.display,
            gridColumns: layoutStyle.gridTemplateColumns,
            width: layoutRect.width,
            left: layoutRect.left,
            right: layoutRect.right
          },
          sidebar: {
            left: sidebarRect.left,
            right: sidebarRect.right,
            width: sidebarRect.width,
            computedWidth: sidebarStyle.width,
            maxWidth: sidebarStyle.maxWidth,
            order: sidebarStyle.order
          },
          calendar: {
            left: calendarRect.left,
            right: calendarRect.right,
            width: calendarRect.width
          },
          gap: sidebarRect.left - calendarRect.right,
          isOverlapping,
          isOutsideViewport,
          isOutsideContainer,
          hasHorizontalScroll: document.documentElement.scrollWidth > window.innerWidth
        };
      });

      console.log(`   📊 상세 분석:`);
      console.log(`      뷰포트: ${analysis.viewport.width}x${analysis.viewport.height}`);
      console.log(`      컨테이너: ${analysis.container.width}px (${analysis.container.left} ~ ${analysis.container.right})`);
      console.log(`      레이아웃: ${analysis.layout.display}, ${analysis.layout.gridColumns}`);
      console.log(`      사이드바: ${analysis.sidebar.left} ~ ${analysis.sidebar.right} (width: ${analysis.sidebar.width}px)`);
      console.log(`      캘린더: ${analysis.calendar.left} ~ ${analysis.calendar.right} (width: ${analysis.calendar.width}px)`);
      console.log(`      갭: ${analysis.gap}px`);

      // 문제 감지
      let hasIssue = false;
      const issues = [];

      if (analysis.isOverlapping) {
        issues.push('겹침');
        hasIssue = true;
      }
      if (analysis.isOutsideViewport) {
        issues.push('뷰포트 오버플로우');
        hasIssue = true;
      }
      if (analysis.isOutsideContainer) {
        issues.push('컨테이너 오버플로우');
        hasIssue = true;
      }
      if (analysis.hasHorizontalScroll) {
        issues.push('가로 스크롤');
        hasIssue = true;
      }
      if (analysis.gap < 0) {
        issues.push('음수 갭');
        hasIssue = true;
      }

      if (hasIssue) {
        console.log(`   ❌ 문제 감지: ${issues.join(', ')}`);

        await page.screenshot({
          path: `/var/www/html/topmkt/events-issue-${condition.name.toLowerCase()}.png`,
          fullPage: false
        });
        console.log(`   📸 문제 스크린샷: events-issue-${condition.name.toLowerCase()}.png`);
      } else {
        console.log('   ✅ 정상');
      }

    } catch (error) {
      console.error(`   ❌ ${condition.name} 테스트 실패:`, error.message);
    }

    await page.close();
  }

  await browser.close();
  console.log('\n🏁 극한 조건 테스트 완료');
})();