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
    console.log('couple.net 접속 및 로그인...');
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

    // Base64 디코딩해보기
    const base64Param = 'RjIwMjIwOTAwMDA1MQ==';
    const decoded = Buffer.from(base64Param, 'base64').toString('utf8');
    console.log('Base64 디코딩 결과:', decoded);

    // 회원 프로필이나 사진 관련 섹션 찾기
    console.log('회원 관련 페이지들 탐색...');

    // 가능한 페이지들 시도
    const pagesToTry = [
      'https://www.couple.net/kr/member/',
      'https://www.couple.net/kr/photo/',
      'https://www.couple.net/kr/profile/',
      'https://www.couple.net/kr/mypage/',
      'https://www.couple.net/member/',
      'https://www.couple.net/photo/',
      'https://www.couple.net/profile/'
    ];

    for (const url of pagesToTry) {
      try {
        console.log('시도 중:', url);
        const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 10000 });

        if (response.status() === 200) {
          console.log('성공:', url, '(상태:', response.status(), ')');

          // 페이지에서 img_view.asp를 포함하는 링크나 이미지 찾기
          const relevantLinks = await page.$$eval('a', links =>
            links.filter(link => link.href.includes('img_view.asp')).map(link => ({
              href: link.href,
              text: link.textContent.trim()
            }))
          );

          if (relevantLinks.length > 0) {
            console.log('img_view.asp 링크 발견:', relevantLinks);
          }

          // 페이지의 모든 이미지 확인
          const images = await page.$$eval('img', imgs =>
            imgs.map(img => ({
              src: img.src,
              alt: img.alt || '',
              onclick: img.onclick ? img.onclick.toString() : ''
            })).filter(img => img.src.includes('couple.net') || img.onclick.includes('img_view'))
          );

          if (images.length > 0) {
            console.log('관련 이미지 발견:', images.length);
            images.forEach((img, index) => {
              console.log('이미지 ' + (index + 1) + ':', img.src);
              if (img.onclick) {
                console.log('  onclick:', img.onclick.substring(0, 100) + '...');
              }
            });
          }

          await page.screenshot({ path: '/var/www/html/topmkt/couple-net-page-' + url.split('/').pop() + '.png', fullPage: true });
        }
      } catch (e) {
        console.log('실패:', url, '(오류:', e.message, ')');
      }

      await page.waitForTimeout(1000);
    }

    // JavaScript로 직접 img_view 함수 실행 시도
    console.log('JavaScript 함수 직접 실행 시도...');
    try {
      await page.evaluate(() => {
        // img_view 함수가 있는지 확인
        if (typeof img_view === 'function') {
          console.log('img_view 함수 발견');
          img_view('N', 'RjIwMjIwOTAwMDA1MQ==', '5');
        } else if (typeof window.img_view === 'function') {
          console.log('window.img_view 함수 발견');
          window.img_view('N', 'RjIwMjIwOTAwMDA1MQ==', '5');
        } else {
          console.log('img_view 함수를 찾을 수 없음');
        }
      });
    } catch (e) {
      console.log('JavaScript 실행 오류:', e.message);
    }

    // 새 탭으로 열기 시도 (팝업)
    console.log('새 탭에서 이미지 접근 시도...');
    const newPage = await context.newPage();

    // 참조자(referrer) 설정
    await newPage.goto('https://www.couple.net/kr/', { waitUntil: 'domcontentloaded' });

    // 이미지 URL에 접근
    await newPage.goto('https://www.couple.net/proc/img_view.asp?p2=N&p1=RjIwMjIwOTAwMDA1MQ==&p3=5', { waitUntil: 'domcontentloaded' });

    const newPageContent = await newPage.content();
    console.log('새 탭에서 접근한 결과:', newPageContent);

    await newPage.screenshot({ path: '/var/www/html/topmkt/couple-net-new-tab-result.png', fullPage: true });
    await newPage.close();

  } catch (error) {
    console.error('오류 발생:', error.message);
    await page.screenshot({ path: '/var/www/html/topmkt/couple-net-search-error.png', fullPage: true });
  } finally {
    await browser.close();
    console.log('브라우저 종료됨');
  }
})();