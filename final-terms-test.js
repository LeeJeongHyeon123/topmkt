import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  try {
    console.log('🏆 최종 이용약관 체크박스 테스트...');
    
    // 회원가입 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/signup', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);

    // 콘솔 로그 캡처를 위한 설정
    page.on('console', msg => {
      if (msg.text().includes('폼 유효성') || msg.text().includes('체크박스') || msg.text().includes('버튼')) {
        console.log('🖥️ 브라우저:', msg.text());
      }
    });

    // 1. 초기 상태
    const initialBtn = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 1. 초기 상태:', initialBtn ? '비활성화 ✅' : '활성화 ❌');

    // 2. 모든 필드 채우기 + 휴대폰 인증 완료 처리 (이용약관 제외)
    console.log('📝 2. 모든 필드 채우기 + 휴대폰 인증 완료...');
    
    const step2Result = await page.evaluate(() => {
      // 필드들 채우기
      document.getElementById('nickname').value = '테스트사용자';
      document.getElementById('phone').value = '010-1234-1234';
      document.getElementById('email').value = 'test@example.com';
      document.getElementById('password').value = 'TestPassword123!';
      document.getElementById('password_confirm').value = 'TestPassword123!';
      
      // 휴대폰 인증 완료로 설정 (전역변수와 hidden input 모두 설정)
      window.isPhoneVerified = true;
      document.getElementById('phone_verified').value = '1';
      
      // 입력 이벤트 발생시키기 (각 필드에)
      ['nickname', 'phone', 'email', 'password', 'password_confirm'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
          element.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
      
      // 폼 유효성 검사 함수 직접 실행
      if (typeof validateForm === 'function') {
        const result = validateForm();
        console.log('📝 validateForm 실행 결과:', result);
        return result;
      }
      
      return false;
    });
    
    await page.waitForTimeout(1000);
    
    const withoutTermsBtn = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 2. 모든 필드 채움 (이용약관 제외):', withoutTermsBtn ? '비활성화 ✅' : '활성화 ❌');

    // 3. 이용약관 체크하기
    console.log('☑️ 3. 이용약관 체크...');
    
    const step3Result = await page.evaluate(() => {
      const termsCheckbox = document.querySelector('input[name="terms"]');
      if (termsCheckbox) {
        termsCheckbox.checked = true;
        termsCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
        
        // 폼 유효성 검사 다시 실행
        if (typeof validateForm === 'function') {
          const result = validateForm();
          console.log('☑️ 이용약관 체크 후 validateForm 결과:', result);
          return result;
        }
      }
      return false;
    });
    
    await page.waitForTimeout(1000);
    
    const withTermsBtn = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 3. 이용약관 체크 후:', withTermsBtn ? '비활성화 ❌' : '활성화 ✅');

    // 4. 이용약관 체크 해제하기
    console.log('❌ 4. 이용약관 체크 해제...');
    
    await page.evaluate(() => {
      const termsCheckbox = document.querySelector('input[name="terms"]');
      if (termsCheckbox) {
        termsCheckbox.checked = false;
        termsCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
        
        // 폼 유효성 검사 다시 실행
        if (typeof validateForm === 'function') {
          const result = validateForm();
          console.log('❌ 이용약관 체크 해제 후 validateForm 결과:', result);
        }
      }
    });
    
    await page.waitForTimeout(1000);
    
    const uncheckTermsBtn = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 4. 이용약관 체크 해제 후:', uncheckTermsBtn ? '비활성화 ✅' : '활성화 ❌');

    // 5. 내부 변수 상태 확인
    const internalState = await page.evaluate(() => {
      return {
        isPhoneVerified: typeof window.isPhoneVerified !== 'undefined' ? window.isPhoneVerified : 'undefined',
        phoneVerifiedInput: document.getElementById('phone_verified')?.value,
        termsChecked: document.querySelector('input[name="terms"]')?.checked,
        signupBtnDisabled: document.getElementById('signup-btn')?.disabled,
        // validateForm이 접근하는 값들 직접 확인
        nickname: document.getElementById('nickname')?.value?.length,
        phone: document.getElementById('phone')?.value,
        email: document.getElementById('email')?.value?.length,
        password: document.getElementById('password')?.value?.length,
        passwordConfirm: document.getElementById('password_confirm')?.value?.length
      };
    });
    
    console.log('🔍 내부 상태:', internalState);

    // 테스트 결과 요약
    console.log('\n🎯 테스트 결과 요약:');
    console.log('1. 초기 상태:', initialBtn ? '비활성화 ✅' : '활성화 ❌');
    console.log('2. 모든 필드 + 이용약관 미체크:', withoutTermsBtn ? '비활성화 ✅' : '활성화 ❌');
    console.log('3. 이용약관 체크 후:', withTermsBtn ? '비활성화 ❌' : '활성화 ✅');
    console.log('4. 이용약관 체크 해제 후:', uncheckTermsBtn ? '비활성화 ✅' : '활성화 ❌');

    const testPassed = initialBtn && withoutTermsBtn && !withTermsBtn && uncheckTermsBtn;
    console.log('\n🏆 최종 결과:', testPassed ? '✅ 모든 테스트 통과!' : '❌ 테스트 실패');
    
  } catch (error) {
    console.error('❌ 테스트 오류:', error);
  } finally {
    await browser.close();
    console.log('🏁 테스트 완료');
  }
})();