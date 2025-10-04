const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  console.log('🔑 우리집탄이 계정으로 로그인 시도...');

  try {
    // 로그인 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/login', { waitUntil: 'networkidle' });

    // 로그인 폼 찾기
    const loginForm = await page.locator('form').first();

    // 우리집탄이 계정 정보 입력 (사용자 ID 4)
    await page.fill('input[name="username"], input[name="email"], input[name="phone"]', '우리집탄이');
    await page.fill('input[name="password"]', 'password123'); // 기본 비밀번호 시도

    console.log('📝 로그인 정보 입력 완료');

    // 로그인 버튼 클릭
    await page.click('button[type="submit"], input[type="submit"]');

    await page.waitForTimeout(3000);

    // 로그인 결과 확인
    const currentUrl = page.url();
    const loginResult = await page.evaluate(() => {
      const errorMsg = document.querySelector('.error, .alert-danger, .alert-error');
      const successMsg = document.querySelector('.success, .alert-success');

      return {
        currentUrl: window.location.href,
        hasError: !!errorMsg,
        errorText: errorMsg ? errorMsg.textContent.trim() : null,
        hasSuccess: !!successMsg,
        successText: successMsg ? successMsg.textContent.trim() : null
      };
    });

    console.log('🎯 로그인 결과:');
    console.log('현재 URL:', currentUrl);
    console.log('결과:', loginResult);

    if (currentUrl.includes('/profile') || currentUrl.includes('/dashboard')) {
      console.log('✅ 로그인 성공! 프로필 페이지 접속 시도...');

      await page.goto('https://www.topmktx.com/profile', { waitUntil: 'networkidle' });

      const profileCheck = await page.evaluate(() => {
        return {
          url: window.location.href,
          title: document.title,
          hasProfileContent: !!document.querySelector('.profile, .user-profile, .user-info, .profile-container')
        };
      });

      console.log('프로필 페이지 상태:', profileCheck);

      await page.screenshot({
        path: '/var/www/html/topmkt/profile-after-login.png',
        fullPage: true
      });

    } else {
      console.log('❌ 로그인 실패 - 다른 비밀번호 시도 필요');

      await page.screenshot({
        path: '/var/www/html/topmkt/login-failed.png',
        fullPage: true
      });
    }

  } catch (error) {
    console.error('❌ 로그인 시도 실패:', error.message);
  }

  await browser.close();
})();