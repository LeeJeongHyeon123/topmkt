import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  try {
    console.log('🔍 validateForm 함수 디버깅...');
    
    // 회원가입 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/signup', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);

    // validateForm 함수 내부의 모든 변수 값을 확인
    const debugValidateForm = await page.evaluate(() => {
      // 모든 필드 채우기
      document.getElementById('nickname').value = '테스트사용자';
      document.getElementById('phone').value = '010-1234-1234';
      document.getElementById('email').value = 'test@example.com';
      document.getElementById('password').value = 'TestPassword123!';
      document.getElementById('password_confirm').value = 'TestPassword123!';
      
      // 휴대폰 인증 설정
      window.isPhoneVerified = true;
      document.getElementById('phone_verified').value = '1';
      
      // 이용약관 체크
      document.querySelector('input[name="terms"]').checked = true;
      
      // validateForm 함수 직접 분해해서 각 단계별 확인
      const nickname = document.getElementById('nickname').value.trim();
      const phone = document.getElementById('phone').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const passwordConfirm = document.getElementById('password_confirm').value;
      const termsChecked = document.querySelector('input[name="terms"]').checked;
      
      // 각 검증 함수 직접 호출
      const isValidPhoneFormat = (phone) => {
        const pattern = /^010-[0-9]{3,4}-[0-9]{4}$/;
        return pattern.test(phone);
      };
      
      const isValidEmailFormat = (email) => {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
      };
      
      const isNicknameValid = nickname.length >= 2 && nickname.length <= 20;
      const isPhoneValid = isValidPhoneFormat(phone);
      const isEmailValid = isValidEmailFormat(email);
      const isPasswordValid = password.length >= 8;
      const isPasswordMatch = password === passwordConfirm;
      
      return {
        inputs: {
          nickname,
          phone,
          email,
          passwordLength: password.length,
          passwordConfirmLength: passwordConfirm.length,
          termsChecked
        },
        validations: {
          isNicknameValid,
          isPhoneValid,
          isEmailValid,
          isPasswordValid,
          isPasswordMatch
        },
        phoneVerification: {
          windowIsPhoneVerified: window.isPhoneVerified,
          phoneVerifiedInputValue: document.getElementById('phone_verified').value,
          globalVariable: typeof isPhoneVerified !== 'undefined' ? isPhoneVerified : 'undefined'
        },
        finalCheck: isNicknameValid && isPhoneValid && isEmailValid && isPasswordValid && isPasswordMatch && window.isPhoneVerified && termsChecked
      };
    });
    
    console.log('🔍 validateForm 디버깅 결과:', debugValidateForm);
    
    // 실제 validateForm 함수 호출해서 비교
    const actualValidateResult = await page.evaluate(() => {
      if (typeof validateForm === 'function') {
        return validateForm();
      }
      return 'validateForm function not found';
    });
    
    console.log('📋 실제 validateForm 결과:', actualValidateResult);
    
    const buttonState = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 최종 버튼 상태:', buttonState ? '비활성화' : '활성화');
    
  } catch (error) {
    console.error('❌ 오류:', error);
  } finally {
    await browser.close();
    console.log('🏁 완료');
  }
})();