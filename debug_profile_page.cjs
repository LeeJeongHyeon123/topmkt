const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  // 콘솔 오류 캡처
  page.on('console', msg => {
    if (msg.type() === 'error') {
      console.log('JS 오류:', msg.text());
    }
  });

  page.on('pageerror', error => {
    console.log('페이지 오류:', error.message);
  });

  try {
    // DevLoginHelper로 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', { waitUntil: 'networkidle' });

    // 프로필 페이지 접속
    const response = await page.goto('https://www.topmktx.com/profile', { waitUntil: 'networkidle' });

    console.log('응답 상태 코드:', response.status());

    // 페이지 소스에서 오류 메시지 찾기
    const pageContent = await page.content();

    // PHP 오류 패턴 검색
    const phpErrorPatterns = [
      /Fatal error:/gi,
      /Parse error:/gi,
      /Warning:/gi,
      /Notice:/gi,
      /Error:/gi
    ];

    console.log('');
    console.log('🔍 PHP 오류 검색 결과:');

    let hasError = false;
    phpErrorPatterns.forEach((pattern, index) => {
      const matches = pageContent.match(pattern);
      if (matches) {
        hasError = true;
        console.log(`패턴 ${index + 1} 매치: ${matches.length}개`);

        // 오류 주변 텍스트 추출
        const errorLines = pageContent.split('\n').filter(line => pattern.test(line));
        errorLines.slice(0, 3).forEach(line => {
          console.log('  오류 라인:', line.trim());
        });
      }
    });

    if (!hasError) {
      console.log('✅ PHP 오류 없음');
    }

    // HTML 구조 분석
    const structureAnalysis = await page.evaluate(() => {
      return {
        hasMain: !!document.querySelector('main, .main-content'),
        hasContainer: !!document.querySelector('.container, .profile-container'),
        hasProfile: !!document.querySelector('.profile, .user-profile'),
        bodyClasses: document.body.className,
        htmlLength: document.documentElement.innerHTML.length,
        titleText: document.title,
        metaContent: document.querySelector('main, .main-content, .container')?.textContent?.trim() || 'No main content'
      };
    });

    console.log('');
    console.log('🏗️ HTML 구조 분석:');
    console.log('Main 요소 존재:', structureAnalysis.hasMain ? '✅' : '❌');
    console.log('Container 존재:', structureAnalysis.hasContainer ? '✅' : '❌');
    console.log('Profile 요소 존재:', structureAnalysis.hasProfile ? '✅' : '❌');
    console.log('HTML 길이:', structureAnalysis.htmlLength);
    console.log('페이지 제목:', structureAnalysis.titleText);
    console.log('메인 콘텐츠 텍스트:', structureAnalysis.metaContent.substring(0, 100));

    // 페이지 HTML을 파일로 저장
    const fs = require('fs');
    fs.writeFileSync('/var/www/html/topmkt/profile-page-source.html', pageContent);
    console.log('');
    console.log('📄 페이지 소스 저장: profile-page-source.html');

  } catch (error) {
    console.error('❌ 테스트 실패:', error.message);
  }

  await browser.close();
})();