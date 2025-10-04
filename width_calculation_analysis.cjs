const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 Width 계산 분석 시작...');

    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    await page.waitForTimeout(1000);

    // 🔍 부모 컨테이너 체인 분석
    const widthAnalysis = await page.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      if (!layout) return { error: 'Layout not found' };

      // 부모 체인 따라 올라가면서 width 분석
      const parentChain = [];
      let current = layout;

      while (current && current !== document.body) {
        const computed = window.getComputedStyle(current);
        const rect = current.getBoundingClientRect();

        parentChain.push({
          tagName: current.tagName,
          className: current.className || 'none',
          id: current.id || 'none',
          computedStyle: {
            width: computed.width,
            maxWidth: computed.maxWidth,
            minWidth: computed.minWidth,
            boxSizing: computed.boxSizing,
            padding: computed.padding,
            margin: computed.margin,
            display: computed.display,
            position: computed.position
          },
          boundingRect: {
            width: rect.width,
            height: rect.height,
            left: rect.left,
            right: rect.right
          },
          scrollWidth: current.scrollWidth,
          clientWidth: current.clientWidth,
          offsetWidth: current.offsetWidth
        });

        current = current.parentElement;
      }

      // Grid 계산 상세 분석
      const gridAnalysis = {
        layout: {
          computedStyle: window.getComputedStyle(layout),
          boundingRect: layout.getBoundingClientRect(),
          scrollWidth: layout.scrollWidth,
          clientWidth: layout.clientWidth,
          offsetWidth: layout.offsetWidth
        },
        calculation: {
          expectedTotalWidth: 0,
          availableWidth: 0,
          gap: 0,
          firstColumnCalculated: 0,
          secondColumn: 260
        }
      };

      // 계산 로직
      const layoutRect = layout.getBoundingClientRect();
      const layoutComputed = window.getComputedStyle(layout);

      gridAnalysis.calculation.availableWidth = layoutRect.width;
      gridAnalysis.calculation.gap = parseFloat(layoutComputed.gridColumnGap) || 20;
      gridAnalysis.calculation.firstColumnCalculated =
        gridAnalysis.calculation.availableWidth -
        gridAnalysis.calculation.gap -
        gridAnalysis.calculation.secondColumn;

      return {
        timestamp: new Date().toLocaleString('ko-KR'),
        viewport: {
          innerWidth: window.innerWidth,
          innerHeight: window.innerHeight
        },
        parentChain: parentChain.reverse(), // body부터 layout까지
        gridAnalysis,
        gridTemplateColumns: layoutComputed.gridTemplateColumns
      };
    });

    console.log('📊 Width 계산 분석 결과:');
    console.log(JSON.stringify(widthAnalysis, null, 2));

    // 🎯 계산 오류 원인 분석
    console.log('\n🎯 Grid 계산 분석:');
    const calc = widthAnalysis.gridAnalysis.calculation;
    console.log(`사용 가능한 너비: ${calc.availableWidth}px`);
    console.log(`Grid Gap: ${calc.gap}px`);
    console.log(`두 번째 컬럼: ${calc.secondColumn}px`);
    console.log(`첫 번째 컬럼 계산값: ${calc.firstColumnCalculated}px`);
    console.log(`실제 grid-template-columns: ${widthAnalysis.gridTemplateColumns}`);

    // 🔍 문제 진단
    console.log('\n🔍 문제 진단:');
    if (calc.firstColumnCalculated !== 1240) {
      console.log(`❌ 계산 불일치! 예상: ${calc.firstColumnCalculated}px, 실제: 1240px`);
      console.log('⚠️ 브라우저가 다른 기준으로 1fr을 계산하고 있습니다.');
    } else {
      console.log('✅ 계산이 일치합니다.');
    }

    // 부모 컨테이너 문제 체크
    console.log('\n📦 부모 컨테이너 체크:');
    widthAnalysis.parentChain.forEach((parent, index) => {
      if (parent.boundingRect.width > 1300) {
        console.log(`⚠️ ${parent.tagName}.${parent.className}: ${parent.boundingRect.width}px (너무 큼)`);
      }
    });

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ Width 계산 분석 완료');
  }
})();