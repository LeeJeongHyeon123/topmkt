import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  try {
    console.log('🔄 완전한 이용약관 체크박스 테스트...');
    
    // 회원가입 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/signup', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);

    // 1. 초기 상태
    const initialBtn = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 1. 초기 상태:', initialBtn ? '비활성화 ✅' : '활성화 ❌');

    // 2. 모든 필드 채우기 + 휴대폰 인증 시뮬레이션 (이용약관 제외)
    console.log('📝 2. 모든 필드 채우기...');
    
    await page.evaluate(() => {
      // 필드들 채우기
      document.getElementById('nickname').value = '테스트사용자';
      document.getElementById('phone').value = '010-1234-1234';
      document.getElementById('email').value = 'test@example.com';
      document.getElementById('password').value = 'TestPassword123!';
      document.getElementById('password_confirm').value = 'TestPassword123!';
      
      // 휴대폰 인증 완료로 설정
      window.isPhoneVerified = true;
      document.getElementById('phone_verified').value = '1';
      
      // 폼 유효성 검사 함수가 존재하면 실행
      if (typeof validateForm === 'function') {
        validateForm();
      }
      
      console.log('✅ 모든 필드 채움 완료');
    });
    
    await page.waitForTimeout(1000);
    
    const withoutTermsBtn = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 2. 모든 필드 채움 (이용약관 제외):', withoutTermsBtn ? '비활성화 ✅' : '활성화 ❌');

    // 3. 이용약관 체크하기
    console.log('☑️ 3. 이용약관 체크...');
    
    await page.evaluate(() => {
      const termsCheckbox = document.querySelector('input[name="terms"]');
      if (termsCheckbox) {
        termsCheckbox.checked = true;
        termsCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
        
        // 폼 유효성 검사 실행
        if (typeof validateForm === 'function') {
          validateForm();
        }
        
        console.log('✅ 이용약관 체크 완료');
      }
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
        
        // 폼 유효성 검사 실행
        if (typeof validateForm === 'function') {
          validateForm();
        }
        
        console.log('❌ 이용약관 체크 해제 완료');
      }
    });
    
    await page.waitForTimeout(1000);
    
    const uncheckTermsBtn = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 4. 이용약관 체크 해제 후:', uncheckTermsBtn ? '비활성화 ✅' : '활성화 ❌');

    // 5. 폼 상태 최종 확인
    const finalFormState = await page.evaluate(() => {
      return {
        nickname: document.getElementById('nickname').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value.length > 0 ? '[SET]' : '[EMPTY]',
        passwordConfirm: document.getElementById('password_confirm').value.length > 0 ? '[SET]' : '[EMPTY]',
        termsChecked: document.querySelector('input[name="terms"]').checked,
        phoneVerified: document.getElementById('phone_verified').value,
        isPhoneVerifiedVar: typeof window.isPhoneVerified !== 'undefined' ? window.isPhoneVerified : 'undefined'
      };
    });
    
    console.log('📊 최종 폼 상태:', finalFormState);

    // 테스트 결과 요약
    console.log('\n🎯 테스트 결과 요약:');
    console.log('1. 초기 상태:', initialBtn ? '비활성화 ✅' : '활성화 ❌');
    console.log('2. 모든 필드 + 이용약관 미체크:', withoutTermsBtn ? '비활성화 ✅' : '활성화 ❌');
    console.log('3. 이용약관 체크 후:', withTermsBtn ? '비활성화 ❌' : '활성화 ✅');
    console.log('4. 이용약관 체크 해제 후:', uncheckTermsBtn ? '비활성화 ✅' : '활성화 ❌');

    const testPassed = initialBtn && withoutTermsBtn && !withTermsBtn && uncheckTermsBtn;
    console.log('\n🏆 최종 결과:', testPassed ? '✅ 모든 테스트 통과!' : '❌ 테스트 실패');

    // 스크린샷
    await page.screenshot({ path: 'complete-terms-test.png', fullPage: true });
    
  } catch (error) {
    console.error('❌ 테스트 오류:', error);
  } finally {
    await browser.close();
    console.log('🏁 테스트 완료');
  }
})();