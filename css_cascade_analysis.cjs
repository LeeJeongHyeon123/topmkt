const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 CSS Cascade 완전 분석 시작...');

    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    console.log('✅ 페이지 로딩 완료');

    // JavaScript 실행 후 상태 분석
    await page.waitForTimeout(1000);

    // 🔍 1. 모든 CSS 규칙 추출
    const cssAnalysis = await page.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      if (!layout) return { error: 'Layout element not found' };

      // 모든 적용된 CSS 규칙 수집
      const allRules = [];
      const sheets = Array.from(document.styleSheets);

      sheets.forEach((sheet, sheetIndex) => {
        try {
          const rules = Array.from(sheet.cssRules || sheet.rules || []);
          rules.forEach((rule, ruleIndex) => {
            if (rule.selectorText && rule.selectorText.includes('events-layout')) {
              allRules.push({
                sheetIndex,
                ruleIndex,
                selector: rule.selectorText,
                cssText: rule.cssText,
                gridTemplateColumns: rule.style.gridTemplateColumns || null,
                display: rule.style.display || null,
                maxWidth: rule.style.maxWidth || null,
                href: sheet.href || 'inline',
                specificity: calculateSpecificity(rule.selectorText)
              });
            }
          });
        } catch (e) {
          console.log(`CORS 오류 sheet ${sheetIndex}:`, e.message);
        }
      });

      // CSS Specificity 계산 함수
      function calculateSpecificity(selector) {
        const ids = (selector.match(/#/g) || []).length;
        const classes = (selector.match(/\./g) || []).length;
        const elements = (selector.match(/[a-zA-Z]/g) || []).length - classes;
        return { ids, classes, elements, total: ids * 100 + classes * 10 + elements };
      }

      // 현재 computed styles
      const computedStyle = window.getComputedStyle(layout);

      // 인라인 스타일
      const inlineStyle = {
        gridTemplateColumns: layout.style.gridTemplateColumns || null,
        display: layout.style.display || null,
        maxWidth: layout.style.maxWidth || null,
        cssText: layout.style.cssText || 'none'
      };

      return {
        timestamp: new Date().toLocaleString('ko-KR'),
        elementInfo: {
          className: layout.className,
          id: layout.id || 'none',
          tagName: layout.tagName
        },
        computedStyle: {
          gridTemplateColumns: computedStyle.gridTemplateColumns,
          display: computedStyle.display,
          maxWidth: computedStyle.maxWidth,
          width: computedStyle.width,
          gridGap: computedStyle.gridGap
        },
        inlineStyle,
        matchingRules: allRules.sort((a, b) => b.specificity.total - a.specificity.total),
        ruleCount: allRules.length
      };
    });

    console.log('📊 CSS Cascade 분석 결과:');
    console.log(JSON.stringify(cssAnalysis, null, 2));

    // 🔍 2. 1240px 260px 값 추적
    console.log('\n🎯 1240px 260px 값 추적:');
    if (cssAnalysis.matchingRules) {
      cssAnalysis.matchingRules.forEach((rule, index) => {
        if (rule.gridTemplateColumns) {
          console.log(`${index + 1}. ${rule.selector} (specificity: ${rule.specificity.total})`);
          console.log(`   grid-template-columns: ${rule.gridTemplateColumns}`);
          console.log(`   파일: ${rule.href}`);
          console.log('');
        }
      });
    }

    // 🔍 3. JavaScript 동적 변경 감지
    const jsModifications = await page.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      const modifications = [];

      // MutationObserver로 스타일 변경 감지
      return new Promise((resolve) => {
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
              modifications.push({
                time: Date.now(),
                oldValue: mutation.oldValue,
                newValue: mutation.target.style.cssText
              });
            }
          });
        });

        observer.observe(layout, {
          attributes: true,
          attributeOldValue: true,
          attributeFilter: ['style']
        });

        // 2초 후 결과 반환
        setTimeout(() => {
          observer.disconnect();
          resolve({
            modificationsDetected: modifications.length,
            modifications: modifications.slice(-5) // 최근 5개만
          });
        }, 2000);
      });
    });

    console.log('\n🔄 JavaScript 동적 변경 감지:');
    console.log(JSON.stringify(jsModifications, null, 2));

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ CSS Cascade 분석 완료');
  }
})();