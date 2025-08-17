const { chromium } = require('playwright');

(async () => {
  console.log('🔍 Ultra Think 2단계: 신고 관리 메뉴 현재 상태 확인');
  
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
    
    console.log('🔍 3단계: 사이드바에서 신고 관리 메뉴 확인...');
    
    // 신고 관리 메뉴 존재 여부 확인
    const reportsMenuExists = await page.isVisible('a[href="/admin/reports"]');
    console.log(`신고 관리 메뉴 존재: ${reportsMenuExists ? '✅ 있음' : '❌ 없음'}`);
    
    if (reportsMenuExists) {
      // 신고 관리 메뉴의 텍스트 확인
      const menuText = await page.textContent('a[href="/admin/reports"]');
      console.log(`메뉴 텍스트: "${menuText}"`);
      
      // 신고 관리 메뉴 클릭 시도
      console.log('🖱️ 4단계: 신고 관리 메뉴 클릭...');
      
      // 응답 모니터링
      let responseStatus = null;
      page.on('response', async response => {
        if (response.url().includes('/admin/reports')) {
          responseStatus = response.status();
          console.log(`📡 /admin/reports 응답: ${response.status()}`);
        }
      });
      
      await page.click('a[href="/admin/reports"]');
      
      // 응답 대기
      await page.waitForTimeout(5000);
      
      const finalUrl = page.url();
      console.log(`최종 URL: ${finalUrl}`);
      
      if (responseStatus) {
        console.log(`HTTP 응답 상태: ${responseStatus}`);
        
        if (responseStatus === 404) {
          console.log('✅ 예상대로 404 에러 - 신고 관리 기능이 구현되지 않음');
        } else if (responseStatus === 200) {
          console.log('⚠️ 200 응답 - 신고 관리 페이지가 존재할 수 있음');
          // 페이지 내용 확인
          const pageTitle = await page.title();
          const pageText = await page.textContent('body');
          console.log(`페이지 제목: ${pageTitle}`);
          console.log(`페이지 내용 (처음 200자): ${pageText.slice(0, 200)}...`);
        }
      } else {
        console.log('⚠️ /admin/reports 응답을 받지 못함');
      }
    }
    
    console.log('📝 5단계: 전체 사이드바 메뉴 구조 확인...');
    
    // 모든 사이드바 메뉴 항목 확인
    const allMenuItems = await page.locator('.sidebar-nav a.nav-item').all();
    console.log(`\n📋 전체 메뉴 항목 수: ${allMenuItems.length}`);
    
    for (let i = 0; i < allMenuItems.length; i++) {
      const item = allMenuItems[i];
      const href = await item.getAttribute('href');
      const text = await item.textContent();
      const cleanText = text.replace(/\s+/g, ' ').trim();
      
      if (href && href.includes('reports')) {
        console.log(`🚨 ${i + 1}. ${href} - "${cleanText}" ← 신고 관리 메뉴`);
      } else {
        console.log(`   ${i + 1}. ${href} - "${cleanText}"`);
      }
    }
    
    // 스크린샷 촬영
    await page.screenshot({ path: '/var/www/html/topmkt/admin_sidebar_with_reports.png', fullPage: true });
    console.log('💾 관리자 사이드바 스크린샷 저장: admin_sidebar_with_reports.png');
    
  } catch (error) {
    console.log(`❌ 테스트 실패: ${error.message}`);
    await page.screenshot({ path: '/var/www/html/topmkt/reports_menu_error.png', fullPage: true });
    console.log('💾 에러 스크린샷 저장: reports_menu_error.png');
  } finally {
    await browser.close();
    console.log('🔚 브라우저 종료');
  }
})();