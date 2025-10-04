const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🔍 room-name / room-last-message 텍스트 표시 문제 진단 ===\n');

    // 모바일 뷰포트 설정
    await page.setViewportSize({ width: 390, height: 844 });

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

    // 텍스트 표시 상태 상세 분석
    const textAnalysis = await page.evaluate(() => {
      const chatRoomItems = document.querySelectorAll('.chat-room-item');

      const results = Array.from(chatRoomItems).map((item, index) => {
        const roomName = item.querySelector('.room-name');
        const roomTime = item.querySelector('.room-time');
        const roomLastMessage = item.querySelector('.room-last-message');

        if (!roomName || !roomTime || !roomLastMessage) {
          return { index: index + 1, error: '필수 요소 누락' };
        }

        // 요소의 실제 크기와 스타일 정보 수집
        const nameRect = roomName.getBoundingClientRect();
        const messageRect = roomLastMessage.getBoundingClientRect();
        const nameStyles = window.getComputedStyle(roomName);
        const messageStyles = window.getComputedStyle(roomLastMessage);
        const itemRect = item.getBoundingClientRect();

        return {
          index: index + 1,
          roomName: {
            text: roomName.textContent.trim(),
            textLength: roomName.textContent.trim().length,
            rect: {
              width: Math.round(nameRect.width),
              height: Math.round(nameRect.height),
              left: Math.round(nameRect.left),
              right: Math.round(nameRect.right)
            },
            styles: {
              maxWidth: nameStyles.maxWidth,
              width: nameStyles.width,
              display: nameStyles.display,
              visibility: nameStyles.visibility,
              opacity: nameStyles.opacity,
              color: nameStyles.color,
              fontSize: nameStyles.fontSize,
              overflow: nameStyles.overflow,
              textOverflow: nameStyles.textOverflow,
              whiteSpace: nameStyles.whiteSpace
            },
            visible: nameRect.width > 0 && nameRect.height > 0
          },
          roomLastMessage: {
            text: roomLastMessage.textContent.trim(),
            textLength: roomLastMessage.textContent.trim().length,
            rect: {
              width: Math.round(messageRect.width),
              height: Math.round(messageRect.height),
              left: Math.round(messageRect.left),
              right: Math.round(messageRect.right)
            },
            styles: {
              maxWidth: messageStyles.maxWidth,
              width: messageStyles.width,
              display: messageStyles.display,
              visibility: messageStyles.visibility,
              opacity: messageStyles.opacity,
              color: messageStyles.color,
              fontSize: messageStyles.fontSize,
              overflow: messageStyles.overflow,
              textOverflow: messageStyles.textOverflow,
              whiteSpace: messageStyles.whiteSpace
            },
            visible: messageRect.width > 0 && messageRect.height > 0
          },
          roomTime: {
            text: roomTime.textContent.trim(),
            rect: {
              width: Math.round(roomTime.getBoundingClientRect().width),
              left: Math.round(roomTime.getBoundingClientRect().left),
              right: Math.round(roomTime.getBoundingClientRect().right)
            }
          },
          containerWidth: Math.round(itemRect.width)
        };
      });

      return {
        totalItems: chatRoomItems.length,
        results: results
      };
    });

    console.log(`📊 분석 결과 (${textAnalysis.totalItems}개 채팅방):\n`);

    textAnalysis.results.forEach(result => {
      if (result.error) {
        console.log(`❌ 채팅방 ${result.index}: ${result.error}`);
        return;
      }

      console.log(`🔹 채팅방 ${result.index} (컨테이너 너비: ${result.containerWidth}px):`);

      // room-name 분석
      console.log(`   📝 room-name: "${result.roomName.text}" (${result.roomName.textLength}자)`);
      console.log(`      크기: ${result.roomName.rect.width}×${result.roomName.rect.height}px`);
      console.log(`      위치: ${result.roomName.rect.left}px ~ ${result.roomName.rect.right}px`);
      console.log(`      max-width: ${result.roomName.styles.maxWidth}`);
      console.log(`      display: ${result.roomName.styles.display}, visibility: ${result.roomName.styles.visibility}`);
      console.log(`      color: ${result.roomName.styles.color}, opacity: ${result.roomName.styles.opacity}`);
      console.log(`      표시 여부: ${result.roomName.visible ? '✅ 보임' : '❌ 안 보임'}`);

      // room-last-message 분석
      console.log(`   💬 room-last-message: "${result.roomLastMessage.text}" (${result.roomLastMessage.textLength}자)`);
      console.log(`      크기: ${result.roomLastMessage.rect.width}×${result.roomLastMessage.rect.height}px`);
      console.log(`      위치: ${result.roomLastMessage.rect.left}px ~ ${result.roomLastMessage.rect.right}px`);
      console.log(`      max-width: ${result.roomLastMessage.styles.maxWidth}`);
      console.log(`      display: ${result.roomLastMessage.styles.display}, visibility: ${result.roomLastMessage.styles.visibility}`);
      console.log(`      color: ${result.roomLastMessage.styles.color}, opacity: ${result.roomLastMessage.styles.opacity}`);
      console.log(`      표시 여부: ${result.roomLastMessage.visible ? '✅ 보임' : '❌ 안 보임'}`);

      // room-time 위치 (참조)
      console.log(`   ⏰ room-time: "${result.roomTime.text}"`);
      console.log(`      위치: ${result.roomTime.rect.left}px ~ ${result.roomTime.rect.right}px (너비: ${result.roomTime.rect.width}px)`);

      console.log('');
    });

    // 스크린샷 촬영
    await page.screenshot({
      path: 'text-visibility-debug.png',
      fullPage: false
    });
    console.log('📸 디버그 스크린샷: text-visibility-debug.png');

  } catch (error) {
    console.error('❌ 텍스트 표시 진단 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 텍스트 표시 진단 완료');
  }
})();