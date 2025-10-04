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
    console.log('채팅 페이지로 이동...');
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    console.log('페이지 로딩 대기...');
    await page.waitForTimeout(3000);

    // 페이지 분석
    const title = await page.title();
    console.log('페이지 제목:', title);

    // 로그인 필요 여부 확인
    const loginElements = await page.$$('input[type="password"], button:has-text("로그인")');
    if (loginElements.length > 0) {
      console.log('⚠️ 로그인이 필요한 상태입니다.');
    }

    // 채팅 관련 요소들 확인
    const chatElements = await page.$$('.chat-list, .conversation-list, .message-list, .chat-item, .conversation-item');
    console.log('채팅 관련 요소:', chatElements.length + '개');

    // 현재 URL 확인
    const currentUrl = page.url();
    console.log('현재 URL:', currentUrl);

    // 스크린샷 촬영
    console.log('모바일 스크린샷 촬영 중...');
    await page.screenshot({
      path: '/var/www/html/topmkt/mobile-chat-text-truncation-check.png',
      fullPage: true
    });

    console.log('✅ 스크린샷이 성공적으로 저장되었습니다!');
    console.log('파일 위치: /var/www/html/topmkt/mobile-chat-text-truncation-check.png');

    // 파일 크기 확인
    const fs = require('fs');
    const stats = fs.statSync('/var/www/html/topmkt/mobile-chat-text-truncation-check.png');
    console.log('파일 크기:', Math.round(stats.size / 1024) + 'KB');

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
  }
})();