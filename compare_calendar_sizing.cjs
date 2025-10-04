const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 강의 일정 vs 행사 일정 캘린더 크기 비교...');

  // 강의 일정 페이지 분석
  const lecturesPage = await browser.newPage();
  await lecturesPage.setViewportSize({ width: 1440, height: 900 });

  await lecturesPage.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await lecturesPage.goto('https://www.topmktx.com/lectures', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await lecturesPage.waitForTimeout(2000);

  const lecturesAnalysis = await lecturesPage.evaluate(() => {
    const eventsMain = document.querySelector('.lectures-main') || document.querySelector('.events-main');
    const calendarView = document.querySelector('.calendar-view');
    const calendarHeader = document.querySelector('.calendar-header');
    const dayHeaders = document.querySelectorAll('.day-header');

    return {
      eventsMain: eventsMain ? {
        offsetWidth: eventsMain.offsetWidth,
        computedWidth: getComputedStyle(eventsMain).width
      } : null,
      calendarView: calendarView ? {
        offsetWidth: calendarView.offsetWidth,
        computedWidth: getComputedStyle(calendarView).width
      } : null,
      calendarHeader: calendarHeader ? {
        offsetWidth: calendarHeader.offsetWidth,
        computedWidth: getComputedStyle(calendarHeader).width,
        gridTemplateColumns: getComputedStyle(calendarHeader).gridTemplateColumns
      } : null,
      dayHeadersCount: dayHeaders.length,
      dayHeaderWidths: Array.from(dayHeaders).map(h => h.getBoundingClientRect().width)
    };
  });

  // 행사 일정 페이지 분석
  const eventsPage = await browser.newPage();
  await eventsPage.setViewportSize({ width: 1440, height: 900 });

  await eventsPage.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await eventsPage.goto('https://www.topmktx.com/events', {
    waitUntil: 'networkidle', timeout: 10000
  });
  await eventsPage.waitForTimeout(2000);

  const eventsAnalysis = await eventsPage.evaluate(() => {
    const eventsMain = document.querySelector('.events-main');
    const calendarView = document.querySelector('.calendar-view');
    const calendarHeader = document.querySelector('.calendar-header');
    const dayHeaders = document.querySelectorAll('.day-header');

    return {
      eventsMain: eventsMain ? {
        offsetWidth: eventsMain.offsetWidth,
        computedWidth: getComputedStyle(eventsMain).width
      } : null,
      calendarView: calendarView ? {
        offsetWidth: calendarView.offsetWidth,
        computedWidth: getComputedStyle(calendarView).width
      } : null,
      calendarHeader: calendarHeader ? {
        offsetWidth: calendarHeader.offsetWidth,
        computedWidth: getComputedStyle(calendarHeader).width,
        gridTemplateColumns: getComputedStyle(calendarHeader).gridTemplateColumns
      } : null,
      dayHeadersCount: dayHeaders.length,
      dayHeaderWidths: Array.from(dayHeaders).map(h => h.getBoundingClientRect().width)
    };
  });

  console.log('\n📊 강의 일정 페이지 분석:');
  console.log('   Events Main:', lecturesAnalysis.eventsMain?.offsetWidth, 'px');
  console.log('   Calendar View:', lecturesAnalysis.calendarView?.offsetWidth, 'px');
  console.log('   Calendar Header:', lecturesAnalysis.calendarHeader?.offsetWidth, 'px');
  console.log('   Grid Template Columns:', lecturesAnalysis.calendarHeader?.gridTemplateColumns);
  console.log('   요일 헤더 수:', lecturesAnalysis.dayHeadersCount);
  console.log('   요일 헤더 폭:', lecturesAnalysis.dayHeaderWidths?.map(w => w.toFixed(1)).join(', '));

  console.log('\n📊 행사 일정 페이지 분석:');
  console.log('   Events Main:', eventsAnalysis.eventsMain?.offsetWidth, 'px');
  console.log('   Calendar View:', eventsAnalysis.calendarView?.offsetWidth, 'px');
  console.log('   Calendar Header:', eventsAnalysis.calendarHeader?.offsetWidth, 'px');
  console.log('   Grid Template Columns:', eventsAnalysis.calendarHeader?.gridTemplateColumns);
  console.log('   요일 헤더 수:', eventsAnalysis.dayHeadersCount);
  console.log('   요일 헤더 폭:', eventsAnalysis.dayHeaderWidths?.map(w => w.toFixed(1)).join(', '));

  // 차이점 분석
  console.log('\n🔍 차이점 분석:');
  const eventMainDiff = eventsAnalysis.eventsMain?.offsetWidth - lecturesAnalysis.eventsMain?.offsetWidth;
  const calendarViewDiff = eventsAnalysis.calendarView?.offsetWidth - lecturesAnalysis.calendarView?.offsetWidth;
  const calendarHeaderDiff = eventsAnalysis.calendarHeader?.offsetWidth - lecturesAnalysis.calendarHeader?.offsetWidth;

  console.log('   Events Main 차이:', eventMainDiff, 'px');
  console.log('   Calendar View 차이:', calendarViewDiff, 'px');
  console.log('   Calendar Header 차이:', calendarHeaderDiff, 'px');

  // 스크린샷 촬영
  await lecturesPage.screenshot({
    path: `/var/www/html/topmkt/lectures-calendar-comparison.png`,
    fullPage: false
  });

  await eventsPage.screenshot({
    path: `/var/www/html/topmkt/events-calendar-comparison.png`,
    fullPage: false
  });

  await browser.close();
  console.log('\n🏁 비교 분석 완료');
})();