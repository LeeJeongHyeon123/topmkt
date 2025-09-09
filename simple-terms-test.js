import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  try {
    console.log('🔍 간단한 체크박스 상태 확인...');
    
    // 회원가입 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/signup', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);

    // 체크박스 요소 확인
    const termsCheckboxExists = await page.locator('input[name="terms"]').count();
    console.log('📋 이용약관 체크박스 존재:', termsCheckboxExists > 0 ? 'YES' : 'NO');

    if (termsCheckboxExists > 0) {
      // 체크박스 상태 확인
      const isChecked = await page.locator('input[name="terms"]').isChecked();
      console.log('☑️ 체크박스 체크 상태:', isChecked);

      // JavaScript로 직접 체크
      console.log('📝 JavaScript로 체크박스 조작...');
      
      // 1. 체크박스 체크하기
      await page.evaluate(() => {
        const checkbox = document.querySelector('input[name="terms"]');
        if (checkbox) {
          checkbox.checked = true;
          checkbox.dispatchEvent(new Event('change', { bubbles: true }));
          console.log('✅ 체크박스 체크됨');
        }
      });
      
      await page.waitForTimeout(1000);
      
      // 버튼 상태 확인
      const btnAfterCheck = await page.locator('#signup-btn').isDisabled();
      console.log('🔘 체크 후 버튼:', btnAfterCheck ? '비활성화' : '활성화');

      // 2. 체크박스 해제하기
      await page.evaluate(() => {
        const checkbox = document.querySelector('input[name="terms"]');
        if (checkbox) {
          checkbox.checked = false;
          checkbox.dispatchEvent(new Event('change', { bubbles: true }));
          console.log('❌ 체크박스 해제됨');
        }
      });
      
      await page.waitForTimeout(1000);
      
      // 버튼 상태 확인
      const btnAfterUncheck = await page.locator('#signup-btn').isDisabled();
      console.log('🔘 해제 후 버튼:', btnAfterUncheck ? '비활성화' : '활성화');
    }

    // 폼 유효성 상태 확인
    const formValidation = await page.evaluate(() => {
      const nickname = document.getElementById('nickname')?.value || '';
      const phone = document.getElementById('phone')?.value || '';
      const email = document.getElementById('email')?.value || '';
      const password = document.getElementById('password')?.value || '';
      const passwordConfirm = document.getElementById('password_confirm')?.value || '';
      const termsChecked = document.querySelector('input[name="terms"]')?.checked || false;
      const phoneVerified = document.getElementById('phone_verified')?.value || '0';
      
      return {
        nickname: nickname.length,
        phone: phone,
        email: email.length,
        password: password.length,
        passwordConfirm: passwordConfirm.length,
        termsChecked,
        phoneVerified
      };
    });
    
    console.log('📊 폼 상태:', formValidation);
    
    // 스크린샷
    await page.screenshot({ path: 'simple-terms-test.png', fullPage: true });
    
  } catch (error) {
    console.error('❌ 오류:', error);
  } finally {
    await browser.close();
    console.log('🏁 완료');
  }
})();