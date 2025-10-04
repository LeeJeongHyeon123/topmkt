const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 행사 일정 페이지 접속 중...');
    await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    console.log('📸 스크린샷 촬영 중...');
    await page.screenshot({
      path: 'events-header-debug.png',
      fullPage: false,
      clip: { x: 0, y: 0, width: 1200, height: 800 }
    });

    console.log('🎨 events-header CSS 분석 중...');
    const headerInfo = await page.evaluate(() => {
      const header = document.querySelector('.events-header');
      if (!header) return { error: 'events-header 요소를 찾을 수 없음' };

      const styles = window.getComputedStyle(header);
      const rect = header.getBoundingClientRect();

      return {
        exists: true,
        background: styles.background,
        backgroundColor: styles.backgroundColor,
        backgroundImage: styles.backgroundImage,
        display: styles.display,
        visibility: styles.visibility,
        opacity: styles.opacity,
        width: styles.width,
        height: styles.height,
        padding: styles.padding,
        margin: styles.margin,
        borderRadius: styles.borderRadius,
        position: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
        innerHTML: header.innerHTML.substring(0, 200) + '...'
      };
    });

    console.log('📊 CSS 분석 결과:');
    console.log(JSON.stringify(headerInfo, null, 2));

    // 모든 CSS 규칙 확인
    console.log('\n🔧 CSS 규칙 충돌 확인 중...');
    const cssRules = await page.evaluate(() => {
      const header = document.querySelector('.events-header');
      if (!header) return [];

      const rules = [];
      for (let sheet of document.styleSheets) {
        try {
          for (let rule of sheet.cssRules || sheet.rules) {
            if (rule.selectorText && rule.selectorText.includes('events-header')) {
              rules.push({
                selector: rule.selectorText,
                background: rule.style.background || 'none',
                backgroundColor: rule.style.backgroundColor || 'none',
                backgroundImage: rule.style.backgroundImage || 'none',
                important: rule.cssText.includes('!important')
              });
            }
          }
        } catch (e) {
          // CORS 오류 무시
        }
      }
      return rules;
    });

    console.log('📝 발견된 CSS 규칙들:');
    cssRules.forEach((rule, index) => {
      console.log(`${index + 1}. ${rule.selector}`);
      console.log(`   background: ${rule.background}`);
      console.log(`   backgroundColor: ${rule.backgroundColor}`);
      console.log(`   backgroundImage: ${rule.backgroundImage}`);
      console.log(`   !important: ${rule.important}`);
      console.log('');
    });

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('✅ 분석 완료. events-header-debug.png 파일 생성됨');
  }
})();