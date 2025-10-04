const { chromium } = require('playwright');

(async () => {
  console.log('🎯 직접 브라우저 접속 (캐시 완전 무시)...');

  const browser = await chromium.launch({
    headless: true,
    args: ['--disable-cache', '--disable-application-cache', '--disable-offline-load-stale-cache']
  });

  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 }
  });

  const page = await context.newPage();

  try {
    // 강력한 캐시 무시
    await page.setExtraHTTPHeaders({
      'Cache-Control': 'no-cache, no-store, must-revalidate, max-age=0',
      'Pragma': 'no-cache',
      'Expires': '0'
    });

    // 완전 새로운 타임스탬프로 접속
    const timestamp = Date.now();
    await page.goto(`https://www.topmktx.com/events?year=2025&month=9&view=calendar&nocache=${timestamp}&rand=${Math.random()}`, {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    // 강제 새로고침
    await page.reload({ waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);

    // 현재 HTML 소스 확인
    const htmlAnalysis = await page.evaluate(() => {
      const sidebar = document.querySelector('.events-sidebar');
      if (!sidebar) return { error: 'Sidebar not found' };

      const style = sidebar.getAttribute('style') || '';
      const computedStyle = window.getComputedStyle(sidebar);
      const rect = sidebar.getBoundingClientRect();

      return {
        inlineStyle: style,
        position: computedStyle.position,
        top: computedStyle.top,
        right: computedStyle.right,
        left: computedStyle.left,
        width: computedStyle.width,
        zIndex: computedStyle.zIndex,
        rect: {
          x: rect.x,
          y: rect.y,
          width: rect.width,
          height: rect.height,
          right: rect.right
        },
        viewport: window.innerWidth,
        visible: rect.right > 0 && rect.left < window.innerWidth
      };
    });

    console.log('\n🔍 직접 접속 분석 결과:');
    console.log('=========================');

    if (htmlAnalysis.error) {
      console.log('❌ 오류:', htmlAnalysis.error);
    } else {
      console.log('📝 인라인 스타일:', htmlAnalysis.inlineStyle);
      console.log('📐 Position:', htmlAnalysis.position);
      console.log('📐 Top:', htmlAnalysis.top);
      console.log('📐 Right:', htmlAnalysis.right);
      console.log('📐 Left:', htmlAnalysis.left);
      console.log('📐 Width:', htmlAnalysis.width);
      console.log('📐 Z-Index:', htmlAnalysis.zIndex);
      console.log('📊 실제 위치:', `${Math.round(htmlAnalysis.rect.x)} ~ ${Math.round(htmlAnalysis.rect.right)}px`);
      console.log('📱 Viewport:', `${htmlAnalysis.viewport}px`);
      console.log('👁️ 가시성:', htmlAnalysis.visible ? '✅ 보임' : '❌ 안보임');

      // 성공한 경우 스크린샷
      if (htmlAnalysis.visible) {
        await page.screenshot({
          path: '/var/www/html/topmkt/success-direct-browser.png',
          fullPage: true
        });
        console.log('📸 성공 스크린샷: success-direct-browser.png');
      } else {
        await page.screenshot({
          path: '/var/www/html/topmkt/failed-direct-browser.png',
          fullPage: true
        });
        console.log('📸 실패 스크린샷: failed-direct-browser.png');
      }
    }

  } catch (error) {
    console.error('❌ 직접 접속 실패:', error.message);
  } finally {
    await browser.close();
  }
})();