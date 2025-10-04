const { chromium } = require('playwright');

(async () => {
  console.log('🎯 간단한 Grid 테스트 시작...');

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    // 직접 URL 접근
    await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    // 페이지 로드 대기
    await page.waitForTimeout(3000);

    // 사이드바 요소 확인
    const elementCheck = await page.evaluate(() => {
      const layout = document.querySelector('.events-layout');
      const sidebar = document.querySelector('.events-sidebar');
      const main = document.querySelector('.events-main');

      return {
        layout_exists: !!layout,
        sidebar_exists: !!sidebar,
        main_exists: !!main,
        layout_html: layout ? layout.outerHTML.substring(0, 200) + '...' : null,
        page_title: document.title,
        body_classes: document.body.className
      };
    });

    console.log('\n📋 요소 존재 확인:');
    console.log(`- Layout 존재: ${elementCheck.layout_exists}`);
    console.log(`- Sidebar 존재: ${elementCheck.sidebar_exists}`);
    console.log(`- Main 존재: ${elementCheck.main_exists}`);
    console.log(`- Page Title: ${elementCheck.page_title}`);
    console.log(`- Body Classes: ${elementCheck.body_classes}`);

    if (elementCheck.layout_html) {
      console.log(`\n📄 Layout HTML (일부): ${elementCheck.layout_html}`);
    }

    // 스크린샷 촬영
    await page.screenshot({
      path: '/var/www/html/topmkt/simple-grid-test.png',
      fullPage: true
    });

    console.log('\n📸 스크린샷 저장: /var/www/html/topmkt/simple-grid-test.png');

    // 현재 Grid 상태 확인 (요소가 존재할 때만)
    if (elementCheck.sidebar_exists && elementCheck.main_exists) {
      const gridStatus = await page.evaluate(() => {
        const sidebar = document.querySelector('.events-sidebar');
        const main = document.querySelector('.events-main');

        const sidebarRect = sidebar.getBoundingClientRect();
        const mainRect = main.getBoundingClientRect();

        return {
          sidebar: { x: sidebarRect.x, y: sidebarRect.y, width: sidebarRect.width, height: sidebarRect.height },
          main: { x: mainRect.x, y: mainRect.y, width: mainRect.width, height: mainRect.height },
          overlap: sidebarRect.x < (mainRect.x + mainRect.width) && (sidebarRect.x + sidebarRect.width) > mainRect.x
        };
      });

      console.log('\n📐 위치 정보:');
      console.log(`- Sidebar 위치: (${gridStatus.sidebar.x}, ${gridStatus.sidebar.y}), 크기: ${gridStatus.sidebar.width} x ${gridStatus.sidebar.height}`);
      console.log(`- Main 위치: (${gridStatus.main.x}, ${gridStatus.main.y}), 크기: ${gridStatus.main.width} x ${gridStatus.main.height}`);
      console.log(`- 겹침 여부: ${gridStatus.overlap ? '❌ 겹침 발생' : '✅ 분리됨'}`);
    }

  } catch (error) {
    console.error('❌ 테스트 실패:', error.message);
  } finally {
    await browser.close();
  }
})();