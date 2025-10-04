const { chromium } = require('playwright');

(async () => {
  console.log('🎯 강의 일정 vs 행사 일정 UI 비교 검증...');

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 }
  });

  try {
    // 강의 일정 페이지 캡처
    const lecturesPage = await context.newPage();
    await lecturesPage.goto('https://www.topmktx.com/lectures?year=2025&month=9&view=calendar', {
      waitUntil: 'networkidle',
      timeout: 10000
    });
    await lecturesPage.waitForTimeout(2000);
    await lecturesPage.screenshot({
      path: '/var/www/html/topmkt/lectures-ui-reference.png',
      fullPage: true
    });

    // 행사 일정 페이지 캡처
    const eventsPage = await context.newPage();
    await eventsPage.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
      waitUntil: 'networkidle',
      timeout: 10000
    });
    await eventsPage.waitForTimeout(2000);
    await eventsPage.screenshot({
      path: '/var/www/html/topmkt/events-ui-final.png',
      fullPage: true
    });

    // 레이아웃 동일성 검증
    const lecturesAnalysis = await lecturesPage.evaluate(() => {
      const layout = document.querySelector('.lectures-layout');
      const sidebar = document.querySelector('.lectures-sidebar');
      const main = document.querySelector('.lectures-main');

      if (!layout || !sidebar || !main) return { error: 'Grid 요소 없음' };

      const layoutStyles = window.getComputedStyle(layout);
      const sidebarStyles = window.getComputedStyle(sidebar);
      const mainStyles = window.getComputedStyle(main);

      const layoutRect = layout.getBoundingClientRect();
      const sidebarRect = sidebar.getBoundingClientRect();
      const mainRect = main.getBoundingClientRect();

      return {
        layout: {
          display: layoutStyles.display,
          gridTemplateColumns: layoutStyles.gridTemplateColumns,
          gap: layoutStyles.gap,
          maxWidth: layoutStyles.maxWidth,
          rect: { width: layoutRect.width, height: layoutRect.height }
        },
        sidebar: {
          width: sidebarStyles.width,
          maxWidth: sidebarStyles.maxWidth,
          rect: { x: sidebarRect.x, y: sidebarRect.y, width: sidebarRect.width, height: sidebarRect.height }
        },
        main: {
          width: mainStyles.width,
          maxWidth: mainStyles.maxWidth,
          rect: { x: mainRect.x, y: mainRect.y, width: mainRect.width, height: mainRect.height }
        }
      };
    });

    const eventsAnalysis = await eventsPage.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      const sidebar = document.querySelector('.events-sidebar');
      const main = document.querySelector('.events-main');

      if (!layout || !sidebar || !main) return { error: 'Grid 요소 없음' };

      const layoutStyles = window.getComputedStyle(layout);
      const sidebarStyles = window.getComputedStyle(sidebar);
      const mainStyles = window.getComputedStyle(main);

      const layoutRect = layout.getBoundingClientRect();
      const sidebarRect = sidebar.getBoundingClientRect();
      const mainRect = main.getBoundingClientRect();

      return {
        layout: {
          display: layoutStyles.display,
          gridTemplateColumns: layoutStyles.gridTemplateColumns,
          gap: layoutStyles.gap,
          maxWidth: layoutStyles.maxWidth,
          rect: { width: layoutRect.width, height: layoutRect.height }
        },
        sidebar: {
          width: sidebarStyles.width,
          maxWidth: sidebarStyles.maxWidth,
          rect: { x: sidebarRect.x, y: sidebarRect.y, width: sidebarRect.width, height: sidebarRect.height }
        },
        main: {
          width: mainStyles.width,
          maxWidth: mainStyles.maxWidth,
          rect: { x: mainRect.x, y: mainRect.y, width: mainRect.width, height: mainRect.height }
        }
      };
    });

    console.log('\n📊 강의 일정 vs 행사 일정 UI 동일성 검증 결과:');
    console.log('==================================================');

    if (lecturesAnalysis.error || eventsAnalysis.error) {
      console.log('❌ 오류:', lecturesAnalysis.error || eventsAnalysis.error);
      return;
    }

    console.log('\n🎓 강의 일정:');
    console.log(`- Grid: ${lecturesAnalysis.layout.gridTemplateColumns}`);
    console.log(`- Gap: ${lecturesAnalysis.layout.gap}`);
    console.log(`- Max Width: ${lecturesAnalysis.layout.maxWidth}`);
    console.log(`- Sidebar: ${lecturesAnalysis.sidebar.width} (위치: ${lecturesAnalysis.sidebar.rect.x})`);
    console.log(`- Main: ${lecturesAnalysis.main.width} (위치: ${lecturesAnalysis.main.rect.x})`);

    console.log('\n🎭 행사 일정:');
    console.log(`- Grid: ${eventsAnalysis.layout.gridTemplateColumns}`);
    console.log(`- Gap: ${eventsAnalysis.layout.gap}`);
    console.log(`- Max Width: ${eventsAnalysis.layout.maxWidth}`);
    console.log(`- Sidebar: ${eventsAnalysis.sidebar.width} (위치: ${eventsAnalysis.sidebar.rect.x})`);
    console.log(`- Main: ${eventsAnalysis.main.width} (위치: ${eventsAnalysis.main.rect.x})`);

    // 동일성 체크
    const isIdentical =
      lecturesAnalysis.layout.gridTemplateColumns === eventsAnalysis.layout.gridTemplateColumns &&
      lecturesAnalysis.layout.gap === eventsAnalysis.layout.gap &&
      lecturesAnalysis.sidebar.width === eventsAnalysis.sidebar.width &&
      lecturesAnalysis.main.width === eventsAnalysis.main.width &&
      Math.abs(lecturesAnalysis.sidebar.rect.x - eventsAnalysis.sidebar.rect.x) <= 10 &&
      Math.abs(lecturesAnalysis.main.rect.x - eventsAnalysis.main.rect.x) <= 10;

    console.log('\n🎯 UI 동일성 검증:');
    console.log('==================================================');
    console.log(isIdentical ? '✅ 강의 일정과 행사 일정 UI가 완전히 동일합니다!' : '❌ 여전히 차이점이 존재합니다.');

    console.log('\n📋 상세 비교:');
    console.log(`✅ Grid Template: ${lecturesAnalysis.layout.gridTemplateColumns === eventsAnalysis.layout.gridTemplateColumns ? '동일' : '다름'}`);
    console.log(`✅ Gap: ${lecturesAnalysis.layout.gap === eventsAnalysis.layout.gap ? '동일' : '다름'}`);
    console.log(`✅ Sidebar Width: ${lecturesAnalysis.sidebar.width === eventsAnalysis.sidebar.width ? '동일' : '다름'}`);
    console.log(`✅ Main Width: ${lecturesAnalysis.main.width === eventsAnalysis.main.width ? '동일' : '다름'}`);
    console.log(`✅ Sidebar Position: ${Math.abs(lecturesAnalysis.sidebar.rect.x - eventsAnalysis.sidebar.rect.x) <= 10 ? '동일' : '다름'}`);
    console.log(`✅ Main Position: ${Math.abs(lecturesAnalysis.main.rect.x - eventsAnalysis.main.rect.x) <= 10 ? '동일' : '다름'}`);

    console.log('\n📸 스크린샷 저장 완료:');
    console.log('- 강의 일정: /var/www/html/topmkt/lectures-ui-reference.png');
    console.log('- 행사 일정: /var/www/html/topmkt/events-ui-final.png');

  } catch (error) {
    console.error('❌ 검증 실패:', error.message);
  } finally {
    await browser.close();
  }
})();