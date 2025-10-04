const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  // 모바일 뷰포트 설정 (iPhone 6/7/8 크기)
  await page.setViewportSize({ width: 375, height: 667 });

  console.log('📱 모바일 뷰포트 설정 완료: 375x667');

  // 채팅 페이지 접속
  await page.goto('https://www.topmktx.com/chat', {
    waitUntil: 'networkidle',
    timeout: 30000
  });

  console.log('📄 채팅 페이지 로딩 완료');

  // 페이지 로딩 대기
  await page.waitForTimeout(2000);

  // 헤더 요소들 확인
  const headerInfo = await page.evaluate(() => {
    const commonHeader = document.querySelector('header');
    const chatHeader = document.querySelector('.chat-header, h1, .page-header');

    // 다양한 헤더 후보들 검색
    const possibleHeaders = [
      document.querySelector('h1'),
      document.querySelector('.chat-header'),
      document.querySelector('.page-header'),
      document.querySelector('[class*="header"]'),
      document.querySelector('.chat-container h1'),
      document.querySelector('.container h1')
    ].filter(el => el !== null);

    const firstHeader = possibleHeaders[0];

    const commonHeaderRect = commonHeader ? commonHeader.getBoundingClientRect() : null;
    const chatHeaderRect = firstHeader ? firstHeader.getBoundingClientRect() : null;

    // 채팅 헤더 스타일 정보
    const chatHeaderStyles = firstHeader ? window.getComputedStyle(firstHeader) : null;

    return {
      commonHeader: {
        exists: !!commonHeader,
        height: commonHeaderRect?.height || 0,
        bottom: commonHeaderRect?.bottom || 0
      },
      chatHeader: {
        exists: !!firstHeader,
        top: chatHeaderRect?.top || 0,
        marginTop: chatHeaderStyles?.marginTop || 'none',
        element: firstHeader?.tagName || 'none',
        className: firstHeader?.className || 'none',
        text: firstHeader?.textContent?.trim() || 'none'
      },
      gap: chatHeaderRect && commonHeaderRect ?
           Math.round(chatHeaderRect.top - commonHeaderRect.bottom) : 0,
      totalHeaders: possibleHeaders.length
    };
  });

  console.log('📋 헤더 정보 분석:');
  console.log('공통 헤더:', headerInfo.commonHeader);
  console.log('채팅 헤더:', headerInfo.chatHeader);
  console.log('헤더 간 간격:', headerInfo.gap + 'px');
  console.log('발견된 헤더 수:', headerInfo.totalHeaders);

  // 스크린샷 촬영
  const timestamp = Date.now();
  const screenshotPath = `chat-mobile-header-${timestamp}.png`;

  await page.screenshot({
    path: screenshotPath,
    fullPage: false  // 현재 뷰포트만 캡처
  });

  console.log(`📸 스크린샷 저장: ${screenshotPath}`);

  await browser.close();

  console.log('✅ 모바일 채팅 헤더 검증 완료');

  // 스크린샷 파일 정보
  const fs = require('fs');
  try {
    const stats = fs.statSync(screenshotPath);
    console.log(`📁 파일 크기: ${Math.round(stats.size / 1024)}KB`);
  } catch (error) {
    console.log('❌ 스크린샷 파일 확인 실패');
  }
})();