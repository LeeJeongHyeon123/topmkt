const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('🔍 실제 네비게이션 상태 증명 캡처 시작...');

    // DevLoginHelper로 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    console.log('✅ 로그인 완료');

    // 행사 일정 페이지로 이동
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    console.log('✅ 행사 일정 페이지 로딩 완료');

    // 네비게이션 영역만 정확히 캡처
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const filename = `navigation_proof_${timestamp}.png`;

    await page.screenshot({
      path: filename,
      fullPage: false,
      clip: { x: 0, y: 0, width: 1400, height: 200 }
    });

    console.log(`📸 캡처 완료: ${filename}`);

    // 네비게이션 상태 상세 분석
    const navDetails = await page.evaluate(() => {
      const nav = document.querySelector('.main-nav');
      const menu = document.querySelector('.nav-menu');
      const menuItems = document.querySelectorAll('.nav-menu a');

      const navRect = nav ? nav.getBoundingClientRect() : null;
      const menuRect = menu ? menu.getBoundingClientRect() : null;

      return {
        현재시간: new Date().toLocaleString('ko-KR'),
        URL: window.location.href,
        네비게이션존재: !!nav,
        메뉴존재: !!menu,
        메뉴항목수: menuItems.length,
        메뉴텍스트: Array.from(menuItems).map(a => a.textContent.trim()),
        네비게이션위치: navRect ? { x: navRect.x, y: navRect.y, width: navRect.width, height: navRect.height } : null,
        메뉴위치: menuRect ? { x: menuRect.x, y: menuRect.y, width: menuRect.width, height: menuRect.height } : null,
        화면크기: { width: window.innerWidth, height: window.innerHeight }
      };
    });

    console.log('\n📊 네비게이션 상세 분석 결과:');
    console.log(JSON.stringify(navDetails, null, 2));

    console.log(`\n🎯 캡처 파일 경로: /var/www/html/topmkt/${filename}`);

  } catch (error) {
    console.error('❌ 오류 발생:', error.message);
  } finally {
    await browser.close();
  }
})();