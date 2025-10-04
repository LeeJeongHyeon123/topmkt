const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 행사 일정 페이지 접속 중...');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    console.log('📸 네비게이션 상태 스크린샷 촬영 중...');
    await page.screenshot({
      path: 'events-navigation-debug.png',
      fullPage: false,
      clip: { x: 0, y: 0, width: 1200, height: 400 }
    });

    console.log('🔍 네비게이션 요소 분석 중...');
    const navInfo = await page.evaluate(() => {
      // 메인 네비게이션 확인
      const mainNav = document.querySelector('.main-nav');
      const navMenu = document.querySelector('.nav-menu');
      const hamburger = document.querySelector('.mobile-hamburger');
      const header = document.querySelector('.main-header');

      const getElementInfo = (element, name) => {
        if (!element) return { name, exists: false };

        const styles = window.getComputedStyle(element);
        const rect = element.getBoundingClientRect();

        return {
          name,
          exists: true,
          display: styles.display,
          visibility: styles.visibility,
          opacity: styles.opacity,
          position: styles.position,
          zIndex: styles.zIndex,
          width: styles.width,
          height: styles.height,
          left: styles.left,
          right: styles.right,
          top: styles.top,
          bottom: styles.bottom,
          rect: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
          innerHTML: element.innerHTML ? element.innerHTML.substring(0, 100) + '...' : 'empty'
        };
      };

      return {
        mainNav: getElementInfo(mainNav, 'main-nav'),
        navMenu: getElementInfo(navMenu, 'nav-menu'),
        hamburger: getElementInfo(hamburger, 'mobile-hamburger'),
        header: getElementInfo(header, 'main-header'),
        screenWidth: window.innerWidth,
        screenHeight: window.innerHeight,
        isDesktop: window.innerWidth > 768
      };
    });

    console.log('📊 네비게이션 분석 결과:');
    console.log(JSON.stringify(navInfo, null, 2));

    // 특정 CSS 규칙 확인
    console.log('\n🔧 네비게이션 CSS 규칙 확인 중...');
    const navCssRules = await page.evaluate(() => {
      const rules = [];
      for (let sheet of document.styleSheets) {
        try {
          for (let rule of sheet.cssRules || sheet.rules) {
            if (rule.selectorText &&
                (rule.selectorText.includes('main-nav') ||
                 rule.selectorText.includes('nav-menu') ||
                 rule.selectorText.includes('mobile-hamburger'))) {
              rules.push({
                selector: rule.selectorText,
                display: rule.style.display || 'auto',
                visibility: rule.style.visibility || 'auto',
                opacity: rule.style.opacity || 'auto',
                cssText: rule.cssText.substring(0, 200) + '...'
              });
            }
          }
        } catch (e) {
          // CORS 오류 무시
        }
      }
      return rules;
    });

    console.log('📝 네비게이션 관련 CSS 규칙들:');
    navCssRules.forEach((rule, index) => {
      console.log(`${index + 1}. ${rule.selector}`);
      console.log(`   display: ${rule.display}`);
      console.log(`   visibility: ${rule.visibility}`);
      console.log(`   opacity: ${rule.opacity}`);
      console.log('');
    });

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('✅ 분석 완료. events-navigation-debug.png 파일 생성됨');
  }
})();