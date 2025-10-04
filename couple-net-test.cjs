const { chromium } = require('playwright');

(async () => {
  console.log('브라우저 시작 중...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
  });
  const page = await context.newPage();

  try {
    console.log('couple.net 접속 중...');
    await page.goto('https://www.couple.net/kr/', { waitUntil: 'networkidle' });

    console.log('현재 페이지 제목:', await page.title());
    console.log('현재 URL:', page.url());

    // 초기 페이지 스크린샷
    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-home.png', fullPage: true });
    console.log('홈페이지 스크린샷 저장됨: couple-net-home.png');

    // 로그인 링크/버튼 찾기
    console.log('로그인 요소 검색 중...');
    const loginSelectors = [
      'a[href*="login"]',
      'input[type="button"][value*="로그인"]',
      'button:has-text("로그인")',
      '.login',
      '#login',
      'a:has-text("로그인")'
    ];

    let loginElement = null;
    for (const selector of loginSelectors) {
      try {
        loginElement = await page.$(selector);
        if (loginElement) {
          console.log('로그인 요소 발견:', selector);
          break;
        }
      } catch (e) {
        continue;
      }
    }

    if (!loginElement) {
      // 페이지의 모든 링크 확인
      const links = await page.$$eval('a', links =>
        links.map(link => ({ href: link.href, text: link.textContent.trim() }))
      );
      console.log('페이지의 모든 링크:');
      links.forEach(link => {
        if (link.text && (link.text.includes('로그인') || link.text.includes('login') || link.href.includes('login'))) {
          console.log('- ' + link.text + ': ' + link.href);
        }
      });
    }

    // 로그인 페이지 직접 접근 시도
    console.log('로그인 페이지 직접 접근 시도...');
    await page.goto('https://www.couple.net/kr/login/', { waitUntil: 'networkidle' });

    if (page.url().includes('login')) {
      console.log('로그인 페이지 접근 성공');
      await page.screenshot({ path: '/var/www/html/topmkt/couple-net-login.png', fullPage: true });
      console.log('로그인 페이지 스크린샷 저장됨: couple-net-login.png');

      // 로그인 폼 요소 찾기
      const idInput = await page.$('input[name="id"], input[name="user_id"], input[name="userid"], input[type="text"]');
      const pwInput = await page.$('input[name="pw"], input[name="password"], input[name="passwd"], input[type="password"]');

      if (idInput && pwInput) {
        console.log('로그인 폼 발견, 로그인 시도...');
        await idInput.fill('redhack777');
        await pwInput.fill('fpemgor77!');

        // 로그인 버튼 찾기 및 클릭
        const submitBtn = await page.$('input[type="submit"], button[type="submit"], input[value*="로그인"], button:has-text("로그인")');
        if (submitBtn) {
          await submitBtn.click();
          console.log('로그인 버튼 클릭됨');

          // 로그인 후 대기
          await page.waitForTimeout(3000);

          console.log('로그인 후 URL:', page.url());
          await page.screenshot({ path: '/var/www/html/topmkt/couple-net-after-login.png', fullPage: true });
          console.log('로그인 후 스크린샷 저장됨: couple-net-after-login.png');

          // 목표 이미지 URL 접근
          console.log('목표 이미지 URL 접근 중...');
          await page.goto('https://www.couple.net/proc/img_view.asp?p2=N&p1=RjIwMjIwOTAwMDA1MQ==&p3=5', { waitUntil: 'networkidle' });

          console.log('이미지 페이지 URL:', page.url());
          await page.screenshot({ path: '/var/www/html/topmkt/couple-net-target-image.png', fullPage: true });
          console.log('목표 이미지 페이지 스크린샷 저장됨: couple-net-target-image.png');

          // 이미지 요소 확인
          const images = await page.$$eval('img', imgs =>
            imgs.map(img => ({ src: img.src, alt: img.alt, width: img.width, height: img.height }))
          );

          console.log('페이지의 이미지들:');
          images.forEach((img, index) => {
            console.log('이미지 ' + (index + 1) + ': ' + img.src + ' (' + img.width + 'x' + img.height + ') - ' + img.alt);
          });

        } else {
          console.log('로그인 버튼을 찾을 수 없음');
        }
      } else {
        console.log('로그인 폼을 찾을 수 없음');
        console.log('ID 입력란:', !!idInput);
        console.log('PW 입력란:', !!pwInput);
      }
    } else {
      console.log('로그인 페이지 접근 실패:', page.url());
    }

  } catch (error) {
    console.error('오류 발생:', error.message);
    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-error.png', fullPage: true });
  } finally {
    await browser.close();
    console.log('브라우저 종료됨');
  }
})();