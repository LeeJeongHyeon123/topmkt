import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch(); // 헤드리스 모드
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  try {
    console.log('📱 휴대폰 번호 백스페이스 테스트 시작...');
    
    // 회원가입 페이지로 이동
    await page.goto('https://www.topmktx.com/auth/signup', { waitUntil: 'domcontentloaded' });
    console.log('✅ 회원가입 페이지 로드 완료');

    // 휴대폰 번호 입력 필드 찾기
    const phoneInput = page.locator('#phone');
    await phoneInput.waitFor({ timeout: 10000 });
    console.log('✅ 휴대폰 번호 입력 필드 발견');

    // 휴대폰 번호 입력 테스트
    console.log('📱 010-1234-1234 입력 중...');
    await phoneInput.click();
    await phoneInput.fill('01012341234'); // 숫자로 입력
    
    // 잠시 대기하여 포맷팅 확인
    await page.waitForTimeout(1000);
    
    // 현재 값 확인
    const currentValue = await phoneInput.inputValue();
    console.log('📱 포맷팅된 값:', currentValue);

    // 백스페이스 연속 테스트
    console.log('🔙 백스페이스 연속 테스트 시작...');
    
    for (let i = 0; i < 15; i++) {
      await page.keyboard.press('Backspace');
      await page.waitForTimeout(200); // 각 키 사이에 약간의 대기
      
      const value = await phoneInput.inputValue();
      console.log(`🔙 백스페이스 ${i + 1}번째:`, value);
      
      // 값이 비어있으면 테스트 완료
      if (value === '') {
        console.log('✅ 완전히 삭제됨 - 테스트 성공!');
        break;
      }
    }

    console.log('📸 스크린샷 촬영...');
    await page.screenshot({ path: 'phone-backspace-test.png' });
    
  } catch (error) {
    console.error('❌ 테스트 오류:', error);
    await page.screenshot({ path: 'phone-backspace-error.png' });
  } finally {
    console.log('🏁 테스트 완료 - 브라우저 종료');
    await browser.close();
  }
})();