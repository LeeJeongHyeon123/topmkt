const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
  console.log('🎯 Grid 레이아웃 검증 시작...');

  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
  });

  const page = await context.newPage();

  try {
    // DevLoginHelper로 자동 로그인
    await page.goto('https://www.topmktx.com/DevLoginHelper.php?user_id=4&redirect_to=/events?year=2025&month=9&view=calendar');
    await page.waitForTimeout(2000);

    console.log('📊 Grid 레이아웃 분석 중...');

    // Grid 레이아웃 분석
    const gridAnalysis = await page.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      const sidebar = document.querySelector('.events-sidebar');
      const main = document.querySelector('.events-main');
      const calendar = document.querySelector('.calendar-view');

      if (!layout || !sidebar || !main) {
        return { error: 'Grid 요소를 찾을 수 없음' };
      }

      const layoutStyles = window.getComputedStyle(layout);
      const sidebarStyles = window.getComputedStyle(sidebar);
      const mainStyles = window.getComputedStyle(main);
      const calendarStyles = calendar ? window.getComputedStyle(calendar) : null;

      const layoutRect = layout.getBoundingClientRect();
      const sidebarRect = sidebar.getBoundingClientRect();
      const mainRect = main.getBoundingClientRect();
      const calendarRect = calendar ? calendar.getBoundingClientRect() : null;

      return {
        layout: {
          display: layoutStyles.display,
          gridTemplateColumns: layoutStyles.gridTemplateColumns,
          gap: layoutStyles.gap,
          width: layoutStyles.width,
          maxWidth: layoutStyles.maxWidth,
          position: { x: layoutRect.x, y: layoutRect.y, width: layoutRect.width, height: layoutRect.height }
        },
        sidebar: {
          display: sidebarStyles.display,
          visibility: sidebarStyles.visibility,
          opacity: sidebarStyles.opacity,
          position: sidebarStyles.position,
          width: sidebarStyles.width,
          maxWidth: sidebarStyles.maxWidth,
          gridColumn: sidebarStyles.gridColumn,
          gridRow: sidebarStyles.gridRow,
          zIndex: sidebarStyles.zIndex,
          position_rect: { x: sidebarRect.x, y: sidebarRect.y, width: sidebarRect.width, height: sidebarRect.height }
        },
        main: {
          display: mainStyles.display,
          width: mainStyles.width,
          maxWidth: mainStyles.maxWidth,
          gridColumn: mainStyles.gridColumn,
          gridRow: mainStyles.gridRow,
          zIndex: mainStyles.zIndex,
          position_rect: { x: mainRect.x, y: mainRect.y, width: mainRect.width, height: mainRect.height }
        },
        calendar: calendarRect ? {
          display: calendarStyles.display,
          gridColumn: calendarStyles.gridColumn,
          gridRow: calendarStyles.gridRow,
          position_rect: { x: calendarRect.x, y: calendarRect.y, width: calendarRect.width, height: calendarRect.height }
        } : null,
        overlap: {
          sidebar_over_main: sidebarRect.x < (mainRect.x + mainRect.width) && (sidebarRect.x + sidebarRect.width) > mainRect.x,
          main_over_sidebar: mainRect.x < (sidebarRect.x + sidebarRect.width) && (mainRect.x + mainRect.width) > sidebarRect.x
        }
      };
    });

    console.log('\n🔍 Grid 레이아웃 분석 결과:');
    console.log('===============================');

    if (gridAnalysis.error) {
      console.log('❌ 오류:', gridAnalysis.error);
      return;
    }

    console.log('\n📐 Layout 컨테이너:');
    console.log(`- Display: ${gridAnalysis.layout.display}`);
    console.log(`- Grid Template Columns: ${gridAnalysis.layout.gridTemplateColumns}`);
    console.log(`- Gap: ${gridAnalysis.layout.gap}`);
    console.log(`- Width: ${gridAnalysis.layout.width}`);
    console.log(`- Max Width: ${gridAnalysis.layout.maxWidth}`);
    console.log(`- Position: (${gridAnalysis.layout.position.x}, ${gridAnalysis.layout.position.y})`);
    console.log(`- Size: ${gridAnalysis.layout.position.width} x ${gridAnalysis.layout.position.height}`);

    console.log('\n📊 Main Content:');
    console.log(`- Display: ${gridAnalysis.main.display}`);
    console.log(`- Width: ${gridAnalysis.main.width}`);
    console.log(`- Max Width: ${gridAnalysis.main.maxWidth}`);
    console.log(`- Grid Column: ${gridAnalysis.main.gridColumn}`);
    console.log(`- Grid Row: ${gridAnalysis.main.gridRow}`);
    console.log(`- Z-Index: ${gridAnalysis.main.zIndex}`);
    console.log(`- Position: (${gridAnalysis.main.position_rect.x}, ${gridAnalysis.main.position_rect.y})`);
    console.log(`- Size: ${gridAnalysis.main.position_rect.width} x ${gridAnalysis.main.position_rect.height}`);

    console.log('\n📋 Sidebar:');
    console.log(`- Display: ${gridAnalysis.sidebar.display}`);
    console.log(`- Visibility: ${gridAnalysis.sidebar.visibility}`);
    console.log(`- Opacity: ${gridAnalysis.sidebar.opacity}`);
    console.log(`- Position: ${gridAnalysis.sidebar.position}`);
    console.log(`- Width: ${gridAnalysis.sidebar.width}`);
    console.log(`- Max Width: ${gridAnalysis.sidebar.maxWidth}`);
    console.log(`- Grid Column: ${gridAnalysis.sidebar.gridColumn}`);
    console.log(`- Grid Row: ${gridAnalysis.sidebar.gridRow}`);
    console.log(`- Z-Index: ${gridAnalysis.sidebar.zIndex}`);
    console.log(`- Position: (${gridAnalysis.sidebar.position_rect.x}, ${gridAnalysis.sidebar.position_rect.y})`);
    console.log(`- Size: ${gridAnalysis.sidebar.position_rect.width} x ${gridAnalysis.sidebar.position_rect.height}`);

    if (gridAnalysis.calendar) {
      console.log('\n📅 Calendar View:');
      console.log(`- Display: ${gridAnalysis.calendar.display}`);
      console.log(`- Grid Column: ${gridAnalysis.calendar.gridColumn}`);
      console.log(`- Grid Row: ${gridAnalysis.calendar.gridRow}`);
      console.log(`- Position: (${gridAnalysis.calendar.position_rect.x}, ${gridAnalysis.calendar.position_rect.y})`);
      console.log(`- Size: ${gridAnalysis.calendar.position_rect.width} x ${gridAnalysis.calendar.position_rect.height}`);
    }

    console.log('\n🔗 Overlap 분석:');
    console.log(`- Sidebar over Main: ${gridAnalysis.overlap.sidebar_over_main ? '❌ 겹침' : '✅ 분리됨'}`);
    console.log(`- Main over Sidebar: ${gridAnalysis.overlap.main_over_sidebar ? '❌ 겹침' : '✅ 분리됨'}`);

    // 성공 여부 판단
    const isGridWorking =
      gridAnalysis.layout.display === 'grid' &&
      gridAnalysis.layout.gridTemplateColumns.includes('1fr') &&
      gridAnalysis.layout.gridTemplateColumns.includes('260px') &&
      gridAnalysis.sidebar.gridColumn === '2' &&
      gridAnalysis.main.gridColumn === '1' &&
      !gridAnalysis.overlap.sidebar_over_main &&
      !gridAnalysis.overlap.main_over_sidebar;

    console.log('\n🎯 Grid 레이아웃 상태:');
    console.log(isGridWorking ? '✅ Grid 레이아웃 정상 작동' : '❌ Grid 레이아웃 문제 발생');

    // 스크린샷 촬영
    await page.screenshot({
      path: '/var/www/html/topmkt/grid-layout-verification.png',
      fullPage: true
    });
    console.log('\n📸 스크린샷 저장: /var/www/html/topmkt/grid-layout-verification.png');

  } catch (error) {
    console.error('❌ Grid 레이아웃 검증 실패:', error);
  } finally {
    await browser.close();
  }
})();