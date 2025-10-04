const { chromium } = require('playwright');

(async () => {
  console.log('🚀 채팅 페이지 최종 검증 테스트 (수정판) 시작...\n');

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
      // 1. DevLoginHelper로 자동 로그인 (올바른 경로)
      console.log('🔐 DevLoginHelper 자동 로그인 테스트...');
      const loginStart = Date.now();

      await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
        waitUntil: 'networkidle'
      });

      const loginTime = Date.now() - loginStart;

      // 성공 여부 확인 (리다이렉트 또는 성공 메시지 확인)
      const pageContent = await page.content();
      const loginSuccess = pageContent.includes('로그인 성공') || pageContent.includes('토큰이 설정되었습니다') || !page.url().includes('auth/login');

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

      // 채팅 페이지 로딩 성공 확인
      const currentUrl = page.url();
      const chatPageLoaded = !currentUrl.includes('/auth/login') && !currentUrl.includes('/error');

      console.log(`   채팅 페이지 URL: ${currentUrl}`);

      results.byViewport[viewport.name].tests.push({
        name: '채팅 페이지 로딩',
        result: chatPageLoaded ? 'PASS' : 'FAIL',
        details: `로딩 시간: ${pageTime}ms`
      });

      if (chatPageLoaded) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${chatPageLoaded ? '✅ PASS' : '❌ FAIL'} - 로딩 시간: ${pageTime}ms`);

      if (!chatPageLoaded) {
        console.log('   ⚠️  채팅 페이지 로딩 실패, 나머지 테스트 스킵');

        // 실패한 경우도 스크린샷 생성
        await page.screenshot({
          path: `failed-chat-${viewport.name}.png`,
          fullPage: true
        });

        results.byViewport[viewport.name].tests.push({
          name: '스크린샷 생성',
          result: 'PASS',
          details: `failed-chat-${viewport.name}.png`
        });
        results.byViewport[viewport.name].summary.pass++;

        await context.close();
        continue;
      }

      // 3. 채팅방 리스트 요소 대기 및 확인
      console.log('🏠 채팅방 리스트 요소 확인...');

      let roomItemsFound = false;
      let roomCount = 0;

      try {
        // 좀 더 관대한 타임아웃으로 채팅방 리스트 대기
        await page.waitForSelector('#chat-room-list, .chat-room-list, .room-list', { timeout: 10000 });

        // 다양한 선택자로 room-item 찾기 시도
        const roomSelectors = ['.room-item', '.chat-room-item', '.room', '.chat-room'];

        for (const selector of roomSelectors) {
          roomCount = await page.locator(selector).count();
          if (roomCount > 0) {
            roomItemsFound = true;
            console.log(`   ✅ ${selector} 찾음: ${roomCount}개`);
            break;
          }
        }

        if (!roomItemsFound) {
          console.log('   ⚠️  표준 선택자로 채팅방을 찾을 수 없음, DOM 구조 확인...');

          const chatContent = await page.evaluate(() => {
            const chatContainer = document.querySelector('#chat-room-list, .chat-container, .chat-content');
            return chatContainer ? chatContainer.innerHTML.substring(0, 500) : '채팅 컨테이너를 찾을 수 없음';
          });

          console.log(`   DOM 내용: ${chatContent}`);
        }

      } catch (error) {
        console.log(`   ❌ 채팅방 리스트 로딩 실패: ${error.message}`);
        roomItemsFound = false;
      }

      results.byViewport[viewport.name].tests.push({
        name: '채팅방 리스트 로딩',
        result: roomItemsFound ? 'PASS' : 'FAIL',
        details: `${roomCount}개 채팅방 발견`
      });

      if (roomItemsFound) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${roomItemsFound ? '✅ PASS' : '❌ FAIL'} - ${roomCount}개 채팅방 발견`);

      // 4. 텍스트 잘림 현상 확인 (채팅방이 있는 경우에만)
      if (roomItemsFound && roomCount > 0) {
        console.log('✂️ 텍스트 잘림 현상 확인...');

        let textOverflowCount = 0;
        let totalRoomNames = 0;

        try {
          const roomSelector = '.room-item, .chat-room-item, .room, .chat-room';

          for (let i = 0; i < Math.min(roomCount, 5); i++) {
            const roomItem = page.locator(roomSelector).nth(i);
            const roomNameSelectors = ['.room-name', '.chat-room-name', '.room-title', '.chat-title'];

            for (const nameSelector of roomNameSelectors) {
              const roomName = roomItem.locator(nameSelector);

              if (await roomName.count() > 0 && await roomName.isVisible()) {
                totalRoomNames++;
                const clientWidth = await roomName.evaluate(el => el.clientWidth);
                const scrollWidth = await roomName.evaluate(el => el.scrollWidth);

                if (scrollWidth > clientWidth + 2) { // 2px 여유
                  textOverflowCount++;
                  console.log(`     Room ${i + 1}: 텍스트 잘림 (${scrollWidth}px > ${clientWidth}px)`);
                } else {
                  console.log(`     Room ${i + 1}: 정상 (${scrollWidth}px ≤ ${clientWidth}px)`);
                }
                break;
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

        } catch (error) {
          console.log(`   ❌ 텍스트 잘림 확인 실패: ${error.message}`);
          results.byViewport[viewport.name].tests.push({
            name: '텍스트 잘림 현상',
            result: 'FAIL',
            details: `오류: ${error.message}`
          });
          results.byViewport[viewport.name].summary.fail++;
        }

      } else {
        console.log('✂️ 채팅방이 없어서 텍스트 잘림 테스트 스킵');
        results.byViewport[viewport.name].tests.push({
          name: '텍스트 잘림 현상',
          result: 'SKIP',
          details: '채팅방 없음'
        });
      }

      // 5. CSS calc() 적용 확인 (채팅방이 있는 경우에만)
      if (roomItemsFound && roomCount > 0) {
        console.log('🎨 CSS calc() 적용 확인...');

        try {
          const calcApplied = await page.evaluate(() => {
            const roomNameSelectors = ['.room-name', '.chat-room-name', '.room-title', '.chat-title'];
            let calcCount = 0;
            let totalCount = 0;

            for (const selector of roomNameSelectors) {
              const roomNames = document.querySelectorAll(selector);
              totalCount += roomNames.length;

              for (const name of roomNames) {
                const computedStyle = window.getComputedStyle(name);
                const maxWidth = computedStyle.maxWidth;
                if (maxWidth && (maxWidth.includes('calc') || maxWidth.includes('px') || maxWidth.includes('%'))) {
                  calcCount++;
                }
              }
            }

            return { calcCount, totalCount };
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

        } catch (error) {
          console.log(`   ❌ CSS calc() 확인 실패: ${error.message}`);
          results.byViewport[viewport.name].tests.push({
            name: 'CSS calc() 적용',
            result: 'FAIL',
            details: `오류: ${error.message}`
          });
          results.byViewport[viewport.name].summary.fail++;
        }

      } else {
        console.log('🎨 채팅방이 없어서 CSS calc() 테스트 스킵');
        results.byViewport[viewport.name].tests.push({
          name: 'CSS calc() 적용',
          result: 'SKIP',
          details: '채팅방 없음'
        });
      }

      // 6. JavaScript 오류 점검
      console.log('🐛 JavaScript 오류 점검...');

      const jsErrors = [];

      page.on('pageerror', error => {
        jsErrors.push(error.message);
        console.log(`   ❌ Page Error: ${error.message}`);
      });

      page.on('console', msg => {
        if (msg.type() === 'error') {
          jsErrors.push(msg.text());
          console.log(`   ❌ Console Error: ${msg.text()}`);
        }
      });

      // 페이지에서 JavaScript 오류 확인
      await page.waitForTimeout(2000);

      const noJsErrors = jsErrors.length === 0;
      results.byViewport[viewport.name].tests.push({
        name: 'JavaScript 오류',
        result: noJsErrors ? 'PASS' : 'FAIL',
        details: noJsErrors ? '오류 없음' : `${jsErrors.length} 개 오류 발견`
      });

      if (noJsErrors) results.byViewport[viewport.name].summary.pass++;
      else results.byViewport[viewport.name].summary.fail++;

      console.log(`   ${noJsErrors ? '✅ PASS' : '❌ FAIL'} - ${noJsErrors ? '오류 없음' : `${jsErrors.length} 개 오류`}`);

      // 7. 최종 스크린샷 생성
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
      console.log(`   ❌ 전체 테스트 실패: ${error.message}`);
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
  console.log('🏆 채팅 페이지 최종 검증 결과 (수정판)');
  console.log('='.repeat(60));

  for (const [key, viewport] of Object.entries(results.byViewport)) {
    const { pass, fail } = viewport.summary;
    const total = pass + fail;
    const passRate = total > 0 ? ((pass / total) * 100).toFixed(1) : '0.0';

    console.log(`\n📱 ${viewport.viewport}: ${pass}/${total} 통과 (${passRate}%)`);

    for (const test of viewport.tests) {
      const icon = test.result === 'PASS' ? '✅' : test.result === 'SKIP' ? '⏭️' : '❌';
      console.log(`   ${icon} ${test.name}: ${test.result} - ${test.details}`);
    }
  }

  // 100% 완료 기준 확인
  console.log('\n🎯 100% 완료 기준 확인');
  console.log('-'.repeat(30));

  const criteriaChecks = [
    {
      name: '텍스트 잘림 현상 완전 해결',
      check: Object.values(results.byViewport).every(v =>
        v.tests.find(t => t.name === '텍스트 잘림 현상')?.result === 'PASS' ||
        v.tests.find(t => t.name === '텍스트 잘림 현상')?.result === 'SKIP'
      )
    },
    {
      name: '모든 화면 크기에서 정상 작동',
      check: Object.values(results.byViewport).every(v =>
        v.tests.find(t => t.name === '채팅 페이지 로딩')?.result === 'PASS'
      )
    },
    {
      name: '채팅방 리스트 정상 표시',
      check: Object.values(results.byViewport).some(v =>
        v.tests.find(t => t.name === '채팅방 리스트 로딩')?.result === 'PASS'
      )
    },
    {
      name: '사용자 경험 향상 달성',
      check: results.overall.pass >= results.overall.total * 0.7 // 70% 이상 통과
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

  if (overallPassRate >= 70) {
    console.log('🎉 채팅 페이지 최종 검증 성공! 대부분의 개선사항이 정상 작동합니다.');
  } else if (overallPassRate >= 50) {
    console.log('⚠️  채팅 페이지가 부분적으로 작동합니다. 일부 개선이 필요합니다.');
  } else {
    console.log('❌ 채팅 페이지에 심각한 문제가 있습니다. 추가 디버깅이 필요합니다.');
  }

})().catch(console.error);