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
    console.log('테스트 채팅 UI 페이지로 이동...');
    await page.goto('file:///var/www/html/topmkt/test_mobile_chat_ui.html', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    console.log('페이지 로딩 대기...');
    await page.waitForTimeout(2000);

    // 채팅방 목록의 텍스트 요소들 분석
    const roomItems = await page.$$('.room-item');
    console.log('채팅방 아이템 개수:', roomItems.length);

    for (let i = 0; i < roomItems.length; i++) {
      const roomName = await roomItems[i].$eval('.room-name', el => ({
        text: el.textContent.trim(),
        scrollWidth: el.scrollWidth,
        offsetWidth: el.offsetWidth,
        isTruncated: el.scrollWidth > el.offsetWidth
      }));

      const roomMessage = await roomItems[i].$eval('.room-last-message', el => ({
        text: el.textContent.trim(),
        scrollWidth: el.scrollWidth,
        offsetWidth: el.offsetWidth,
        isTruncated: el.scrollWidth > el.offsetWidth
      }));

      console.log(`\n채팅방 ${i + 1}:`);
      console.log(`- 방 이름: "${roomName.text.substring(0, 30)}${roomName.text.length > 30 ? '...' : ''}"`);
      console.log(`- 방 이름 잘림: ${roomName.isTruncated ? '⚠️ YES' : '✅ NO'} (${roomName.offsetWidth}px vs ${roomName.scrollWidth}px)`);
      console.log(`- 마지막 메시지: "${roomMessage.text.substring(0, 40)}${roomMessage.text.length > 40 ? '...' : ''}"`);
      console.log(`- 메시지 잘림: ${roomMessage.isTruncated ? '⚠️ YES' : '✅ NO'} (${roomMessage.offsetWidth}px vs ${roomMessage.scrollWidth}px)`);
    }

    // 스크린샷 촬영
    console.log('\n📸 모바일 스크린샷 촬영 중...');
    await page.screenshot({
      path: '/var/www/html/topmkt/mobile-chat-text-truncation-check.png',
      fullPage: true
    });

    console.log('✅ 스크린샷이 성공적으로 저장되었습니다!');
    console.log('파일 위치: /var/www/html/topmkt/mobile-chat-text-truncation-check.png');

    // 뷰포트 크기별 추가 테스트
    console.log('\n📱 다른 모바일 사이즈도 테스트...');

    // iPhone 5/SE (320px)
    await page.setViewportSize({ width: 320, height: 568 });
    await page.waitForTimeout(1000);
    await page.screenshot({
      path: '/var/www/html/topmkt/mobile-chat-320px-test.png',
      fullPage: true
    });
    console.log('320px 뷰포트 스크린샷 저장됨');

    // 파일 크기 확인
    const fs = require('fs');
    const stats = fs.statSync('/var/www/html/topmkt/mobile-chat-text-truncation-check.png');
    console.log('주요 파일 크기:', Math.round(stats.size / 1024) + 'KB');

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
  }
})();