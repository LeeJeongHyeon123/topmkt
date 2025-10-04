const { chromium } = require('playwright');

(async () => {
  console.log('플레이라이트 브라우저 시작...');
  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  console.log('새 페이지 생성 및 모바일 뷰포트 설정...');
  const page = await browser.newPage({
    viewport: { width: 375, height: 667 }  // iPhone SE 사이즈
  });

  try {
    console.log('로그인 페이지로 이동...');
    await page.goto('https://www.topmktx.com/auth/login', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    console.log('페이지 로딩 대기...');
    await page.waitForTimeout(2000);

    // 테스트 계정으로 로그인 시도 (일반적인 테스트 계정)
    console.log('테스트 계정으로 로그인 시도...');

    // 휴대폰 번호 입력
    const phoneInput = await page.$('input[placeholder*="휴대폰"], input[name="phone"], input[type="tel"]');
    if (phoneInput) {
      await phoneInput.fill('010-1234-5678');  // 테스트 번호
    }

    // 비밀번호 입력
    const passwordInput = await page.$('input[type="password"]');
    if (passwordInput) {
      await passwordInput.fill('test123');  // 테스트 비밀번호
    }

    // 로그인 버튼 클릭
    const loginButton = await page.$('button[type="submit"], button:has-text("로그인")');
    if (loginButton) {
      console.log('로그인 버튼 클릭...');
      await loginButton.click();
      await page.waitForTimeout(3000);
    }

    // 로그인 실패시에도 채팅 페이지로 직접 이동 시도
    console.log('채팅 페이지로 이동 시도...');
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(3000);

    // 현재 상태 확인
    const currentUrl = page.url();
    const title = await page.title();
    console.log('현재 URL:', currentUrl);
    console.log('페이지 제목:', title);

    // 채팅 관련 요소들 확인 (다양한 선택자 시도)
    const chatSelectors = [
      '.chat-container',
      '.chat-list',
      '.conversation-list',
      '.message-list',
      '.chat-room',
      '.chat-item',
      '.message-item',
      '[class*="chat"]',
      '[class*="conversation"]',
      '[class*="message"]'
    ];

    let foundChatElements = false;
    for (const selector of chatSelectors) {
      const elements = await page.$$(selector);
      if (elements.length > 0) {
        console.log(`찾은 채팅 요소 (${selector}):`, elements.length + '개');
        foundChatElements = true;
      }
    }

    if (!foundChatElements) {
      console.log('채팅 관련 요소를 찾을 수 없습니다.');
    }

    // 텍스트 요소들 확인
    const textElements = await page.$$('p, span, div, h1, h2, h3, a');
    console.log('전체 텍스트 요소:', textElements.length + '개');

    // 스크린샷 촬영
    console.log('모바일 스크린샷 촬영 중...');
    await page.screenshot({
      path: '/var/www/html/topmkt/mobile-chat-with-login-attempt.png',
      fullPage: true
    });

    console.log('✅ 스크린샷이 성공적으로 저장되었습니다!');
    console.log('파일 위치: /var/www/html/topmkt/mobile-chat-with-login-attempt.png');

    // 파일 크기 확인
    const fs = require('fs');
    const stats = fs.statSync('/var/www/html/topmkt/mobile-chat-with-login-attempt.png');
    console.log('파일 크기:', Math.round(stats.size / 1024) + 'KB');

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
  }
})();