const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 events-sidebar 수정 후 상태 확인...');

    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    console.log('✅ 페이지 로딩 완료');

    // 전체 페이지 스크린샷
    await page.screenshot({
      path: 'events-sidebar-fixed-test.png',
      fullPage: true
    });

    // 사이드바 위치 및 가시성 분석
    const sidebarStatus = await page.evaluate(() => {
      const sidebar = document.querySelector('.events-sidebar');
      const layout = document.querySelector('.events-layout');

      if (!sidebar) return { error: 'sidebar not found' };
      if (!layout) return { error: 'layout not found' };

      const sidebarRect = sidebar.getBoundingClientRect();
      const layoutRect = layout.getBoundingClientRect();
      const viewportWidth = window.innerWidth;
      const viewportHeight = window.innerHeight;

      return {
        sidebar: {
          visible: sidebarRect.x >= 0 && sidebarRect.x < viewportWidth,
          position: { x: sidebarRect.x, y: sidebarRect.y },
          size: { width: sidebarRect.width, height: sidebarRect.height },
          inViewport: sidebarRect.right <= viewportWidth && sidebarRect.bottom <= viewportHeight
        },
        layout: {
          position: { x: layoutRect.x, y: layoutRect.y },
          size: { width: layoutRect.width, height: layoutRect.height }
        },
        viewport: { width: viewportWidth, height: viewportHeight },
        gridInfo: {
          computedStyle: window.getComputedStyle(layout),
          gridTemplateColumns: window.getComputedStyle(layout).gridTemplateColumns
        }
      };
    });

    console.log('📊 사이드바 수정 후 상태:');
    console.log(JSON.stringify(sidebarStatus, null, 2));

    console.log('\n🎯 결과 분석:');
    if (sidebarStatus.sidebar) {
      console.log(`✅ 사이드바 가시성: ${sidebarStatus.sidebar.visible ? '보임' : '안보임'}`);
      console.log(`📐 사이드바 위치: x=${sidebarStatus.sidebar.position.x}, y=${sidebarStatus.sidebar.position.y}`);
      console.log(`📏 사이드바 크기: ${sidebarStatus.sidebar.size.width}x${sidebarStatus.sidebar.size.height}`);
      console.log(`🖥️ 뷰포트 내 완전 표시: ${sidebarStatus.sidebar.inViewport ? '예' : '아니오'}`);
    }

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ 분석 완료. events-sidebar-fixed-test.png 생성됨');
  }
})();