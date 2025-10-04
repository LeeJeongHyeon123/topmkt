const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 달력 짤림 문제 해결 검증 테스트...');

  const page = await browser.newPage();
  await page.setViewportSize({ width: 1440, height: 900 });

  await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await page.goto('https://www.topmktx.com/events', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await page.waitForTimeout(2000);

  const calendarAnalysis = await page.evaluate(() => {
    const calendarView = document.querySelector('.calendar-view');
    const calendarHeader = document.querySelector('.calendar-header');
    const calendarBody = document.querySelector('.calendar-body');
    const dayHeaders = document.querySelectorAll('.day-header');

    if (!calendarView || !calendarHeader || !calendarBody) {
      return { error: '달력 요소를 찾을 수 없음' };
    }

    const calendarRect = calendarView.getBoundingClientRect();
    const headerRect = calendarHeader.getBoundingClientRect();
    const bodyRect = calendarBody.getBoundingClientRect();

    // 요일 헤더 분석
    const dayHeaderInfo = Array.from(dayHeaders).map((header, index) => {
      const rect = header.getBoundingClientRect();
      return {
        index: index,
        text: header.textContent,
        left: rect.left,
        right: rect.right,
        width: rect.width,
        visible: rect.right <= window.innerWidth && rect.left >= 0
      };
    });

    return {
      viewport: {
        width: window.innerWidth,
        height: window.innerHeight
      },
      calendar: {
        left: calendarRect.left,
        right: calendarRect.right,
        width: calendarRect.width,
        visible: calendarRect.right <= window.innerWidth
      },
      header: {
        left: headerRect.left,
        right: headerRect.right,
        width: headerRect.width,
        visible: headerRect.right <= window.innerWidth
      },
      body: {
        left: bodyRect.left,
        right: bodyRect.right,
        width: bodyRect.width,
        visible: bodyRect.right <= window.innerWidth
      },
      dayHeaders: dayHeaderInfo,
      allDaysVisible: dayHeaderInfo.every(day => day.visible),
      visibleDaysCount: dayHeaderInfo.filter(day => day.visible).length
    };
  });

  console.log('\n📊 달력 표시 분석 결과:');
  console.log('   뷰포트:', calendarAnalysis.viewport.width, 'x', calendarAnalysis.viewport.height);
  console.log('   달력 전체:', calendarAnalysis.calendar.width, 'px');
  console.log('   헤더:', calendarAnalysis.header.width, 'px');
  console.log('   바디:', calendarAnalysis.body.width, 'px');
  console.log('   표시된 요일 수:', calendarAnalysis.visibleDaysCount, '/ 7');

  console.log('\n📅 요일별 표시 상태:');
  calendarAnalysis.dayHeaders.forEach(day => {
    const status = day.visible ? '✅' : '❌';
    console.log(`   ${status} ${day.text}: ${day.left} ~ ${day.right} (${day.width}px)`);
  });

  const success = calendarAnalysis.allDaysVisible && calendarAnalysis.visibleDaysCount === 7;

  if (success) {
    console.log('\n🎉 성공! 모든 요일이 완전히 표시됩니다!');
  } else {
    console.log('\n❌ 문제: 일부 요일이 짤려서 보이지 않습니다.');
  }

  // 스크린샷 촬영
  await page.screenshot({
    path: `/var/www/html/topmkt/calendar-fix-test.png`,
    fullPage: false
  });
  console.log('   📸 스크린샷: calendar-fix-test.png');

  await page.close();
  await browser.close();

  console.log('\n🏁 달력 표시 검증 완료');
})();