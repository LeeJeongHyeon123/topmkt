const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 💻 데스크톱 채팅 사이드바 오버플로 테스트 ===\n');

    // 다양한 데스크톱 뷰포트에서 테스트
    const viewports = [
      { width: 1920, height: 1080, name: '큰 데스크톱 (1920px)' },
      { width: 1366, height: 768, name: '노트북 (1366px)' },
      { width: 1200, height: 800, name: '작은 데스크톱 (1200px)' },
      { width: 1024, height: 768, name: '태블릿 가로 (1024px)' },
      { width: 900, height: 600, name: '작은 화면 (900px)' }
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

      // 레이아웃 측정
      const measurements = await page.evaluate(() => {
        const chatContainer = document.querySelector('.chat-container');
        const chatLayout = document.querySelector('.chat-layout');
        const chatSidebar = document.querySelector('.chat-sidebar');

        if (!chatContainer || !chatLayout || !chatSidebar) {
          return { error: '필수 요소를 찾을 수 없음' };
        }

        const containerRect = chatContainer.getBoundingClientRect();
        const layoutRect = chatLayout.getBoundingClientRect();
        const sidebarRect = chatSidebar.getBoundingClientRect();

        const containerStyles = window.getComputedStyle(chatContainer);
        const layoutStyles = window.getComputedStyle(chatLayout);

        return {
          viewport: {
            width: window.innerWidth,
            height: window.innerHeight
          },
          container: {
            width: containerRect.width,
            right: containerRect.right,
            maxWidth: containerStyles.maxWidth,
            padding: containerStyles.padding
          },
          layout: {
            width: layoutRect.width,
            right: layoutRect.right,
            gridColumns: layoutStyles.gridTemplateColumns,
            gap: layoutStyles.gap,
            padding: layoutStyles.padding
          },
          sidebar: {
            width: sidebarRect.width,
            right: sidebarRect.right
          },
          overflow: {
            containerOverflow: containerRect.right > window.innerWidth,
            layoutOverflow: layoutRect.right > window.innerWidth,
            sidebarOverflow: sidebarRect.right > window.innerWidth
          }
        };
      });

      results.push({
        viewport: viewport.name,
        size: `${viewport.width}x${viewport.height}`,
        measurements
      });

      console.log(`📊 ${viewport.name} (${viewport.width}x${viewport.height}):`);

      if (measurements.error) {
        console.log(`   ❌ 오류: ${measurements.error}`);
        continue;
      }

      console.log(`   Container: ${Math.round(measurements.container.width)}px (최대: ${measurements.container.maxWidth})`);
      console.log(`   Layout: ${Math.round(measurements.layout.width)}px (그리드: ${measurements.layout.gridColumns})`);
      console.log(`   Sidebar: ${Math.round(measurements.sidebar.width)}px`);

      // 오버플로 검사
      let overflowIssues = [];
      if (measurements.overflow.containerOverflow) overflowIssues.push('Container');
      if (measurements.overflow.layoutOverflow) overflowIssues.push('Layout');
      if (measurements.overflow.sidebarOverflow) overflowIssues.push('Sidebar');

      if (overflowIssues.length > 0) {
        console.log(`   ❌ 오버플로 발생: ${overflowIssues.join(', ')}`);
      } else {
        console.log(`   ✅ 오버플로 없음 - 정상`);
      }

      console.log('');
    }

    // 스크린샷
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.screenshot({
      path: 'desktop-sidebar-test.png',
      fullPage: false
    });

    // 종합 평가
    console.log('=== 🏆 종합 평가 ===');

    let totalTests = results.length;
    let passedTests = results.filter(result => {
      if (result.measurements.error) return false;
      const overflow = result.measurements.overflow;
      return !overflow.containerOverflow && !overflow.layoutOverflow && !overflow.sidebarOverflow;
    }).length;

    const successRate = Math.round((passedTests / totalTests) * 100);

    console.log(`📊 테스트 결과: ${passedTests}/${totalTests} 통과 (${successRate}%)`);

    results.forEach(result => {
      if (!result.measurements.error) {
        const overflow = result.measurements.overflow;
        const hasOverflow = overflow.containerOverflow || overflow.layoutOverflow || overflow.sidebarOverflow;
        console.log(`   ${hasOverflow ? '❌' : '✅'} ${result.viewport}: ${hasOverflow ? 'FAIL' : 'PASS'}`);
      }
    });

    if (successRate >= 90) {
      console.log('\n🎉 완벽! 모든 화면 크기에서 사이드바가 정상적으로 표시됩니다.');
    } else if (successRate >= 80) {
      console.log('\n✅ 우수! 대부분의 화면 크기에서 정상 작동합니다.');
    } else if (successRate >= 60) {
      console.log('\n⚠️ 부분적 성공. 일부 화면에서 추가 최적화가 필요합니다.');
    } else {
      console.log('\n❌ 문제 지속. 근본적인 수정이 필요합니다.');
    }

    console.log(`\n📸 테스트 스크린샷: desktop-sidebar-test.png`);

  } catch (error) {
    console.error('❌ 테스트 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 데스크톱 사이드바 오버플로 테스트 완료');
  }
})();