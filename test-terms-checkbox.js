import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  try {
    console.log('☑️ 이용약관 체크박스 테스트 시작...');
    
    // 회원가입 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/signup', { waitUntil: 'domcontentloaded' });
    console.log('✅ 회원가입 페이지 로드 완료');

    // 페이지 로드 완료 대기
    await page.waitForTimeout(2000);

    // 1. 초기 상태 확인 (모든 필드 비어있음)
    console.log('📋 1. 초기 상태 - 버튼 비활성화 확인');
    const initialDisabled = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 초기 버튼 상태:', initialDisabled ? '비활성화 ✅' : '활성화 ❌');

    // 2. 모든 필드 채우기 (이용약관 체크 제외)
    console.log('📝 2. 모든 필드 채우기 (이용약관 제외)...');
    
    await page.fill('#nickname', '테스트사용자');
    await page.fill('#phone', '01012341234');  
    await page.fill('#email', 'test@example.com');
    await page.fill('#password', 'TestPassword123!');
    await page.fill('#password_confirm', 'TestPassword123!');

    // 휴대폰 인증 완료로 시뮬레이션
    await page.evaluate(() => {
      if (typeof window.isPhoneVerified !== 'undefined') {
        window.isPhoneVerified = true;
      }
      const phoneVerifiedInput = document.getElementById('phone_verified');
      if (phoneVerifiedInput) {
        phoneVerifiedInput.value = '1';
      }
    });

    await page.waitForTimeout(1000);

    // 이용약관 체크 안한 상태에서 버튼 상태 확인
    const withoutTermsDisabled = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 이용약관 체크 안함:', withoutTermsDisabled ? '비활성화 ✅' : '활성화 ❌');

    // 3. 이용약관 체크하기
    console.log('☑️ 3. 이용약관 체크...');
    await page.locator('input[name="terms"]').scrollIntoViewIfNeeded();
    await page.check('input[name="terms"]');
    await page.waitForTimeout(500);

    // 이용약관 체크 후 버튼 상태 확인
    const withTermsDisabled = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 이용약관 체크 후:', withTermsDisabled ? '비활성화 ❌' : '활성화 ✅');

    // 4. 이용약관 체크 해제하기
    console.log('❌ 4. 이용약관 체크 해제...');
    await page.uncheck('input[name="terms"]');
    await page.waitForTimeout(500);

    // 체크 해제 후 버튼 상태 확인
    const uncheckTermsDisabled = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 이용약관 체크 해제 후:', uncheckTermsDisabled ? '비활성화 ✅' : '활성화 ❌');

    // 5. 최종 스크린샷
    console.log('📸 최종 상태 스크린샷...');
    await page.screenshot({ 
      path: 'terms-checkbox-test.png',
      fullPage: true
    });

    // 테스트 결과 요약
    console.log('\n📊 테스트 결과 요약:');
    console.log('✅ 초기 상태 (모든 필드 비어있음):', initialDisabled ? '비활성화 ✅' : '활성화 ❌');
    console.log('✅ 모든 필드 채움 + 이용약관 미체크:', withoutTermsDisabled ? '비활성화 ✅' : '활성화 ❌');
    console.log('✅ 이용약관 체크 후:', withTermsDisabled ? '비활성화 ❌' : '활성화 ✅');
    console.log('✅ 이용약관 체크 해제 후:', uncheckTermsDisabled ? '비활성화 ✅' : '활성화 ❌');

    const allTestsPassed = initialDisabled && withoutTermsDisabled && !withTermsDisabled && uncheckTermsDisabled;
    console.log('\n🎯 전체 테스트 결과:', allTestsPassed ? '✅ 성공' : '❌ 실패');
    
  } catch (error) {
    console.error('❌ 테스트 오류:', error);
  } finally {
    await browser.close();
    console.log('🏁 테스트 완료');
  }
})();