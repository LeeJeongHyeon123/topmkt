const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  // 모바일 뷰포트 설정 (375px)
  await page.setViewportSize({ width: 375, height: 667 });

  console.log('🔐 1. DevLoginHelper로 우리집탄이 계정 로그인...');
  await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
  await page.waitForTimeout(2000);

  console.log('📅 2. 행사 일정 캘린더 뷰 접속...');
  await page.goto('https://www.topmktx.com/events?view=calendar');
  await page.waitForTimeout(3000);

  console.log('🔍 3. DOM 요소 확인 중...');

  // 3-1. .calendar-view 확인
  const calendarView = await page.$('.calendar-view');
  const calendarViewDisplay = calendarView ? await page.evaluate(el => getComputedStyle(el).display, calendarView) : null;
  console.log(`  - .calendar-view 존재: ${!!calendarView} / display: ${calendarViewDisplay}`);

  // 3-2. .calendar-header 확인
  const calendarHeader = await page.$('.calendar-header');
  const headerVisible = calendarHeader ? await page.evaluate(el => getComputedStyle(el).display !== 'none', calendarHeader) : false;
  console.log(`  - .calendar-header 존재: ${!!calendarHeader} / 표시됨: ${headerVisible}`);

  // 3-3. .calendar-body 확인
  const calendarBody = await page.$('.calendar-body');
  const bodyVisible = calendarBody ? await page.evaluate(el => getComputedStyle(el).display !== 'none', calendarBody) : false;
  console.log(`  - .calendar-body 존재: ${!!calendarBody} / 표시됨: ${bodyVisible}`);

  // 3-4. .calendar-day 요소들 확인
  const calendarDays = await page.$$('.calendar-day');
  console.log(`  - .calendar-day 요소 개수: ${calendarDays.length}`);

  // 3-5. .day-number 요소들 확인
  const dayNumbers = await page.$$('.day-number');
  console.log(`  - .day-number 요소 개수: ${dayNumbers.length}`);

  console.log('🎨 4. CSS 스타일 확인 중...');

  // 4-1. events-container의 overflow-x 확인
  const eventsContainer = await page.$('.events-container');
  const overflowX = eventsContainer ? await page.evaluate(el => getComputedStyle(el).overflowX, eventsContainer) : null;
  console.log(`  - events-container overflow-x: ${overflowX}`);

  // 4-2. calendar-view의 min-width 확인
  const minWidth = calendarView ? await page.evaluate(el => getComputedStyle(el).minWidth, calendarView) : null;
  console.log(`  - calendar-view min-width: ${minWidth}`);

  // 4-3. 캐시 버전 확인
  const cacheVersion = await page.evaluate(() => {
    const link = document.querySelector('link[href*="events-calendar.css"]');
    return link ? link.href : 'CSS 파일 없음';
  });
  console.log(`  - CSS 캐시 버전: ${cacheVersion}`);

  console.log('📸 5. 스크린샷 촬영...');
  await page.screenshot({
    path: '/var/www/html/topmkt/calendar-recovery-test.png',
    fullPage: true
  });

  console.log('✅ 테스트 완료!');

  // 종합 결과 판정
  const isRecovered = !!(calendarView && calendarViewDisplay === 'block' && calendarDays.length > 0 && dayNumbers.length > 0);
  console.log(`🎯 달력 복구 상태: ${isRecovered ? '✅ 복구됨' : '❌ 미복구'}`);

  await browser.close();
})().catch(console.error);