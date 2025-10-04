const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🎯 최종 채팅 사이드바 여백 검증 테스트 ===\n');

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

    // 정밀한 여백 측정
    const finalMeasurements = await page.evaluate(() => {
      const chatLayout = document.querySelector('.chat-layout');
      const chatSidebar = document.querySelector('.chat-sidebar');
      const chatHeader = document.querySelector('.chat-header');
      const roomTimes = document.querySelectorAll('.room-time');
      const roomMetas = document.querySelectorAll('.room-meta');

      // 각 요소의 정확한 위치 측정
      const layoutRect = chatLayout ? chatLayout.getBoundingClientRect() : null;
      const sidebarRect = chatSidebar ? chatSidebar.getBoundingClientRect() : null;
      const headerRect = chatHeader ? chatHeader.getBoundingClientRect() : null;

      // CSS 스타일 확인
      const layoutStyles = chatLayout ? window.getComputedStyle(chatLayout) : null;
      const headerStyles = chatHeader ? window.getComputedStyle(chatHeader) : null;

      // 시간 정보 정렬 검사
      const timeAlignmentCheck = Array.from(roomTimes).map((time, index) => {
        const styles = window.getComputedStyle(time);
        const rect = time.getBoundingClientRect();
        const parentMeta = time.closest('.room-meta');
        const parentMetaRect = parentMeta ? parentMeta.getBoundingClientRect() : null;

        return {
          index,
          textAlign: styles.textAlign,
          whiteSpace: styles.whiteSpace,
          timeRight: rect.right,
          metaRight: parentMetaRect?.right || 0,
          alignmentOffset: parentMetaRect ? Math.abs(rect.right - parentMetaRect.right) : 0
        };
      });

      return {
        layout: {
          left: layoutRect?.left || 0,
          right: layoutRect?.right || 0,
          padding: layoutStyles?.padding || 'none',
          paddingLeft: parseInt(layoutStyles?.paddingLeft) || 0,
          paddingRight: parseInt(layoutStyles?.paddingRight) || 0
        },
        sidebar: {
          left: sidebarRect?.left || 0,
          right: sidebarRect?.right || 0
        },
        header: {
          left: headerRect?.left || 0,
          right: headerRect?.right || 0,
          padding: headerStyles?.padding || 'none',
          paddingLeft: parseInt(headerStyles?.paddingLeft) || 0,
          paddingRight: parseInt(headerStyles?.paddingRight) || 0
        },
        timeAlignmentCheck,
        roomCount: roomTimes.length
      };
    });

    console.log('📏 최종 정밀 측정 결과:');
    console.log(`   Layout 패딩: ${finalMeasurements.layout.padding}`);
    console.log(`   Header 패딩: ${finalMeasurements.header.padding}`);
    console.log(`   사이드바 Left: ${Math.round(finalMeasurements.sidebar.left)}px`);
    console.log(`   헤더 Left: ${Math.round(finalMeasurements.header.left)}px`);

    // 여백 일관성 계산
    const leftDifference = Math.abs(finalMeasurements.sidebar.left - finalMeasurements.header.left);
    console.log(`   📐 Left 차이: ${Math.round(leftDifference)}px`);

    // 시간 정렬 검사
    console.log(`\n🕐 시간 정렬 검사 (${finalMeasurements.timeAlignmentCheck.length}개 항목):`);
    let perfectTimeAlignment = 0;
    finalMeasurements.timeAlignmentCheck.forEach(check => {
      const isRightAligned = check.textAlign === 'right';
      const isNoWrap = check.whiteSpace === 'nowrap';
      const isWellAligned = check.alignmentOffset <= 2; // 2px 이내 오차 허용

      if (isRightAligned && isNoWrap && isWellAligned) {
        perfectTimeAlignment++;
      }

      console.log(`   항목 ${check.index + 1}: ${isRightAligned ? '✅' : '❌'} 정렬, ${isNoWrap ? '✅' : '❌'} 줄바꿈, ${isWellAligned ? '✅' : '❌'} 위치`);
    });

    // 스크린샷
    await page.screenshot({ path: 'final-chat-spacing.png', fullPage: false });
    console.log(`\n📸 최종 스크린샷: final-chat-spacing.png`);

    // 최종 점수 계산
    console.log('\n=== 🏆 최종 평가 ===');

    let totalScore = 0;
    const maxScore = 100;

    // 1. 여백 일관성 (50점)
    if (leftDifference <= 2) {
      totalScore += 50;
      console.log('✅ 여백 일관성 완벽 (50/50점)');
    } else if (leftDifference <= 5) {
      totalScore += 45;
      console.log('✅ 여백 일관성 우수 (45/50점)');
    } else if (leftDifference <= 10) {
      totalScore += 35;
      console.log('✅ 여백 일관성 양호 (35/50점)');
    } else {
      console.log(`❌ 여백 일관성 부족 (차이: ${Math.round(leftDifference)}px, 0/50점)`);
    }

    // 2. 시간 정렬 완성도 (50점)
    if (finalMeasurements.timeAlignmentCheck.length > 0) {
      const alignmentPercent = (perfectTimeAlignment / finalMeasurements.timeAlignmentCheck.length) * 100;
      const timeScore = Math.round((alignmentPercent / 100) * 50);
      totalScore += timeScore;

      if (alignmentPercent === 100) {
        console.log(`✅ 시간 정렬 완벽 (${timeScore}/50점)`);
      } else if (alignmentPercent >= 80) {
        console.log(`✅ 시간 정렬 우수 (${timeScore}/50점)`);
      } else {
        console.log(`⚠️ 시간 정렬 개선 필요 (${timeScore}/50점)`);
      }
    } else {
      totalScore += 30; // 채팅방이 없는 경우 기본점수
      console.log('⚠️ 시간 정보 없음 (30/50점)');
    }

    const finalPercentage = Math.round((totalScore / maxScore) * 100);
    console.log(`\n🎯 최종 점수: ${totalScore}/${maxScore} (${finalPercentage}%)`);

    if (finalPercentage >= 95) {
      console.log('🎉 완벽! 채팅 사이드바 여백과 시간 정렬이 완벽하게 개선되었습니다.');
    } else if (finalPercentage >= 85) {
      console.log('🎉 우수! 채팅 사이드바가 매우 깔끔하게 정렬되었습니다.');
    } else if (finalPercentage >= 70) {
      console.log('✅ 양호! 주요 개선이 완료되었습니다.');
    } else {
      console.log('⚠️ 개선 필요! 추가 조정이 필요합니다.');
    }

  } catch (error) {
    console.error('❌ 테스트 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 최종 채팅 사이드바 여백 검증 완료');
  }
})();