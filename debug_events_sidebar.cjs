const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 events-sidebar 상태 분석 시작...');

    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    console.log('✅ 페이지 로딩 완료');

    // 전체 페이지 스크린샷 (사이드바 포함)
    await page.screenshot({
      path: 'events-sidebar-debug.png',
      fullPage: true
    });

    // 사이드바 관련 요소 분석
    const sidebarAnalysis = await page.evaluate(() => {
      const sidebar = document.querySelector('.events-sidebar');
      const layout = document.querySelector('.events-layout');
      const main = document.querySelector('.events-main');
      const todayEvents = document.querySelector('.today-events');
      const upcomingEvents = document.querySelector('.upcoming-events');
      const sidebarSections = document.querySelectorAll('.sidebar-section');

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
          width: styles.width,
          height: styles.height,
          rect: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
          innerHTML: element.innerHTML ? element.innerHTML.substring(0, 200) + '...' : 'empty'
        };
      };

      return {
        sidebar: getElementInfo(sidebar, 'events-sidebar'),
        layout: getElementInfo(layout, 'events-layout'),
        main: getElementInfo(main, 'events-main'),
        todayEvents: getElementInfo(todayEvents, 'today-events'),
        upcomingEvents: getElementInfo(upcomingEvents, 'upcoming-events'),
        sidebarSectionsCount: sidebarSections.length,
        screenWidth: window.innerWidth,
        screenHeight: window.innerHeight,
        bodyHTML: document.body.innerHTML.includes('events-sidebar') ? 'events-sidebar 클래스 발견' : 'events-sidebar 클래스 없음'
      };
    });

    console.log('📊 사이드바 분석 결과:');
    console.log(JSON.stringify(sidebarAnalysis, null, 2));

    // CSS 규칙 확인
    const sidebarCssRules = await page.evaluate(() => {
      const rules = [];
      for (let sheet of document.styleSheets) {
        try {
          for (let rule of sheet.cssRules || sheet.rules) {
            if (rule.selectorText &&
                (rule.selectorText.includes('events-sidebar') ||
                 rule.selectorText.includes('events-layout') ||
                 rule.selectorText.includes('sidebar-section'))) {
              rules.push({
                selector: rule.selectorText,
                display: rule.style.display || null,
                visibility: rule.style.visibility || null,
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

    console.log('\n📝 사이드바 관련 CSS 규칙들:');
    sidebarCssRules.forEach((rule, index) => {
      console.log(`${index + 1}. ${rule.selector}`);
      if (rule.display) console.log(`   display: ${rule.display}`);
      if (rule.visibility) console.log(`   visibility: ${rule.visibility}`);
      console.log('');
    });

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ 분석 완료. events-sidebar-debug.png 파일 생성됨');
  }
})();