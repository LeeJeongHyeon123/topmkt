const { chromium } = require('playwright');

(async () => {
  console.log('🕵️ CSS Cascade 탐정 모드 - max-width 1400px 범인 찾기...');

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar&_=' + Date.now(), {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    await page.waitForTimeout(3000);

    // CSS cascade 완전 분석
    const cascadeAnalysis = await page.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      if (!layout) return { error: 'Layout element not found' };

      // 1. Computed style 분석
      const computedStyle = window.getComputedStyle(layout);

      // 2. CSS 규칙 추적
      const styleSheets = Array.from(document.styleSheets);
      const cssRules = [];

      try {
        styleSheets.forEach((sheet, sheetIndex) => {
          try {
            const rules = Array.from(sheet.cssRules || sheet.rules || []);
            rules.forEach((rule, ruleIndex) => {
              if (rule.style && rule.selectorText) {
                // events-layout과 관련된 규칙만 수집
                if (rule.selectorText.includes('events-layout') ||
                    rule.selectorText.includes('.events-layout') ||
                    layout.matches(rule.selectorText)) {

                  cssRules.push({
                    sheetIndex,
                    ruleIndex,
                    selectorText: rule.selectorText,
                    maxWidth: rule.style.maxWidth || '',
                    width: rule.style.width || '',
                    display: rule.style.display || '',
                    gridTemplateColumns: rule.style.gridTemplateColumns || '',
                    href: sheet.href || 'inline',
                    cssText: rule.cssText.substring(0, 200) + '...'
                  });
                }
              }
            });
          } catch (e) {
            console.log('Cannot access stylesheet rules:', e.message);
          }
        });
      } catch (e) {
        console.log('Error accessing stylesheets:', e.message);
      }

      // 3. 인라인 스타일 확인
      const inlineStyle = layout.style;

      // 4. 모든 적용된 스타일 확인
      const allStylesApplied = {};
      const importantProperties = ['max-width', 'width', 'display', 'grid-template-columns'];

      importantProperties.forEach(prop => {
        allStylesApplied[prop] = computedStyle.getPropertyValue(prop);
      });

      // 5. 부모 요소들의 제약 확인
      const parentConstraints = [];
      let currentElement = layout.parentElement;
      let level = 1;

      while (currentElement && level <= 5) {
        const parentStyle = window.getComputedStyle(currentElement);
        parentConstraints.push({
          level,
          tagName: currentElement.tagName,
          className: currentElement.className,
          maxWidth: parentStyle.maxWidth,
          width: parentStyle.width,
          overflow: parentStyle.overflow,
          overflowX: parentStyle.overflowX
        });
        currentElement = currentElement.parentElement;
        level++;
      }

      // 6. 특정 CSS 파일에서 max-width 규칙 찾기
      const maxWidthRules = [];
      cssRules.forEach(rule => {
        if (rule.maxWidth && (rule.maxWidth.includes('1400') || rule.maxWidth.includes('95vw'))) {
          maxWidthRules.push(rule);
        }
      });

      return {
        computedStyles: allStylesApplied,
        inlineStyles: {
          maxWidth: inlineStyle.maxWidth,
          width: inlineStyle.width,
          display: inlineStyle.display,
          gridTemplateColumns: inlineStyle.gridTemplateColumns
        },
        cssRules: cssRules,
        maxWidthRules: maxWidthRules,
        parentConstraints: parentConstraints,
        totalStyleSheets: styleSheets.length,
        elementRect: layout.getBoundingClientRect()
      };
    });

    console.log('\n🔍 CSS Cascade 분석 결과:');
    console.log('============================');

    if (cascadeAnalysis.error) {
      console.log('❌ 오류:', cascadeAnalysis.error);
      return;
    }

    console.log('\n📊 최종 Computed Styles:');
    Object.entries(cascadeAnalysis.computedStyles).forEach(([prop, value]) => {
      console.log(`- ${prop}: ${value}`);
    });

    console.log('\n📝 인라인 Styles:');
    Object.entries(cascadeAnalysis.inlineStyles).forEach(([prop, value]) => {
      console.log(`- ${prop}: ${value || '(없음)'}`);
    });

    console.log('\n🎯 Max-Width 제한 규칙들:');
    if (cascadeAnalysis.maxWidthRules.length > 0) {
      cascadeAnalysis.maxWidthRules.forEach((rule, index) => {
        console.log(`\n--- 범인 ${index + 1} ---`);
        console.log(`선택자: ${rule.selectorText}`);
        console.log(`Max Width: ${rule.maxWidth}`);
        console.log(`CSS 파일: ${rule.href}`);
        console.log(`CSS 텍스트: ${rule.cssText}`);
      });
    } else {
      console.log('⚠️ 직접적인 max-width 규칙을 찾지 못했습니다.');
    }

    console.log('\n👨‍👩‍👧‍👦 부모 요소 제약들:');
    cascadeAnalysis.parentConstraints.forEach(parent => {
      console.log(`\nLevel ${parent.level}: <${parent.tagName}> .${parent.className}`);
      console.log(`- Max Width: ${parent.maxWidth}`);
      console.log(`- Width: ${parent.width}`);
      console.log(`- Overflow: ${parent.overflow}`);
      console.log(`- OverflowX: ${parent.overflowX}`);
    });

    console.log('\n📜 모든 관련 CSS 규칙들:');
    console.log(`총 스타일시트 수: ${cascadeAnalysis.totalStyleSheets}`);
    console.log(`관련 CSS 규칙 수: ${cascadeAnalysis.cssRules.length}`);

    if (cascadeAnalysis.cssRules.length > 0) {
      cascadeAnalysis.cssRules.forEach((rule, index) => {
        console.log(`\n--- 규칙 ${index + 1} ---`);
        console.log(`선택자: ${rule.selectorText}`);
        console.log(`CSS 파일: ${rule.href}`);
        if (rule.maxWidth) console.log(`Max Width: ${rule.maxWidth}`);
        if (rule.width) console.log(`Width: ${rule.width}`);
        if (rule.display) console.log(`Display: ${rule.display}`);
        if (rule.gridTemplateColumns) console.log(`Grid Template: ${rule.gridTemplateColumns}`);
      });
    }

    console.log('\n📐 Element 위치:');
    console.log(`- X: ${cascadeAnalysis.elementRect.x}`);
    console.log(`- Y: ${cascadeAnalysis.elementRect.y}`);
    console.log(`- Width: ${cascadeAnalysis.elementRect.width}`);
    console.log(`- Height: ${cascadeAnalysis.elementRect.height}`);

    console.log('\n🚨 범인 추적 완료!');

  } catch (error) {
    console.error('❌ CSS 탐정 실패:', error.message);
  } finally {
    await browser.close();
  }
})();