const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({
    headless: true,  // 헤드리스 모드로 실행
    slowMo: 1000
  });

  console.log('🔍 실제 브라우저 환경에서 CSS 디버깅...');

  const page = await browser.newPage();
  await page.setViewportSize({ width: 1440, height: 900 });

  await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
    waitUntil: 'networkidle', timeout: 10000
  });

  await page.goto('https://www.topmktx.com/events', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await page.waitForTimeout(3000);

  // 강제 새로고침
  await page.reload({ waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);

  const realBrowserAnalysis = await page.evaluate(() => {
    const calendarView = document.querySelector('.calendar-view');
    const calendarHeader = document.querySelector('.calendar-header');
    const calendarBody = document.querySelector('.calendar-body');
    const eventsMain = document.querySelector('.events-main');
    const dayHeaders = document.querySelectorAll('.day-header');

    if (!calendarView) return { error: 'calendar-view not found' };

    const calendarStyle = getComputedStyle(calendarView);
    const headerStyle = calendarHeader ? getComputedStyle(calendarHeader) : null;
    const bodyStyle = calendarBody ? getComputedStyle(calendarBody) : null;
    const mainStyle = eventsMain ? getComputedStyle(eventsMain) : null;

    // 요일 헤더 분석 추가
    const dayHeaderAnalysis = Array.from(dayHeaders).map((header, index) => {
      const rect = header.getBoundingClientRect();
      const style = getComputedStyle(header);
      return {
        index: index,
        text: header.textContent,
        rect: {
          left: rect.left,
          right: rect.right,
          width: rect.width,
          visible: rect.right <= window.innerWidth && rect.left >= 0
        },
        style: {
          width: style.width,
          minWidth: style.minWidth,
          maxWidth: style.maxWidth,
          display: style.display,
          gridColumn: style.gridColumn
        }
      };
    });

    return {
      viewport: {
        width: window.innerWidth,
        height: window.innerHeight
      },
      calendarView: {
        width: calendarStyle.width,
        maxWidth: calendarStyle.maxWidth,
        minWidth: calendarStyle.minWidth,
        offsetWidth: calendarView.offsetWidth,
        clientWidth: calendarView.clientWidth,
        scrollWidth: calendarView.scrollWidth,
        rect: calendarView.getBoundingClientRect()
      },
      calendarHeader: headerStyle ? {
        width: headerStyle.width,
        maxWidth: headerStyle.maxWidth,
        minWidth: headerStyle.minWidth,
        offsetWidth: calendarHeader.offsetWidth,
        rect: calendarHeader.getBoundingClientRect(),
        gridTemplateColumns: headerStyle.gridTemplateColumns
      } : null,
      calendarBody: bodyStyle ? {
        width: bodyStyle.width,
        maxWidth: bodyStyle.maxWidth,
        minWidth: bodyStyle.minWidth,
        offsetWidth: calendarBody.offsetWidth,
        rect: calendarBody.getBoundingClientRect()
      } : null,
      eventsMain: mainStyle ? {
        width: mainStyle.width,
        maxWidth: mainStyle.maxWidth,
        minWidth: mainStyle.minWidth,
        offsetWidth: eventsMain.offsetWidth,
        rect: eventsMain.getBoundingClientRect()
      } : null,
      dayHeaders: dayHeaderAnalysis,
      dayHeadersCount: dayHeaders.length,
      allDayHeadersVisible: dayHeaderAnalysis.every(day => day.rect.visible)
    };
  });

  console.log('\n🖥️ 실제 브라우저 환경 분석:');
  console.log('   뷰포트:', realBrowserAnalysis.viewport.width, 'x', realBrowserAnalysis.viewport.height);

  if (realBrowserAnalysis.eventsMain) {
    console.log('   Events Main:', realBrowserAnalysis.eventsMain.offsetWidth, 'px');
    console.log('     - computed width:', realBrowserAnalysis.eventsMain.width);
    console.log('     - max-width:', realBrowserAnalysis.eventsMain.maxWidth);
  }

  console.log('   Calendar View:', realBrowserAnalysis.calendarView.offsetWidth, 'px');
  console.log('     - computed width:', realBrowserAnalysis.calendarView.width);
  console.log('     - max-width:', realBrowserAnalysis.calendarView.maxWidth);
  console.log('     - min-width:', realBrowserAnalysis.calendarView.minWidth);

  if (realBrowserAnalysis.calendarHeader) {
    console.log('   Calendar Header:', realBrowserAnalysis.calendarHeader.offsetWidth, 'px');
    console.log('     - computed width:', realBrowserAnalysis.calendarHeader.width);
    console.log('     - min-width:', realBrowserAnalysis.calendarHeader.minWidth);
    console.log('     - grid-template-columns:', realBrowserAnalysis.calendarHeader.gridTemplateColumns);
  }

  console.log('\n📅 요일 헤더 상세 분석:');
  console.log('   총 요일 헤더 수:', realBrowserAnalysis.dayHeadersCount);
  console.log('   모든 요일 표시 여부:', realBrowserAnalysis.allDayHeadersVisible);

  if (realBrowserAnalysis.dayHeaders) {
    realBrowserAnalysis.dayHeaders.forEach((header, index) => {
      const status = header.rect.visible ? '✅' : '❌';
      console.log(`   ${status} ${header.text || `헤더${index+1}`}: ${header.rect.left.toFixed(1)} ~ ${header.rect.right.toFixed(1)} (폭: ${header.rect.width.toFixed(1)}px)`);
      console.log(`      - computed width: ${header.style.width}`);
      console.log(`      - display: ${header.style.display}`);
    });
  }

  // 스크린샷 촬영
  await page.screenshot({
    path: `/var/www/html/topmkt/real-browser-debug.png`,
    fullPage: false
  });
  console.log('   📸 실제 브라우저 스크린샷: real-browser-debug.png');

  // 3초 대기 후 자동 종료
  await page.waitForTimeout(3000);
  await browser.close();

  console.log('\n🏁 실제 브라우저 디버깅 완료');
})();