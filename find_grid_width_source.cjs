const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 강의 일정의 1070px Grid 너비 설정 소스 추적...');

  const page = await browser.newPage();
  await page.setViewportSize({ width: 1440, height: 900 });

  await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await page.goto('https://www.topmktx.com/lectures', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await page.waitForTimeout(2000);

  const cssAnalysis = await page.evaluate(() => {
    const layout = document.querySelector('.lectures-layout');
    if (!layout) return { error: 'Layout not found' };

    // 모든 CSS 규칙 검사
    const matchedRules = [];
    const stylesheets = Array.from(document.styleSheets);

    for (const stylesheet of stylesheets) {
      try {
        const rules = Array.from(stylesheet.cssRules || stylesheet.rules || []);
        for (const rule of rules) {
          if (rule.type === CSSRule.STYLE_RULE) {
            // 강의 레이아웃과 관련된 선택자 찾기
            if (rule.selectorText &&
                (rule.selectorText.includes('.lectures-layout') ||
                 rule.selectorText.includes('lectures-layout'))) {

              const cssText = rule.cssText;
              if (cssText.includes('grid-template-columns') ||
                  cssText.includes('1070px') ||
                  cssText.includes('max-width')) {
                matchedRules.push({
                  selector: rule.selectorText,
                  cssText: cssText,
                  href: stylesheet.href || 'inline'
                });
              }
            }
          }
        }
      } catch (e) {
        // Cross-origin stylesheets는 무시
        console.log('Skipping stylesheet:', e.message);
      }
    }

    // 인라인 스타일도 확인
    const inlineStyle = layout.style.cssText;

    // 현재 계산된 스타일
    const computed = getComputedStyle(layout);

    return {
      matchedRules: matchedRules,
      inlineStyle: inlineStyle,
      computedGridColumns: computed.gridTemplateColumns,
      computedMaxWidth: computed.maxWidth,
      computedWidth: computed.width
    };
  });

  console.log('\n📋 CSS 규칙 분석 결과:');
  console.log('   계산된 Grid 컬럼:', cssAnalysis.computedGridColumns);
  console.log('   계산된 Max Width:', cssAnalysis.computedMaxWidth);
  console.log('   계산된 Width:', cssAnalysis.computedWidth);

  if (cssAnalysis.inlineStyle) {
    console.log('   인라인 스타일:', cssAnalysis.inlineStyle);
  }

  console.log('\n🎯 매칭된 CSS 규칙들:');
  cssAnalysis.matchedRules.forEach((rule, index) => {
    console.log(`   ${index + 1}. ${rule.selector}`);
    console.log(`      소스: ${rule.href}`);
    console.log(`      CSS: ${rule.cssText.substring(0, 200)}...`);
    console.log('');
  });

  await page.close();
  await browser.close();

  console.log('🏁 Grid 너비 소스 추적 완료');
})();