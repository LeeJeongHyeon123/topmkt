const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

  await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar&_=' + Date.now(), { waitUntil: 'networkidle' });
  await page.waitForTimeout(3000);

  // max-width 1400px 제약의 원인을 찾기
  const maxWidthAnalysis = await page.evaluate(() => {
    const layout = document.querySelector('.events-layout');

    if (!layout) return { error: 'Layout not found' };

    // 모든 상위 요소들의 스타일 확인
    let current = layout;
    const hierarchy = [];

    while (current && current !== document.body) {
      const computedStyle = window.getComputedStyle(current);
      const rect = current.getBoundingClientRect();

      hierarchy.push({
        tagName: current.tagName,
        className: current.className,
        maxWidth: computedStyle.maxWidth,
        width: computedStyle.width,
        actualWidth: rect.width,
        computedWidth: computedStyle.width
      });

      current = current.parentElement;
    }

    // CSS 규칙들 확인
    const allRules = [];
    for (let sheet of document.styleSheets) {
      try {
        for (let rule of sheet.cssRules || sheet.rules) {
          if (rule.style && rule.style.maxWidth === '1400px') {
            allRules.push({
              selector: rule.selectorText,
              maxWidth: rule.style.maxWidth,
              cssText: rule.cssText
            });
          }
        }
      } catch(e) {
        // Cross-origin stylesheet
      }
    }

    return {
      hierarchy,
      rules1400: allRules,
      layoutStyle: {
        maxWidth: layout.style.maxWidth,
        computedMaxWidth: window.getComputedStyle(layout).maxWidth,
        width: window.getComputedStyle(layout).width
      }
    };
  });

  console.log('🔍 Max-width 1400px 제약 분석:');
  console.log('');
  console.log('📊 레이아웃 스타일:');
  console.log(maxWidthAnalysis.layoutStyle);
  console.log('');
  console.log('🏗️ 상위 요소 계층:');
  maxWidthAnalysis.hierarchy.forEach((item, index) => {
    console.log(`${index + 1}. ${item.tagName}.${item.className}`);
    console.log(`   max-width: ${item.maxWidth}, width: ${item.width}, actual: ${item.actualWidth}px`);
  });
  console.log('');
  console.log('📝 1400px 관련 CSS 규칙:');
  maxWidthAnalysis.rules1400.forEach(rule => {
    console.log(`- ${rule.selector}: ${rule.maxWidth}`);
  });

  await browser.close();
})();