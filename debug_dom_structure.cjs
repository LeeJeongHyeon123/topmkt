const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });

  console.log('🔍 강의 vs 행사 일정 DOM 구조 및 CSS 직접 비교...');

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

  const lecturesData = await lecturesPage.evaluate(() => {
    const layout = document.querySelector('.lectures-layout');
    if (!layout) return { error: 'lectures-layout not found' };

    const layoutStyle = getComputedStyle(layout);
    const allStyles = {};
    for (let i = 0; i < layoutStyle.length; i++) {
      const prop = layoutStyle[i];
      if (prop.includes('grid') || prop.includes('width') || prop.includes('max')) {
        allStyles[prop] = layoutStyle.getPropertyValue(prop);
      }
    }

    return {
      className: layout.className,
      innerHTML: layout.innerHTML.substring(0, 200) + '...',
      computedStyles: allStyles,
      offsetWidth: layout.offsetWidth,
      clientWidth: layout.clientWidth,
      scrollWidth: layout.scrollWidth
    };
  });

  console.log('\n🎓 강의 일정 분석:');
  console.log('   클래스:', lecturesData.className);
  console.log('   Grid 컬럼:', lecturesData.computedStyles['grid-template-columns']);
  console.log('   Max Width:', lecturesData.computedStyles['max-width']);
  console.log('   Width:', lecturesData.computedStyles['width']);
  console.log('   실제 너비:', lecturesData.offsetWidth);

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

  const eventsData = await eventsPage.evaluate(() => {
    const layout = document.querySelector('.events-layout');
    if (!layout) return { error: 'events-layout not found' };

    const layoutStyle = getComputedStyle(layout);
    const allStyles = {};
    for (let i = 0; i < layoutStyle.length; i++) {
      const prop = layoutStyle[i];
      if (prop.includes('grid') || prop.includes('width') || prop.includes('max')) {
        allStyles[prop] = layoutStyle.getPropertyValue(prop);
      }
    }

    return {
      className: layout.className,
      innerHTML: layout.innerHTML.substring(0, 200) + '...',
      computedStyles: allStyles,
      offsetWidth: layout.offsetWidth,
      clientWidth: layout.clientWidth,
      scrollWidth: layout.scrollWidth
    };
  });

  console.log('\n🎪 행사 일정 분석:');
  console.log('   클래스:', eventsData.className);
  console.log('   Grid 컬럼:', eventsData.computedStyles['grid-template-columns']);
  console.log('   Max Width:', eventsData.computedStyles['max-width']);
  console.log('   Width:', eventsData.computedStyles['width']);
  console.log('   실제 너비:', eventsData.offsetWidth);

  // 차이점 분석
  console.log('\n🔍 차이점 분석:');
  console.log('   Grid 컬럼 비교:');
  console.log('     강의:', lecturesData.computedStyles['grid-template-columns']);
  console.log('     행사:', eventsData.computedStyles['grid-template-columns']);

  await lecturesPage.close();
  await eventsPage.close();
  await browser.close();

  console.log('\n🏁 DOM 구조 분석 완료');
})();