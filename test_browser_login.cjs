const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  console.log('🔑 브라우저를 통한 로그인 및 프로필 페이지 테스트...');

  try {
    // 1. DevLoginHelper로 로그인
    console.log('1️⃣ DevLoginHelper로 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    // 페이지 로딩 대기
    await page.waitForTimeout(3000);

    console.log('   ✅ DevLoginHelper 페이지 로드 완료');

    // 쿠키 확인
    const cookies = await page.context().cookies();
    const authCookie = cookies.find(c => c.name === 'auth_token');

    if (authCookie) {
      console.log('   ✅ auth_token 쿠키 설정됨');
      console.log('   🍪 토큰 길이:', authCookie.value.length, '문자');
    } else {
      console.log('   ❌ auth_token 쿠키가 없음');
      console.log('   📋 현재 쿠키들:', cookies.map(c => c.name).join(', '));
    }

    // 2. 프로필 페이지로 이동
    console.log('\n2️⃣ 프로필 페이지로 이동...');
    const response = await page.goto('https://www.topmktx.com/profile', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    console.log('   📡 응답 상태:', response.status());
    console.log('   🌐 현재 URL:', page.url());

    // 3. 페이지 내용 분석
    const pageAnalysis = await page.evaluate(() => {
      return {
        title: document.title,
        hasProfileContainer: !!document.querySelector('.profile-container, .profile-content, .user-profile'),
        hasErrorMessage: !!document.querySelector('.error, .alert-danger') || document.body.textContent.includes('프로필을 불러오는 중 오류가 발생했습니다'),
        hasUserName: document.body.textContent.includes('우리집탄이'),
        bodyContentLength: document.body.textContent.trim().length,
        mainContentText: document.querySelector('main')?.textContent?.trim()?.substring(0, 200) || 'No main content'
      };
    });

    console.log('\n3️⃣ 페이지 분석 결과:');
    console.log('   📄 제목:', pageAnalysis.title);
    console.log('   👤 프로필 컨테이너 존재:', pageAnalysis.hasProfileContainer ? '✅' : '❌');
    console.log('   ❌ 오류 메시지 존재:', pageAnalysis.hasErrorMessage ? '❌' : '✅');
    console.log('   🏷️ 우리집탄이 이름 포함:', pageAnalysis.hasUserName ? '✅' : '❌');
    console.log('   📝 본문 내용 길이:', pageAnalysis.bodyContentLength, '문자');
    console.log('   📖 메인 콘텐츠:', pageAnalysis.mainContentText);

    // 4. 결과 판정
    console.log('\n4️⃣ 최종 결과:');
    if (pageAnalysis.hasProfileContainer && pageAnalysis.hasUserName && !pageAnalysis.hasErrorMessage) {
      console.log('   🎉 성공! 프로필 페이지가 정상적으로 표시됨');

      // 성공 스크린샷
      await page.screenshot({
        path: '/var/www/html/topmkt/profile-browser-success.png',
        fullPage: true
      });
      console.log('   📸 성공 스크린샷: profile-browser-success.png');

    } else if (pageAnalysis.hasErrorMessage) {
      console.log('   ❌ 실패! 오류 메시지가 표시됨');

      // 실패 스크린샷
      await page.screenshot({
        path: '/var/www/html/topmkt/profile-browser-error.png',
        fullPage: true
      });
      console.log('   📸 오류 스크린샷: profile-browser-error.png');

    } else {
      console.log('   ⚠️ 부분 성공! 프로필 내용이 표시되지 않음');

      // 부분 성공 스크린샷
      await page.screenshot({
        path: '/var/www/html/topmkt/profile-browser-partial.png',
        fullPage: true
      });
      console.log('   📸 부분 성공 스크린샷: profile-browser-partial.png');
    }

    // 5. 추가 디버깅 정보
    if (pageAnalysis.bodyContentLength < 5000) {
      console.log('\n5️⃣ 추가 디버깅 (내용이 적음):');

      const htmlContent = await page.content();

      // HTML에서 오류 패턴 검색
      const errorPatterns = [
        /Fatal error:/gi,
        /Parse error:/gi,
        /Warning:/gi,
        /Notice:/gi,
        /프로필을 불러오는 중 오류가 발생했습니다/gi
      ];

      let hasHtmlError = false;
      errorPatterns.forEach((pattern, index) => {
        const matches = htmlContent.match(pattern);
        if (matches) {
          hasHtmlError = true;
          console.log(`   🚫 HTML 오류 패턴 ${index + 1} 발견: ${matches.length}개`);
        }
      });

      if (!hasHtmlError) {
        console.log('   ✅ HTML에서 PHP 오류 패턴 없음');
      }
    }

  } catch (error) {
    console.error('❌ 테스트 실패:', error.message);

    // 오류 스크린샷
    await page.screenshot({
      path: '/var/www/html/topmkt/profile-browser-crash.png',
      fullPage: true
    });
    console.log('📸 오류 스크린샷: profile-browser-crash.png');
  }

  await browser.close();
  console.log('\n🏁 브라우저 테스트 완료');
})();