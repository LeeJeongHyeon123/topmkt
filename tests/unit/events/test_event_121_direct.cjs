const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('1. 행사 상세 페이지(ID: 121) 직접 접속...');
    await page.goto('https://www.topmktx.com/events/detail?id=121');
    await page.waitForTimeout(3000);

    console.log('2. 페이지 로딩 상태 확인...');
    const title = await page.title();
    console.log('페이지 제목:', title);

    // 페이지 URL 확인
    const currentUrl = page.url();
    console.log('현재 URL:', currentUrl);

    // 행사 제목 확인
    const eventTitle = await page.textContent('h1').catch(() => null);
    console.log('행사 제목:', eventTitle);

    console.log('3. 신청 버튼 영역 분석...');

    // 페이지 전체 HTML 일부 확인 (디버깅용)
    const bodyContent = await page.textContent('body').catch(() => '');
    console.log('페이지 내용 미리보기:', bodyContent.substring(0, 200) + '...');

    // 신청 관련 텍스트 검색
    const hasOwnEventText = bodyContent.includes('본인이 등록한 행사');
    const hasRegisterText = bodyContent.includes('신청하기') || bodyContent.includes('신청');
    const hasClosedText = bodyContent.includes('마감') || bodyContent.includes('종료');

    console.log('본인 행사 텍스트 포함:', hasOwnEventText);
    console.log('신청 관련 텍스트 포함:', hasRegisterText);
    console.log('마감 관련 텍스트 포함:', hasClosedText);

    // 모든 버튼 텍스트 확인
    const buttons = await page.locator('button').all();
    console.log('페이지 내 버튼 수:', buttons.length);
    for (let i = 0; i < buttons.length; i++) {
      const buttonText = await buttons[i].textContent().catch(() => '');
      if (buttonText.trim()) {
        console.log(`버튼 ${i + 1}: "${buttonText.trim()}"`);
      }
    }

    // 링크 중에서 신청 관련된 것 확인
    const links = await page.locator('a').all();
    console.log('페이지 내 링크 수:', links.length);
    for (let i = 0; i < links.length; i++) {
      const linkText = await links[i].textContent().catch(() => '');
      if (linkText.trim() && (linkText.includes('신청') || linkText.includes('등록'))) {
        console.log(`신청 관련 링크: "${linkText.trim()}"`);
      }
    }

    console.log('4. 스크린샷 촬영...');
    await page.screenshot({
      path: 'event-detail-121-direct.png',
      fullPage: true
    });

    console.log('5. 특정 영역 분석...');
    // 이벤트 관련 클래스들 찾기
    const eventClasses = [
      '.event-detail',
      '.event-info',
      '.event-content',
      '.application-area',
      '.register-section',
      '.event-actions',
      '.btn-register',
      '.register-btn'
    ];

    for (const className of eventClasses) {
      const element = await page.locator(className).first().isVisible().catch(() => false);
      if (element) {
        console.log(`찾은 클래스: ${className}`);
        const elementText = await page.locator(className).first().textContent().catch(() => '');
        console.log(`내용: ${elementText.substring(0, 100)}...`);
      }
    }

    console.log('\n=== 테스트 완료 ===');
    console.log('스크린샷: event-detail-121-direct.png');

  } catch (error) {
    console.error('오류 발생:', error.message);
    await page.screenshot({ path: 'event-detail-121-error.png' });
  }

  await browser.close();
})();