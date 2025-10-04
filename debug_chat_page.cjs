const { chromium } = require('playwright');

(async () => {
  console.log('🔍 채팅 페이지 디버깅 시작...\n');

  const browser = await chromium.launch({
    headless: true,  // 헤드리스 모드로 변경
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage({
    viewport: { width: 1920, height: 1080 }
  });

  try {
    // 1. 자동 로그인
    console.log('🔐 자동 로그인 중...');
    await page.goto('https://www.topmktx.com/dev_login_helper.php?user_id=4', {
      waitUntil: 'networkidle'
    });

    const loginSuccess = await page.url().includes('topmktx.com');
    console.log(`로그인 상태: ${loginSuccess ? '성공' : '실패'}`);

    // 2. 채팅 페이지 접속
    console.log('💬 채팅 페이지 접속 중...');
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle'
    });

    console.log(`현재 URL: ${page.url()}`);
    console.log(`페이지 제목: ${await page.title()}`);

    // 3. 페이지 소스 확인
    console.log('\n📄 페이지 소스 일부 확인:');
    const bodyContent = await page.evaluate(() => {
      return document.body.innerHTML.substring(0, 500);
    });
    console.log(bodyContent);

    // 4. chat-room-list 요소 존재 확인
    console.log('\n🔍 chat-room-list 요소 확인:');
    const chatRoomListExists = await page.locator('#chat-room-list').count();
    console.log(`chat-room-list 요소 개수: ${chatRoomListExists}`);

    if (chatRoomListExists > 0) {
      const isVisible = await page.locator('#chat-room-list').isVisible();
      console.log(`chat-room-list 가시성: ${isVisible}`);

      const innerHTML = await page.locator('#chat-room-list').innerHTML();
      console.log(`chat-room-list 내용 길이: ${innerHTML.length}`);
      console.log(`내용 일부: ${innerHTML.substring(0, 200)}`);
    }

    // 5. room-item 요소들 확인
    console.log('\n🏠 room-item 요소들 확인:');
    const roomItemsCount = await page.locator('.room-item').count();
    console.log(`room-item 개수: ${roomItemsCount}`);

    if (roomItemsCount > 0) {
      for (let i = 0; i < Math.min(roomItemsCount, 3); i++) {
        const roomItem = page.locator('.room-item').nth(i);
        const roomName = await roomItem.locator('.room-name').textContent();
        const roomTime = await roomItem.locator('.room-time').textContent();
        console.log(`  Room ${i + 1}: ${roomName?.trim()} - ${roomTime?.trim()}`);
      }
    }

    // 6. JavaScript 오류 확인
    console.log('\n🐛 JavaScript 오류 확인:');
    page.on('pageerror', error => {
      console.log(`❌ Page Error: ${error.message}`);
    });

    page.on('console', msg => {
      if (msg.type() === 'error') {
        console.log(`❌ Console Error: ${msg.text()}`);
      }
    });

    // 7. 네트워크 요청 확인
    console.log('\n🌐 네트워크 요청 확인:');
    page.on('response', response => {
      if (response.url().includes('chat') || response.url().includes('room')) {
        console.log(`📡 ${response.status()} - ${response.url()}`);
      }
    });

    // 페이지 새로고침 후 잠시 대기
    await page.reload({ waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);

    // 8. 스크린샷 생성
    console.log('\n📸 디버깅 스크린샷 생성...');
    await page.screenshot({
      path: 'debug-chat-page.png',
      fullPage: true
    });

    console.log('✅ 디버깅 스크린샷 저장: debug-chat-page.png');

  } catch (error) {
    console.log(`❌ 오류 발생: ${error.message}`);
  }

  await browser.close();
  console.log('\n🏁 채팅 페이지 디버깅 완료');

})().catch(console.error);