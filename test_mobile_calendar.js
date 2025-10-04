import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  // 모바일 뷰포트 설정 (iPhone SE)
  await page.setViewportSize({ width: 375, height: 667 });

  console.log('1. 우리집탄이 계정으로 로그인 중...');
  await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
  await page.waitForTimeout(2000);

  console.log('2. 행사 일정 캘린더 뷰 접속 중...');
  await page.goto('https://www.topmktx.com/events?view=calendar');
  await page.waitForTimeout(3000);

  console.log('3. 달력 요소들 검사 중...');

  // .calendar-view 요소 확인
  const calendarView = await page.$('.calendar-view');
  if (calendarView) {
    const display = await page.evaluate(el => window.getComputedStyle(el).display, calendarView);
    const visibility = await page.evaluate(el => window.getComputedStyle(el).visibility, calendarView);
    const opacity = await page.evaluate(el => window.getComputedStyle(el).opacity, calendarView);
    const width = await page.evaluate(el => window.getComputedStyle(el).width, calendarView);
    const minWidth = await page.evaluate(el => window.getComputedStyle(el).minWidth, calendarView);

    console.log('✅ .calendar-view 요소 발견');
    console.log('   - display:', display);
    console.log('   - visibility:', visibility);
    console.log('   - opacity:', opacity);
    console.log('   - width:', width);
    console.log('   - min-width:', minWidth);
  } else {
    console.log('❌ .calendar-view 요소 없음');
  }

  // .calendar-header 확인
  const calendarHeader = await page.$('.calendar-header');
  console.log(calendarHeader ? '✅ .calendar-header 요소 발견' : '❌ .calendar-header 요소 없음');

  // .calendar-body 확인
  const calendarBody = await page.$('.calendar-body');
  console.log(calendarBody ? '✅ .calendar-body 요소 발견' : '❌ .calendar-body 요소 없음');

  // .calendar-day 요소들 확인
  const calendarDays = await page.$$('.calendar-day');
  console.log(`📅 .calendar-day 요소 개수: ${calendarDays.length}개`);

  // 스크린샷 촬영
  console.log('4. 스크린샷 촬영 중...');
  await page.screenshot({
    path: '/var/www/html/topmkt/mobile-calendar-test.png',
    fullPage: true
  });

  console.log('✅ 테스트 완료! 스크린샷: mobile-calendar-test.png');

  await browser.close();
})();