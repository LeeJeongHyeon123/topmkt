const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 실제 사용자 환경 검증 시작...');

    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    console.log('✅ 페이지 로딩 완료');

    // 전체 페이지 스크린샷 (실제 브라우저 크기)
    await page.screenshot({
      path: 'real-user-test.png',
      fullPage: true
    });

    // PC 사이즈에서 사이드바 상태 확인
    const realStatus = await page.evaluate(() => {
      const sidebar = document.querySelector('.events-sidebar');
      const layout = document.querySelector('.events-layout');

      if (!sidebar || !layout) {
        return { error: 'Elements not found' };
      }

      const sidebarRect = sidebar.getBoundingClientRect();
      const layoutRect = layout.getBoundingClientRect();
      const viewportWidth = window.innerWidth;

      // 사이드바 내용 확인
      const todaySection = sidebar.querySelector('.sidebar-section:first-child h3');
      const upcomingSection = sidebar.querySelector('.sidebar-section:last-child h3');

      return {
        timestamp: new Date().toLocaleString('ko-KR'),
        viewport: { width: viewportWidth, height: window.innerHeight },
        sidebar: {
          exists: true,
          visible: sidebarRect.x >= 0 && sidebarRect.x + sidebarRect.width <= viewportWidth,
          position: { x: sidebarRect.x, y: sidebarRect.y },
          size: { width: sidebarRect.width, height: sidebarRect.height },
          computedWidth: window.getComputedStyle(sidebar).width,
          display: window.getComputedStyle(sidebar).display,
          visibility: window.getComputedStyle(sidebar).visibility
        },
        layout: {
          position: { x: layoutRect.x, y: layoutRect.y },
          size: { width: layoutRect.width, height: layoutRect.height },
          gridColumns: window.getComputedStyle(layout).gridTemplateColumns
        },
        content: {
          todayTitle: todaySection ? todaySection.textContent : 'not found',
          upcomingTitle: upcomingSection ? upcomingSection.textContent : 'not found',
          sidebarHTML: sidebar.innerHTML.substring(0, 200) + '...'
        },
        userView: {
          canSeeSidebar: sidebarRect.x >= 0 && sidebarRect.x + sidebarRect.width <= viewportWidth,
          sidebarRightEdge: sidebarRect.x + sidebarRect.width,
          viewportRightEdge: viewportWidth,
          isOverflowing: sidebarRect.x + sidebarRect.width > viewportWidth
        }
      };
    });

    console.log('📊 실제 사용자 환경 검증 결과:');
    console.log(JSON.stringify(realStatus, null, 2));

    console.log('\n🎯 사용자가 실제로 보는 것:');
    if (realStatus.userView) {
      console.log(`사이드바 보임: ${realStatus.userView.canSeeSidebar ? 'YES' : 'NO'}`);
      console.log(`사이드바 위치: x=${realStatus.sidebar.position.x}`);
      console.log(`사이드바 오른쪽 끝: ${realStatus.userView.sidebarRightEdge}px`);
      console.log(`화면 오른쪽 끝: ${realStatus.userView.viewportRightEdge}px`);
      console.log(`오버플로우 상태: ${realStatus.userView.isOverflowing ? 'YES (화면 밖)' : 'NO (화면 안)'}`);
    }

    console.log('\n📝 사이드바 내용:');
    console.log(`오늘의 행사 제목: "${realStatus.content.todayTitle}"`);
    console.log(`다가오는 행사 제목: "${realStatus.content.upcomingTitle}"`);

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
    console.log('\n✅ 실제 사용자 환경 검증 완료. real-user-test.png 생성됨');
  }
})();