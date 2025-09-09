import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  try {
    console.log('🔍 페이지의 모든 함수 찾기...');
    
    // 회원가입 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/signup', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);

    // window 객체의 모든 함수 찾기
    const windowFunctions = await page.evaluate(() => {
      const functions = [];
      for (let prop in window) {
        if (typeof window[prop] === 'function' && prop.includes('valid')) {
          functions.push(prop);
        }
      }
      
      // 전역 변수 확인
      const globals = {
        isPhoneVerified: typeof window.isPhoneVerified,
        validateForm: typeof window.validateForm
      };
      
      return { functions, globals };
    });
    
    console.log('🔍 찾은 validate 관련 함수들:', windowFunctions.functions);
    console.log('🔍 전역 변수 상태:', windowFunctions.globals);
    
    // 버튼을 직접 활성화해보기
    const directButtonTest = await page.evaluate(() => {
      const button = document.getElementById('signup-btn');
      if (button) {
        console.log('🔘 버튼 발견, 현재 disabled:', button.disabled);
        
        // 강제로 버튼 활성화
        button.disabled = false;
        
        return {
          originalDisabled: button.disabled,
          afterForceEnable: button.disabled
        };
      }
      return { error: 'button not found' };
    });
    
    console.log('🔘 직접 버튼 조작:', directButtonTest);
    
    await page.waitForTimeout(1000);
    const finalButtonState = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 최종 버튼 상태:', finalButtonState ? '비활성화' : '활성화');
    
    // 스크린샷
    await page.screenshot({ path: 'find-validate-debug.png', fullPage: true });
    
  } catch (error) {
    console.error('❌ 오류:', error);
  } finally {
    await browser.close();
    console.log('🏁 완료');
  }
})();