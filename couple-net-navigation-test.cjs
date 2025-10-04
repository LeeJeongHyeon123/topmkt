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
    // 메인 페이지 접속 및 로그인
    await page.goto('https://www.couple.net/kr/', { waitUntil: 'networkidle' });

    const loginLink = await page.$('.login');
    if (loginLink) {
      await loginLink.click();
      await page.waitForTimeout(2000);
    }

    await page.fill('input[name="txt_login_id"]', 'redhack777');
    await page.fill('input[name="txt_login_pwd"]', 'fpemgor77!');
    await page.click('button:has-text("로그인")');
    await page.waitForTimeout(3000);

    console.log('로그인 완료');

    // 팝업 핸들러 설정 (새 창이 열릴 경우 대비)
    context.on('page', async (newPage) => {
      console.log('새 페이지가 열렸습니다:', newPage.url());

      try {
        await newPage.waitForLoadState('domcontentloaded');
        const content = await newPage.content();
        console.log('새 페이지 내용 길이:', content.length);

        if (content.length > 200) { // 의미있는 내용이 있다면
          console.log('새 페이지 내용 (처음 500자):');
          console.log(content.substring(0, 500));

          // 이미지 확인
          const images = await newPage.$$eval('img', imgs =>
            imgs.map(img => ({
              src: img.src,
              width: img.naturalWidth,
              height: img.naturalHeight
            }))
          );

          if (images.length > 0) {
            console.log('새 페이지에서 이미지 발견:', images);
            await newPage.screenshot({ path: '/var/www/html/topmkt/couple-net-popup-image.png', fullPage: true });
            console.log('팝업 이미지 스크린샷 저장됨');
          }
        }
      } catch (e) {
        console.log('새 페이지 처리 오류:', e.message);
      }
    });

    // JavaScript로 이미지 뷰어 함수 시뮬레이션
    console.log('이미지 뷰어 함수 정의 및 실행...');

    await page.evaluate(() => {
      // 일반적인 이미지 뷰어 함수 정의
      window.showImage = function(p1, p2, p3) {
        const url = '/proc/img_view.asp?p2=' + p2 + '&p1=' + p1 + '&p3=' + p3;
        console.log('이미지 URL 생성:', url);

        // 새 창으로 열기 시도
        const popup = window.open(url, 'imageView', 'width=800,height=600,scrollbars=yes,resizable=yes');

        if (popup) {
          console.log('팝업 창 열기 성공');
          return popup;
        } else {
          console.log('팝업 차단됨');
          return null;
        }
      };

      // img_view 함수가 존재하는지 확인하고 실행
      if (typeof img_view !== 'undefined') {
        console.log('기존 img_view 함수 발견');
        return img_view('N', 'RjIwMjIwOTAwMDA1MQ==', '5');
      } else {
        console.log('img_view 함수 없음, 직접 실행');
        return window.showImage('RjIwMjIwOTAwMDA1MQ==', 'N', '5');
      }
    });

    await page.waitForTimeout(3000);

    // 직접 iframe으로 로드 시도
    console.log('iframe으로 이미지 로드 시도...');

    await page.evaluate(() => {
      const iframe = document.createElement('iframe');
      iframe.src = '/proc/img_view.asp?p2=N&p1=RjIwMjIwOTAwMDA1MQ==&p3=5';
      iframe.width = '800';
      iframe.height = '600';
      iframe.id = 'imageFrame';
      document.body.appendChild(iframe);
    });

    await page.waitForTimeout(5000);

    // iframe 내용 확인
    const iframeContent = await page.evaluate(() => {
      const iframe = document.getElementById('imageFrame');
      if (iframe) {
        try {
          return iframe.contentDocument ? iframe.contentDocument.documentElement.outerHTML : '접근 불가';
        } catch (e) {
          return '크로스 오리진 오류: ' + e.message;
        }
      }
      return 'iframe 없음';
    });

    console.log('iframe 내용:', iframeContent);

    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-with-iframe.png', fullPage: true });

    // 네비게이션을 통한 접근 시도 (같은 탭에서)
    console.log('같은 탭에서 이미지 페이지로 이동...');

    // 현재 페이지에서 이미지 URL로 이동
    await page.goto('https://www.couple.net/proc/img_view.asp?p2=N&p1=RjIwMjIwOTAwMDA1MQ==&p3=5');
    await page.waitForTimeout(2000);

    const finalContent = await page.content();
    console.log('최종 결과:', finalContent);

    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-same-tab-result.png', fullPage: true });

  } catch (error) {
    console.error('오류 발생:', error.message);
    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-nav-error.png', fullPage: true });
  } finally {
    await browser.close();
    console.log('브라우저 종료됨');
  }
})();