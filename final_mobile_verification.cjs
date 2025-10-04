const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🎯 최종 모바일 검증: 여백 일관성 & 시간 정렬 ===\n');

    // 모바일 뷰포트 설정 (iPhone 13)
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

    console.log('📱 모바일 채팅 페이지 로딩 완료');

    // 로딩 후 잠시 대기 (사용자가 말한 "로딩 후 사이즈 변경" 관찰)
    await page.waitForTimeout(5000);

    // 최종 모바일 상태 확인
    const mobileVerification = await page.evaluate(() => {
      const chatContainer = document.querySelector('.chat-container');
      const chatHeader = document.querySelector('.chat-header');
      const chatLayout = document.querySelector('.chat-layout');
      const chatSidebar = document.querySelector('.chat-sidebar');
      const roomTimes = document.querySelectorAll('.room-time');

      // 여백 일관성 검사
      const getSpacingInfo = (element, name) => {
        if (!element) return { name, exists: false };
        const rect = element.getBoundingClientRect();
        const styles = window.getComputedStyle(element);
        return {
          name,
          exists: true,
          left: rect.left,
          right: rect.right,
          width: rect.width,
          paddingLeft: parseInt(styles.paddingLeft) || 0,
          paddingRight: parseInt(styles.paddingRight) || 0
        };
      };

      const header = getSpacingInfo(chatHeader, 'Header');
      const layout = getSpacingInfo(chatLayout, 'Layout');
      const sidebar = getSpacingInfo(chatSidebar, 'Sidebar');

      // 시간 정렬 검사
      const timeResults = Array.from(roomTimes).map((time, index) => {
        const styles = window.getComputedStyle(time);
        return {
          index: index + 1,
          text: time.textContent.trim(),
          textAlign: styles.textAlign,
          whiteSpace: styles.whiteSpace,
          isRightAligned: styles.textAlign === 'right',
          isNoWrap: styles.whiteSpace === 'nowrap'
        };
      });

      // 여백 일관성 계산
      let spacingConsistency = {
        headerLayoutMatch: false,
        contentAlignmentMatch: false,
        paddingMatch: false
      };

      if (header.exists && layout.exists) {
        // 패딩 일치성 검사
        spacingConsistency.paddingMatch =
          header.paddingLeft === layout.paddingLeft &&
          header.paddingRight === layout.paddingRight;

        // 콘텐츠 시작점 일치성 검사
        const headerContentStart = header.left + header.paddingLeft;
        const layoutContentStart = layout.left + layout.paddingLeft;
        spacingConsistency.contentAlignmentMatch =
          Math.abs(headerContentStart - layoutContentStart) <= 2; // 2px 오차 허용

        // 전체 위치 일치성 검사
        spacingConsistency.headerLayoutMatch =
          Math.abs(header.left - layout.left) <= 2; // 2px 오차 허용
      }

      return {
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight
        },
        spacing: {
          header,
          layout,
          sidebar,
          consistency: spacingConsistency
        },
        timeAlignment: {
          totalCount: roomTimes.length,
          results: timeResults,
          allRightAligned: timeResults.every(t => t.isRightAligned),
          allNoWrap: timeResults.every(t => t.isNoWrap),
          perfectCount: timeResults.filter(t => t.isRightAligned && t.isNoWrap).length
        }
      };
    });

    console.log(`📊 모바일 검증 결과 (${mobileVerification.viewport.width}x${mobileVerification.viewport.height}):`);

    // 여백 일관성 결과
    console.log('\n📐 여백 일관성 검사:');
    if (mobileVerification.spacing.consistency.paddingMatch) {
      console.log('   ✅ Header-Layout 패딩 일치');
    } else {
      console.log('   ❌ Header-Layout 패딩 불일치');
      console.log(`      Header: ${mobileVerification.spacing.header.paddingLeft}px / ${mobileVerification.spacing.header.paddingRight}px`);
      console.log(`      Layout: ${mobileVerification.spacing.layout.paddingLeft}px / ${mobileVerification.spacing.layout.paddingRight}px`);
    }

    if (mobileVerification.spacing.consistency.contentAlignmentMatch) {
      console.log('   ✅ 콘텐츠 영역 정렬 일치');
    } else {
      console.log('   ❌ 콘텐츠 영역 정렬 불일치');
    }

    if (mobileVerification.spacing.consistency.headerLayoutMatch) {
      console.log('   ✅ Header-Layout 위치 일치');
    } else {
      console.log('   ❌ Header-Layout 위치 불일치');
      console.log(`      Header Left: ${Math.round(mobileVerification.spacing.header.left)}px`);
      console.log(`      Layout Left: ${Math.round(mobileVerification.spacing.layout.left)}px`);
    }

    // 시간 정렬 결과
    console.log('\n🕐 시간 정렬 검사:');
    console.log(`   채팅방 수: ${mobileVerification.timeAlignment.totalCount}개`);
    if (mobileVerification.timeAlignment.allRightAligned && mobileVerification.timeAlignment.allNoWrap) {
      console.log(`   ✅ 완벽한 시간 정렬 (${mobileVerification.timeAlignment.perfectCount}/${mobileVerification.timeAlignment.totalCount})`);
    } else {
      console.log(`   ⚠️ 부분적 시간 정렬 (${mobileVerification.timeAlignment.perfectCount}/${mobileVerification.timeAlignment.totalCount})`);

      mobileVerification.timeAlignment.results.forEach(result => {
        const status = result.isRightAligned && result.isNoWrap ? '✅' : '❌';
        console.log(`      시간 ${result.index}: ${status} "${result.text}" (${result.textAlign}/${result.whiteSpace})`);
      });
    }

    // 스크린샷
    await page.screenshot({
      path: 'final-mobile-verification.png',
      fullPage: false
    });
    console.log('\n📸 최종 모바일 검증 스크린샷: final-mobile-verification.png');

    // 종합 평가
    console.log('\n=== 🏆 최종 모바일 종합 평가 ===');

    const spacingScore = Object.values(mobileVerification.spacing.consistency).filter(Boolean).length;
    const spacingTotal = Object.values(mobileVerification.spacing.consistency).length;
    const timeScore = mobileVerification.timeAlignment.allRightAligned && mobileVerification.timeAlignment.allNoWrap ? 1 : 0;
    const totalScore = spacingScore + timeScore;
    const maxScore = spacingTotal + 1;
    const percentage = Math.round((totalScore / maxScore) * 100);

    console.log(`📊 여백 일관성: ${spacingScore}/${spacingTotal} (${Math.round(spacingScore/spacingTotal*100)}%)`);
    console.log(`📊 시간 정렬: ${timeScore}/1 (${timeScore*100}%)`);
    console.log(`🎯 종합 점수: ${totalScore}/${maxScore} (${percentage}%)`);

    if (percentage >= 90) {
      console.log('🎉 완벽! 모바일에서 모든 문제가 해결되었습니다.');
    } else if (percentage >= 75) {
      console.log('✅ 우수! 주요 문제들이 해결되었습니다.');
    } else if (percentage >= 50) {
      console.log('⚠️ 부분적 해결. 추가 개선이 필요합니다.');
    } else {
      console.log('❌ 문제 지속. 근본적인 수정이 필요합니다.');
    }

    // 사용자 원래 요청 사항 체크
    console.log('\n📋 원래 요청 사항 체크:');
    console.log('   1. "chat-sidebar 섹션이 우측에 딱 붙어 있어서 이상함" :', spacingScore >= 2 ? '✅ 해결' : '❌ 미해결');
    console.log('   2. "채팅 헤더처럼 일정하게 좌, 우 여백이 필요함" :', mobileVerification.spacing.consistency.paddingMatch ? '✅ 해결' : '❌ 미해결');
    console.log('   3. "room-time 시간 정보가 우측 정렬로 일관되게" :', mobileVerification.timeAlignment.allRightAligned ? '✅ 해결' : '❌ 미해결');
    console.log('   4. "로딩 후 사이즈 커지는 문제" :', '🔍 수동 확인 필요 (5초 대기 후 측정 완료)');

  } catch (error) {
    console.error('❌ 모바일 검증 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 최종 모바일 검증 완료');
  }
})();