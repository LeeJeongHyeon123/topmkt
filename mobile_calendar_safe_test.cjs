const { chromium } = require('playwright');

(async () => {
  console.log('🚀 모바일 달력 안전 테스트 시작...');

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
    await page.waitForTimeout(5000);

    console.log('3️⃣ DOM 요소 및 CSS 확인...');

    // 포괄적 요소 확인
    const analysis = await page.evaluate(() => {
      // body data-view 속성
      const bodyDataView = document.body.getAttribute('data-view');

      // 캘린더 뷰 요소들
      const calendarView = document.querySelector('.calendar-view');
      const listView = document.querySelector('.list-view');
      const calendarTable = document.querySelector('.calendar-view table');
      const monthYear = document.querySelector('.month-year');

      // CSS 스타일 정보
      const calendarViewStyle = calendarView ? {
        display: window.getComputedStyle(calendarView).display,
        visibility: window.getComputedStyle(calendarView).visibility,
        opacity: window.getComputedStyle(calendarView).opacity,
        height: window.getComputedStyle(calendarView).height
      } : null;

      // 페이지 정보
      const pageTitle = document.title;
      const currentUrl = window.location.href;

      // 모든 CSS 클래스 확인
      const allElements = document.querySelectorAll('*');
      const calendarRelatedClasses = [];
      allElements.forEach(el => {
        if (el.className && typeof el.className === 'string') {
          const classes = el.className.split(' ');
          classes.forEach(cls => {
            if (cls.includes('calendar') || cls.includes('month') || cls.includes('view')) {
              calendarRelatedClasses.push(cls);
            }
          });
        }
      });

      return {
        bodyDataView,
        calendarView: !!calendarView,
        listView: !!listView,
        calendarTable: !!calendarTable,
        monthYear: !!monthYear,
        monthYearText: monthYear ? monthYear.textContent : null,
        calendarViewStyle,
        pageTitle,
        currentUrl,
        calendarRelatedClasses: [...new Set(calendarRelatedClasses)]
      };
    });

    console.log('📊 분석 결과:', JSON.stringify(analysis, null, 2));

    // 특정 CSS 선택자들 확인
    const cssTests = await page.evaluate(() => {
      const tests = [];

      // CSS 선택자들 테스트
      const selectors = [
        'body[data-view="calendar"]',
        'body[data-view="calendar"] .calendar-view',
        '.calendar-view',
        '.list-view',
        '.calendar-table',
        'table.calendar-table',
        '.month-year'
      ];

      selectors.forEach(selector => {
        const element = document.querySelector(selector);
        const result = {
          selector,
          exists: !!element,
          display: null,
          visibility: null
        };

        if (element) {
          const style = window.getComputedStyle(element);
          result.display = style.display;
          result.visibility = style.visibility;
        }

        tests.push(result);
      });

      return tests;
    });

    console.log('🔍 CSS 선택자 테스트:', JSON.stringify(cssTests, null, 2));

    // 콘솔 메시지 출력
    console.log('\n📝 콘솔 메시지:');
    consoleMessages.forEach(msg => console.log(msg));

    console.log('\n4️⃣ 스크린샷 촬영...');
    await page.screenshot({
      path: '/var/www/html/topmkt/mobile-calendar-safe-test.png',
      fullPage: true
    });

    console.log('✅ 테스트 완료!');

    // 결과 요약
    console.log('\n📊 최종 결과 요약:');
    console.log('- 페이지 제목:', analysis.pageTitle);
    console.log('- 현재 URL:', analysis.currentUrl);
    console.log('- body data-view:', analysis.bodyDataView);
    console.log('- .calendar-view 존재:', analysis.calendarView);
    console.log('- .list-view 존재:', analysis.listView);
    console.log('- 달력 테이블 존재:', analysis.calendarTable);
    console.log('- .month-year 존재:', analysis.monthYear);
    console.log('- 캘린더 관련 CSS 클래스:', analysis.calendarRelatedClasses);
    console.log('- 스크린샷: mobile-calendar-safe-test.png 저장됨');

  } catch (error) {
    console.error('❌ 테스트 중 오류:', error.message);
  } finally {
    await browser.close();
  }
})();