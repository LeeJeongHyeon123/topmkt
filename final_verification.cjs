const { chromium } = require('playwright');

(async () => {
  console.log('🎯 최종 사이드바 검증 (캐시 무시)...');

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    // 캐시 완전 무시
    ignoreHTTPSErrors: true
  });

  const page = await context.newPage();

  try {
    // 캐시 무시하고 새로 고침
    await page.setExtraHTTPHeaders({
      'Cache-Control': 'no-cache, no-store, must-revalidate',
      'Pragma': 'no-cache',
      'Expires': '0'
    });

    await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar&_=' + Date.now(), {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    await page.waitForTimeout(3000);

    // 최종 분석
    const finalAnalysis = await page.evaluate(() => {
      const viewport = { width: window.innerWidth, height: window.innerHeight };
      const layout = document.querySelector('.events-layout');
      const sidebar = document.querySelector('.events-sidebar');
      const container = document.querySelector('.events-container');

      if (!sidebar || !layout || !container) {
        return { error: 'Elements not found' };
      }

      const layoutRect = layout.getBoundingClientRect();
      const sidebarRect = sidebar.getBoundingClientRect();
      const containerRect = container.getBoundingClientRect();

      const layoutStyles = window.getComputedStyle(layout);
      const sidebarStyles = window.getComputedStyle(sidebar);
      const containerStyles = window.getComputedStyle(container);

      return {
        viewport,
        container: {
          rect: containerRect,
          maxWidth: containerStyles.maxWidth,
          width: containerStyles.width
        },
        layout: {
          rect: layoutRect,
          maxWidth: layoutStyles.maxWidth,
          width: layoutStyles.width,
          gridTemplateColumns: layoutStyles.gridTemplateColumns,
          gap: layoutStyles.gap
        },
        sidebar: {
          rect: sidebarRect,
          width: sidebarStyles.width,
          visibility: sidebarStyles.visibility,
          display: sidebarStyles.display,
          opacity: sidebarStyles.opacity
        },
        visibility: {
          sidebar_visible:
            sidebarRect.right > 0 &&
            sidebarRect.left < viewport.width &&
            sidebarRect.bottom > 0 &&
            sidebarRect.top < viewport.height,
          sidebar_in_viewport:
            sidebarRect.left >= 0 &&
            sidebarRect.right <= viewport.width
        }
      };
    });

    console.log('\n🔍 최종 검증 결과:');
    console.log('===================');

    if (finalAnalysis.error) {
      console.log('❌ 오류:', finalAnalysis.error);
      return;
    }

    console.log('\n📱 Viewport:', finalAnalysis.viewport.width, 'x', finalAnalysis.viewport.height);

    console.log('\n📦 Container:');
    console.log('- Max Width:', finalAnalysis.container.maxWidth);
    console.log('- Width:', finalAnalysis.container.width);
    console.log('- 크기:', finalAnalysis.container.rect.width, 'x', finalAnalysis.container.rect.height);

    console.log('\n📐 Layout:');
    console.log('- Max Width:', finalAnalysis.layout.maxWidth);
    console.log('- Width:', finalAnalysis.layout.width);
    console.log('- Grid:', finalAnalysis.layout.gridTemplateColumns);
    console.log('- Gap:', finalAnalysis.layout.gap);
    console.log('- 크기:', finalAnalysis.layout.rect.width, 'x', finalAnalysis.layout.rect.height);

    console.log('\n📋 Sidebar:');
    console.log('- Width:', finalAnalysis.sidebar.width);
    console.log('- Display:', finalAnalysis.sidebar.display);
    console.log('- Visibility:', finalAnalysis.sidebar.visibility);
    console.log('- Opacity:', finalAnalysis.sidebar.opacity);
    console.log('- 위치:', Math.round(finalAnalysis.sidebar.rect.x), ',', Math.round(finalAnalysis.sidebar.rect.y));
    console.log('- 크기:', finalAnalysis.sidebar.rect.width, 'x', finalAnalysis.sidebar.rect.height);

    console.log('\n🎯 가시성:');
    console.log('- 화면에 보임:', finalAnalysis.visibility.sidebar_visible ? '✅' : '❌');
    console.log('- 완전히 화면 안:', finalAnalysis.visibility.sidebar_in_viewport ? '✅' : '❌');

    const success = finalAnalysis.visibility.sidebar_visible;
    console.log('\n🎉 최종 결과:');
    console.log(success ? '✅ 사이드바가 정상적으로 보입니다!' : '❌ 사이드바가 여전히 보이지 않습니다.');

    // 최종 스크린샷
    await page.screenshot({
      path: '/var/www/html/topmkt/final-sidebar-verification.png',
      fullPage: true
    });

    console.log('\n📸 최종 스크린샷: /var/www/html/topmkt/final-sidebar-verification.png');

  } catch (error) {
    console.error('❌ 최종 검증 실패:', error.message);
  } finally {
    await browser.close();
  }
})();