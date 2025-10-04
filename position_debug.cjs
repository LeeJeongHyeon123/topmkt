const { chromium } = require('playwright');

(async () => {
  console.log('🎯 사이드바 위치 정밀 분석...');

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 1440, height: 900 }
  });

  try {
    await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    await page.waitForTimeout(2000);

    // 정밀 위치 분석
    const positionAnalysis = await page.evaluate(() => {
      const viewport = { width: window.innerWidth, height: window.innerHeight };
      const layout = document.querySelector('.events-layout');
      const sidebar = document.querySelector('.events-sidebar');
      const main = document.querySelector('.events-main');
      const container = document.querySelector('.events-container');

      if (!sidebar) return { error: 'sidebar not found' };

      const layoutRect = layout ? layout.getBoundingClientRect() : null;
      const sidebarRect = sidebar.getBoundingClientRect();
      const mainRect = main ? main.getBoundingClientRect() : null;
      const containerRect = container ? container.getBoundingClientRect() : null;

      const layoutStyles = layout ? window.getComputedStyle(layout) : null;
      const sidebarStyles = window.getComputedStyle(sidebar);
      const containerStyles = container ? window.getComputedStyle(container) : null;

      return {
        viewport,
        container: containerRect ? {
          rect: containerRect,
          overflow: containerStyles.overflow,
          overflowX: containerStyles.overflowX,
          overflowY: containerStyles.overflowY,
          width: containerStyles.width,
          maxWidth: containerStyles.maxWidth
        } : null,
        layout: layoutRect ? {
          rect: layoutRect,
          display: layoutStyles.display,
          gridTemplateColumns: layoutStyles.gridTemplateColumns,
          width: layoutStyles.width,
          maxWidth: layoutStyles.maxWidth,
          overflow: layoutStyles.overflow,
          overflowX: layoutStyles.overflowX
        } : null,
        sidebar: {
          rect: sidebarRect,
          gridColumn: sidebarStyles.gridColumn,
          gridRow: sidebarStyles.gridRow,
          position: sidebarStyles.position,
          transform: sidebarStyles.transform,
          zIndex: sidebarStyles.zIndex,
          overflow: sidebarStyles.overflow,
          visibility: sidebarStyles.visibility,
          display: sidebarStyles.display,
          opacity: sidebarStyles.opacity
        },
        main: mainRect ? {
          rect: mainRect,
          gridColumn: window.getComputedStyle(main).gridColumn,
          gridRow: window.getComputedStyle(main).gridRow
        } : null,
        analysis: {
          sidebar_visible_in_viewport:
            sidebarRect.right > 0 &&
            sidebarRect.left < viewport.width &&
            sidebarRect.bottom > 0 &&
            sidebarRect.top < viewport.height,
          sidebar_completely_in_viewport:
            sidebarRect.left >= 0 &&
            sidebarRect.right <= viewport.width &&
            sidebarRect.top >= 0 &&
            sidebarRect.bottom <= viewport.height,
          horizontal_overflow: sidebarRect.right > viewport.width,
          vertical_overflow: sidebarRect.bottom > viewport.height,
          sidebar_left_edge: sidebarRect.left,
          sidebar_right_edge: sidebarRect.right,
          viewport_width: viewport.width
        }
      };
    });

    console.log('\n🔍 위치 분석 결과:');
    console.log('====================');

    if (positionAnalysis.error) {
      console.log('❌ 오류:', positionAnalysis.error);
      return;
    }

    console.log('\n📱 Viewport:');
    console.log(`- 크기: ${positionAnalysis.viewport.width} x ${positionAnalysis.viewport.height}`);

    if (positionAnalysis.container) {
      console.log('\n📦 Container:');
      console.log(`- 위치: (${positionAnalysis.container.rect.x}, ${positionAnalysis.container.rect.y})`);
      console.log(`- 크기: ${positionAnalysis.container.rect.width} x ${positionAnalysis.container.rect.height}`);
      console.log(`- Overflow: ${positionAnalysis.container.overflow}`);
      console.log(`- OverflowX: ${positionAnalysis.container.overflowX}`);
      console.log(`- Max Width: ${positionAnalysis.container.maxWidth}`);
    }

    if (positionAnalysis.layout) {
      console.log('\n📐 Layout:');
      console.log(`- 위치: (${positionAnalysis.layout.rect.x}, ${positionAnalysis.layout.rect.y})`);
      console.log(`- 크기: ${positionAnalysis.layout.rect.width} x ${positionAnalysis.layout.rect.height}`);
      console.log(`- Grid: ${positionAnalysis.layout.gridTemplateColumns}`);
      console.log(`- Max Width: ${positionAnalysis.layout.maxWidth}`);
      console.log(`- Overflow: ${positionAnalysis.layout.overflow}`);
      console.log(`- OverflowX: ${positionAnalysis.layout.overflowX}`);
    }

    console.log('\n📋 Sidebar:');
    console.log(`- 위치: (${positionAnalysis.sidebar.rect.x}, ${positionAnalysis.sidebar.rect.y})`);
    console.log(`- 크기: ${positionAnalysis.sidebar.rect.width} x ${positionAnalysis.sidebar.rect.height}`);
    console.log(`- Grid Column: ${positionAnalysis.sidebar.gridColumn}`);
    console.log(`- Grid Row: ${positionAnalysis.sidebar.gridRow}`);
    console.log(`- Position: ${positionAnalysis.sidebar.position}`);
    console.log(`- Z-Index: ${positionAnalysis.sidebar.zIndex}`);
    console.log(`- Transform: ${positionAnalysis.sidebar.transform}`);
    console.log(`- Display: ${positionAnalysis.sidebar.display}`);
    console.log(`- Visibility: ${positionAnalysis.sidebar.visibility}`);
    console.log(`- Opacity: ${positionAnalysis.sidebar.opacity}`);

    if (positionAnalysis.main) {
      console.log('\n📊 Main:');
      console.log(`- 위치: (${positionAnalysis.main.rect.x}, ${positionAnalysis.main.rect.y})`);
      console.log(`- 크기: ${positionAnalysis.main.rect.width} x ${positionAnalysis.main.rect.height}`);
      console.log(`- Grid Column: ${positionAnalysis.main.gridColumn}`);
      console.log(`- Grid Row: ${positionAnalysis.main.gridRow}`);
    }

    console.log('\n🎯 가시성 분석:');
    console.log(`- Viewport 내 일부 보임: ${positionAnalysis.analysis.sidebar_visible_in_viewport ? '✅' : '❌'}`);
    console.log(`- Viewport 내 완전 포함: ${positionAnalysis.analysis.sidebar_completely_in_viewport ? '✅' : '❌'}`);
    console.log(`- 가로 오버플로우: ${positionAnalysis.analysis.horizontal_overflow ? '❌' : '✅'}`);
    console.log(`- 세로 오버플로우: ${positionAnalysis.analysis.vertical_overflow ? '❌' : '✅'}`);
    console.log(`- 사이드바 왼쪽 끝: ${positionAnalysis.analysis.sidebar_left_edge}px`);
    console.log(`- 사이드바 오른쪽 끝: ${positionAnalysis.analysis.sidebar_right_edge}px`);
    console.log(`- Viewport 너비: ${positionAnalysis.analysis.viewport_width}px`);

    const isOverflowing = positionAnalysis.analysis.sidebar_right_edge > positionAnalysis.analysis.viewport_width;
    console.log('\n🚨 문제 진단:');
    console.log(isOverflowing ?
      '❌ 사이드바가 화면 오른쪽 밖으로 넘어갔습니다!' :
      '✅ 사이드바 위치가 정상입니다.'
    );

    // 가로 스크롤 범위 스크린샷
    await page.screenshot({
      path: '/var/www/html/topmkt/sidebar-position-debug.png',
      fullPage: false,
      clip: { x: 0, y: 300, width: 1600, height: 600 }
    });

    console.log('\n📸 스크린샷 저장: /var/www/html/topmkt/sidebar-position-debug.png');

  } catch (error) {
    console.error('❌ 위치 분석 실패:', error.message);
  } finally {
    await browser.close();
  }
})();