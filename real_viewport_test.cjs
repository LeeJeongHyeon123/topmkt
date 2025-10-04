const { chromium } = require('playwright');

(async () => {
  console.log('🎯 실제 Viewport 환경 테스트...');

  const browser = await chromium.launch({ headless: true });

  // 다양한 viewport 크기로 테스트
  const viewports = [
    { width: 1280, height: 720, name: '1280px' },
    { width: 1366, height: 768, name: '1366px' },
    { width: 1440, height: 900, name: '1440px' },
    { width: 1920, height: 1080, name: '1920px' }
  ];

  for (const viewport of viewports) {
    console.log(`\n🔍 ${viewport.name} 환경 테스트:`);
    console.log('===============================');

    const page = await browser.newPage({ viewport });

    try {
      await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar&_=' + Date.now(), {
        waitUntil: 'networkidle',
        timeout: 10000
      });

      await page.waitForTimeout(2000);

      const analysis = await page.evaluate(() => {
        const sidebar = document.querySelector('.events-sidebar');
        const layout = document.querySelector('.events-layout');

        if (!sidebar || !layout) return { error: 'Elements not found' };

        const sidebarRect = sidebar.getBoundingClientRect();
        const layoutRect = layout.getBoundingClientRect();
        const viewportWidth = window.innerWidth;

        return {
          viewport: viewportWidth,
          layout: {
            width: layoutRect.width,
            maxWidth: window.getComputedStyle(layout).maxWidth
          },
          sidebar: {
            x: sidebarRect.x,
            right: sidebarRect.right,
            width: sidebarRect.width
          },
          visibility: {
            visible: sidebarRect.right > 0 && sidebarRect.left < viewportWidth,
            fullyInView: sidebarRect.left >= 0 && sidebarRect.right <= viewportWidth
          }
        };
      });

      if (analysis.error) {
        console.log('❌ 오류:', analysis.error);
      } else {
        console.log(`📱 Viewport: ${analysis.viewport}px`);
        console.log(`📐 Layout: ${analysis.layout.width}px (max: ${analysis.layout.maxWidth})`);
        console.log(`📋 Sidebar: ${analysis.sidebar.x}px ~ ${analysis.sidebar.right}px`);
        console.log(`🎯 가시성: ${analysis.visibility.visible ? '✅ 보임' : '❌ 안보임'}`);
        console.log(`🎯 완전 표시: ${analysis.visibility.fullyInView ? '✅ 완전' : '❌ 잘림'}`);

        // 성공한 경우 스크린샷 저장
        if (analysis.visibility.visible) {
          await page.screenshot({
            path: `/var/www/html/topmkt/success-${viewport.name}-sidebar.png`,
            fullPage: true
          });
          console.log(`📸 성공 스크린샷: success-${viewport.name}-sidebar.png`);
        }
      }

    } catch (error) {
      console.log('❌ 테스트 실패:', error.message);
    } finally {
      await page.close();
    }
  }

  await browser.close();
  console.log('\n🎉 모든 viewport 테스트 완료!');
})();