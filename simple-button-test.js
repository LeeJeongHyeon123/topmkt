import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  try {
    console.log('🔘 간단한 버튼 상태 확인...');
    
    // 회원가입 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/signup', { waitUntil: 'domcontentloaded' });
    
    // 페이지 로드 완료 대기
    await page.waitForTimeout(2000);

    // 비활성화 상태 스크린샷
    console.log('📸 비활성화 상태 스크린샷...');
    await page.screenshot({ 
      path: 'signup-button-disabled.png',
      fullPage: true
    });

    // 버튼 상태 확인
    const isDisabled = await page.locator('#signup-btn').isDisabled();
    console.log('🔘 버튼 비활성화 상태:', isDisabled);

    // 버튼의 계산된 스타일 확인
    const buttonStyles = await page.locator('#signup-btn').evaluate(el => {
      const styles = window.getComputedStyle(el);
      return {
        opacity: styles.opacity,
        backgroundColor: styles.backgroundColor,
        color: styles.color,
        cursor: styles.cursor,
        filter: styles.filter
      };
    });
    
    console.log('🎨 버튼 스타일:', buttonStyles);
    
  } catch (error) {
    console.error('❌ 테스트 오류:', error);
  } finally {
    await browser.close();
    console.log('🏁 테스트 완료');
  }
})();