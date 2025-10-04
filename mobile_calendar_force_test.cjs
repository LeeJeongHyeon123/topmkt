const { chromium } = require('playwright');

(async () => {
  console.log('🚀 모바일 달력 강제 표시 테스트 시작...');

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 375, height: 667 }, // iPhone SE 크기
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
  });

  const page = await context.newPage();

  // 콘솔 메시지 캡처
  const consoleMessages = [];
  page.on('console', msg => {
    consoleMessages.push(`[${msg.type()}] ${msg.text()}`);
  });

  try {
    console.log('1️⃣ DevLoginHelper로 우리집탄이 계정 로그인...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 30000
    });
    await page.waitForTimeout(2000);

    console.log('2️⃣ 행사 일정 캘린더 뷰 접속...');
    await page.goto('https://www.topmktx.com/events?view=calendar', {
      waitUntil: 'networkidle',
      timeout: 30000
    });
    await page.waitForTimeout(3000);

    console.log('3️⃣ DOM 요소 및 CSS 확인...');

    // body data-view 속성 확인
    const bodyDataView = await page.getAttribute('body', 'data-view');
    console.log('📋 body data-view 속성:', bodyDataView);

    // CSS 스타일 확인
    const calendarViewDisplay = await page.evaluate(() => {
      const element = document.querySelector('.calendar-view');
      return element ? window.getComputedStyle(element).display : 'element not found';
    });
    console.log('📋 .calendar-view display:', calendarViewDisplay);

    const listViewDisplay = await page.evaluate(() => {
      const element = document.querySelector('.list-view');
      return element ? window.getComputedStyle(element).display : 'element not found';
    });
    console.log('📋 .list-view display:', listViewDisplay);

    // 달력 테이블 확인
    const calendarTable = await page.evaluate(() => {
      const table = document.querySelector('.calendar-view table');
      if (!table) return 'table not found';

      const rect = table.getBoundingClientRect();
      return {
        visible: rect.width > 0 && rect.height > 0,
        width: rect.width,
        height: rect.height,
        display: window.getComputedStyle(table).display
      };
    });
    console.log('📋 달력 테이블 상태:', JSON.stringify(calendarTable, null, 2));

    // 현재 월 헤더 확인
    const monthHeader = await page.textContent('.month-year');
    console.log('📅 현재 월 헤더:', monthHeader);

    // CSS 선택자 body[data-view="calendar"] .calendar-view 확인
    const specificSelector = await page.evaluate(() => {
      const bodyView = document.body.getAttribute('data-view');
      const element = document.querySelector('body[data-view="calendar"] .calendar-view');
      const computedStyle = element ? window.getComputedStyle(element) : null;

      return {
        bodyDataView: bodyView,
        selectorExists: !!element,
        display: computedStyle ? computedStyle.display : null,
        visibility: computedStyle ? computedStyle.visibility : null
      };
    });
    console.log('🔍 CSS 선택자 확인:', JSON.stringify(specificSelector, null, 2));

    // 콘솔 메시지 출력
    console.log('\\n📝 콘솔 메시지:');
    consoleMessages.forEach(msg => console.log(msg));

    console.log('\\n4️⃣ 스크린샷 촬영...');
    await page.screenshot({
      path: '/var/www/html/topmkt/mobile-calendar-force-test.png',
      fullPage: true
    });

    console.log('✅ 테스트 완료!');

    // 결과 요약
    console.log('\\n📊 테스트 결과 요약:');
    console.log('- data-view 속성:', bodyDataView);
    console.log('- .calendar-view display:', calendarViewDisplay);
    console.log('- .list-view display:', listViewDisplay);
    console.log('- 달력 테이블 상태:', JSON.stringify(calendarTable));
    console.log('- CSS 선택자 확인:', JSON.stringify(specificSelector));
    console.log('- 스크린샷: mobile-calendar-force-test.png 저장됨');

  } catch (error) {
    console.error('❌ 테스트 중 오류:', error.message);
  } finally {
    await browser.close();
  }
})();