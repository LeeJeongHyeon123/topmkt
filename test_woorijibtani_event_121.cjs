const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    console.log('1. 탑마케팅 홈페이지 접속...');
    await page.goto('https://www.topmktx.com');
    await page.waitForTimeout(2000);

    console.log('2. 우리집탄이 계정으로 로그인 시도...');

    // 로그인 버튼 찾기 (다양한 선택자 시도)
    const loginSelectors = ['.login-btn', 'a:has-text("로그인")', '.btn-login', '[href*="login"]'];

    let loginButtonClicked = false;
    for (const selector of loginSelectors) {
      try {
        const isVisible = await page.locator(selector).first().isVisible();
        if (isVisible) {
          console.log(`로그인 버튼 발견: ${selector}`);
          await page.locator(selector).first().click();
          loginButtonClicked = true;
          break;
        }
      } catch (e) {
        console.log(`${selector} 선택자 시도 실패`);
      }
    }

    if (!loginButtonClicked) {
      console.log('로그인 버튼을 찾을 수 없음. 직접 로그인 페이지로 이동...');
      await page.goto('https://www.topmktx.com/auth/login');
    }

    await page.waitForTimeout(2000);

    // 로그인 폼 입력
    console.log('로그인 정보 입력...');

    // 이메일 필드 찾기 및 입력
    const emailSelectors = ['input[name="email"]', 'input[type="email"]', '#email', '.email-input'];
    for (const selector of emailSelectors) {
      try {
        const isVisible = await page.locator(selector).isVisible();
        if (isVisible) {
          await page.fill(selector, 'woorijibtani@example.com');
          console.log('이메일 입력 완료');
          break;
        }
      } catch (e) {
        console.log(`이메일 필드 ${selector} 시도 실패`);
      }
    }

    // 비밀번호 필드 찾기 및 입력
    const passwordSelectors = ['input[name="password"]', 'input[type="password"]', '#password', '.password-input'];
    for (const selector of passwordSelectors) {
      try {
        const isVisible = await page.locator(selector).isVisible();
        if (isVisible) {
          await page.fill(selector, 'password123');
          console.log('비밀번호 입력 완료');
          break;
        }
      } catch (e) {
        console.log(`비밀번호 필드 ${selector} 시도 실패`);
      }
    }

    // 로그인 버튼 클릭
    const submitSelectors = ['button[type="submit"]', '.btn-submit', 'button:has-text("로그인")', '.login-submit'];
    for (const selector of submitSelectors) {
      try {
        const isVisible = await page.locator(selector).isVisible();
        if (isVisible) {
          await page.locator(selector).click();
          console.log('로그인 버튼 클릭 완료');
          break;
        }
      } catch (e) {
        console.log(`로그인 제출 버튼 ${selector} 시도 실패`);
      }
    }

    await page.waitForTimeout(3000);

    console.log('3. 로그인 상태 확인...');
    const currentUrl = page.url();
    console.log('현재 URL:', currentUrl);

    // 로그인 성공 여부 확인
    const isLoggedIn = currentUrl.includes('/dashboard') ||
                       currentUrl.includes('/home') ||
                       await page.locator('text=우리집탄이').isVisible().catch(() => false) ||
                       await page.locator('.user-menu').isVisible().catch(() => false);

    console.log('로그인 상태:', isLoggedIn ? '성공' : '실패');

    if (isLoggedIn) {
      console.log('4. 행사 상세 페이지(ID: 121) 접속...');
      await page.goto('https://www.topmktx.com/events/detail?id=121');
      await page.waitForTimeout(3000);

      console.log('5. 본인 행사 신청 방지 메시지 확인...');

      // 본인 행사 메시지 확인
      const ownEventNotice = await page.locator('.own-event-notice').isVisible().catch(() => false);
      console.log('본인 행사 안내 박스:', ownEventNotice ? '표시됨' : '표시되지 않음');

      if (ownEventNotice) {
        const noticeText = await page.locator('.own-event-notice').textContent().catch(() => '');
        console.log('본인 행사 안내 내용:', noticeText.substring(0, 100) + '...');
      }

      // 신청 버튼 영역 확인
      const registerButton = await page.locator('button:has-text("신청"), .register-btn, .btn-register').isVisible().catch(() => false);
      console.log('신청 버튼:', registerButton ? '표시됨' : '표시되지 않음');

      // 페이지 스크린샷
      await page.screenshot({
        path: 'event-121-logged-in-woorijibtani.png',
        fullPage: true
      });

      console.log('스크린샷 저장: event-121-logged-in-woorijibtani.png');

    } else {
      console.log('로그인 실패. 현재 페이지 스크린샷 촬영...');
      await page.screenshot({
        path: 'login-failed-woorijibtani.png',
        fullPage: true
      });
    }

  } catch (error) {
    console.error('오류 발생:', error.message);
    await page.screenshot({ path: 'error-woorijibtani-test.png' });
  }

  await browser.close();
})();