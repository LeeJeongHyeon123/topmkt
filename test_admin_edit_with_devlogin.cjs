const { chromium } = require('playwright');

(async () => {
  console.log('🔥 Ultra Think: DevLoginHelper를 활용한 관리자 편집 기능 완전 테스트');
  
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
    if (text.includes('편집') || text.includes('오류') || text.includes('Error') || text.includes('Ultra Think')) {
      console.log(`📱 Browser Console: ${text}`);
    }
  });
  
  // 네트워크 에러 모니터링
  page.on('requestfailed', request => {
    console.log(`❌ Request Failed: ${request.url()} - ${request.failure().errorText}`);
  });
  
  let testResults = {
    devlogin: false,
    admin_access: false,
    user_list: false,
    edit_modal: false,
    edit_submit: false,
    edit_success: false
  };
  
  try {
    console.log('🔑 1단계: DevLoginHelper로 우리집탄이 계정 자동 로그인...');
    
    // DevLoginHelper 사용하여 자동 로그인
    const loginResponse = await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', { 
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    if (loginResponse.status() !== 200) {
      throw new Error(`DevLoginHelper 접근 실패: HTTP ${loginResponse.status()}`);
    }
    
    // 로그인 페이지 내용 확인
    const pageContent = await page.content();
    if (pageContent.includes('자동 로그인 완료')) {
      console.log('✅ DevLoginHelper 성공 응답 확인');
      testResults.devlogin = true;
    } else if (pageContent.includes('❌')) {
      throw new Error('DevLoginHelper 로그인 실패');
    }
    
    // JWT 토큰 설정 대기
    await page.waitForTimeout(3000);
    
    console.log('🛡️ 2단계: 관리자 페이지 접근 테스트...');
    
    // 관리자 페이지로 직접 이동
    const adminResponse = await page.goto('https://www.topmktx.com/admin/users', { 
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    if (adminResponse.status() !== 200) {
      throw new Error(`관리자 페이지 접근 실패: HTTP ${adminResponse.status()}`);
    }
    
    // 현재 URL 확인 (리다이렉트 여부)
    const currentUrl = page.url();
    console.log(`현재 URL: ${currentUrl}`);
    
    if (currentUrl.includes('/auth/login')) {
      throw new Error('관리자 페이지에서 로그인 페이지로 리다이렉트됨 - JWT 토큰 문제');
    }
    
    if (currentUrl.includes('/admin/users')) {
      console.log('✅ 관리자 페이지 접근 성공');
      testResults.admin_access = true;
    }
    
    console.log('📋 3단계: 사용자 목록 테이블 로딩 확인...');
    
    // 사용자 목록 테이블 대기
    try {
      await page.waitForSelector('table', { timeout: 15000 });
      console.log('✅ 사용자 목록 테이블 로드 완료');
      testResults.user_list = true;
    } catch (e) {
      // 테이블이 없으면 페이지 구조 확인
      const pageTitle = await page.title();
      const bodyText = await page.textContent('body');
      console.log(`페이지 제목: ${pageTitle}`);
      console.log(`본문 내용 (처음 200자): ${bodyText.slice(0, 200)}...`);
      
      // 스크린샷 촬영
      await page.screenshot({ path: '/var/www/html/topmkt/admin_page_debug.png', fullPage: true });
      console.log('💾 관리자 페이지 디버깅 스크린샷 저장: admin_page_debug.png');
      
      throw new Error('사용자 목록 테이블을 찾을 수 없음');
    }
    
    console.log('✏️ 4단계: 사용자 편집 버튼 찾기 및 클릭...');
    
    // 편집 버튼 찾기
    const editButtons = await page.locator('button[onclick*="editUser"]').all();
    console.log(`발견된 편집 버튼 수: ${editButtons.length}`);
    
    if (editButtons.length === 0) {
      // 페이지 구조 분석
      const allButtons = await page.locator('button').all();
      console.log(`전체 버튼 수: ${allButtons.length}`);
      
      for (let i = 0; i < Math.min(allButtons.length, 5); i++) {
        const buttonText = await allButtons[i].textContent();
        const buttonOnclick = await allButtons[i].getAttribute('onclick');
        console.log(`버튼 ${i + 1}: "${buttonText}" onclick="${buttonOnclick}"`);
      }
      
      throw new Error('편집 버튼을 찾을 수 없음');
    }
    
    // 사용자 ID 5의 편집 버튼 찾기 (또는 첫 번째 버튼 사용)
    let targetEditButton = editButtons[0];
    for (const button of editButtons) {
      const onclick = await button.getAttribute('onclick');
      if (onclick && onclick.includes('editUser(5)')) {
        targetEditButton = button;
        console.log('✅ 사용자 ID 5 편집 버튼 발견');
        break;
      }
    }
    
    // 편집 버튼 클릭
    await targetEditButton.click();
    console.log('✅ 편집 버튼 클릭 완료');
    
    console.log('📝 5단계: 편집 모달 로딩 및 데이터 입력...');
    
    // 편집 모달 로딩 대기
    await page.waitForSelector('#editUserModal', { timeout: 10000 });
    console.log('✅ 편집 모달 로드 완료');
    testResults.edit_modal = true;
    
    // 모달이 실제로 표시되는지 확인
    const modalVisible = await page.isVisible('#editUserModal');
    if (!modalVisible) {
      throw new Error('편집 모달이 보이지 않음');
    }
    
    // 편집할 데이터 준비
    const timestamp = new Date().getTime().toString().slice(-6);
    const testData = {
      nickname: `UltraTest_${timestamp}`,
      email: `ultratest_${timestamp}@topmktx.com`,
      phone: `010-9999-${timestamp.slice(0, 4)}`,
      status: 'active',
      role: 'ROLE_USER'
    };
    
    console.log(`📝 입력할 데이터:
      - 닉네임: ${testData.nickname}
      - 이메일: ${testData.email}
      - 전화번호: ${testData.phone}
      - 상태: ${testData.status}
      - 권한: ${testData.role}`);
    
    // 폼 필드 입력 (올바른 ID 사용)
    await page.fill('#edit_nickname', testData.nickname);
    await page.fill('#edit_email', testData.email);
    await page.fill('#edit_phone', testData.phone);
    await page.selectOption('#edit_status', testData.status);
    await page.selectOption('#edit_role', testData.role);
    
    console.log('✅ 모든 필드 입력 완료');
    
    console.log('💾 6단계: 편집 저장 및 응답 모니터링...');
    
    // CSRF 토큰 확인
    const csrfTokenElement = await page.locator('meta[name="csrf-token"]').first();
    const csrfToken = await csrfTokenElement.getAttribute('content');
    console.log(`🛡️ CSRF 토큰: ${csrfToken ? '있음' : '없음'}`);
    
    // API 요청 및 응답 모니터링 설정
    let editApiResponse = null;
    let responseError = null;
    let requestBody = null;
    
    // 요청 모니터링
    page.on('request', async request => {
      if (request.url().includes('/admin/users/') && request.url().includes('/edit')) {
        console.log(`📤 Edit API 요청:`, {
          url: request.url(),
          method: request.method(),
          headers: request.headers()
        });
        requestBody = request.postData();
        console.log(`📝 요청 본문: ${requestBody}`);
      }
    });
    
    // 응답 모니터링  
    page.on('response', async response => {
      if (response.url().includes('/admin/users/') && response.url().includes('/edit')) {
        editApiResponse = response;
        console.log(`📡 Edit API 응답 수신: ${response.status()}`);
        
        try {
          const responseText = await response.text();
          console.log(`📄 API 응답 내용 (처음 300자): ${responseText.slice(0, 300)}...`);
          
          // JSON 파싱 시도
          if (responseText.trim().startsWith('{')) {
            const jsonResponse = JSON.parse(responseText);
            if (jsonResponse.success) {
              console.log('🎉 JSON 응답에서 성공 확인');
            } else {
              console.log(`❌ JSON 응답에서 실패: ${jsonResponse.error || jsonResponse.message}`);
              responseError = jsonResponse.error || jsonResponse.message;
            }
          } else if (responseText.includes('<h1>')) {
            console.log('❌ HTML 에러 페이지 응답 (500 에러 가능성)');
            responseError = 'HTML 에러 페이지 응답';
          }
        } catch (e) {
          console.log('⚠️ 응답 내용 파싱 실패:', e.message);
        }
      }
    });
    
    // CSRF 토큰을 폼에 추가 및 디버깅 강화
    if (csrfToken) {
      await page.evaluate((token) => {
        const form = document.getElementById('editUserForm');
        if (form) {
          // 기존 CSRF input 확인
          let csrfInput = form.querySelector('input[name="csrf_token"]');
          if (!csrfInput) {
            // 새로 생성
            csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            form.appendChild(csrfInput);
          }
          csrfInput.value = token;
          console.log(`🛡️ CSRF 토큰 폼에 추가: ${token.substring(0, 20)}...`);
          console.log(`🛡️ 전체 CSRF 토큰: ${token}`);
          
          // 폼 데이터 전체 확인
          const formData = new FormData(form);
          console.log('📝 전송될 폼 데이터:');
          for (let [key, value] of formData.entries()) {
            console.log(`  ${key}: ${value}`);
          }
        } else {
          console.log('❌ editUserForm을 찾을 수 없습니다');
        }
      }, csrfToken);
    } else {
      console.log('❌ CSRF 토큰을 가져올 수 없습니다');
    }
    
    // 저장 버튼 클릭
    await page.click('#saveEditBtn');
    testResults.edit_submit = true;
    
    // API 응답 대기
    console.log('⏳ API 응답 대기 중...');
    await page.waitForTimeout(5000);
    
    if (editApiResponse) {
      console.log(`📊 최종 API 응답 상태: ${editApiResponse.status()}`);
      
      if (editApiResponse.status() === 200 && !responseError) {
        console.log('🎉 편집 성공!');
        testResults.edit_success = true;
        
        // 성공 메시지 또는 모달 닫힘 확인
        await page.waitForTimeout(2000);
        const modalStillVisible = await page.isVisible('#editUserModal');
        console.log(`모달 상태: ${modalStillVisible ? '여전히 열림' : '닫힘'}`);
        
      } else if (editApiResponse.status() === 500) {
        console.log('❌ 500 Internal Server Error 발생');
        responseError = '500 Internal Server Error';
      } else {
        console.log(`⚠️ 예상치 못한 응답: ${editApiResponse.status()}`);
      }
    } else {
      console.log('❌ API 응답을 받지 못했습니다.');
      responseError = 'No API response received';
    }
    
    // 최종 스크린샷 촬영
    await page.screenshot({ path: '/var/www/html/topmkt/ultra_edit_final_result.png', fullPage: true });
    console.log('💾 최종 결과 스크린샷 저장: ultra_edit_final_result.png');
    
    // 테스트 결과 출력
    console.log('\n📊 테스트 결과 종합:');
    console.log(`✅ DevLoginHelper: ${testResults.devlogin ? '성공' : '실패'}`);
    console.log(`✅ 관리자 페이지 접근: ${testResults.admin_access ? '성공' : '실패'}`);
    console.log(`✅ 사용자 목록 로딩: ${testResults.user_list ? '성공' : '실패'}`);
    console.log(`✅ 편집 모달 표시: ${testResults.edit_modal ? '성공' : '실패'}`);
    console.log(`✅ 편집 요청 전송: ${testResults.edit_submit ? '성공' : '실패'}`);
    console.log(`✅ 편집 최종 성공: ${testResults.edit_success ? '성공' : '실패'}`);
    
    if (responseError) {
      console.log(`❌ 마지막 에러: ${responseError}`);
    }
    
    const successCount = Object.values(testResults).filter(Boolean).length;
    const totalTests = Object.keys(testResults).length;
    
    console.log(`\n🎯 전체 성공률: ${successCount}/${totalTests} (${Math.round(successCount/totalTests*100)}%)`);
    
    if (testResults.edit_success) {
      console.log('\n🏆 사용자 편집 기능이 완전히 정상 작동합니다!');
    } else {
      console.log('\n⚠️ 편집 기능에 문제가 있습니다. 로그를 확인하세요.');
    }
    
  } catch (error) {
    console.log(`❌ 테스트 실패: ${error.message}`);
    await page.screenshot({ path: '/var/www/html/topmkt/ultra_edit_test_error.png', fullPage: true });
    console.log('💾 에러 스크린샷 저장: ultra_edit_test_error.png');
    
    // 테스트 결과 출력
    console.log('\n📊 부분 테스트 결과:');
    Object.entries(testResults).forEach(([key, value]) => {
      console.log(`${value ? '✅' : '❌'} ${key}: ${value ? '성공' : '실패'}`);
    });
  } finally {
    await browser.close();
    console.log('🔚 브라우저 종료');
  }
})();