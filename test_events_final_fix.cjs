const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 최종 수정사항 검증 테스트...');

  // 문제가 있었던 뷰포트들만 집중 테스트
  const problemViewports = [
    { width: 1100, height: 700, name: 'Narrow' },
    { width: 1050, height: 600, name: 'VeryNarrow' },
    { width: 1000, height: 600, name: 'Critical' },
    { width: 1200, height: 800, name: 'Edge' }
  ];

  for (const viewport of problemViewports) {
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
        const containerRect = document.querySelector('.events-container').getBoundingClientRect();

        // 겹침 검사
        const isOverlapping = !(sidebarRect.right <= calendarRect.left ||
                                sidebarRect.left >= calendarRect.right ||
                                sidebarRect.bottom <= calendarRect.top ||
                                sidebarRect.top >= calendarRect.bottom);

        // 뷰포트 밖으로 나갔는지 확인
        const isOutsideViewport = sidebarRect.right > window.innerWidth ||
                                  calendarRect.right > window.innerWidth;

        // 세로 배치 확인 (사이드바가 캘린더 위에 있는지)
        const isVerticalLayout = sidebarRect.bottom <= calendarRect.top + 50; // 50px 허용오차

        return {
          viewport: { width: window.innerWidth, height: window.innerHeight },
          layout: {
            display: layoutStyle.display,
            gridColumns: layoutStyle.gridTemplateColumns
          },
          sidebar: {
            top: sidebarRect.top,
            bottom: sidebarRect.bottom,
            left: sidebarRect.left,
            right: sidebarRect.right,
            width: sidebarRect.width
          },
          calendar: {
            top: calendarRect.top,
            bottom: calendarRect.bottom,
            left: calendarRect.left,
            right: calendarRect.right,
            width: calendarRect.width
          },
          container: {
            width: containerRect.width,
            right: containerRect.right
          },
          isOverlapping,
          isOutsideViewport,
          isVerticalLayout,
          hasHorizontalScroll: document.documentElement.scrollWidth > window.innerWidth
        };
      });

      console.log(`   📊 레이아웃: ${analysis.layout.display}, ${analysis.layout.gridColumns}`);
      console.log(`   📋 사이드바: ${analysis.sidebar.left} ~ ${analysis.sidebar.right}`);
      console.log(`   📅 캘린더: ${analysis.calendar.left} ~ ${analysis.calendar.right}`);
      console.log(`   📱 뷰포트: ${analysis.viewport.width}px`);

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
          path: `/var/www/html/topmkt/events-final-issue-${viewport.name.toLowerCase()}.png`,
          fullPage: false
        });
        console.log(`   📸 스크린샷: events-final-issue-${viewport.name.toLowerCase()}.png`);
      } else {
        console.log(`   ✅ 해결됨! ${analysis.isVerticalLayout ? '(세로배치)' : '(가로배치)'}`);

        // 성공한 경우도 스크린샷 촬영
        await page.screenshot({
          path: `/var/www/html/topmkt/events-final-success-${viewport.name.toLowerCase()}.png`,
          fullPage: false
        });
        console.log(`   📸 성공 스크린샷: events-final-success-${viewport.name.toLowerCase()}.png`);
      }

    } catch (error) {
      console.error(`   ❌ ${viewport.name} 테스트 실패:`, error.message);
    }

    await page.close();
  }

  await browser.close();
  console.log('\n🏁 최종 검증 완료');
})();