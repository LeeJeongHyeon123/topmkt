const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  console.log('🔍 행사 일정 페이지 레이아웃 진단 시작...');

  try {
    // 1. DevLoginHelper로 로그인
    console.log('1️⃣ DevLoginHelper로 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    await page.waitForTimeout(2000);
    console.log('   ✅ 로그인 완료');

    // 2. 행사 일정 페이지로 이동
    console.log('2️⃣ 행사 일정 페이지로 이동...');
    await page.goto('https://www.topmktx.com/events', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    await page.waitForTimeout(3000);
    console.log('   ✅ 행사 일정 페이지 로드 완료');

    // 3. 레이아웃 요소들 분석
    console.log('3️⃣ 레이아웃 요소 분석...');

    const layoutAnalysis = await page.evaluate(() => {
      const container = document.querySelector('.events-container');
      const layout = document.querySelector('.events-layout');
      const main = document.querySelector('.events-main');
      const sidebar = document.querySelector('.events-sidebar');
      const calendarView = document.querySelector('.calendar-view');

      return {
        container: container ? {
          width: container.offsetWidth,
          maxWidth: getComputedStyle(container).maxWidth,
          display: getComputedStyle(container).display,
          padding: getComputedStyle(container).padding
        } : null,
        layout: layout ? {
          width: layout.offsetWidth,
          display: getComputedStyle(layout).display,
          gridTemplateColumns: getComputedStyle(layout).gridTemplateColumns,
          gap: getComputedStyle(layout).gap,
          position: getComputedStyle(layout).position
        } : null,
        main: main ? {
          width: main.offsetWidth,
          gridColumn: getComputedStyle(main).gridColumn,
          position: getComputedStyle(main).position,
          zIndex: getComputedStyle(main).zIndex
        } : null,
        sidebar: sidebar ? {
          width: sidebar.offsetWidth,
          maxWidth: getComputedStyle(sidebar).maxWidth,
          gridColumn: getComputedStyle(sidebar).gridColumn,
          position: getComputedStyle(sidebar).position,
          zIndex: getComputedStyle(sidebar).zIndex,
          left: sidebar.getBoundingClientRect().left,
          right: sidebar.getBoundingClientRect().right
        } : null,
        calendarView: calendarView ? {
          width: calendarView.offsetWidth,
          left: calendarView.getBoundingClientRect().left,
          right: calendarView.getBoundingClientRect().right
        } : null,
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight
        }
      };
    });

    console.log('\n📊 레이아웃 분석 결과:');
    console.log('   📦 Container:', JSON.stringify(layoutAnalysis.container, null, 2));
    console.log('   🏗️ Layout:', JSON.stringify(layoutAnalysis.layout, null, 2));
    console.log('   📄 Main:', JSON.stringify(layoutAnalysis.main, null, 2));
    console.log('   📋 Sidebar:', JSON.stringify(layoutAnalysis.sidebar, null, 2));
    console.log('   📅 Calendar View:', JSON.stringify(layoutAnalysis.calendarView, null, 2));
    console.log('   💻 Viewport:', JSON.stringify(layoutAnalysis.viewport, null, 2));

    // 4. 겹침 문제 확인
    console.log('\n4️⃣ 겹침 문제 분석...');

    const overlapAnalysis = await page.evaluate(() => {
      const sidebar = document.querySelector('.events-sidebar');
      const calendarView = document.querySelector('.calendar-view');

      if (!sidebar || !calendarView) {
        return { error: '사이드바 또는 캘린더 뷰를 찾을 수 없음' };
      }

      const sidebarRect = sidebar.getBoundingClientRect();
      const calendarRect = calendarView.getBoundingClientRect();

      const isOverlapping = !(sidebarRect.right <= calendarRect.left ||
                              sidebarRect.left >= calendarRect.right ||
                              sidebarRect.bottom <= calendarRect.top ||
                              sidebarRect.top >= calendarRect.bottom);

      return {
        isOverlapping,
        sidebar: {
          left: sidebarRect.left,
          right: sidebarRect.right,
          top: sidebarRect.top,
          bottom: sidebarRect.bottom,
          width: sidebarRect.width,
          height: sidebarRect.height
        },
        calendar: {
          left: calendarRect.left,
          right: calendarRect.right,
          top: calendarRect.top,
          bottom: calendarRect.bottom,
          width: calendarRect.width,
          height: calendarRect.height
        },
        gap: sidebarRect.left - calendarRect.right
      };
    });

    console.log('   🔍 겹침 분석:', JSON.stringify(overlapAnalysis, null, 2));

    // 5. 스크린샷 촬영
    await page.screenshot({
      path: '/var/www/html/topmkt/events-layout-debug.png',
      fullPage: true
    });
    console.log('   📸 디버그 스크린샷: events-layout-debug.png');

    // 6. 문제 진단 결과
    console.log('\n5️⃣ 문제 진단 결과:');

    if (overlapAnalysis.isOverlapping) {
      console.log('   ❌ 사이드바와 캘린더가 겹침 감지!');
      console.log(`   📐 갭: ${overlapAnalysis.gap}px (음수면 겹침)`);
    } else {
      console.log('   ✅ 사이드바와 캘린더 겹침 없음');
      console.log(`   📐 갭: ${overlapAnalysis.gap}px`);
    }

    if (layoutAnalysis.layout && layoutAnalysis.layout.display !== 'grid') {
      console.log('   ❌ Grid 레이아웃이 적용되지 않음!');
    } else {
      console.log('   ✅ Grid 레이아웃 적용됨');
    }

  } catch (error) {
    console.error('❌ 분석 실패:', error.message);

    await page.screenshot({
      path: '/var/www/html/topmkt/events-layout-error.png',
      fullPage: true
    });
    console.log('📸 오류 스크린샷: events-layout-error.png');
  }

  await browser.close();
  console.log('\n🏁 레이아웃 진단 완료');
})();