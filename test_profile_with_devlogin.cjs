const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  console.log('🔑 DevLoginHelper로 우리집탄이 계정(user_id=4) 로그인...');

  try {
    // DevLoginHelper로 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    console.log('✅ DevLoginHelper 로그인 완료');

    // 프로필 페이지 접속
    await page.goto('https://www.topmktx.com/profile', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    const profileResult = await page.evaluate(() => {
      return {
        url: window.location.href,
        title: document.title,
        hasProfile: !!document.querySelector('.profile, .user-profile, .profile-container, .profile-header'),
        hasError: !!document.querySelector('.error, .alert-danger'),
        hasLoginForm: !!document.querySelector('form[action*="login"]'),
        bodyClass: document.body.className,
        mainContent: document.querySelector('main, .main-content, .container')?.innerHTML?.substring(0, 200) || 'No main content'
      };
    });

    console.log('🎯 프로필 페이지 접속 결과:');
    console.log('URL:', profileResult.url);
    console.log('제목:', profileResult.title);
    console.log('프로필 콘텐츠 존재:', profileResult.hasProfile ? '✅' : '❌');
    console.log('오류 메시지 존재:', profileResult.hasError ? '❌' : '✅');
    console.log('로그인 폼 존재:', profileResult.hasLoginForm ? '❌ (아직 로그인 안됨)' : '✅');

    if (profileResult.url.includes('/profile') && !profileResult.url.includes('/login')) {
      console.log('');
      console.log('🎉 성공! 프로필 페이지 정상 접속됨');

      await page.screenshot({
        path: '/var/www/html/topmkt/profile-success-우리집탄이.png',
        fullPage: true
      });

      console.log('📸 성공 스크린샷: profile-success-우리집탄이.png');

    } else {
      console.log('');
      console.log('❌ 프로필 페이지 접속 실패 - 여전히 로그인 페이지로 리다이렉트됨');

      await page.screenshot({
        path: '/var/www/html/topmkt/profile-fail-after-devlogin.png',
        fullPage: true
      });

      console.log('📸 실패 스크린샷: profile-fail-after-devlogin.png');
      console.log('메인 콘텐츠:', profileResult.mainContent);
    }

  } catch (error) {
    console.error('❌ 테스트 실패:', error.message);
  }

  await browser.close();
})();