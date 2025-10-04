const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🚫 캐시 무시하고 모바일 텍스트 상태 재확인 ===\n');

    // 캐시 비활성화
    await page.route('**/*', (route) => {
      const headers = route.request().headers();
      headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
      headers['Pragma'] = 'no-cache';
      headers['Expires'] = '0';
      route.continue({ headers });
    });

    // 모바일 뷰포트 설정
    await page.setViewportSize({ width: 375, height: 667 });

    // DevLoginHelper로 로그인 (캐시 무시)
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4&t=' + Date.now(), {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(2000);

    // 채팅 페이지로 이동 (캐시 무시)
    await page.goto('https://www.topmktx.com/chat?t=' + Date.now(), {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(5000); // 더 길게 대기

    // CSS 실제 적용 상태 확인
    const cssDebug = await page.evaluate(() => {
      // 첫 번째 채팅방 아이템의 room-last-message 요소 찾기
      const messageElement = document.querySelector('.chat-room-item .room-last-message');
      if (!messageElement) return { error: 'room-last-message 요소를 찾을 수 없음' };

      const styles = window.getComputedStyle(messageElement);
      const parentWidth = messageElement.parentElement.getBoundingClientRect().width;

      // CSS 규칙들이 실제로 적용되었는지 확인
      const allStyleSheets = Array.from(document.styleSheets);
      let foundRules = [];

      try {
        allStyleSheets.forEach((sheet, sheetIndex) => {
          if (sheet.cssRules) {
            Array.from(sheet.cssRules).forEach((rule, ruleIndex) => {
              if (rule.selectorText && rule.selectorText.includes('room-last-message')) {
                foundRules.push({
                  sheet: sheetIndex,
                  rule: ruleIndex,
                  selector: rule.selectorText,
                  maxWidth: rule.style.maxWidth || 'none'
                });
              }
            });
          }
        });
      } catch (e) {
        foundRules.push({ error: 'CSS 규칙 접근 오류: ' + e.message });
      }

      return {
        element: {
          text: messageElement.textContent.trim(),
          actualWidth: messageElement.getBoundingClientRect().width,
          parentWidth: parentWidth,
          computedMaxWidth: styles.maxWidth,
          computedMaxWidthValue: styles.getPropertyValue('max-width')
        },
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight
        },
        mediaQueries: {
          matches768: window.matchMedia('(max-width: 768px)').matches,
          matches480: window.matchMedia('(max-width: 480px)').matches
        },
        cssRules: foundRules
      };
    });

    console.log('🔍 CSS 적용 상태 분석:');
    console.log(JSON.stringify(cssDebug, null, 2));

    // 강제로 CSS 다시 적용 시도
    await page.addStyleTag({
      content: `
        @media (max-width: 480px) {
          .room-last-message {
            max-width: 75% !important;
            border: 2px solid red !important; /* 디버그용 */
          }
          .room-name {
            max-width: 85% !important;
            border: 2px solid blue !important; /* 디버그용 */
          }
        }
      `
    });

    await page.waitForTimeout(2000);

    // 강제 적용 후 스크린샷
    await page.screenshot({
      path: 'mobile-forced-css.png',
      fullPage: false
    });
    console.log('📸 강제 CSS 적용 후 스크린샷: mobile-forced-css.png');

  } catch (error) {
    console.error('❌ 캐시 우회 테스트 중 오류:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 캐시 우회 테스트 완료');
  }
})();