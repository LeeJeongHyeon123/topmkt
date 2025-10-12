const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({
    headless: false, // 디버깅용으로 브라우저 표시
    slowMo: 1000
  });

  const page = await browser.newPage();

  try {
    // 모바일 뷰포트 설정 (iPhone 13 크기)
    await page.setViewportSize({ width: 390, height: 844 });
    console.log('📱 모바일 뷰포트 설정 완료: 390x844');

    // 먼저 홈페이지로 이동
    await page.goto('https://www.topmktx.com/', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    console.log('🏠 홈페이지 로딩 완료');

    // 페이지에서 채팅 관련 요소를 직접 확인
    const pageInfo = await page.evaluate(() => {
      return {
        title: document.title,
        url: window.location.href,
        hasAuthForms: !!document.querySelector('form[action*="auth"]'),
        hasLoginForm: !!document.querySelector('input[type="password"]'),
        bodyClasses: document.body.className,
        chatLinks: Array.from(document.querySelectorAll('a[href*="chat"]')).map(a => ({
          href: a.href,
          text: a.textContent.trim()
        }))
      };
    });

    console.log('📄 페이지 정보:', pageInfo);

    // 채팅 링크가 있다면 클릭해보기
    if (pageInfo.chatLinks.length > 0) {
      console.log('🔗 채팅 링크 발견, 클릭 시도...');
      await page.click('a[href*="chat"]');
      await page.waitForTimeout(3000);

      const chatPageInfo = await page.evaluate(() => {
        return {
          url: window.location.href,
          title: document.title,
          hasToggleBtn: !!document.querySelector('.mobile-chat-toggle'),
          hasChatSidebar: !!document.querySelector('.chat-sidebar'),
          hasAuthRequired: document.body.textContent.includes('로그인') || document.body.textContent.includes('안전한'),
          bodyText: document.body.textContent.substring(0, 200)
        };
      });

      console.log('💬 채팅 페이지 정보:', chatPageInfo);
    }

    // 개발자 도구로 확인할 수 있도록 잠시 대기
    console.log('🔍 브라우저에서 직접 확인하세요. 10초 후 자동 종료됩니다...');
    await page.waitForTimeout(10000);

  } catch (error) {
    console.error('❌ 테스트 중 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('✅ 테스트 완료');
  }
})();