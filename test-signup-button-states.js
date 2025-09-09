import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  try {
    console.log('🔘 회원가입 버튼 상태 테스트 시작...');
    
    // 회원가입 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/signup', { waitUntil: 'domcontentloaded' });
    console.log('✅ 회원가입 페이지 로드 완료');

    // 회원가입 버튼 찾기
    const signupBtn = page.locator('#signup-btn');
    await signupBtn.waitFor({ timeout: 10000 });
    console.log('✅ 회원가입 버튼 발견');

    // 1. 초기 비활성화 상태 스크린샷
    console.log('📸 1. 초기 비활성화 상태 스크린샷 촬영...');
    const isDisabled = await signupBtn.isDisabled();
    console.log('🔘 버튼 비활성화 상태:', isDisabled);
    
    // 버튼 영역으로 스크롤
    await page.locator('#signup-btn').scrollIntoViewIfNeeded();
    await page.screenshot({ 
      path: 'signup-btn-disabled-state.png'
    });

    // 2. 비활성화 버튼 호버 테스트
    console.log('🖱️ 2. 비활성화 버튼에 호버 테스트...');
    await signupBtn.hover();
    await page.waitForTimeout(500);
    
    await page.screenshot({ 
      path: 'signup-btn-disabled-hover.png'
    });

    // 3. 모든 필드를 채워서 활성화 상태 만들기
    console.log('📝 3. 모든 필드 채우기 테스트...');
    
    // 닉네임 입력
    await page.fill('#nickname', '테스트사용자');
    console.log('✅ 닉네임 입력 완료');

    // 휴대폰 번호 입력
    await page.fill('#phone', '01012341234');
    console.log('✅ 휴대폰 번호 입력 완료');

    // 이메일 입력
    await page.fill('#email', 'test@example.com');
    console.log('✅ 이메일 입력 완료');

    // 비밀번호 입력
    await page.fill('#password', 'TestPassword123!');
    console.log('✅ 비밀번호 입력 완료');

    // 비밀번호 확인 입력
    await page.fill('#password_confirm', 'TestPassword123!');
    console.log('✅ 비밀번호 확인 입력 완료');

    // 이용약관 체크
    await page.check('input[name="terms"]');
    console.log('✅ 이용약관 체크 완료');

    // 휴대폰 인증을 완료로 시뮬레이션 (JavaScript로)
    await page.evaluate(() => {
      window.isPhoneVerified = true;
      document.getElementById('phone_verified').value = '1';
      // 폼 유효성 검사 함수 호출
      if (typeof validateForm === 'function') {
        validateForm();
      }
    });
    console.log('📱 휴대폰 인증 시뮬레이션 완료');

    // 잠시 대기하여 버튼 상태 변경 확인
    await page.waitForTimeout(1000);

    // 4. 활성화된 버튼 상태 스크린샷
    console.log('📸 4. 활성화된 버튼 상태 스크린샷 촬영...');
    const isNowEnabled = await signupBtn.isEnabled();
    console.log('🔘 버튼 활성화 상태:', isNowEnabled);
    
    await page.screenshot({ 
      path: 'signup-btn-enabled-state.png'
    });

    // 5. 활성화 버튼 호버 상태
    console.log('🖱️ 5. 활성화 버튼에 호버 테스트...');
    await signupBtn.hover();
    await page.waitForTimeout(500);
    
    await page.screenshot({ 
      path: 'signup-btn-enabled-hover.png'
    });

    console.log('✅ 모든 버튼 상태 테스트 완료');
    
  } catch (error) {
    console.error('❌ 테스트 오류:', error);
    await page.screenshot({ path: 'signup-btn-test-error.png' });
  } finally {
    console.log('🏁 테스트 완료 - 브라우저 종료');
    await browser.close();
  }
})();