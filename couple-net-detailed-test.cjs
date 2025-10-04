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

    // 로그인 링크 클릭
    console.log('로그인 요소 클릭 시도...');
    const loginLink = await page.$('.login');
    if (loginLink) {
      await loginLink.click();
      console.log('로그인 링크 클릭됨');
      await page.waitForTimeout(2000);
    }

    console.log('현재 URL:', page.url());
    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-login-page.png', fullPage: true });
    console.log('로그인 페이지 스크린샷 저장됨');

    // 모든 입력 요소 찾기
    const allInputs = await page.$$eval('input', inputs =>
      inputs.map(input => ({
        type: input.type,
        name: input.name,
        id: input.id,
        placeholder: input.placeholder,
        value: input.value
      }))
    );

    console.log('페이지의 모든 입력 요소:');
    allInputs.forEach((input, index) => {
      console.log('입력 ' + (index + 1) + ': type=' + input.type + ', name=' + input.name + ', id=' + input.id + ', placeholder=' + input.placeholder);
    });

    // 모든 버튼 찾기
    const allButtons = await page.$$eval('button, input[type="submit"], input[type="button"]', buttons =>
      buttons.map(btn => ({
        tag: btn.tagName,
        type: btn.type,
        value: btn.value,
        text: btn.textContent.trim()
      }))
    );

    console.log('페이지의 모든 버튼:');
    allButtons.forEach((btn, index) => {
      console.log('버튼 ' + (index + 1) + ': ' + btn.tag + ', type=' + btn.type + ', value=' + btn.value + ', text=' + btn.text);
    });

    // 아이디와 비밀번호 입력 시도 (다양한 셀렉터로)
    let idFilled = false;
    let pwFilled = false;

    // 아이디 입력란 찾기 시도
    const idSelectors = [
      'input[name="userid"]',
      'input[name="user_id"]',
      'input[name="id"]',
      'input[name="login_id"]',
      'input[name="member_id"]',
      'input[placeholder*="아이디"]',
      'input[placeholder*="ID"]',
      'input[type="text"]'
    ];

    for (const selector of idSelectors) {
      try {
        const element = await page.$(selector);
        if (element) {
          await element.fill('redhack777');
          console.log('아이디 입력 성공 (' + selector + ')');
          idFilled = true;
          break;
        }
      } catch (e) {
        continue;
      }
    }

    // 비밀번호 입력란 찾기 시도
    const pwSelectors = [
      'input[name="password"]',
      'input[name="passwd"]',
      'input[name="pw"]',
      'input[name="user_pw"]',
      'input[placeholder*="비밀번호"]',
      'input[placeholder*="Password"]',
      'input[type="password"]'
    ];

    for (const selector of pwSelectors) {
      try {
        const element = await page.$(selector);
        if (element) {
          await element.fill('fpemgor77!');
          console.log('비밀번호 입력 성공 (' + selector + ')');
          pwFilled = true;
          break;
        }
      } catch (e) {
        continue;
      }
    }

    if (idFilled && pwFilled) {
      console.log('로그인 정보 입력 완료');
      await page.screenshot({ path: '/var/www/html/topmkt/couple-net-before-submit.png', fullPage: true });

      // 로그인 버튼 클릭 시도
      const submitSelectors = [
        'input[type="submit"]',
        'button[type="submit"]',
        'input[value*="로그인"]',
        'button:has-text("로그인")',
        '.login-btn',
        '#login-btn',
        '.btn-login'
      ];

      let submitted = false;
      for (const selector of submitSelectors) {
        try {
          const element = await page.$(selector);
          if (element) {
            await element.click();
            console.log('로그인 버튼 클릭됨 (' + selector + ')');
            submitted = true;
            break;
          }
        } catch (e) {
          continue;
        }
      }

      if (submitted) {
        // 로그인 결과 대기
        await page.waitForTimeout(5000);
        console.log('로그인 후 URL:', page.url());
        await page.screenshot({ path: '/var/www/html/topmkt/couple-net-after-login.png', fullPage: true });

        // 목표 이미지 페이지로 이동
        console.log('목표 이미지 페이지로 이동...');
        await page.goto('https://www.couple.net/proc/img_view.asp?p2=N&p1=RjIwMjIwOTAwMDA1MQ==&p3=5', { waitUntil: 'networkidle' });

        console.log('이미지 페이지 URL:', page.url());
        await page.screenshot({ path: '/var/www/html/topmkt/couple-net-target-image.png', fullPage: true });
        console.log('목표 이미지 페이지 스크린샷 저장됨');

        // 페이지 내용 분석
        const pageContent = await page.content();
        console.log('페이지 내용 길이:', pageContent.length);

        // 이미지 요소 확인
        const images = await page.$$eval('img', imgs =>
          imgs.map(img => ({
            src: img.src,
            alt: img.alt,
            width: img.width,
            height: img.height,
            naturalWidth: img.naturalWidth,
            naturalHeight: img.naturalHeight
          }))
        );

        console.log('페이지의 이미지 개수:', images.length);
        images.forEach((img, index) => {
          console.log('이미지 ' + (index + 1) + ':');
          console.log('  - src: ' + img.src);
          console.log('  - alt: ' + img.alt);
          console.log('  - 표시 크기: ' + img.width + 'x' + img.height);
          console.log('  - 실제 크기: ' + img.naturalWidth + 'x' + img.naturalHeight);
        });

        // 페이지 제목 확인
        console.log('페이지 제목:', await page.title());

        // 페이지에 특정 텍스트가 있는지 확인
        const bodyText = await page.textContent('body');
        console.log('페이지 본문 텍스트 (처음 500자):');
        console.log(bodyText.substring(0, 500));

      } else {
        console.log('로그인 버튼을 찾을 수 없음');
      }
    } else {
      console.log('로그인 정보 입력 실패');
      console.log('아이디 입력:', idFilled);
      console.log('비밀번호 입력:', pwFilled);
    }

  } catch (error) {
    console.error('오류 발생:', error.message);
    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-error.png', fullPage: true });
    console.log('오류 스크린샷 저장됨');
  } finally {
    await browser.close();
    console.log('브라우저 종료됨');
  }
})();