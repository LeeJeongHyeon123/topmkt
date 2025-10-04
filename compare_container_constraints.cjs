const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 강의 vs 행사 일정 컨테이너 제약 조건 비교...');

  // 강의 일정 분석
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
    const container = document.querySelector('.lectures-container');
    const layout = document.querySelector('.lectures-layout');
    const main = document.querySelector('.lectures-main');
    const calendar = document.querySelector('.calendar-view');

    const getElementInfo = (element, name) => {
      if (!element) return { error: `${name} not found` };

      const style = getComputedStyle(element);
      const rect = element.getBoundingClientRect();

      return {
        tagName: element.tagName,
        className: element.className,
        offsetWidth: element.offsetWidth,
        clientWidth: element.clientWidth,
        scrollWidth: element.scrollWidth,
        computedWidth: style.width,
        computedMaxWidth: style.maxWidth,
        computedPadding: `${style.paddingLeft} ${style.paddingRight}`,
        computedMargin: `${style.marginLeft} ${style.marginRight}`,
        computedBorder: `${style.borderLeftWidth} ${style.borderRightWidth}`,
        rectWidth: rect.width,
        rectLeft: rect.left,
        rectRight: rect.right
      };
    };

    return {
      container: getElementInfo(container, 'container'),
      layout: getElementInfo(layout, 'layout'),
      main: getElementInfo(main, 'main'),
      calendar: getElementInfo(calendar, 'calendar')
    };
  });

  console.log('\n🎓 강의 일정 컨테이너 분석:');
  console.log('   Container:', lecturesAnalysis.container.offsetWidth, 'px');
  console.log('   Layout:', lecturesAnalysis.layout.offsetWidth, 'px');
  console.log('   Main:', lecturesAnalysis.main.offsetWidth, 'px');
  console.log('   Calendar:', lecturesAnalysis.calendar.offsetWidth, 'px');

  // 행사 일정 분석
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
    const container = document.querySelector('.events-container');
    const layout = document.querySelector('.events-layout');
    const main = document.querySelector('.events-main');
    const calendar = document.querySelector('.calendar-view');

    const getElementInfo = (element, name) => {
      if (!element) return { error: `${name} not found` };

      const style = getComputedStyle(element);
      const rect = element.getBoundingClientRect();

      return {
        tagName: element.tagName,
        className: element.className,
        offsetWidth: element.offsetWidth,
        clientWidth: element.clientWidth,
        scrollWidth: element.scrollWidth,
        computedWidth: style.width,
        computedMaxWidth: style.maxWidth,
        computedPadding: `${style.paddingLeft} ${style.paddingRight}`,
        computedMargin: `${style.marginLeft} ${style.marginRight}`,
        computedBorder: `${style.borderLeftWidth} ${style.borderRightWidth}`,
        rectWidth: rect.width,
        rectLeft: rect.left,
        rectRight: rect.right
      };
    };

    return {
      container: getElementInfo(container, 'container'),
      layout: getElementInfo(layout, 'layout'),
      main: getElementInfo(main, 'main'),
      calendar: getElementInfo(calendar, 'calendar')
    };
  });

  console.log('\n🎪 행사 일정 컨테이너 분석:');
  console.log('   Container:', eventsAnalysis.container.offsetWidth, 'px');
  console.log('   Layout:', eventsAnalysis.layout.offsetWidth, 'px');
  console.log('   Main:', eventsAnalysis.main.offsetWidth, 'px');
  console.log('   Calendar:', eventsAnalysis.calendar.offsetWidth, 'px');

  // 상세 비교
  console.log('\n🔍 상세 비교 (강의 vs 행사):');
  console.log('   Container 너비:', lecturesAnalysis.container.offsetWidth, 'vs', eventsAnalysis.container.offsetWidth);
  console.log('   Layout 너비:', lecturesAnalysis.layout.offsetWidth, 'vs', eventsAnalysis.layout.offsetWidth);
  console.log('   Main 너비:', lecturesAnalysis.main.offsetWidth, 'vs', eventsAnalysis.main.offsetWidth);
  console.log('   Calendar 너비:', lecturesAnalysis.calendar.offsetWidth, 'vs', eventsAnalysis.calendar.offsetWidth);

  // HTML 구조 차이 확인
  const lecturesStructure = await lecturesPage.evaluate(() => {
    const layout = document.querySelector('.lectures-layout');
    return layout ? layout.innerHTML.replace(/\s+/g, ' ').substring(0, 300) : 'Not found';
  });

  const eventsStructure = await eventsPage.evaluate(() => {
    const layout = document.querySelector('.events-layout');
    return layout ? layout.innerHTML.replace(/\s+/g, ' ').substring(0, 300) : 'Not found';
  });

  console.log('\n📋 HTML 구조 비교:');
  console.log('   강의 구조:', lecturesStructure);
  console.log('   행사 구조:', eventsStructure);

  await lecturesPage.close();
  await eventsPage.close();
  await browser.close();

  console.log('\n🏁 컨테이너 제약 조건 비교 완료');
})();