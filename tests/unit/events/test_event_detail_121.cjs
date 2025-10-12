const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('1. 탑마케팅 홈페이지 접속...');
    await page.goto('https://www.topmktx.com');
    await page.waitForTimeout(2000);

    console.log('2. 우리집탄이 계정으로 로그인...');
    // 로그인 버튼 클릭
    await page.click('.login-btn');
    await page.waitForTimeout(1000);

    // 로그인 폼 입력
    await page.fill('input[name="email"]', 'woorijibtani@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(3000);

    console.log('3. 행사 상세 페이지(ID: 121) 접속...');
    await page.goto('https://www.topmktx.com/events/detail?id=121');
    await page.waitForTimeout(3000);

    console.log('4. 페이지 로딩 상태 확인...');
    const title = await page.title();
    console.log('페이지 제목:', title);

    // 행사 제목 확인
    const eventTitle = await page.textContent('h1').catch(() => null);
    console.log('행사 제목:', eventTitle);

    console.log('5. 신청 버튼 영역 분석...');

    // 신청 버튼 영역 확인
    const applicationSection = await page.locator('.application-section, .event-application, .register-section').first().isVisible().catch(() => false);
    console.log('신청 영역 존재:', applicationSection);

    // 본인 행사 메시지 확인
    const ownEventMessage = await page.locator('text=본인이 등록한 행사입니다').isVisible().catch(() => false);
    console.log('본인 행사 메시지:', ownEventMessage);

    // 신청 버튼 확인
    const registerButton = await page.locator('button:has-text("신청"), .btn-register, .register-btn').first().isVisible().catch(() => false);
    console.log('신청 버튼 존재:', registerButton);

    // 마감 메시지 확인
    const closedMessage = await page.locator('text=마감, text=접수 마감, text=신청 마감').first().isVisible().catch(() => false);
    console.log('마감 메시지:', closedMessage);

    console.log('6. 스크린샷 촬영...');
    await page.screenshot({
      path: 'event-detail-121-full.png',
      fullPage: true
    });

    // 신청 영역만 스크린샷 시도
    const applicationElement = await page.locator('.application-section, .event-application, .register-section, .event-details').first().boundingBox().catch(() => null);
    if (applicationElement) {
      await page.screenshot({
        path: 'event-detail-121-application-area.png',
        clip: applicationElement
      });
    }

    console.log('7. 신청 버튼 클릭 테스트...');
    if (registerButton) {
      console.log('신청 버튼 클릭 시도...');
      await page.click('button:has-text("신청"), .btn-register, .register-btn');
      await page.waitForTimeout(2000);

      // 모달이나 알림 메시지 확인
      const modal = await page.locator('.modal, .alert, .notification').first().isVisible().catch(() => false);
      if (modal) {
        const modalText = await page.locator('.modal, .alert, .notification').first().textContent().catch(() => '');
        console.log('모달/알림 메시지:', modalText);

        await page.screenshot({
          path: 'event-detail-121-after-click.png',
          fullPage: true
        });
      }
    }

    console.log('\n=== 테스트 완료 ===');
    console.log('스크린샷 파일:');
    console.log('- event-detail-121-full.png (전체 페이지)');
    console.log('- event-detail-121-application-area.png (신청 영역)');
    if (registerButton) {
      console.log('- event-detail-121-after-click.png (버튼 클릭 후)');
    }

  } catch (error) {
    console.error('오류 발생:', error.message);
    await page.screenshot({ path: 'event-detail-121-error.png' });
  }

  await browser.close();
})();