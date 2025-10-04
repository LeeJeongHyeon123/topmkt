const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  try {
    console.log('=== 🧠 Ultra Think 채팅 사이드바 여백 & 시간 정렬 테스트 ===\n');

    // 데스크톱과 모바일 테스트를 병렬로 진행
    const [desktopResult, mobileResult] = await Promise.all([
      testDesktopSpacing(browser),
      testMobileSpacing(browser)
    ]);

    // 종합 평가
    console.log('\n=== 📊 종합 평가 ===');

    let totalScore = 0;
    let maxScore = 0;

    // 데스크톱 점수 (50점)
    maxScore += 50;
    totalScore += desktopResult.score;
    console.log(`🖥️ 데스크톱: ${desktopResult.score}/50점`);

    // 모바일 점수 (50점)
    maxScore += 50;
    totalScore += mobileResult.score;
    console.log(`📱 모바일: ${mobileResult.score}/50점`);

    const finalScore = Math.round((totalScore / maxScore) * 100);
    console.log(`\n🎯 최종 점수: ${totalScore}/${maxScore} (${finalScore}%)`);

    if (finalScore >= 90) {
      console.log('🎉 우수! 채팅 사이드바 여백과 시간 정렬이 완벽하게 개선되었습니다.');
    } else if (finalScore >= 70) {
      console.log('✅ 양호! 주요 개선이 완료되었지만 일부 미세조정이 필요합니다.');
    } else {
      console.log('⚠️ 개선 필요! 추가적인 수정이 필요합니다.');
    }

  } catch (error) {
    console.error('❌ 테스트 중 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ 채팅 사이드바 여백 & 시간 정렬 테스트 완료');
  }
})();

// 데스크톱 테스트 함수
async function testDesktopSpacing(browser) {
  const page = await browser.newPage();

  try {
    console.log('🖥️ 데스크톱 테스트 시작...');

    // 데스크톱 뷰포트 설정
    await page.setViewportSize({ width: 1920, height: 1080 });

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

    // 데스크톱 여백 및 정렬 측정
    const desktopMeasurements = await page.evaluate(() => {
      const chatLayout = document.querySelector('.chat-layout');
      const chatSidebar = document.querySelector('.chat-sidebar');
      const chatHeader = document.querySelector('.chat-header');
      const roomTimes = document.querySelectorAll('.room-time');
      const roomMetas = document.querySelectorAll('.room-meta');

      const layoutStyles = chatLayout ? window.getComputedStyle(chatLayout) : null;
      const sidebarRect = chatSidebar ? chatSidebar.getBoundingClientRect() : null;
      const headerRect = chatHeader ? chatHeader.getBoundingClientRect() : null;

      // 시간 정보 정렬 확인
      const timeAlignments = Array.from(roomTimes).map(time => {
        const styles = window.getComputedStyle(time);
        const rect = time.getBoundingClientRect();
        return {
          textAlign: styles.textAlign,
          whiteSpace: styles.whiteSpace,
          right: rect.right
        };
      });

      return {
        layout: {
          padding: layoutStyles?.padding || 'none',
          paddingLeft: layoutStyles?.paddingLeft || 'none',
          paddingRight: layoutStyles?.paddingRight || 'none'
        },
        sidebar: {
          left: sidebarRect?.left || 0,
          right: sidebarRect?.right || 0
        },
        header: {
          left: headerRect?.left || 0,
          right: headerRect?.right || 0
        },
        timeAlignments,
        roomCount: roomTimes.length
      };
    });

    console.log('   📏 데스크톱 측정 결과:');
    console.log('   - chat-layout padding:', desktopMeasurements.layout.padding);
    console.log('   - 사이드바 left:', Math.round(desktopMeasurements.sidebar.left));
    console.log('   - 헤더 left:', Math.round(desktopMeasurements.header.left));
    console.log('   - 채팅방 수:', desktopMeasurements.roomCount);

    // 스크린샷
    await page.screenshot({
      path: 'desktop-chat-spacing.png',
      fullPage: false
    });
    console.log('   📸 데스크톱 스크린샷: desktop-chat-spacing.png');

    // 점수 계산 (50점 만점)
    let score = 0;

    // 여백 일관성 (25점)
    const leftDiff = Math.abs(desktopMeasurements.sidebar.left - desktopMeasurements.header.left);
    if (leftDiff <= 5) {
      score += 25;
      console.log('   ✅ 여백 일관성 완벽 (25/25점)');
    } else if (leftDiff <= 15) {
      score += 20;
      console.log('   ✅ 여백 일관성 양호 (20/25점)');
    } else {
      console.log(`   ❌ 여백 일관성 부족 (차이: ${leftDiff}px, 0/25점)`);
    }

    // 시간 정렬 (25점)
    if (desktopMeasurements.timeAlignments.length > 0) {
      const rightAligned = desktopMeasurements.timeAlignments.every(t =>
        t.textAlign === 'right' || t.textAlign === 'end'
      );
      const noWrap = desktopMeasurements.timeAlignments.every(t =>
        t.whiteSpace === 'nowrap'
      );

      if (rightAligned && noWrap) {
        score += 25;
        console.log('   ✅ 시간 정렬 완벽 (25/25점)');
      } else if (rightAligned) {
        score += 20;
        console.log('   ✅ 시간 정렬 양호 (20/25점)');
      } else {
        console.log('   ❌ 시간 정렬 부족 (0/25점)');
      }
    } else {
      console.log('   ⚠️ 시간 정보 없음 (15/25점)');
      score += 15;
    }

    return { score, measurements: desktopMeasurements };

  } catch (error) {
    console.error('   ❌ 데스크톱 테스트 오류:', error.message);
    return { score: 0, error: error.message };
  } finally {
    await page.close();
  }
}

// 모바일 테스트 함수
async function testMobileSpacing(browser) {
  const page = await browser.newPage();

  try {
    console.log('\n📱 모바일 테스트 시작...');

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

    // 모바일 여백 및 정렬 측정
    const mobileMeasurements = await page.evaluate(() => {
      const chatLayout = document.querySelector('.chat-layout');
      const chatSidebar = document.querySelector('.chat-sidebar');
      const chatHeader = document.querySelector('.chat-header');
      const roomTimes = document.querySelectorAll('.room-time');

      const layoutStyles = chatLayout ? window.getComputedStyle(chatLayout) : null;
      const sidebarRect = chatSidebar ? chatSidebar.getBoundingClientRect() : null;
      const headerRect = chatHeader ? chatHeader.getBoundingClientRect() : null;

      // 시간 정보 정렬 확인
      const timeAlignments = Array.from(roomTimes).map(time => {
        const styles = window.getComputedStyle(time);
        return {
          textAlign: styles.textAlign,
          whiteSpace: styles.whiteSpace
        };
      });

      return {
        layout: {
          padding: layoutStyles?.padding || 'none',
          paddingLeft: layoutStyles?.paddingLeft || 'none',
          paddingRight: layoutStyles?.paddingRight || 'none'
        },
        sidebar: {
          left: sidebarRect?.left || 0,
          right: sidebarRect?.right || 0,
          width: sidebarRect?.width || 0
        },
        header: {
          left: headerRect?.left || 0,
          right: headerRect?.right || 0
        },
        timeAlignments,
        roomCount: roomTimes.length,
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight
        }
      };
    });

    console.log('   📏 모바일 측정 결과:');
    console.log('   - chat-layout padding:', mobileMeasurements.layout.padding);
    console.log('   - 사이드바 left:', Math.round(mobileMeasurements.sidebar.left));
    console.log('   - 헤더 left:', Math.round(mobileMeasurements.header.left));
    console.log('   - 뷰포트:', mobileMeasurements.viewport.width + 'x' + mobileMeasurements.viewport.height);

    // 스크린샷
    await page.screenshot({
      path: 'mobile-chat-spacing.png',
      fullPage: false
    });
    console.log('   📸 모바일 스크린샷: mobile-chat-spacing.png');

    // 점수 계산 (50점 만점)
    let score = 0;

    // 여백 일관성 (25점)
    const leftDiff = Math.abs(mobileMeasurements.sidebar.left - mobileMeasurements.header.left);
    if (leftDiff <= 3) {
      score += 25;
      console.log('   ✅ 모바일 여백 일관성 완벽 (25/25점)');
    } else if (leftDiff <= 10) {
      score += 20;
      console.log('   ✅ 모바일 여백 일관성 양호 (20/25점)');
    } else {
      console.log(`   ❌ 모바일 여백 일관성 부족 (차이: ${leftDiff}px, 0/25점)`);
    }

    // 시간 정렬 (25점)
    if (mobileMeasurements.timeAlignments.length > 0) {
      const rightAligned = mobileMeasurements.timeAlignments.every(t =>
        t.textAlign === 'right' || t.textAlign === 'end'
      );
      const noWrap = mobileMeasurements.timeAlignments.every(t =>
        t.whiteSpace === 'nowrap'
      );

      if (rightAligned && noWrap) {
        score += 25;
        console.log('   ✅ 모바일 시간 정렬 완벽 (25/25점)');
      } else if (rightAligned) {
        score += 20;
        console.log('   ✅ 모바일 시간 정렬 양호 (20/25점)');
      } else {
        console.log('   ❌ 모바일 시간 정렬 부족 (0/25점)');
      }
    } else {
      console.log('   ⚠️ 모바일 시간 정보 없음 (15/25점)');
      score += 15;
    }

    return { score, measurements: mobileMeasurements };

  } catch (error) {
    console.error('   ❌ 모바일 테스트 오류:', error.message);
    return { score: 0, error: error.message };
  } finally {
    await page.close();
  }
}