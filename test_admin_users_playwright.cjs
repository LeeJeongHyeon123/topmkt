const { chromium } = require('playwright');

(async () => {
  console.log('🚀 관리자 사용자 페이지 Playwright 테스트 시작');
  console.log('현재 시간:', new Date().toLocaleString('ko-KR'));
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
  });
  
  const page = await context.newPage();
  
  // 콘솔 로그 캡처
  const consoleMessages = [];
  page.on('console', msg => {
    consoleMessages.push(`[${msg.type()}] ${msg.text()}`);
    console.log(`브라우저 콘솔: [${msg.type()}] ${msg.text()}`);
  });
  
  // 네트워크 오류 캡처
  const networkErrors = [];
  page.on('response', response => {
    if (response.status() >= 400) {
      networkErrors.push(`${response.status()} ${response.url()}`);
      console.log(`네트워크 오류: ${response.status()} ${response.url()}`);
    }
  });
  
  try {
    console.log('\n📋 1단계: 로그인 페이지 접근');
    await page.goto('https://www.topmktx.com/auth/login');
    await page.waitForLoadState('networkidle');
    console.log('✅ 로그인 페이지 로드 완료');
    
    console.log('\n📋 2단계: 우리집탄이 계정으로 로그인');
    
    // 실제 로그인 폼 구조 확인을 위해 잠시 대기
    await page.waitForTimeout(2000);
    
    // 휴대폰 번호 입력 (스크린샷에서 확인된 실제 폼)
    const phoneInput = await page.$('input[placeholder*="010-1234-5678"], input[name*="phone"], input[id*="phone"]');
    if (phoneInput) {
      await phoneInput.fill('010-2659-1346'); // 우리집탄이 전화번호
      console.log('✅ 휴대폰 번호 입력 완료');
    } else {
      console.log('❌ 휴대폰 번호 입력 필드를 찾을 수 없습니다');
    }
    
    // 비밀번호 입력
    const passwordInput = await page.$('input[type="password"]');
    if (passwordInput) {
      await passwordInput.fill('fpemgor77!');
      console.log('✅ 비밀번호 입력 완료');
    } else {
      console.log('❌ 비밀번호 입력 필드를 찾을 수 없습니다');
    }
    
    // 로그인 버튼 클릭 (스크린샷에서 보이는 "로그인" 버튼)
    const loginButton = await page.$('button:has-text("로그인"), input[value="로그인"]');
    if (loginButton) {
      await loginButton.click();
      console.log('✅ 로그인 버튼 클릭 완료');
    } else {
      console.log('❌ 로그인 버튼을 찾을 수 없습니다');
    }
    
    // 로그인 완료 대기
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const currentUrl = page.url();
    console.log('현재 URL:', currentUrl);
    
    console.log('\n📋 3단계: 관리자 페이지 접근');
    await page.goto('https://www.topmktx.com/admin/users');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000); // JavaScript 로딩 대기
    
    console.log('✅ 관리자 사용자 페이지 접근 완료');
    
    console.log('\n📋 4단계: 페이지 상태 확인');
    
    // 페이지 제목 확인
    const title = await page.title();
    console.log('페이지 제목:', title);
    
    // 현재 URL 확인
    console.log('현재 URL:', page.url());
    
    // HTTP 상태 확인
    const response = await page.goto('https://www.topmktx.com/admin/users');
    console.log('HTTP 상태:', response.status());
    
    console.log('\n📋 5단계: 통계 카드 확인');
    
    try {
      await page.waitForSelector('#totalUsers', { timeout: 5000 });
      const totalUsers = await page.textContent('#totalUsers');
      console.log('✅ 총 회원 수:', totalUsers);
      
      const todaySignups = await page.textContent('#todaySignups');
      console.log('✅ 오늘 신규 가입:', todaySignups);
      
      const activeUsers = await page.textContent('#activeUsers');
      console.log('✅ 활성 회원:', activeUsers);
      
    } catch (error) {
      console.log('❌ 통계 카드를 찾을 수 없습니다:', error.message);
    }
    
    console.log('\n📋 6단계: 사용자 테이블 확인');
    
    try {
      await page.waitForSelector('#usersTable', { timeout: 5000 });
      const tableVisible = await page.isVisible('#usersTable');
      console.log('✅ 사용자 테이블 표시 여부:', tableVisible);
      
      if (tableVisible) {
        const rows = await page.$$('#usersTable tbody tr');
        console.log('✅ 사용자 테이블 행 수:', rows.length);
      }
      
    } catch (error) {
      console.log('❌ 사용자 테이블을 찾을 수 없습니다:', error.message);
    }
    
    console.log('\n📋 7단계: 사용자 상세보기 버튼 테스트');
    
    try {
      // 첫 번째 상세보기 버튼 찾기
      const detailButton = await page.$('.btn-view, button[onclick*="viewUserDetail"]');
      
      if (detailButton) {
        console.log('✅ 상세보기 버튼 발견');
        
        // 버튼 클릭
        await detailButton.click();
        console.log('✅ 상세보기 버튼 클릭 완료');
        
        await page.waitForTimeout(2000);
        
        // 모달 확인
        const modal = await page.$('#userDetailModal');
        if (modal) {
          const modalVisible = await page.isVisible('#userDetailModal');
          console.log('✅ 사용자 상세 모달 표시 여부:', modalVisible);
        } else {
          console.log('❌ 사용자 상세 모달을 찾을 수 없습니다');
        }
        
      } else {
        console.log('❌ 상세보기 버튼을 찾을 수 없습니다');
      }
      
    } catch (error) {
      console.log('❌ 상세보기 버튼 테스트 오류:', error.message);
    }
    
    console.log('\n📋 8단계: JavaScript 오류 확인');
    console.log('캡처된 콘솔 메시지 수:', consoleMessages.length);
    
    const errorMessages = consoleMessages.filter(msg => msg.includes('[error]'));
    if (errorMessages.length > 0) {
      console.log('❌ JavaScript 오류 발견:');
      errorMessages.forEach(error => console.log('  ', error));
    } else {
      console.log('✅ JavaScript 오류 없음');
    }
    
    console.log('\n📋 9단계: 네트워크 오류 확인');
    if (networkErrors.length > 0) {
      console.log('❌ 네트워크 오류 발견:');
      networkErrors.forEach(error => console.log('  ', error));
    } else {
      console.log('✅ 네트워크 오류 없음');
    }
    
    console.log('\n📋 10단계: 스크린샷 촬영');
    await page.screenshot({ path: '/var/www/html/topmkt/admin_users_test_screenshot.png', fullPage: true });
    console.log('✅ 스크린샷 저장 완료: admin_users_test_screenshot.png');
    
    console.log('\n🎉 테스트 완료!');
    console.log('완료 시간:', new Date().toLocaleString('ko-KR'));
    
  } catch (error) {
    console.log('\n❌ 테스트 중 오류 발생:');
    console.log('오류:', error.message);
    console.log('스택:', error.stack);
    
    // 오류 발생 시에도 스크린샷 촬영
    try {
      await page.screenshot({ path: '/var/www/html/topmkt/admin_users_error_screenshot.png', fullPage: true });
      console.log('✅ 오류 스크린샷 저장 완료: admin_users_error_screenshot.png');
    } catch (screenshotError) {
      console.log('❌ 스크린샷 저장 실패:', screenshotError.message);
    }
  } finally {
    await browser.close();
    console.log('✅ 브라우저 종료 완료');
  }
})();