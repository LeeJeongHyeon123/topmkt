const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🚀 최종 모바일 텍스트 표시 테스트 ===\n');

    // 캐시 완전 우회
    await page.route('**/*', (route) => {
      route.continue({
        headers: {
          ...route.request().headers(),
          'Cache-Control': 'no-cache, no-store, must-revalidate',
          'Pragma': 'no-cache',
          'Expires': '0'
        }
      });
    });

    // 모바일 뷰포트 (iPhone SE)
    await page.setViewportSize({ width: 375, height: 667 });

    // 타임스탬프로 캐시 우회
    const timestamp = Date.now();
    await page.goto(`https://www.topmktx.com/dev/login_helper.php?user_id=4&t=${timestamp}`);
    await page.waitForTimeout(2000);

    await page.goto(`https://www.topmktx.com/chat?t=${timestamp}`);
    await page.waitForTimeout(5000);

    // CSS 하드 리프레시
    await page.evaluate(() => {
      // 모든 스타일시트 재로드 강제
      const links = document.querySelectorAll('link[rel="stylesheet"]');
      links.forEach(link => {
        const href = link.href;
        link.href = href + (href.includes('?') ? '&' : '?') + 't=' + Date.now();
      });
    });

    await page.waitForTimeout(2000);

    // 최종 텍스트 분석
    const finalResults = await page.evaluate(() => {
      const items = document.querySelectorAll('.chat-room-item');

      return Array.from(items).map((item, index) => {
        const roomName = item.querySelector('.room-name');
        const roomLastMessage = item.querySelector('.room-last-message');

        if (!roomName || !roomLastMessage) return null;

        const nameStyles = window.getComputedStyle(roomName);
        const messageStyles = window.getComputedStyle(roomLastMessage);

        return {
          index: index + 1,
          roomName: {
            text: roomName.textContent.trim(),
            maxWidth: nameStyles.maxWidth,
            actualWidth: Math.round(roomName.getBoundingClientRect().width)
          },
          roomLastMessage: {
            text: roomLastMessage.textContent.trim(),
            maxWidth: messageStyles.maxWidth,
            actualWidth: Math.round(roomLastMessage.getBoundingClientRect().width)
          },
          containerWidth: Math.round(item.getBoundingClientRect().width)
        };
      }).filter(Boolean);
    });

    console.log('📊 최종 결과:');
    finalResults.forEach(result => {
      console.log(`🔹 채팅방 ${result.index}:`);
      console.log(`   📝 "${result.roomName.text}" (${result.roomName.maxWidth} → ${result.roomName.actualWidth}px)`);
      console.log(`   💬 "${result.roomLastMessage.text}" (${result.roomLastMessage.maxWidth} → ${result.roomLastMessage.actualWidth}px)`);
      console.log('');
    });

    // 최종 스크린샷
    await page.screenshot({
      path: 'final-mobile-test.png',
      fullPage: false
    });
    console.log('📸 최종 모바일 테스트 스크린샷: final-mobile-test.png');

  } catch (error) {
    console.error('❌ 최종 테스트 오류:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 최종 모바일 테스트 완료');
  }
})();