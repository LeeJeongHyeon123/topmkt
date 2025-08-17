const { chromium } = require('playwright');

(async () => {
  console.log('🗑️ 변경 사유 필드 제거 확인 테스트');
  
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
    console.log('🔑 1단계: DevLoginHelper로 자동 로그인...');
    
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
    
    await page.waitForTimeout(2000);
    
    console.log('🛡️ 2단계: 관리자 페이지 접근...');
    
    await page.goto('https://www.topmktx.com/admin/users', { 
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    console.log('✅ 관리자 페이지 접근 성공');
    
    console.log('📋 3단계: 편집 모달 열기...');
    
    // 첫 번째 편집 버튼 클릭 (사용자 ID 5)
    const editButton = await page.locator('button[onclick*="editUser"]').first();
    await editButton.click();
    console.log('✅ 편집 버튼 클릭 완료');
    
    // 편집 모달 로딩 대기
    await page.waitForSelector('#editUserModal', { timeout: 10000 });
    console.log('✅ 편집 모달 로드 완료');
    
    console.log('🔍 4단계: 변경 사유 필드 존재 여부 확인...');
    
    // 변경 사유 필드가 존재하는지 확인
    const reasonFieldExists = await page.isVisible('#edit_status_reason');
    
    if (reasonFieldExists) {
      console.log('❌ 변경 사유 필드가 아직 존재합니다!');
      
      // 스크린샷 촬영
      await page.screenshot({ path: '/var/www/html/topmkt/reason_field_still_exists.png', fullPage: true });
      console.log('💾 문제 스크린샷 저장: reason_field_still_exists.png');
      
    } else {
      console.log('✅ 변경 사유 필드가 성공적으로 제거되었습니다!');
    }
    
    console.log('🔍 5단계: 모달 구조 확인...');
    
    // 모달 내부의 모든 input, select, textarea 필드 확인
    const formFields = await page.locator('#editUserModal input, #editUserModal select, #editUserModal textarea').all();
    console.log(`📋 편집 모달 내 전체 필드 수: ${formFields.length}`);
    
    for (let i = 0; i < formFields.length; i++) {
      const field = formFields[i];
      const tagName = await field.evaluate(el => el.tagName.toLowerCase());
      const type = await field.evaluate(el => el.type || 'N/A');
      const name = await field.evaluate(el => el.name || 'N/A');
      const id = await field.evaluate(el => el.id || 'N/A');
      const placeholder = await field.evaluate(el => el.placeholder || 'N/A');
      
      console.log(`  ${i + 1}. ${tagName}[${type}] - id: ${id}, name: ${name}, placeholder: ${placeholder}`);
    }
    
    console.log('✅ 6단계: 권한 변경 테스트...');
    
    // 권한을 기업 회원으로 변경해서 정상 작동 확인
    await page.selectOption('#edit_role', 'ROLE_CORPORATE');
    console.log('✅ 권한을 기업 회원으로 변경');
    
    // 다른 필드 업데이트 (타임스탬프 사용)
    const timestamp = Date.now().toString().slice(-6);
    await page.fill('#edit_nickname', `변경사유제거테스트_${timestamp}`);
    
    console.log('💾 7단계: 저장 테스트...');
    
    // API 응답 모니터링
    let saveSuccess = false;
    page.on('response', async response => {
      if (response.url().includes('/admin/users/') && response.url().includes('/edit')) {
        console.log(`📡 Edit API 응답: ${response.status()}`);
        
        if (response.status() === 200) {
          try {
            const responseText = await response.text();
            const jsonData = JSON.parse(responseText);
            if (jsonData.success) {
              console.log('🎉 변경 사유 없이 권한 변경 완전 성공!');
              console.log(`💾 변경사항: ${jsonData.changes.join(', ')}`);
              saveSuccess = true;
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
    await page.waitForTimeout(3000);
    
    if (saveSuccess) {
      console.log('✅ 변경 사유 필드 제거 후 정상 작동 확인!');
    } else {
      console.log('⚠️ 저장 응답 확인 필요');
    }
    
    // 최종 스크린샷
    await page.screenshot({ path: '/var/www/html/topmkt/no_reason_field_success.png', fullPage: true });
    console.log('💾 최종 스크린샷 저장: no_reason_field_success.png');
    
    console.log('\n📊 테스트 결과 요약:');
    console.log(`🗑️ 변경 사유 필드 제거: ${!reasonFieldExists ? '✅ 성공' : '❌ 실패'}`);
    console.log(`💾 권한 변경 저장: ${saveSuccess ? '✅ 성공' : '⚠️ 확인 필요'}`);
    console.log(`📋 편집 모달 필드 수: ${formFields.length}개`);
    
  } catch (error) {
    console.log(`❌ 테스트 실패: ${error.message}`);
    await page.screenshot({ path: '/var/www/html/topmkt/no_reason_field_error.png', fullPage: true });
    console.log('💾 에러 스크린샷 저장: no_reason_field_error.png');
  } finally {
    await browser.close();
    console.log('🔚 브라우저 종료');
  }
})();