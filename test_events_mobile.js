import playwright from 'playwright';

(async () => {
  console.log('🚀 Playwright로 모바일 뷰포트 테스트 시작...');
  
  const browser = await playwright.chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
  });
  
  const context = await browser.newContext({
    viewport: { width: 375, height: 667 },
    deviceScaleFactor: 2,
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1'
  });
  
  const page = await context.newPage();
  
  try {
    // 탑마케팅 홈페이지 접속
    console.log('📱 모바일 뷰포트 (375x667)로 이벤트 페이지 접속...');
    await page.goto('https://www.topmktx.com/events', { 
      waitUntil: 'networkidle',
      timeout: 30000 
    });
    
    // 페이지 로딩 대기
    await page.waitForTimeout(3000);
    
    // 스크린샷 촬영
    await page.screenshot({ 
      path: '/var/www/html/topmkt/events_mobile_fixed.png',
      fullPage: true,
      type: 'png'
    });
    
    console.log('✅ 모바일 스크린샷 저장 완료: events_mobile_fixed.png');
    
    // 터치 타겟 크기 검증
    const touchTargets = await page.evaluate(() => {
      const elements = [
        ...document.querySelectorAll('.nav-btn'),
        ...document.querySelectorAll('.view-btn'),
        ...document.querySelectorAll('.create-event-btn'),
        ...document.querySelectorAll('.event-item'),
        ...document.querySelectorAll('.modal-close')
      ];
      
      return elements.map(el => {
        const rect = el.getBoundingClientRect();
        return {
          selector: el.className,
          width: rect.width,
          height: rect.height,
          area: rect.width * rect.height
        };
      });
    });
    
    console.log('🎯 터치 타겟 크기 검증:');
    touchTargets.forEach(target => {
      const meetsStandard = target.width >= 44 && target.height >= 44;
      console.log(`  ${target.selector}: ${Math.round(target.width)}x${Math.round(target.height)}px ${meetsStandard ? '✅' : '❌'}`);
    });
    
  } catch (error) {
    console.error('❌ 에러:', error.message);
  } finally {
    await browser.close();
  }
})();