const { chromium } = require('playwright');

(async () => {
  console.log('🔍 수동 로그인으로 채팅 페이지 테스트 시작...\n');

  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage({
    viewport: { width: 1920, height: 1080 }
  });

  try {
    // 1. 로그인 페이지로 이동
    console.log('🔐 로그인 페이지 접속...');
    await page.goto('https://www.topmktx.com/auth/login', {
      waitUntil: 'networkidle'
    });

    // 2. 로그인 양식 작성 (우리집탄이 계정)
    console.log('📝 로그인 정보 입력...');

    // 휴대폰 번호와 비밀번호 필드 찾기
    await page.waitForSelector('input[name="phone"], input[type="tel"]', { timeout: 5000 });
    await page.waitForSelector('input[type="password"], input[name="password"]', { timeout: 5000 });

    // 휴대폰 번호 입력
    const phoneField = page.locator('input[name="phone"], input[type="tel"]').first();
    await phoneField.fill('010-2659-1346');

    // 비밀번호 입력 (일반적인 테스트 비밀번호 시도)
    const passwordField = page.locator('input[type="password"], input[name="password"]').first();
    await passwordField.fill('password123');

    // 3. 로그인 버튼 클릭
    console.log('🚀 로그인 버튼 클릭...');
    const loginButton = page.locator('button[type="submit"], input[type="submit"], .btn-login, .login-btn').first();
    await loginButton.click();

    // 로그인 처리 대기
    await page.waitForTimeout(2000);

    console.log(`로그인 후 URL: ${page.url()}`);

    // 4. 채팅 페이지로 직접 이동
    console.log('💬 채팅 페이지 접속 시도...');
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle'
    });

    console.log(`채팅 페이지 URL: ${page.url()}`);

    if (page.url().includes('/auth/login')) {
      console.log('❌ 여전히 로그인 페이지로 리다이렉트됨');

      // 쿠키 확인
      const cookies = await page.context().cookies();
      console.log(`쿠키 개수: ${cookies.length}`);
      for (const cookie of cookies) {
        if (cookie.name.includes('token') || cookie.name.includes('auth') || cookie.name.includes('session')) {
          console.log(`인증 쿠키: ${cookie.name} = ${cookie.value.substring(0, 20)}...`);
        }
      }

      return;
    }

    // 5. 채팅방 리스트 확인
    console.log('🏠 채팅방 리스트 확인...');

    // chat-room-list 대기
    try {
      await page.waitForSelector('#chat-room-list', { timeout: 10000 });
      console.log('✅ chat-room-list 발견');

      const chatRoomList = await page.locator('#chat-room-list').innerHTML();
      console.log(`chat-room-list 내용 길이: ${chatRoomList.length}`);

      // room-item 요소들 확인
      await page.waitForSelector('.room-item', { timeout: 5000 });
      const roomCount = await page.locator('.room-item').count();
      console.log(`✅ room-item 개수: ${roomCount}`);

      if (roomCount > 0) {
        console.log('\n📋 채팅방 목록:');
        for (let i = 0; i < Math.min(roomCount, 5); i++) {
          const roomItem = page.locator('.room-item').nth(i);
          const roomName = await roomItem.locator('.room-name').textContent();
          const roomTime = await roomItem.locator('.room-time').textContent();
          console.log(`  ${i + 1}. ${roomName?.trim()} - ${roomTime?.trim()}`);
        }

        // 텍스트 잘림 확인
        console.log('\n✂️ 텍스트 잘림 확인:');
        let overflowCount = 0;

        for (let i = 0; i < Math.min(roomCount, 5); i++) {
          const roomName = page.locator('.room-item').nth(i).locator('.room-name');
          const clientWidth = await roomName.evaluate(el => el.clientWidth);
          const scrollWidth = await roomName.evaluate(el => el.scrollWidth);

          if (scrollWidth > clientWidth + 2) {
            overflowCount++;
            console.log(`  ❌ Room ${i + 1}: 텍스트 잘림 (${scrollWidth}px > ${clientWidth}px)`);
          } else {
            console.log(`  ✅ Room ${i + 1}: 정상 (${scrollWidth}px ≤ ${clientWidth}px)`);
          }
        }

        console.log(`\n📊 텍스트 잘림 결과: ${overflowCount}/${roomCount} 개 방에서 잘림`);

        // CSS calc() 확인
        console.log('\n🎨 CSS calc() 적용 확인:');
        const calcInfo = await page.evaluate(() => {
          const roomNames = document.querySelectorAll('.room-name');
          let calcCount = 0;
          let details = [];

          for (let i = 0; i < roomNames.length; i++) {
            const el = roomNames[i];
            const computedStyle = window.getComputedStyle(el);
            const maxWidth = computedStyle.maxWidth;

            if (maxWidth && maxWidth.includes('calc')) {
              calcCount++;
              details.push(`Room ${i + 1}: ${maxWidth}`);
            } else {
              details.push(`Room ${i + 1}: ${maxWidth || 'none'}`);
            }
          }

          return { calcCount, totalCount: roomNames.length, details };
        });

        console.log(`CSS calc() 적용: ${calcInfo.calcCount}/${calcInfo.totalCount}`);
        for (const detail of calcInfo.details.slice(0, 5)) {
          console.log(`  ${detail}`);
        }
      }

    } catch (error) {
      console.log(`❌ 채팅방 리스트를 찾을 수 없음: ${error.message}`);
    }

    // 6. 최종 스크린샷
    console.log('\n📸 스크린샷 생성...');
    await page.screenshot({
      path: 'manual-login-chat-test.png',
      fullPage: true
    });
    console.log('✅ 스크린샷 저장: manual-login-chat-test.png');

  } catch (error) {
    console.log(`❌ 오류 발생: ${error.message}`);
    console.log(error.stack);
  }

  await browser.close();
  console.log('\n🏁 테스트 완료');

})().catch(console.error);