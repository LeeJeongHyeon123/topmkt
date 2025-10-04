const { chromium } = require('playwright');

(async () => {
  console.log('🎯 최종 Grid 레이아웃 검증 시작...');

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    // 행사 일정 페이지 접근
    await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    await page.waitForTimeout(3000);

    // Grid 레이아웃 상세 분석
    const gridAnalysis = await page.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      const sidebar = document.querySelector('.events-sidebar');
      const main = document.querySelector('.events-main');

      if (!layout || !sidebar || !main) {
        return { error: 'Grid 요소를 찾을 수 없음' };
      }

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
          width: layoutStyles.width,
          rect: { x: layoutRect.x, y: layoutRect.y, width: layoutRect.width, height: layoutRect.height }
        },
        sidebar: {
          display: sidebarStyles.display,
          visibility: sidebarStyles.visibility,
          width: sidebarStyles.width,
          maxWidth: sidebarStyles.maxWidth,
          gridColumn: sidebarStyles.gridColumn,
          gridRow: sidebarStyles.gridRow,
          rect: { x: sidebarRect.x, y: sidebarRect.y, width: sidebarRect.width, height: sidebarRect.height }
        },
        main: {
          display: mainStyles.display,
          width: mainStyles.width,
          maxWidth: mainStyles.maxWidth,
          gridColumn: mainStyles.gridColumn,
          gridRow: mainStyles.gridRow,
          rect: { x: mainRect.x, y: mainRect.y, width: mainRect.width, height: mainRect.height }
        },
        overlap: {
          horizontal: sidebarRect.x < (mainRect.x + mainRect.width) && (sidebarRect.x + sidebarRect.width) > mainRect.x,
          vertical: sidebarRect.y < (mainRect.y + mainRect.height) && (sidebarRect.y + sidebarRect.height) > mainRect.y
        },
        gap_calculation: {
          expected_gap: 20,
          actual_gap: Math.abs(sidebarRect.x - (mainRect.x + mainRect.width)),
          gap_match: Math.abs(Math.abs(sidebarRect.x - (mainRect.x + mainRect.width)) - 20) <= 5
        }
      };
    });

    console.log('\n🔍 최종 Grid 레이아웃 분석 결과:');
    console.log('==========================================');

    if (gridAnalysis.error) {
      console.log('❌ 오류:', gridAnalysis.error);
      return;
    }

    console.log('\n📐 Layout 컨테이너:');
    console.log(`- Display: ${gridAnalysis.layout.display}`);
    console.log(`- Grid Template Columns: ${gridAnalysis.layout.gridTemplateColumns}`);
    console.log(`- Gap: ${gridAnalysis.layout.gap}`);
    console.log(`- Max Width: ${gridAnalysis.layout.maxWidth}`);
    console.log(`- 실제 크기: ${gridAnalysis.layout.rect.width} x ${gridAnalysis.layout.rect.height}`);

    console.log('\n📊 Main Content:');
    console.log(`- Width: ${gridAnalysis.main.width}`);
    console.log(`- Max Width: ${gridAnalysis.main.maxWidth}`);
    console.log(`- Grid Column: ${gridAnalysis.main.gridColumn}`);
    console.log(`- 위치: (${gridAnalysis.main.rect.x}, ${gridAnalysis.main.rect.y})`);
    console.log(`- 크기: ${gridAnalysis.main.rect.width} x ${gridAnalysis.main.rect.height}`);

    console.log('\n📋 Sidebar:');
    console.log(`- Display: ${gridAnalysis.sidebar.display}`);
    console.log(`- Visibility: ${gridAnalysis.sidebar.visibility}`);
    console.log(`- Width: ${gridAnalysis.sidebar.width}`);
    console.log(`- Max Width: ${gridAnalysis.sidebar.maxWidth}`);
    console.log(`- Grid Column: ${gridAnalysis.sidebar.gridColumn}`);
    console.log(`- 위치: (${gridAnalysis.sidebar.rect.x}, ${gridAnalysis.sidebar.rect.y})`);
    console.log(`- 크기: ${gridAnalysis.sidebar.rect.width} x ${gridAnalysis.sidebar.rect.height}`);

    console.log('\n🔗 겹침 분석:');
    console.log(`- 가로 겹침: ${gridAnalysis.overlap.horizontal ? '❌ 겹침' : '✅ 분리됨'}`);
    console.log(`- 세로 겹침: ${gridAnalysis.overlap.vertical ? '❌ 겹침' : '✅ 분리됨'}`);

    console.log('\n📏 Gap 분석:');
    console.log(`- 예상 Gap: ${gridAnalysis.gap_calculation.expected_gap}px`);
    console.log(`- 실제 Gap: ${gridAnalysis.gap_calculation.actual_gap}px`);
    console.log(`- Gap 일치: ${gridAnalysis.gap_calculation.gap_match ? '✅ 정확' : '❌ 불일치'}`);

    // 성공 여부 판단
    const isLayoutFixed =
      gridAnalysis.layout.display === 'grid' &&
      gridAnalysis.layout.gridTemplateColumns.includes('320px') &&
      gridAnalysis.layout.maxWidth === '1600px' &&
      gridAnalysis.sidebar.gridColumn === '2' &&
      gridAnalysis.main.gridColumn === '1' &&
      !gridAnalysis.overlap.horizontal &&
      !gridAnalysis.overlap.vertical &&
      gridAnalysis.gap_calculation.gap_match;

    console.log('\n🎯 최종 결과:');
    console.log('==========================================');
    console.log(isLayoutFixed ? '✅ 강의 일정과 완전히 동일한 Grid 레이아웃 달성!' : '❌ 여전히 레이아웃 문제 존재');

    // 상세 체크리스트
    console.log('\n📋 체크리스트:');
    console.log(`✅ Grid Display: ${gridAnalysis.layout.display === 'grid' ? '정상' : '실패'}`);
    console.log(`✅ Grid Columns (320px): ${gridAnalysis.layout.gridTemplateColumns.includes('320px') ? '정상' : '실패'}`);
    console.log(`✅ Max Width (1600px): ${gridAnalysis.layout.maxWidth === '1600px' ? '정상' : '실패'}`);
    console.log(`✅ Sidebar Grid Position: ${gridAnalysis.sidebar.gridColumn === '2' ? '정상' : '실패'}`);
    console.log(`✅ Main Grid Position: ${gridAnalysis.main.gridColumn === '1' ? '정상' : '실패'}`);
    console.log(`✅ 가로 분리: ${!gridAnalysis.overlap.horizontal ? '정상' : '실패'}`);
    console.log(`✅ 세로 분리: ${!gridAnalysis.overlap.vertical ? '정상' : '실패'}`);
    console.log(`✅ Gap 정확성: ${gridAnalysis.gap_calculation.gap_match ? '정상' : '실패'}`);

    // 스크린샷 촬영
    await page.screenshot({
      path: '/var/www/html/topmkt/final-grid-verification.png',
      fullPage: true
    });

    console.log('\n📸 스크린샷 저장: /var/www/html/topmkt/final-grid-verification.png');

  } catch (error) {
    console.error('❌ 검증 실패:', error.message);
  } finally {
    await browser.close();
  }
})();