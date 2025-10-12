const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🎯 채팅 시간 정렬 및 포맷 테스트 ===\n');

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
      const roomHeaders = document.querySelectorAll('.room-header');
      const roomNames = document.querySelectorAll('.room-name');

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

      // 새로운 가로 정렬 구조 검사
      const alignmentResults = Array.from(roomHeaders).map((roomHeader, index) => {
        const styles = window.getComputedStyle(roomHeader);
        const roomNameEl = roomHeader.querySelector('.room-name');
        const roomTimeEl = roomHeader.querySelector('.room-time');

        return {
          index: index + 1,
          display: styles.display,
          justifyContent: styles.justifyContent,
          alignItems: styles.alignItems,
          hasRoomName: !!roomNameEl,
          hasRoomTime: !!roomTimeEl,
          roomNameText: roomNameEl ? roomNameEl.textContent.trim() : '',
          roomTimeText: roomTimeEl ? roomTimeEl.textContent.trim() : '',
          isFlexContainer: styles.display === 'flex',
          isSpaceBetween: styles.justifyContent === 'space-between'
        };
      });

      // 시간 정렬 검사
      const timeResults = Array.from(roomTimes).map((time, index) => {
        const styles = window.getComputedStyle(time);
        const text = time.textContent.trim();

        // 새로운 시간 포맷 검사
        let timeFormat = 'unknown';
        if (text.includes(':') && !text.includes('월') && !text.includes('.')) {
          timeFormat = 'today'; // 오늘 (시:분)
        } else if (text.includes('월') && text.includes('일')) {
          timeFormat = 'sameYear'; // 같은 년도 (x월 x일)
        } else if (text.includes('.') && text.match(/^\d{4}\.\d{2}\.\d{2}$/)) {
          timeFormat = 'pastYear'; // 지난 년도 (년.월.일)
        }

        return {
          index: index + 1,
          text: text,
          textAlign: styles.textAlign,
          whiteSpace: styles.whiteSpace,
          flexShrink: styles.flexShrink,
          timeFormat: timeFormat,
          isRightAligned: styles.textAlign === 'right',
          isNoWrap: styles.whiteSpace === 'nowrap',
          isFlexShrinkZero: styles.flexShrink === '0'
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
        horizontalAlignment: {
          totalHeaders: roomHeaders.length,
          results: alignmentResults,
          allFlex: alignmentResults.every(r => r.isFlexContainer),
          allSpaceBetween: alignmentResults.every(r => r.isSpaceBetween),
          perfectCount: alignmentResults.filter(r => r.isFlexContainer && r.isSpaceBetween && r.hasRoomName && r.hasRoomTime).length
        },
        timeAlignment: {
          totalCount: roomTimes.length,
          results: timeResults,
          allRightAligned: timeResults.every(t => t.isRightAligned),
          allNoWrap: timeResults.every(t => t.isNoWrap),
          allFlexShrinkZero: timeResults.every(t => t.isFlexShrinkZero),
          perfectCount: timeResults.filter(t => t.isRightAligned && t.isNoWrap && t.isFlexShrinkZero).length
        }
      };
    });

    console.log(`📊 모바일 채팅 시간 정렬 테스트 (${mobileVerification.viewport.width}x${mobileVerification.viewport.height}):`);

    // 여백 일관성 결과
    console.log('\n📐 여백 일관성 검사:');
    if (mobileVerification.spacing.consistency.paddingMatch) {
      console.log('   ✅ Header-Layout 패딩 일치');
    } else {
      console.log('   ❌ Header-Layout 패딩 불일치');
      console.log(`      Header: ${mobileVerification.spacing.header.paddingLeft}px / ${mobileVerification.spacing.header.paddingRight}px`);
      console.log(`      Layout: ${mobileVerification.spacing.layout.paddingLeft}px / ${mobileVerification.spacing.layout.paddingRight}px`);
    }

    // 가로 정렬 결과
    console.log('\n🏗️ 가로 정렬 구조 검사:');
    console.log(`   채팅방 헤더 수: ${mobileVerification.horizontalAlignment.totalHeaders}개`);
    if (mobileVerification.horizontalAlignment.allFlex && mobileVerification.horizontalAlignment.allSpaceBetween) {
      console.log(`   ✅ 완벽한 가로 정렬 (${mobileVerification.horizontalAlignment.perfectCount}/${mobileVerification.horizontalAlignment.totalHeaders})`);
    } else {
      console.log(`   ⚠️ 부분적 가로 정렬 (${mobileVerification.horizontalAlignment.perfectCount}/${mobileVerification.horizontalAlignment.totalHeaders})`);

      mobileVerification.horizontalAlignment.results.forEach(result => {
        const status = result.isFlexContainer && result.isSpaceBetween && result.hasRoomName && result.hasRoomTime ? '✅' : '❌';
        console.log(`      헤더 ${result.index}: ${status} "${result.roomNameText}" | "${result.roomTimeText}"`);
        if (!result.isFlexContainer) console.log(`        - display: ${result.display} (flex 필요)`);
        if (!result.isSpaceBetween) console.log(`        - justify-content: ${result.justifyContent} (space-between 필요)`);
      });
    }

    // 시간 정렬 결과
    console.log('\n🕐 시간 정렬 및 포맷 검사:');
    console.log(`   시간 요소 수: ${mobileVerification.timeAlignment.totalCount}개`);
    if (mobileVerification.timeAlignment.allRightAligned && mobileVerification.timeAlignment.allNoWrap && mobileVerification.timeAlignment.allFlexShrinkZero) {
      console.log(`   ✅ 완벽한 시간 정렬 (${mobileVerification.timeAlignment.perfectCount}/${mobileVerification.timeAlignment.totalCount})`);
    } else {
      console.log(`   ⚠️ 부분적 시간 정렬 (${mobileVerification.timeAlignment.perfectCount}/${mobileVerification.timeAlignment.totalCount})`);

      mobileVerification.timeAlignment.results.forEach(result => {
        const status = result.isRightAligned && result.isNoWrap && result.isFlexShrinkZero ? '✅' : '❌';
        console.log(`      시간 ${result.index}: ${status} "${result.text}" [${result.timeFormat}]`);
        if (!result.isRightAligned) console.log(`        - text-align: ${result.textAlign} (right 필요)`);
        if (!result.isNoWrap) console.log(`        - white-space: ${result.whiteSpace} (nowrap 필요)`);
        if (!result.isFlexShrinkZero) console.log(`        - flex-shrink: ${result.flexShrink} (0 필요)`);
      });
    }

    // 시간 포맷 분석
    console.log('\n⏰ 시간 포맷 분석:');
    const formatCounts = mobileVerification.timeAlignment.results.reduce((acc, result) => {
      acc[result.timeFormat] = (acc[result.timeFormat] || 0) + 1;
      return acc;
    }, {});

    console.log(`   오늘 (시:분): ${formatCounts.today || 0}개`);
    console.log(`   같은년도 (x월 x일): ${formatCounts.sameYear || 0}개`);
    console.log(`   지난년도 (년.월.일): ${formatCounts.pastYear || 0}개`);
    console.log(`   알 수 없는 포맷: ${formatCounts.unknown || 0}개`);

    // 스크린샷
    await page.screenshot({
      path: 'mobile-time-alignment.png',
      fullPage: false
    });
    console.log('\n📸 모바일 시간 정렬 테스트 스크린샷: mobile-time-alignment.png');

    // 종합 평가
    console.log('\n=== 🏆 채팅 시간 정렬 종합 평가 ===');

    const spacingScore = Object.values(mobileVerification.spacing.consistency).filter(Boolean).length;
    const spacingTotal = Object.values(mobileVerification.spacing.consistency).length;
    const alignmentScore = mobileVerification.horizontalAlignment.allFlex && mobileVerification.horizontalAlignment.allSpaceBetween ? 1 : 0;
    const timeScore = mobileVerification.timeAlignment.allRightAligned && mobileVerification.timeAlignment.allNoWrap && mobileVerification.timeAlignment.allFlexShrinkZero ? 1 : 0;
    const totalScore = spacingScore + alignmentScore + timeScore;
    const maxScore = spacingTotal + 2;
    const percentage = Math.round((totalScore / maxScore) * 100);

    console.log(`📊 여백 일관성: ${spacingScore}/${spacingTotal} (${Math.round(spacingScore/spacingTotal*100)}%)`);
    console.log(`📊 가로 정렬: ${alignmentScore}/1 (${alignmentScore*100}%)`);
    console.log(`📊 시간 정렬: ${timeScore}/1 (${timeScore*100}%)`);
    console.log(`🎯 종합 점수: ${totalScore}/${maxScore} (${percentage}%)`);

    if (percentage >= 90) {
      console.log('🎉 완벽! 모든 채팅 시간 정렬이 완료되었습니다.');
    } else if (percentage >= 75) {
      console.log('✅ 우수! 주요 기능들이 정상 작동합니다.');
    } else if (percentage >= 50) {
      console.log('⚠️ 부분적 성공. 추가 개선이 필요합니다.');
    } else {
      console.log('❌ 문제 지속. 근본적인 수정이 필요합니다.');
    }

    // 사용자 요청 사항 체크
    console.log('\n📋 사용자 요청 사항 체크:');
    console.log('   1. "room-time과 room-name 가로 일직선 배치" :', alignmentScore ? '✅ 해결' : '❌ 미해결');
    console.log('   2. "room-name은 좌측, room-time 우측" :', alignmentScore ? '✅ 해결' : '❌ 미해결');
    console.log('   3. "오늘일 때 시:분만 표기" :', formatCounts.today > 0 ? '✅ 적용됨' : '🔍 확인 필요');
    console.log('   4. "같은년도 x월 x일 표기" :', formatCounts.sameYear > 0 ? '✅ 적용됨' : '🔍 확인 필요');
    console.log('   5. "지난년도 년.월.일 표기" :', formatCounts.pastYear > 0 ? '✅ 적용됨' : '🔍 확인 필요');

  } catch (error) {
    console.error('❌ 채팅 시간 정렬 테스트 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 채팅 시간 정렬 테스트 완료');
  }
})();