const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🚀 ULTRA THINK: DevLoginHelper로 우리집탄이 계정 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    console.log('✅ 로그인 완료, 행사 일정 페이지로 이동 중...');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    console.log('📸 실제 사용자 환경 스크린샷 촬영...');
    await page.screenshot({
      path: 'ultra-debug-navigation.png',
      fullPage: false,
      clip: { x: 0, y: 0, width: 1400, height: 500 }
    });

    console.log('🔍 ULTRA THINK: 완전한 CSS cascade 분석 시작...');

    // 1. 네비게이션 요소 존재 확인
    const navElements = await page.evaluate(() => {
      const elements = {
        mainNav: document.querySelector('.main-nav'),
        navMenu: document.querySelector('.nav-menu'),
        header: document.querySelector('.main-header'),
        hamburger: document.querySelector('.mobile-hamburger')
      };

      const results = {};
      for (const [key, element] of Object.entries(elements)) {
        if (element) {
          const styles = window.getComputedStyle(element);
          const rect = element.getBoundingClientRect();
          results[key] = {
            exists: true,
            display: styles.display,
            visibility: styles.visibility,
            opacity: styles.opacity,
            position: styles.position,
            zIndex: styles.zIndex,
            left: styles.left,
            right: styles.right,
            top: styles.top,
            transform: styles.transform,
            width: styles.width,
            height: styles.height,
            overflow: styles.overflow,
            pointerEvents: styles.pointerEvents,
            rect: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
            isVisible: rect.width > 0 && rect.height > 0 && styles.opacity !== '0' && styles.visibility !== 'hidden'
          };
        } else {
          results[key] = { exists: false };
        }
      }
      return results;
    });

    console.log('📊 네비게이션 요소 상태:');
    console.log(JSON.stringify(navElements, null, 2));

    // 2. 모든 CSS 규칙 추출 및 충돌 분석
    console.log('\n🔧 CSS 규칙 충돌 완전 분석...');
    const allCssRules = await page.evaluate(() => {
      const rules = [];
      for (let sheet of document.styleSheets) {
        try {
          for (let rule of sheet.cssRules || sheet.rules) {
            if (rule.selectorText &&
                (rule.selectorText.includes('main-nav') ||
                 rule.selectorText.includes('nav-menu') ||
                 rule.selectorText.includes('header'))) {
              rules.push({
                selector: rule.selectorText,
                display: rule.style.display || null,
                visibility: rule.style.visibility || null,
                opacity: rule.style.opacity || null,
                position: rule.style.position || null,
                left: rule.style.left || null,
                right: rule.style.right || null,
                transform: rule.style.transform || null,
                zIndex: rule.style.zIndex || null,
                important: rule.cssText.includes('!important'),
                cssText: rule.cssText
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
    allCssRules.forEach((rule, index) => {
      console.log(`${index + 1}. ${rule.selector}`);
      if (rule.display) console.log(`   display: ${rule.display}`);
      if (rule.visibility) console.log(`   visibility: ${rule.visibility}`);
      if (rule.opacity) console.log(`   opacity: ${rule.opacity}`);
      if (rule.position) console.log(`   position: ${rule.position}`);
      if (rule.left) console.log(`   left: ${rule.left}`);
      if (rule.transform) console.log(`   transform: ${rule.transform}`);
      console.log(`   !important: ${rule.important}`);
      console.log('');
    });

    // 3. JavaScript에 의한 동적 변경 확인
    console.log('\n🔍 JavaScript 동적 변경 확인...');
    const jsInterference = await page.evaluate(() => {
      const mainNav = document.querySelector('.main-nav');
      if (!mainNav) return { error: 'main-nav 요소 없음' };

      return {
        inlineStyles: mainNav.style.cssText,
        classList: Array.from(mainNav.classList),
        parentElement: mainNav.parentElement ? mainNav.parentElement.tagName : null,
        childElementCount: mainNav.childElementCount,
        innerHTML: mainNav.innerHTML.substring(0, 200) + '...'
      };
    });

    console.log('🎯 JavaScript 영향 분석:');
    console.log(JSON.stringify(jsInterference, null, 2));

    // 4. 뷰포트 및 미디어 쿼리 확인
    console.log('\n📱 뷰포트 및 미디어 쿼리 분석...');
    const viewportInfo = await page.evaluate(() => {
      return {
        innerWidth: window.innerWidth,
        innerHeight: window.innerHeight,
        devicePixelRatio: window.devicePixelRatio,
        userAgent: navigator.userAgent,
        matchMedia768: window.matchMedia('(max-width: 768px)').matches,
        matchMedia769: window.matchMedia('(min-width: 769px)').matches,
        matchMedia1024: window.matchMedia('(max-width: 1024px)').matches
      };
    });

    console.log('📱 뷰포트 정보:');
    console.log(JSON.stringify(viewportInfo, null, 2));

    // 5. 강제로 네비게이션 표시 시도
    console.log('\n🔧 네비게이션 강제 표시 시도...');
    const forceShowResult = await page.evaluate(() => {
      const mainNav = document.querySelector('.main-nav');
      const navMenu = document.querySelector('.nav-menu');

      if (mainNav) {
        mainNav.style.cssText = `
          display: flex !important;
          visibility: visible !important;
          opacity: 1 !important;
          position: relative !important;
          z-index: 9999 !important;
          left: auto !important;
          right: auto !important;
          transform: none !important;
          width: auto !important;
          height: auto !important;
          overflow: visible !important;
          pointer-events: auto !important;
          margin: 0 40px !important;
          padding: 0 !important;
        `;
      }

      if (navMenu) {
        navMenu.style.cssText = `
          display: flex !important;
          visibility: visible !important;
          opacity: 1 !important;
        `;
      }

      return {
        mainNavForced: mainNav ? true : false,
        navMenuForced: navMenu ? true : false
      };
    });

    console.log('🔧 강제 표시 결과:', forceShowResult);

    // 6. 강제 표시 후 다시 스크린샷
    console.log('📸 강제 표시 후 스크린샷...');
    await page.screenshot({
      path: 'ultra-debug-navigation-fixed.png',
      fullPage: false,
      clip: { x: 0, y: 0, width: 1400, height: 500 }
    });

    // 7. 최종 상태 확인
    const finalCheck = await page.evaluate(() => {
      const mainNav = document.querySelector('.main-nav');
      if (!mainNav) return { error: 'main-nav 여전히 없음' };

      const styles = window.getComputedStyle(mainNav);
      const rect = mainNav.getBoundingClientRect();

      return {
        finalDisplay: styles.display,
        finalVisibility: styles.visibility,
        finalOpacity: styles.opacity,
        finalRect: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
        isNowVisible: rect.width > 0 && rect.height > 0 && styles.opacity !== '0' && styles.visibility !== 'hidden'
      };
    });

    console.log('\n✅ 최종 확인 결과:');
    console.log(JSON.stringify(finalCheck, null, 2));

  } catch (error) {
    console.error('❌ ULTRA THINK 오류:', error.message);
  } finally {
    await browser.close();
    console.log('\n🎯 ULTRA THINK 완료. 스크린샷: ultra-debug-navigation.png, ultra-debug-navigation-fixed.png');
  }
})();