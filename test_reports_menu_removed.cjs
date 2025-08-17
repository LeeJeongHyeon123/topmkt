const { chromium } = require('playwright');

(async () => {
  console.log('🔍 Ultra Think 6단계: 신고 관리 메뉴 제거 검증 테스트');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 }
  });
  
  const page = await context.newPage();
  
  try {
    console.log('🔑 1단계: DevLoginHelper로 우리집탄이 관리자 로그인...');
    
    // DevLoginHelper로 관리자 로그인
    const loginResponse = await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', { 
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    if (loginResponse.status() !== 200) {
      throw new Error(`DevLoginHelper 접근 실패: HTTP ${loginResponse.status()}`);
    }
    
    const pageContent = await page.content();
    if (pageContent.includes('자동 로그인 완료')) {
      console.log('✅ DevLoginHelper 성공');
    } else {
      throw new Error('DevLoginHelper 로그인 실패');
    }
    
    await page.waitForTimeout(3000);
    
    console.log('📊 2단계: 관리자 대시보드 접근...');
    
    // 관리자 대시보드로 이동
    await page.goto('https://www.topmktx.com/admin', { 
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    const currentUrl = page.url();
    if (!currentUrl.includes('/admin')) {
      throw new Error('관리자 페이지 접근 실패');
    }
    console.log('✅ 관리자 대시보드 접근 성공');
    
    console.log('🚫 3단계: 신고 관리 메뉴 제거 확인...');
    
    // 신고 관리 메뉴가 제거되었는지 확인
    const reportsMenuExists = await page.isVisible('a[href="/admin/reports"]');
    console.log(`신고 관리 메뉴 존재: ${reportsMenuExists ? '❌ 아직 있음' : '✅ 성공적으로 제거됨'}`);
    
    if (reportsMenuExists) {
      console.log('⚠️ 오류: 신고 관리 메뉴가 아직 존재합니다!');
      const menuText = await page.textContent('a[href="/admin/reports"]');
      console.log(`메뉴 텍스트: "${menuText}"`);
    } else {
      console.log('✅ 신고 관리 메뉴 제거 성공');
    }
    
    console.log('📝 4단계: 나머지 관리자 메뉴 정상 작동 확인...');
    
    // 모든 사이드바 메뉴 항목 확인
    const allMenuItems = await page.locator('.sidebar-nav a.nav-item').all();
    console.log(`\n📋 현재 메뉴 항목 수: ${allMenuItems.length}`);
    
    const menuTestResults = [];
    
    for (let i = 0; i < allMenuItems.length; i++) {
      const item = allMenuItems[i];
      const href = await item.getAttribute('href');
      const text = await item.textContent();
      const cleanText = text.replace(/\s+/g, ' ').trim();
      
      // 신고 관련 메뉴가 없는지 다시 한 번 확인
      if (href && (href.includes('reports') || cleanText.includes('신고'))) {
        console.log(`❌ ${i + 1}. ${href} - "${cleanText}" ← 신고 관련 메뉴가 여전히 존재!`);
        menuTestResults.push({ index: i + 1, href, text: cleanText, status: 'FAIL', reason: '신고 관련 메뉴 발견' });
      } else {
        console.log(`✅ ${i + 1}. ${href} - "${cleanText}"`);
        menuTestResults.push({ index: i + 1, href, text: cleanText, status: 'PASS', reason: '정상' });
      }
    }
    
    console.log('🔗 5단계: 주요 메뉴 클릭 테스트...');
    
    // 몇 개 주요 메뉴가 정상 작동하는지 테스트
    const testMenus = [
      { selector: 'a[href="/admin"]', name: '메인 대시보드' },
      { selector: 'a[href="/admin/users"]', name: '회원 목록' },
      { selector: 'a[href="/admin/posts"]', name: '게시글 관리' }
    ];
    
    for (const menu of testMenus) {
      try {
        console.log(`🖱️ ${menu.name} 클릭 테스트...`);
        
        const menuElement = await page.locator(menu.selector).first();
        const isVisible = await menuElement.isVisible();
        
        if (isVisible) {
          await menuElement.click();
          await page.waitForTimeout(2000);
          
          const newUrl = page.url();
          console.log(`   결과: ${newUrl} ✅`);
        } else {
          console.log(`   ${menu.name} 메뉴가 보이지 않음 ⚠️`);
        }
      } catch (error) {
        console.log(`   ${menu.name} 테스트 실패: ${error.message} ❌`);
      }
    }
    
    // 최종 스크린샷 촬영
    await page.goto('https://www.topmktx.com/admin', { waitUntil: 'networkidle' });
    await page.screenshot({ path: '/var/www/html/topmkt/admin_sidebar_after_removal.png', fullPage: true });
    console.log('💾 제거 후 관리자 사이드바 스크린샷 저장: admin_sidebar_after_removal.png');
    
    // 테스트 결과 요약
    console.log('\n📊 테스트 결과 요약:');
    console.log('='.repeat(50));
    
    const failedTests = menuTestResults.filter(result => result.status === 'FAIL');
    const passedTests = menuTestResults.filter(result => result.status === 'PASS');
    
    console.log(`✅ 통과: ${passedTests.length}개 메뉴`);
    console.log(`❌ 실패: ${failedTests.length}개 메뉴`);
    
    if (failedTests.length === 0) {
      console.log('\n🎉 테스트 성공: 신고 관리 메뉴가 완전히 제거되었습니다!');
    } else {
      console.log('\n⚠️ 테스트 실패: 아래 문제를 해결해야 합니다:');
      failedTests.forEach(fail => {
        console.log(`   - ${fail.href}: ${fail.reason}`);
      });
    }
    
  } catch (error) {
    console.log(`❌ 테스트 실패: ${error.message}`);
    await page.screenshot({ path: '/var/www/html/topmkt/reports_removal_test_error.png', fullPage: true });
    console.log('💾 에러 스크린샷 저장: reports_removal_test_error.png');
  } finally {
    await browser.close();
    console.log('🔚 브라우저 종료');
  }
})();