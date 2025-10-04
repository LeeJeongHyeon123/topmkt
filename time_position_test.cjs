const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🎯 시간 정보 우측 고정 위치 테스트 ===\n');

    // 테스트할 화면 크기들
    const viewports = [
      { width: 1920, height: 1080, name: 'PC 대형 화면' },
      { width: 1366, height: 768, name: 'PC 일반 화면' },
      { width: 1024, height: 768, name: '태블릿' },
      { width: 768, height: 1024, name: '태블릿 세로' },
      { width: 390, height: 844, name: '모바일 (iPhone 13)' },
      { width: 375, height: 667, name: '모바일 (iPhone SE)' },
      { width: 320, height: 568, name: '작은 모바일' }
    ];

    const results = [];

    for (const viewport of viewports) {
      await page.setViewportSize({ width: viewport.width, height: viewport.height });

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

      // 시간 정보 위치 분석
      const timePositionAnalysis = await page.evaluate(() => {
        const roomHeaders = document.querySelectorAll('.room-header');
        const roomTimes = document.querySelectorAll('.room-time');
        const chatSidebar = document.querySelector('.chat-sidebar');

        if (!chatSidebar) {
          return { error: 'chat-sidebar를 찾을 수 없음' };
        }

        const sidebarRect = chatSidebar.getBoundingClientRect();
        const sidebarRight = sidebarRect.right;
        const sidebarWidth = sidebarRect.width;

        const timeAnalysis = Array.from(roomTimes).map((timeEl, index) => {
          const timeRect = timeEl.getBoundingClientRect();
          const headerEl = timeEl.closest('.room-header');
          const nameEl = headerEl ? headerEl.querySelector('.room-name') : null;

          let headerRect = null;
          let nameRect = null;
          let headerWidth = 0;
          let nameWidth = 0;

          if (headerEl) {
            headerRect = headerEl.getBoundingClientRect();
            headerWidth = headerRect.width;
          }

          if (nameEl) {
            nameRect = nameEl.getBoundingClientRect();
            nameWidth = nameRect.width;
          }

          // 시간이 우측에 정렬되어 있는지 확인
          const distanceFromSidebarRight = Math.abs(timeRect.right - sidebarRight);
          const isNearSidebarRight = distanceFromSidebarRight <= 20; // 20px 오차 허용

          // 시간과 이름 사이의 간격
          const nameTimeGap = nameRect && timeRect ? (timeRect.left - nameRect.right) : 0;

          const timeStyles = window.getComputedStyle(timeEl);

          return {
            index: index + 1,
            text: timeEl.textContent.trim(),
            position: {
              left: Math.round(timeRect.left),
              right: Math.round(timeRect.right),
              width: Math.round(timeRect.width)
            },
            sidebar: {
              right: Math.round(sidebarRight),
              width: Math.round(sidebarWidth)
            },
            header: {
              width: Math.round(headerWidth)
            },
            name: {
              width: Math.round(nameWidth)
            },
            spacing: {
              distanceFromSidebarRight: Math.round(distanceFromSidebarRight),
              nameTimeGap: Math.round(nameTimeGap)
            },
            alignment: {
              isNearSidebarRight: isNearSidebarRight,
              textAlign: timeStyles.textAlign,
              marginLeft: timeStyles.marginLeft,
              flexShrink: timeStyles.flexShrink,
              minWidth: timeStyles.minWidth
            }
          };
        });

        return {
          viewport: {
            width: window.innerWidth,
            height: window.innerHeight
          },
          sidebar: {
            width: Math.round(sidebarWidth),
            right: Math.round(sidebarRight)
          },
          totalTimes: roomTimes.length,
          timeAnalysis: timeAnalysis
        };
      });

      results.push({
        viewport: viewport.name,
        size: `${viewport.width}x${viewport.height}`,
        analysis: timePositionAnalysis
      });

      console.log(`📊 ${viewport.name} (${viewport.width}x${viewport.height}):`);

      if (timePositionAnalysis.error) {
        console.log(`   ❌ 오류: ${timePositionAnalysis.error}`);
        continue;
      }

      console.log(`   사이드바 너비: ${timePositionAnalysis.sidebar.width}px`);
      console.log(`   시간 요소 수: ${timePositionAnalysis.totalTimes}개`);

      let properlyAligned = 0;
      timePositionAnalysis.timeAnalysis.forEach(time => {
        const status = time.alignment.isNearSidebarRight ? '✅' : '❌';
        console.log(`   시간 ${time.index}: ${status} "${time.text}"`);
        console.log(`      위치: ${time.position.left}px ~ ${time.position.right}px (너비: ${time.position.width}px)`);
        console.log(`      사이드바 우측까지 거리: ${time.spacing.distanceFromSidebarRight}px`);
        console.log(`      이름-시간 간격: ${time.spacing.nameTimeGap}px`);

        if (time.alignment.isNearSidebarRight) {
          properlyAligned++;
        }
      });

      console.log(`   ✅ 정상 정렬: ${properlyAligned}/${timePositionAnalysis.totalTimes}`);
      console.log('');
    }

    // 데스크톱과 모바일에서 각각 스크린샷
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('https://www.topmktx.com/chat', { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);
    await page.screenshot({
      path: 'desktop-time-position.png',
      fullPage: false
    });

    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('https://www.topmktx.com/chat', { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);
    await page.screenshot({
      path: 'mobile-time-position.png',
      fullPage: false
    });

    // 종합 평가
    console.log('=== 🏆 시간 위치 정렬 종합 평가 ===');

    let totalTests = 0;
    let passedTests = 0;

    results.forEach(result => {
      if (!result.analysis.error) {
        const analysis = result.analysis;
        totalTests += analysis.totalTimes;
        const aligned = analysis.timeAnalysis.filter(t => t.alignment.isNearSidebarRight).length;
        passedTests += aligned;

        const success = aligned === analysis.totalTimes;
        console.log(`   ${success ? '✅' : '❌'} ${result.viewport}: ${aligned}/${analysis.totalTimes}`);
      }
    });

    const successRate = totalTests > 0 ? Math.round((passedTests / totalTests) * 100) : 0;
    console.log(`\n📊 전체 성공률: ${passedTests}/${totalTests} (${successRate}%)`);

    if (successRate >= 90) {
      console.log('🎉 완벽! 모든 화면에서 시간이 우측에 정확히 정렬되었습니다.');
    } else if (successRate >= 80) {
      console.log('✅ 우수! 대부분 화면에서 정상 정렬됩니다.');
    } else if (successRate >= 60) {
      console.log('⚠️ 부분적 성공. 일부 화면에서 추가 조정이 필요합니다.');
    } else {
      console.log('❌ 문제 지속. CSS 구조 재검토가 필요합니다.');
    }

    console.log('\n📸 스크린샷 생성: desktop-time-position.png, mobile-time-position.png');

  } catch (error) {
    console.error('❌ 시간 위치 테스트 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 시간 위치 정렬 테스트 완료');
  }
})();