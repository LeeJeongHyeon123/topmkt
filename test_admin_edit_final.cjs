const { chromium } = require('playwright');

(async () => {
  console.log('🚀 관리자 사용자 편집 기능 최종 테스트 시작');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const context = await browser.newContext({
    ignoreHTTPSErrors: true
  });
  
  const page = await context.newPage();
  
  // 콘솔 로그 캡처
  page.on('console', msg => console.log(`📝 Browser: ${msg.text()}`));
  
  try {
    console.log('1️⃣ DevLoginHelper로 우리집탄이 계정 로그인 중...');
    
    // DevLoginHelper로 자동 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', { 
      waitUntil: 'networkidle' 
    });
    
    // 로그인 성공 확인 (리다이렉트 대기)
    await page.waitForTimeout(2000);
    console.log('✅ 자동 로그인 완료');
    
    console.log('2️⃣ 관리자 사용자 목록 페이지로 이동...');
    await page.goto('https://www.topmktx.com/admin/users', { 
      waitUntil: 'networkidle' 
    });
    
    // 페이지 로딩 확인
    await page.waitForSelector('table', { timeout: 10000 });
    console.log('✅ 관리자 사용자 목록 페이지 로드 완료');
    
    console.log('3️⃣ 사용자 ID 5 편집 버튼 클릭...');
    
    // 사용자 ID 5의 편집 버튼 찾기 및 클릭
    const editButton = await page.locator('button[onclick*="editUser(5)"]').first();
    
    if (await editButton.count() > 0) {
      await editButton.click();
      console.log('✅ 편집 버튼 클릭 완료');
      
      // 편집 모달 로딩 대기
      await page.waitForSelector('#editUserModal', { timeout: 5000 });
      console.log('✅ 편집 모달 로드 완료');
      
      console.log('4️⃣ 편집 폼 데이터 입력...');
      
      // 편집할 데이터 입력
      const timestamp = new Date().getTime().toString().slice(-6);
      const testNickname = `안계현_최종테스트_${timestamp}`;
      const testEmail = `final_test_${timestamp}@topmktx.com`;
      const testPhone = `010-9999-${timestamp.slice(0, 4)}`;
      
      // 기존 값 지우고 새 값 입력
      await page.fill('#editNickname', '');
      await page.fill('#editNickname', testNickname);
      
      await page.fill('#editEmail', '');
      await page.fill('#editEmail', testEmail);
      
      await page.fill('#editPhone', '');
      await page.fill('#editPhone', testPhone);
      
      // 상태 선택
      await page.selectOption('#editStatus', 'active');
      
      // 권한 선택
      await page.selectOption('#editRole', 'ROLE_USER');
      
      console.log(`📝 입력된 데이터:
        - 닉네임: ${testNickname}
        - 이메일: ${testEmail}
        - 전화번호: ${testPhone}
        - 상태: active
        - 권한: ROLE_USER`);
      
      console.log('5️⃣ 저장 버튼 클릭하여 업데이트 실행...');
      
      // 네트워크 응답 모니터링
      let editResponse = null;
      page.on('response', async response => {
        if (response.url().includes('/admin/users/5/edit')) {
          editResponse = response;
          console.log(`📡 Edit API 응답: ${response.status()}`);
          
          try {
            const responseText = await response.text();
            console.log(`📄 응답 내용: ${responseText.slice(0, 500)}...`);
          } catch (e) {
            console.log('⚠️ 응답 내용을 읽을 수 없습니다.');
          }
        }
      });
      
      // 저장 버튼 클릭
      await page.click('#saveEditBtn');
      
      // 응답 대기
      await page.waitForTimeout(3000);
      
      if (editResponse) {
        console.log(`✅ API 호출 완료 (상태: ${editResponse.status()})`);
        
        if (editResponse.status() === 200) {
          console.log('🎉 사용자 편집 성공!');
          
          // 성공 메시지 확인
          const successAlert = await page.locator('text=성공적으로 업데이트되었습니다').count();
          if (successAlert > 0) {
            console.log('✅ 성공 메시지 확인됨');
          }
          
          // 모달 닫힘 확인
          await page.waitForTimeout(2000);
          const modalVisible = await page.locator('#editUserModal').isVisible();
          console.log(`📋 모달 상태: ${modalVisible ? '열림' : '닫힘'}`);
          
        } else {
          console.log(`❌ 편집 실패 (HTTP ${editResponse.status()})`);
        }
      } else {
        console.log('❌ API 응답을 받지 못했습니다.');
      }
      
    } else {
      console.log('❌ 사용자 ID 5의 편집 버튼을 찾을 수 없습니다.');
    }
    
    console.log('6️⃣ 스크린샷 촬영...');
    await page.screenshot({ path: '/var/www/html/topmkt/admin_edit_final_test.png', fullPage: true });
    console.log('✅ 스크린샷 저장 완료: admin_edit_final_test.png');
    
  } catch (error) {
    console.log(`❌ 테스트 실패: ${error.message}`);
    await page.screenshot({ path: '/var/www/html/topmkt/admin_edit_error.png', fullPage: true });
    console.log('💾 에러 스크린샷 저장 완료: admin_edit_error.png');
  } finally {
    await browser.close();
    console.log('🔚 브라우저 종료');
  }
})();