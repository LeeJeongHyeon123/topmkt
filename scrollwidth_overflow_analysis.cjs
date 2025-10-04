const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 ScrollWidth 오버플로우 원인 추적 시작...');

    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    await page.waitForTimeout(1000);

    // 🔍 모든 자식 요소의 overflow 분석
    const overflowAnalysis = await page.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      if (!layout) return { error: 'Layout not found' };

      // 모든 자식 요소 분석
      const allChildren = layout.querySelectorAll('*');
      const overflowingElements = [];

      // layout 자체 정보
      const layoutInfo = {
        element: 'events-layout',
        className: layout.className,
        computedWidth: window.getComputedStyle(layout).width,
        clientWidth: layout.clientWidth,
        scrollWidth: layout.scrollWidth,
        offsetWidth: layout.offsetWidth,
        boundingRect: layout.getBoundingClientRect()
      };

      // 각 자식 요소 검사
      allChildren.forEach((child, index) => {
        const rect = child.getBoundingClientRect();
        const computed = window.getComputedStyle(child);

        // layout의 오른쪽 경계를 넘어서는 요소들 찾기
        const layoutRight = layout.getBoundingClientRect().right;

        if (rect.right > layoutRight + 10) { // 10px 여유
          overflowingElements.push({
            index,
            tagName: child.tagName,
            className: child.className || 'none',
            id: child.id || 'none',
            rect: {
              left: rect.left,
              right: rect.right,
              width: rect.width,
              overflowAmount: rect.right - layoutRight
            },
            computedStyle: {
              width: computed.width,
              maxWidth: computed.maxWidth,
              position: computed.position,
              display: computed.display,
              overflow: computed.overflow,
              overflowX: computed.overflowX
            },
            innerHTML: child.innerHTML ? child.innerHTML.substring(0, 100) + '...' : 'empty'
          });
        }
      });

      // 직접 자식들의 총 너비 계산
      const directChildren = Array.from(layout.children);
      const childrenAnalysis = directChildren.map((child, index) => {
        const rect = child.getBoundingClientRect();
        const computed = window.getComputedStyle(child);

        return {
          index,
          tagName: child.tagName,
          className: child.className || 'none',
          rect: {
            left: rect.left,
            right: rect.right,
            width: rect.width
          },
          computedStyle: {
            width: computed.width,
            gridColumn: computed.gridColumn || 'auto',
            gridColumnStart: computed.gridColumnStart || 'auto',
            gridColumnEnd: computed.gridColumnEnd || 'auto'
          }
        };
      });

      // Grid 계산 상세
      const gridCalculation = {
        gridTemplateColumns: window.getComputedStyle(layout).gridTemplateColumns,
        gridGap: window.getComputedStyle(layout).gridColumnGap,
        totalCalculatedWidth: 0,
        columnsArray: window.getComputedStyle(layout).gridTemplateColumns.split(' ')
      };

      // 각 컬럼 너비 합계
      gridCalculation.columnsArray.forEach(col => {
        const width = parseFloat(col);
        if (!isNaN(width)) {
          gridCalculation.totalCalculatedWidth += width;
        }
      });

      return {
        timestamp: new Date().toLocaleString('ko-KR'),
        layoutInfo,
        overflowingElementsCount: overflowingElements.length,
        overflowingElements: overflowingElements.slice(0, 10), // 상위 10개만
        directChildrenCount: directChildren.length,
        directChildren: childrenAnalysis,
        gridCalculation,
        scrollAnalysis: {
          scrollWidth: layout.scrollWidth,
          clientWidth: layout.clientWidth,
          scrollWidthExcess: layout.scrollWidth - layout.clientWidth
        }
      };
    });

    console.log('📊 ScrollWidth 오버플로우 분석 결과:');
    console.log(JSON.stringify(overflowAnalysis, null, 2));

    // 🎯 오버플로우 원인 요약
    console.log('\n🎯 오버플로우 원인 분석:');
    console.log(`ScrollWidth 초과: ${overflowAnalysis.scrollAnalysis.scrollWidthExcess}px`);
    console.log(`오버플로우 요소 수: ${overflowAnalysis.overflowingElementsCount}개`);

    if (overflowAnalysis.overflowingElements.length > 0) {
      console.log('\n가장 심각한 오버플로우 요소들:');
      overflowAnalysis.overflowingElements.forEach((elem, i) => {
        console.log(`${i+1}. ${elem.tagName}.${elem.className}`);
        console.log(`   오버플로우: ${elem.rect.overflowAmount.toFixed(1)}px`);
        console.log(`   너비: ${elem.rect.width.toFixed(1)}px`);
        console.log('');
      });
    }

    console.log('\n📦 Grid 직접 자식들:');
    overflowAnalysis.directChildren.forEach((child, i) => {
      console.log(`${i+1}. ${child.tagName}.${child.className}: ${child.rect.width.toFixed(1)}px`);
    });

    console.log(`\n🔧 Grid 계산: ${overflowAnalysis.gridCalculation.gridTemplateColumns}`);
    console.log(`총 계산된 너비: ${overflowAnalysis.gridCalculation.totalCalculatedWidth}px`);

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ ScrollWidth 오버플로우 분석 완료');
  }
})();