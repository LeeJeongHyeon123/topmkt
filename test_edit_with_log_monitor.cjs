const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
  console.log('🔥 Ultra Think 실시간 로그 모니터링과 편집 테스트');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const context = await browser.newContext({
    ignoreHTTPSErrors: true
  });
  
  const page = await context.newPage();
  
  // 콘솔 로그 캡처
  page.on('console', msg => {
    const text = msg.text();
    if (text.includes('편집') || text.includes('오류') || text.includes('Error')) {
      console.log(`📱 Browser: ${text}`);
    }
  });
  
  try {
    console.log('1️⃣ DevLoginHelper로 우리집탄이 계정 로그인...');
    
    // DevLoginHelper로 자동 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', { 
      waitUntil: 'networkidle' 
    });
    
    // 로그인 성공 확인
    await page.waitForTimeout(3000);
    console.log('✅ 자동 로그인 완료');
    
    console.log('2️⃣ 관리자 사용자 목록 페이지로 이동...');
    await page.goto('https://www.topmktx.com/admin/users', { 
      waitUntil: 'networkidle' 
    });
    
    // 페이지 로딩 확인
    try {
      await page.waitForSelector('table', { timeout: 15000 });
      console.log('✅ 관리자 사용자 목록 페이지 로드 완료');
    } catch (e) {
      console.log('❌ 테이블을 찾을 수 없음, 현재 URL 확인...');
      const currentUrl = page.url();
      console.log(`현재 URL: ${currentUrl}`);
      
      // 페이지 스크린샷 촬영
      await page.screenshot({ path: '/var/www/html/topmkt/admin_page_state.png', fullPage: true });
      console.log('💾 페이지 상태 스크린샷 저장: admin_page_state.png');
      
      throw e;
    }
    
    console.log('3️⃣ 사용자 ID 5 편집 버튼 클릭...');
    
    // 사용자 ID 5의 편집 버튼 찾기
    const editButtons = await page.locator('button[onclick*="editUser"]').all();
    console.log(`편집 버튼 수량: ${editButtons.length}`);
    
    if (editButtons.length > 0) {
      // 첫 번째 편집 버튼 클릭 (사용자 ID 5가 아닐 수도 있지만 테스트용)
      const editButton = editButtons[0];
      await editButton.click();
      console.log('✅ 편집 버튼 클릭 완료');
      
      // 편집 모달 로딩 대기
      await page.waitForSelector('#editUserModal', { timeout: 10000 });
      console.log('✅ 편집 모달 로드 완료');
      
      console.log('4️⃣ 편집 폼 데이터 입력...');
      
      // 편집할 데이터 입력
      const timestamp = new Date().getTime().toString().slice(-6);
      const testNickname = `Ultra편집_${timestamp}`;
      const testEmail = `ultra_${timestamp}@topmktx.com`;
      const testPhone = `010-1111-${timestamp.slice(0, 4)}`;
      
      // 기존 값 지우고 새 값 입력
      await page.fill('#editNickname', testNickname);
      await page.fill('#editEmail', testEmail);
      await page.fill('#editPhone', testPhone);
      
      console.log(`📝 입력된 데이터:
        - 닉네임: ${testNickname}
        - 이메일: ${testEmail}
        - 전화번호: ${testPhone}`);
      
      console.log('5️⃣ 저장 버튼 클릭하여 업데이트 실행...');
      
      // 네트워크 요청/응답 모니터링
      let editResponse = null;
      page.on('response', async response => {
        if (response.url().includes('/admin/users/') && response.url().includes('/edit')) {
          editResponse = response;
          console.log(`📡 Edit API 응답: ${response.status()}`);
          
          try {
            const responseText = await response.text();
            console.log(`📄 응답 내용 (처음 500자): ${responseText.slice(0, 500)}...`);
            
            // HTML 응답인지 JSON 응답인지 확인
            if (responseText.trim().startsWith('<')) {
              console.log('❌ HTML 응답이 반환됨 (500 에러 페이지일 가능성)');
            } else {
              console.log('✅ JSON 응답이 반환됨');
            }
          } catch (e) {
            console.log('⚠️ 응답 내용을 읽을 수 없습니다.');
          }
        }
      });
      
      // 저장 버튼 클릭
      await page.click('#saveEditBtn');
      
      // 응답 대기 (충분한 시간)
      await page.waitForTimeout(5000);
      
      if (editResponse) {
        console.log(`📊 최종 API 응답 상태: ${editResponse.status()}`);
        
        if (editResponse.status() === 500) {
          console.log('🔥 500 에러 발생! 로그에서 상세 정보 확인 필요');
        } else if (editResponse.status() === 200) {
          console.log('🎉 편집 성공!');
        } else {
          console.log(`⚠️ 예상치 못한 응답 코드: ${editResponse.status()}`);
        }
      } else {
        console.log('❌ API 응답을 받지 못했습니다.');
      }
      
    } else {
      console.log('❌ 편집 버튼을 찾을 수 없습니다.');
    }
    
  } catch (error) {
    console.log(`❌ 테스트 실패: ${error.message}`);
    await page.screenshot({ path: '/var/www/html/topmkt/ultra_edit_error.png', fullPage: true });
    console.log('💾 에러 스크린샷 저장: ultra_edit_error.png');
  } finally {
    await browser.close();
    console.log('🔚 브라우저 종료');
  }
})();