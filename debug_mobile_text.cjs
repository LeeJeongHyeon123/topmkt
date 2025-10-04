const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 📱 모바일 뷰포트에서 텍스트 표시 상태 확인 ===\n');

    // 모바일 뷰포트 설정 (iPhone SE)
    await page.setViewportSize({ width: 375, height: 667 });

    // DevLoginHelper로 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(2000);

    // 채팅 페이지로 이동
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(3000);

    // 현재 적용된 CSS 미디어 쿼리 확인
    const mediaQueryInfo = await page.evaluate(() => {
      const width = window.innerWidth;
      const height = window.innerHeight;

      // 미디어 쿼리 매칭 확인
      const mq768 = window.matchMedia('(max-width: 768px)').matches;
      const mq480 = window.matchMedia('(max-width: 480px)').matches;

      return {
        viewportWidth: width,
        viewportHeight: height,
        matchesTablet: mq768,
        matchesMobile: mq480
      };
    });

    console.log(`📱 뷰포트 정보:`);
    console.log(`   크기: ${mediaQueryInfo.viewportWidth}×${mediaQueryInfo.viewportHeight}px`);
    console.log(`   태블릿 미디어쿼리 (768px 이하): ${mediaQueryInfo.matchesTablet ? '✅ 매칭됨' : '❌ 매칭안됨'}`);
    console.log(`   모바일 미디어쿼리 (480px 이하): ${mediaQueryInfo.matchesMobile ? '✅ 매칭됨' : '❌ 매칭안됨'}`);
    console.log('');

    // 텍스트 표시 상태 분석 (모바일 환경)
    const textAnalysis = await page.evaluate(() => {
      const chatRoomItems = document.querySelectorAll('.chat-room-item');

      const results = Array.from(chatRoomItems).map((item, index) => {
        const roomName = item.querySelector('.room-name');
        const roomLastMessage = item.querySelector('.room-last-message');

        if (!roomName || !roomLastMessage) {
          return { index: index + 1, error: '필수 요소 누락' };
        }

        // 현재 적용된 CSS 스타일 가져오기
        const nameStyles = window.getComputedStyle(roomName);
        const messageStyles = window.getComputedStyle(roomLastMessage);
        const nameRect = roomName.getBoundingClientRect();
        const messageRect = roomLastMessage.getBoundingClientRect();

        return {
          index: index + 1,
          roomName: {
            text: roomName.textContent.trim(),
            textLength: roomName.textContent.trim().length,
            actualWidth: Math.round(nameRect.width),
            maxWidth: nameStyles.maxWidth,
            computedMaxWidth: nameStyles.getPropertyValue('max-width')
          },
          roomLastMessage: {
            text: roomLastMessage.textContent.trim(),
            textLength: roomLastMessage.textContent.trim().length,
            actualWidth: Math.round(messageRect.width),
            maxWidth: messageStyles.maxWidth,
            computedMaxWidth: messageStyles.getPropertyValue('max-width')
          },
          containerWidth: Math.round(item.getBoundingClientRect().width)
        };
      });

      return results;
    });

    console.log(`📊 모바일 텍스트 분석 결과 (${textAnalysis.length}개 채팅방):\n`);

    textAnalysis.forEach(result => {
      if (result.error) {
        console.log(`❌ 채팅방 ${result.index}: ${result.error}`);
        return;
      }

      console.log(`🔹 채팅방 ${result.index} (컨테이너: ${result.containerWidth}px):`);
      console.log(`   📝 room-name: "${result.roomName.text}" (${result.roomName.textLength}자)`);
      console.log(`      실제 너비: ${result.roomName.actualWidth}px`);
      console.log(`      max-width CSS: ${result.roomName.computedMaxWidth}`);

      console.log(`   💬 room-last-message: "${result.roomLastMessage.text}" (${result.roomLastMessage.textLength}자)`);
      console.log(`      실제 너비: ${result.roomLastMessage.actualWidth}px`);
      console.log(`      max-width CSS: ${result.roomLastMessage.computedMaxWidth}`);
      console.log('');
    });

    // 모바일 스크린샷 촬영
    await page.screenshot({
      path: 'mobile-text-debug.png',
      fullPage: false
    });
    console.log('📸 모바일 디버그 스크린샷: mobile-text-debug.png');

  } catch (error) {
    console.error('❌ 모바일 텍스트 진단 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 모바일 텍스트 진단 완료');
  }
})();