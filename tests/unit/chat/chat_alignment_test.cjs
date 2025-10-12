const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🔍 채팅 UI 정렬 및 겹침 문제 분석 ===\n');

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

    // 정렬 및 겹침 문제 분석
    const alignmentAnalysis = await page.evaluate(() => {
      const chatRoomItems = document.querySelectorAll('.chat-room-item');

      const results = Array.from(chatRoomItems).map((item, index) => {
        const roomName = item.querySelector('.room-name');
        const roomTime = item.querySelector('.room-time');
        const roomLastMessage = item.querySelector('.room-last-message');
        const roomHeader = item.querySelector('.room-header');
        const roomDetails = item.querySelector('.room-details');

        if (!roomName || !roomTime || !roomLastMessage || !roomHeader) {
          return { index, error: '필수 요소 누락' };
        }

        // 위치 정보 수집
        const nameRect = roomName.getBoundingClientRect();
        const timeRect = roomTime.getBoundingClientRect();
        const messageRect = roomLastMessage.getBoundingClientRect();
        const headerRect = roomHeader.getBoundingClientRect();
        const itemRect = item.getBoundingClientRect();

        // 정렬 분석
        const nameVerticalCenter = nameRect.top + nameRect.height / 2;
        const timeVerticalCenter = timeRect.top + timeRect.height / 2;
        const verticalAlignment = Math.abs(nameVerticalCenter - timeVerticalCenter);

        // 겹침 분석
        const timeLeft = timeRect.left;
        const messageRight = messageRect.right;
        const isOverlapping = timeLeft < messageRight;
        const overlapAmount = isOverlapping ? messageRight - timeLeft : 0;

        // 텍스트 길이 확인
        const messageText = roomLastMessage.textContent.trim();
        const messageLength = messageText.length;

        return {
          index: index + 1,
          roomName: roomName.textContent.trim(),
          timeText: roomTime.textContent.trim(),
          messageText: messageText,
          messageLength: messageLength,
          alignment: {
            nameCenter: Math.round(nameVerticalCenter),
            timeCenter: Math.round(timeVerticalCenter),
            verticalDiff: Math.round(verticalAlignment),
            isAligned: verticalAlignment <= 2 // 2px 오차 허용
          },
          overlap: {
            timeLeft: Math.round(timeLeft),
            messageRight: Math.round(messageRight),
            isOverlapping: isOverlapping,
            overlapAmount: Math.round(overlapAmount)
          },
          positions: {
            itemHeight: Math.round(itemRect.height),
            headerHeight: Math.round(headerRect.height),
            nameTop: Math.round(nameRect.top - itemRect.top),
            timeTop: Math.round(timeRect.top - itemRect.top),
            messageTop: Math.round(messageRect.top - itemRect.top)
          }
        };
      });

      return {
        totalItems: chatRoomItems.length,
        results: results
      };
    });

    console.log(`📊 분석 결과 (${alignmentAnalysis.totalItems}개 채팅방):`);

    let alignmentIssues = 0;
    let overlapIssues = 0;

    alignmentAnalysis.results.forEach(result => {
      if (result.error) {
        console.log(`❌ 채팅방 ${result.index}: ${result.error}`);
        return;
      }

      console.log(`\n🔹 채팅방 ${result.index}: "${result.roomName}"`);
      console.log(`   시간: "${result.timeText}"`);
      console.log(`   메시지: "${result.messageText}" (${result.messageLength}자)`);

      // 정렬 분석
      if (result.alignment.isAligned) {
        console.log(`   ✅ 수직 정렬: 정상 (차이: ${result.alignment.verticalDiff}px)`);
      } else {
        console.log(`   ❌ 수직 정렬: 불일치 (차이: ${result.alignment.verticalDiff}px)`);
        console.log(`      - room-name 중앙: ${result.alignment.nameCenter}px`);
        console.log(`      - room-time 중앙: ${result.alignment.timeCenter}px`);
        alignmentIssues++;
      }

      // 겹침 분석
      if (result.overlap.isOverlapping) {
        console.log(`   ❌ 겹침 발생: ${result.overlap.overlapAmount}px`);
        console.log(`      - 메시지 우측 끝: ${result.overlap.messageRight}px`);
        console.log(`      - 시간 좌측 시작: ${result.overlap.timeLeft}px`);
        overlapIssues++;
      } else {
        console.log(`   ✅ 겹침 없음 (여백: ${result.overlap.timeLeft - result.overlap.messageRight}px)`);
      }

      // 위치 상세 정보
      console.log(`   📏 위치 정보:`);
      console.log(`      - 아이템 높이: ${result.positions.itemHeight}px`);
      console.log(`      - 헤더 높이: ${result.positions.headerHeight}px`);
      console.log(`      - room-name 위치: ${result.positions.nameTop}px`);
      console.log(`      - room-time 위치: ${result.positions.timeTop}px`);
      console.log(`      - room-message 위치: ${result.positions.messageTop}px`);
    });

    // 종합 평가
    console.log('\n=== 🏆 종합 문제 분석 ===');
    console.log(`📊 정렬 문제: ${alignmentIssues}/${alignmentAnalysis.totalItems}개 채팅방`);
    console.log(`📊 겹침 문제: ${overlapIssues}/${alignmentAnalysis.totalItems}개 채팅방`);

    const totalIssues = alignmentIssues + overlapIssues;
    if (totalIssues === 0) {
      console.log('🎉 모든 문제가 해결되었습니다!');
    } else if (totalIssues <= 2) {
      console.log('⚠️ 소수의 문제가 발견되었습니다. 미세 조정이 필요합니다.');
    } else {
      console.log('❌ 여러 문제가 발견되었습니다. 근본적인 수정이 필요합니다.');
    }

    // 스크린샷 촬영
    await page.screenshot({
      path: 'chat-alignment-analysis.png',
      fullPage: false
    });
    console.log('\n📸 분석 스크린샷: chat-alignment-analysis.png');

  } catch (error) {
    console.error('❌ 분석 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 채팅 UI 정렬 분석 완료');
  }
})();