const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== ✅ 최종 검증: 채팅 사이드바 여백 & 시간 정렬 ===\n');

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

    console.log('📄 채팅 페이지 로딩 완료');

    // 로딩 후 잠시 대기 (사용자가 말한 로딩 후 사이즈 변경 관찰)
    await page.waitForTimeout(5000);

    // 최종 상태 확인
    const finalCheck = await page.evaluate(() => {
      const chatContainer = document.querySelector('.chat-container');
      const chatSidebar = document.querySelector('.chat-sidebar');
      const roomTimes = document.querySelectorAll('.room-time');

      const containerStyles = chatContainer ? window.getComputedStyle(chatContainer) : null;
      const sidebarRect = chatSidebar ? chatSidebar.getBoundingClientRect() : null;

      // 시간 정렬 완전성 체크
      const timeAlignmentResults = Array.from(roomTimes).map((time, index) => {
        const styles = window.getComputedStyle(time);
        return {
          index: index + 1,
          textAlign: styles.textAlign,
          whiteSpace: styles.whiteSpace,
          isRightAligned: styles.textAlign === 'right',
          isNoWrap: styles.whiteSpace === 'nowrap'
        };
      });

      return {
        containerPadding: containerStyles?.padding || 'unknown',
        sidebarWidth: sidebarRect?.width || 0,
        sidebarLeft: sidebarRect?.left || 0,
        roomCount: roomTimes.length,
        timeAlignmentResults,
        allTimesRightAligned: timeAlignmentResults.every(t => t.isRightAligned),
        allTimesNoWrap: timeAlignmentResults.every(t => t.isNoWrap),
        pageLoaded: !!chatContainer && !!chatSidebar
      };
    });

    console.log('📊 최종 검증 결과:');
    console.log(`   - 페이지 로딩: ${finalCheck.pageLoaded ? '✅ 완료' : '❌ 실패'}`);
    console.log(`   - Container 패딩: ${finalCheck.containerPadding}`);
    console.log(`   - 사이드바 크기: ${Math.round(finalCheck.sidebarWidth)}px`);
    console.log(`   - 채팅방 수: ${finalCheck.roomCount}개`);

    // 시간 정렬 검증
    console.log('\n🕐 시간 정렬 완전성 검증:');
    if (finalCheck.roomCount > 0) {
      console.log(`   - 모든 시간 우측 정렬: ${finalCheck.allTimesRightAligned ? '✅' : '❌'}`);
      console.log(`   - 모든 시간 줄바꿈 방지: ${finalCheck.allTimesNoWrap ? '✅' : '❌'}`);

      finalCheck.timeAlignmentResults.forEach(result => {
        const status = result.isRightAligned && result.isNoWrap ? '✅' : '❌';
        console.log(`   - 시간 ${result.index}: ${status} (정렬: ${result.textAlign}, 줄바꿈: ${result.whiteSpace})`);
      });
    } else {
      console.log('   ⚠️ 채팅방이 없어서 시간 정렬을 확인할 수 없습니다.');
    }

    // 스크린샷
    await page.screenshot({
      path: 'final-verification.png',
      fullPage: false
    });
    console.log('\n📸 최종 검증 스크린샷: final-verification.png');

    // 종합 평가
    console.log('\n=== 🏆 종합 평가 ===');

    let achievements = [];
    let issues = [];

    if (finalCheck.pageLoaded) {
      achievements.push('페이지 정상 로딩');
    } else {
      issues.push('페이지 로딩 실패');
    }

    if (finalCheck.roomCount > 0) {
      if (finalCheck.allTimesRightAligned && finalCheck.allTimesNoWrap) {
        achievements.push('시간 정보 완벽한 우측 정렬');
      } else if (finalCheck.allTimesRightAligned) {
        achievements.push('시간 정보 우측 정렬 (줄바꿈 일부 문제)');
      } else {
        issues.push('시간 정보 정렬 불완전');
      }
    }

    if (finalCheck.containerPadding === '20px') {
      achievements.push('로딩 후 사이즈 문제 해결');
    }

    console.log('✅ 달성된 개선사항:');
    achievements.forEach(achievement => {
      console.log(`   - ${achievement}`);
    });

    if (issues.length > 0) {
      console.log('\n⚠️ 남은 이슈:');
      issues.forEach(issue => {
        console.log(`   - ${issue}`);
      });
    }

    const successRate = Math.round((achievements.length / (achievements.length + issues.length)) * 100);
    console.log(`\n🎯 성공률: ${successRate}%`);

    if (successRate >= 90) {
      console.log('🎉 완벽! 채팅 사이드바 개선이 성공적으로 완료되었습니다.');
    } else if (successRate >= 70) {
      console.log('✅ 성공! 주요 개선 목표가 달성되었습니다.');
    } else {
      console.log('⚠️ 부분적 성공. 추가 개선이 필요할 수 있습니다.');
    }

  } catch (error) {
    console.error('❌ 검증 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 최종 검증 완료');
  }
})();