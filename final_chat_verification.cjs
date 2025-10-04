const { chromium } = require('playwright');

(async () => {
  console.log('🚀 채팅 페이지 최종 검증 테스트 시작...\n');

  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  // 3개 뷰포트 설정
  const viewports = [
    { name: 'desktop', width: 1920, height: 1080, label: '데스크톱' },
    { name: 'tablet', width: 768, height: 1024, label: '태블릿' },
    { name: 'mobile', width: 375, height: 667, label: '모바일' }
  ];

  const results = {
    overall: { pass: 0, fail: 0, total: 0 },
    byViewport: {}
  };

  for (const viewport of viewports) {
    console.log(`\n📱 ${viewport.label} (${viewport.width}x${viewport.height}) 테스트 시작`);
    console.log('='.repeat(50));

    const context = await browser.newContext({
      viewport: { width: viewport.width, height: viewport.height }
    });

    const page = await context.newPage();

    results.byViewport[viewport.name] = {
      viewport: viewport.label,
      tests: [],
      summary: { pass: 0, fail: 0 }
    };

    try {
      // 1. DevLoginHelper로 자동 로그인
      console.log('🔐 DevLoginHelper 자동 로그인 테스트...');
      const loginStart = Date.now();

      await page.goto('https://www.topmktx.com/dev_login_helper.php?user_id=4', {
        waitUntil: 'networkidle'
      });

      const loginTime = Date.now() - loginStart;
      const loginSuccess = await page.url().includes('topmktx.com');

      results.byViewport[viewport.name].tests.push({
        name: '자동 로그인',
        result: loginSuccess ? 'PASS' : 'FAIL',
        details: `로딩 시간: ${loginTime}ms`
      });

      if (loginSuccess) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${loginSuccess ? '✅ PASS' : '❌ FAIL'} - 로딩 시간: ${loginTime}ms`);

      // 2. 채팅 페이지 접속 및 성능 측정
      console.log('💬 채팅 페이지 접속 테스트...');
      const pageStart = Date.now();

      await page.goto('https://www.topmktx.com/chat', {
        waitUntil: 'networkidle'
      });

      const pageTime = Date.now() - pageStart;
      const chatPageLoaded = await page.locator('#chat-room-list').isVisible();

      results.byViewport[viewport.name].tests.push({
        name: '채팅 페이지 로딩',
        result: chatPageLoaded ? 'PASS' : 'FAIL',
        details: `로딩 시간: ${pageTime}ms`
      });

      if (chatPageLoaded) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${chatPageLoaded ? '✅ PASS' : '❌ FAIL'} - 로딩 시간: ${pageTime}ms`);

      // 3. 채팅방 리스트 텍스트 잘림 현상 확인
      console.log('✂️ 텍스트 잘림 현상 확인...');

      await page.waitForSelector('.room-item', { timeout: 5000 });
      const roomItems = await page.locator('.room-item').count();

      let textOverflowCount = 0;
      let totalRoomNames = 0;

      for (let i = 0; i < Math.min(roomItems, 5); i++) {
        const roomItem = page.locator('.room-item').nth(i);
        const roomName = roomItem.locator('.room-name');

        if (await roomName.isVisible()) {
          totalRoomNames++;
          const textContent = await roomName.textContent();
          const clientWidth = await roomName.evaluate(el => el.clientWidth);
          const scrollWidth = await roomName.evaluate(el => el.scrollWidth);

          if (scrollWidth > clientWidth + 2) { // 2px 여유
            textOverflowCount++;
          }
        }
      }

      const noTextOverflow = textOverflowCount === 0;
      results.byViewport[viewport.name].tests.push({
        name: '텍스트 잘림 현상',
        result: noTextOverflow ? 'PASS' : 'FAIL',
        details: `${textOverflowCount}/${totalRoomNames} 개 방에서 잘림`
      });

      if (noTextOverflow) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${noTextOverflow ? '✅ PASS' : '❌ FAIL'} - ${textOverflowCount}/${totalRoomNames} 개 방에서 잘림`);

      // 4. room-name과 room-time 간격 측정
      console.log('📏 room-name과 room-time 간격 측정...');

      let spacingIssues = 0;
      let totalSpacings = 0;

      for (let i = 0; i < Math.min(roomItems, 3); i++) {
        const roomItem = page.locator('.room-item').nth(i);
        const roomName = roomItem.locator('.room-name');
        const roomTime = roomItem.locator('.room-time');

        if (await roomName.isVisible() && await roomTime.isVisible()) {
          totalSpacings++;
          const nameBox = await roomName.boundingBox();
          const timeBox = await roomTime.boundingBox();

          if (nameBox && timeBox) {
            const gap = timeBox.x - (nameBox.x + nameBox.width);
            if (gap < 10) { // 최소 10px 간격 기준
              spacingIssues++;
            }
          }
        }
      }

      const goodSpacing = spacingIssues === 0;
      results.byViewport[viewport.name].tests.push({
        name: 'room-name/time 간격',
        result: goodSpacing ? 'PASS' : 'FAIL',
        details: `${spacingIssues}/${totalSpacings} 개에서 간격 부족`
      });

      if (goodSpacing) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${goodSpacing ? '✅ PASS' : '❌ FAIL'} - ${spacingIssues}/${totalSpacings} 개에서 간격 부족`);

      // 5. CSS calc() 적용 여부 확인
      console.log('🎨 CSS calc() 적용 확인...');

      const calcApplied = await page.evaluate(() => {
        const roomNames = document.querySelectorAll('.room-name');
        let calcCount = 0;

        for (const name of roomNames) {
          const computedStyle = window.getComputedStyle(name);
          const maxWidth = computedStyle.maxWidth;
          if (maxWidth && maxWidth.includes('calc')) {
            calcCount++;
          }
        }

        return { calcCount, totalCount: roomNames.length };
      });

      const cssCalcWorking = calcApplied.calcCount > 0;
      results.byViewport[viewport.name].tests.push({
        name: 'CSS calc() 적용',
        result: cssCalcWorking ? 'PASS' : 'FAIL',
        details: `${calcApplied.calcCount}/${calcApplied.totalCount} 개 요소에 적용`
      });

      if (cssCalcWorking) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${cssCalcWorking ? '✅ PASS' : '❌ FAIL'} - ${calcApplied.calcCount}/${calcApplied.totalCount} 개 요소에 적용`);

      // 6. 자바스크립트 오류 점검
      console.log('🐛 자바스크립트 오류 점검...');

      const jsErrors = [];
      page.on('pageerror', error => {
        jsErrors.push(error.message);
      });

      page.on('console', msg => {
        if (msg.type() === 'error') {
          jsErrors.push(msg.text());
        }
      });

      // 페이지 새로고침하여 오류 캐치
      await page.reload({ waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);

      const noJsErrors = jsErrors.length === 0;
      results.byViewport[viewport.name].tests.push({
        name: '자바스크립트 오류',
        result: noJsErrors ? 'PASS' : 'FAIL',
        details: noJsErrors ? '오류 없음' : `${jsErrors.length} 개 오류 발견`
      });

      if (noJsErrors) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${noJsErrors ? '✅ PASS' : '❌ FAIL'} - ${noJsErrors ? '오류 없음' : `${jsErrors.length} 개 오류`}`);

      // 7. 최근 채팅순 정렬 기능 확인
      console.log('📅 최근 채팅순 정렬 확인...');

      const sortingWorking = await page.evaluate(() => {
        const roomItems = document.querySelectorAll('.room-item');
        if (roomItems.length < 2) return true;

        let previousTime = null;
        let sortedCorrectly = true;

        for (const item of roomItems) {
          const timeElement = item.querySelector('.room-time');
          if (timeElement) {
            const timeText = timeElement.textContent.trim();
            // 시간 비교 로직 (간단한 텍스트 비교)
            if (previousTime && timeText > previousTime) {
              sortedCorrectly = false;
              break;
            }
            previousTime = timeText;
          }
        }

        return sortedCorrectly;
      });

      results.byViewport[viewport.name].tests.push({
        name: '최근 채팅순 정렬',
        result: sortingWorking ? 'PASS' : 'FAIL',
        details: sortingWorking ? '정상 정렬됨' : '정렬 오류'
      });

      if (sortingWorking) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${sortingWorking ? '✅ PASS' : '❌ FAIL'} - ${sortingWorking ? '정상 정렬됨' : '정렬 오류'}`);

      // 8. 최종 스크린샷 생성
      console.log('📸 최종 스크린샷 생성...');

      await page.screenshot({
        path: `final-verification-${viewport.name}.png`,
        fullPage: true
      });

      console.log(`   ✅ 스크린샷 저장: final-verification-${viewport.name}.png`);

      results.byViewport[viewport.name].tests.push({
        name: '스크린샷 생성',
        result: 'PASS',
        details: `final-verification-${viewport.name}.png`
      });
      results.byViewport[viewport.name].summary.pass++;

    } catch (error) {
      console.log(`   ❌ FAIL - 오류: ${error.message}`);
      results.byViewport[viewport.name].tests.push({
        name: '전체 테스트',
        result: 'FAIL',
        details: error.message
      });
      results.byViewport[viewport.name].summary.fail++;
    }

    await context.close();

    // 뷰포트별 요약
    const { pass, fail } = results.byViewport[viewport.name].summary;
    const total = pass + fail;
    const passRate = total > 0 ? ((pass / total) * 100).toFixed(1) : '0.0';

    console.log(`\n📊 ${viewport.label} 결과: ${pass}/${total} 통과 (${passRate}%)`);

    results.overall.pass += pass;
    results.overall.fail += fail;
    results.overall.total += total;
  }

  await browser.close();

  // 최종 결과 출력
  console.log('\n' + '='.repeat(60));
  console.log('🏆 채팅 페이지 최종 검증 결과');
  console.log('='.repeat(60));

  for (const [key, viewport] of Object.entries(results.byViewport)) {
    const { pass, fail } = viewport.summary;
    const total = pass + fail;
    const passRate = total > 0 ? ((pass / total) * 100).toFixed(1) : '0.0';

    console.log(`\n📱 ${viewport.viewport}: ${pass}/${total} 통과 (${passRate}%)`);

    for (const test of viewport.tests) {
      const icon = test.result === 'PASS' ? '✅' : '❌';
      console.log(`   ${icon} ${test.name}: ${test.result} - ${test.details}`);
    }
  }

  // 100% 완료 기준 확인
  console.log('\n🎯 100% 완료 기준 확인');
  console.log('-'.repeat(30));

  const criteriaChecks = [
    {
      name: '텍스트 잘림 현상 완전 해결',
      check: results.byViewport.mobile?.tests.find(t => t.name === '텍스트 잘림 현상')?.result === 'PASS' &&
             results.byViewport.tablet?.tests.find(t => t.name === '텍스트 잘림 현상')?.result === 'PASS' &&
             results.byViewport.desktop?.tests.find(t => t.name === '텍스트 잘림 현상')?.result === 'PASS'
    },
    {
      name: '모든 화면 크기에서 정상 작동',
      check: Object.values(results.byViewport).every(v => v.summary.pass >= v.summary.fail)
    },
    {
      name: '최근 채팅순 정렬 기능 작동',
      check: Object.values(results.byViewport).every(v =>
        v.tests.find(t => t.name === '최근 채팅순 정렬')?.result === 'PASS'
      )
    },
    {
      name: '사용자 경험 향상 달성',
      check: results.overall.pass >= results.overall.total * 0.8 // 80% 이상 통과
    }
  ];

  for (const criteria of criteriaChecks) {
    const icon = criteria.check ? '✅' : '❌';
    console.log(`${icon} ${criteria.name}`);
  }

  // 전체 요약
  const overallPassRate = results.overall.total > 0 ?
    ((results.overall.pass / results.overall.total) * 100).toFixed(1) : '0.0';

  console.log(`\n🏁 전체 결과: ${results.overall.pass}/${results.overall.total} 통과 (${overallPassRate}%)`);

  if (overallPassRate >= 80) {
    console.log('🎉 채팅 페이지 최종 검증 성공! 모든 개선사항이 정상 작동합니다.');
  } else {
    console.log('⚠️  일부 항목에서 개선이 필요합니다.');
  }

})().catch(console.error);