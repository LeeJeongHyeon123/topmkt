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
    // 먼저 메인 페이지 접속
    console.log('couple.net 메인 페이지 접속...');
    await page.goto('https://www.couple.net/kr/', { waitUntil: 'networkidle' });

    // 로그인 버튼 클릭
    const loginLink = await page.$('.login');
    if (loginLink) {
      await loginLink.click();
      await page.waitForTimeout(2000);
    }

    // 로그인 정보 입력
    await page.fill('input[name="txt_login_id"]', 'redhack777');
    await page.fill('input[name="txt_login_pwd"]', 'fpemgor77!');

    // 로그인 버튼 클릭
    await page.click('button:has-text("로그인")');
    await page.waitForTimeout(3000);

    console.log('로그인 완료. 현재 URL:', page.url());

    // 로그인 후 쿠키 확인
    const cookies = await context.cookies();
    console.log('현재 쿠키 수:', cookies.length);
    cookies.forEach((cookie, index) => {
      console.log('쿠키 ' + (index + 1) + ': ' + cookie.name + ' = ' + cookie.value.substring(0, 20) + '...');
    });

    // 목표 URL로 직접 이동 (쿠키 포함)
    console.log('목표 이미지 URL로 이동...');
    const targetUrl = 'https://www.couple.net/proc/img_view.asp?p2=N&p1=RjIwMjIwOTAwMDA1MQ==&p3=5';

    // 네트워크 요청 모니터링
    page.on('response', response => {
      console.log('응답:', response.status(), response.url());
    });

    await page.goto(targetUrl, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);

    console.log('이미지 페이지 접근 후 URL:', page.url());
    console.log('페이지 상태:', await page.evaluate(() => document.readyState));

    // 페이지 HTML 소스 확인
    const htmlContent = await page.content();
    console.log('HTML 내용 길이:', htmlContent.length);
    console.log('HTML 내용 (처음 500자):');
    console.log(htmlContent.substring(0, 500));

    // 스크린샷 저장
    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-final-result.png', fullPage: true });
    console.log('최종 결과 스크린샷 저장됨');

    // 이미지 태그 확인
    const images = await page.$$eval('img', imgs =>
      imgs.map(img => ({
        src: img.src,
        alt: img.alt || '',
        width: img.width,
        height: img.height,
        complete: img.complete,
        naturalWidth: img.naturalWidth,
        naturalHeight: img.naturalHeight
      }))
    );

    console.log('발견된 이미지 수:', images.length);
    images.forEach((img, index) => {
      console.log('이미지 ' + (index + 1) + ':');
      console.log('  - URL: ' + img.src);
      console.log('  - 로딩 완료: ' + img.complete);
      console.log('  - 크기: ' + img.naturalWidth + 'x' + img.naturalHeight);
      console.log('  - 표시 크기: ' + img.width + 'x' + img.height);
      console.log('  - Alt: ' + img.alt);
    });

    // 페이지에 오류 메시지가 있는지 확인
    const bodyText = await page.textContent('body');
    console.log('페이지 텍스트 내용:');
    console.log(bodyText.trim());

    // iframe이나 다른 요소들도 확인
    const iframes = await page.$$('iframe');
    console.log('iframe 수:', iframes.length);

    const scripts = await page.$$('script');
    console.log('스크립트 태그 수:', scripts.length);

    // 만약 이미지가 없다면 다른 형태의 컨텐츠 확인
    if (images.length === 0) {
      console.log('이미지가 없습니다. 다른 요소들 확인...');

      // 모든 요소 확인
      const allElements = await page.$$eval('*', elements =>
        elements.map(el => ({
          tag: el.tagName,
          id: el.id,
          className: el.className,
          textContent: el.textContent ? el.textContent.trim().substring(0, 50) : ''
        })).filter(el => el.textContent.length > 0)
      );

      console.log('텍스트가 있는 요소들:');
      allElements.forEach((el, index) => {
        if (index < 10) { // 처음 10개만
          console.log(el.tag + ' (id: ' + el.id + ', class: ' + el.className + '): ' + el.textContent);
        }
      });
    }

  } catch (error) {
    console.error('오류 발생:', error.message);
    console.error('오류 스택:', error.stack);
    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-error-final.png', fullPage: true });
  } finally {
    await browser.close();
    console.log('브라우저 종료됨');
  }
})();