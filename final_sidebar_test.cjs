const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 최종 사이드바 테스트 시작...');

    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    console.log('✅ 페이지 로딩 완료');

    // JavaScript가 실행될 시간을 기다림
    await page.waitForTimeout(1000);

    // 전체 페이지 스크린샷
    await page.screenshot({
      path: 'final-sidebar-test.png',
      fullPage: true
    });

    // 사이드바 상태 최종 확인
    const finalStatus = await page.evaluate(() => {
      const sidebar = document.querySelector('.events-sidebar');
      const layout = document.querySelector('.events-layout');

      if (!sidebar || !layout) {
        return { error: 'Elements not found' };
      }

      const sidebarRect = sidebar.getBoundingClientRect();
      const layoutRect = layout.getBoundingClientRect();
      const viewportWidth = window.innerWidth;

      // 콘솔 로그 확인
      const consoleMessages = [];

      return {
        timestamp: new Date().toLocaleString('ko-KR'),
        viewport: { width: viewportWidth, height: window.innerHeight },
        layout: {
          position: { x: layoutRect.x, y: layoutRect.y },
          size: { width: layoutRect.width, height: layoutRect.height },
          computedStyle: {
            display: window.getComputedStyle(layout).display,
            gridTemplateColumns: window.getComputedStyle(layout).gridTemplateColumns,
            maxWidth: window.getComputedStyle(layout).maxWidth,
            margin: window.getComputedStyle(layout).margin
          }
        },
        sidebar: {
          position: { x: sidebarRect.x, y: sidebarRect.y },
          size: { width: sidebarRect.width, height: sidebarRect.height },
          visible: sidebarRect.x >= 0 && sidebarRect.x < viewportWidth,
          inViewport: sidebarRect.right <= viewportWidth,
          computedStyle: {
            display: window.getComputedStyle(sidebar).display,
            visibility: window.getComputedStyle(sidebar).visibility,
            width: window.getComputedStyle(sidebar).width
          }
        },
        calculation: {
          layoutWidth: layoutRect.width,
          sidebarPosition: sidebarRect.x,
          expectedSidebarPosition: layoutRect.x + layoutRect.width - sidebarRect.width,
          isCorrectPosition: Math.abs(sidebarRect.x - (layoutRect.x + layoutRect.width - sidebarRect.width)) < 10
        }
      };
    });

    console.log('📊 최종 사이드바 상태 분석:');
    console.log(JSON.stringify(finalStatus, null, 2));

    console.log('\n🎯 결과 요약:');
    if (finalStatus.sidebar) {
      console.log(`✅ 사이드바 가시성: ${finalStatus.sidebar.visible ? '보임' : '안보임'}`);
      console.log(`📐 사이드바 위치: x=${finalStatus.sidebar.position.x}, y=${finalStatus.sidebar.position.y}`);
      console.log(`📏 사이드바 크기: ${finalStatus.sidebar.size.width}x${finalStatus.sidebar.size.height}`);
      console.log(`🖥️ 뷰포트 내 표시: ${finalStatus.sidebar.inViewport ? '예' : '아니오'}`);
      console.log(`🎯 올바른 위치: ${finalStatus.calculation.isCorrectPosition ? '예' : '아니오'}`);
    }

    if (finalStatus.layout) {
      console.log(`📦 레이아웃 크기: ${finalStatus.layout.size.width}x${finalStatus.layout.size.height}`);
      console.log(`🔧 Grid 컬럼: ${finalStatus.layout.computedStyle.gridTemplateColumns}`);
    }

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ 분석 완료. final-sidebar-test.png 생성됨');
  }
})();