const { chromium } = require('playwright');

(async () => {
  console.log('🎯 기업 회원 권한 변경 Playwright 테스트');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 }
  });
  
  const page = await context.newPage();
  
  // 콘솔 로그 캡처
  page.on('console', msg => {
    const text = msg.text();
    if (text.includes('편집') || text.includes('오류') || text.includes('Error') || text.includes('기업')) {
      console.log(`📱 Browser Console: ${text}`);
    }
  });
  
  try {
    console.log('🔑 1단계: DevLoginHelper로 우리집탄이 자동 로그인...');
    
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
    
    console.log('🛡️ 2단계: 관리자 페이지 접근...');
    
    await page.goto('https://www.topmktx.com/admin/users', { 
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    const currentUrl = page.url();
    if (!currentUrl.includes('/admin/users')) {
      throw new Error('관리자 페이지 접근 실패');
    }
    console.log('✅ 관리자 페이지 접근 성공');
    
    console.log('📋 3단계: 사용자 편집 버튼 클릭...');
    
    // 사용자 ID 5의 편집 버튼 찾기 및 클릭
    const editButton = await page.locator('button[onclick*="editUser(5)"]').first();
    if (await editButton.count() === 0) {
      throw new Error('사용자 ID 5 편집 버튼을 찾을 수 없습니다');
    }
    
    await editButton.click();
    console.log('✅ 편집 버튼 클릭 완료');
    
    console.log('📝 4단계: 편집 모달 로딩 및 기업 권한 설정...');
    
    // 편집 모달 대기
    await page.waitForSelector('#editUserModal', { timeout: 10000 });
    console.log('✅ 편집 모달 로드 완료');
    
    // 권한을 기업 회원으로 변경
    await page.selectOption('#edit_role', 'ROLE_CORPORATE');
    console.log('✅ 권한을 기업 회원(ROLE_CORPORATE)으로 설정');
    
    // 다른 필드도 업데이트
    const timestamp = Date.now().toString().slice(-6);
    await page.fill('#edit_nickname', `기업권한Playwright_${timestamp}`);
    await page.fill('#edit_email', `corp_playwright_${timestamp}@topmktx.com`);
    await page.fill('#edit_phone', `010-4444-${timestamp.slice(0, 4)}`);
    
    console.log(`✅ 모든 필드 입력 완료 (타임스탬프: ${timestamp})`);
    
    console.log('💾 5단계: 저장 및 응답 확인...');
    
    // API 응답 모니터링
    let editResponse = null;
    page.on('response', async response => {
      if (response.url().includes('/admin/users/5/edit')) {
        editResponse = response;
        console.log(`📡 Edit API 응답: ${response.status()}`);
        
        if (response.status() === 200) {
          try {
            const responseText = await response.text();
            const jsonData = JSON.parse(responseText);
            if (jsonData.success) {
              console.log('🎉 기업 권한 변경 완전 성공!');
              console.log(`💾 변경사항: ${jsonData.changes.join(', ')}`);
            }
          } catch (e) {
            console.log('⚠️ JSON 파싱 실패');
          }
        }
      }
    });
    
    // 저장 버튼 클릭
    await page.click('#saveEditBtn');
    
    // 응답 대기
    await page.waitForTimeout(5000);
    
    if (editResponse && editResponse.status() === 200) {
      console.log('✅ 기업 회원 권한 변경 테스트 완전 성공!');
      
      // 모달 닫힘 확인
      const modalStillVisible = await page.isVisible('#editUserModal');
      console.log(`모달 상태: ${modalStillVisible ? '열림' : '닫힘'}`);
      
    } else {
      throw new Error('API 응답 실패 또는 응답 없음');
    }
    
    // 최종 스크린샷
    await page.screenshot({ path: '/var/www/html/topmkt/corp_role_success.png', fullPage: true });
    console.log('💾 성공 스크린샷 저장: corp_role_success.png');
    
  } catch (error) {
    console.log(`❌ 테스트 실패: ${error.message}`);
    await page.screenshot({ path: '/var/www/html/topmkt/corp_role_error.png', fullPage: true });
    console.log('💾 에러 스크린샷 저장: corp_role_error.png');
  } finally {
    await browser.close();
    console.log('🔚 브라우저 종료');
  }
})();