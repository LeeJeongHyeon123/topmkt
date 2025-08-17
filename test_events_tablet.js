import playwright from 'playwright';

(async () => {
  console.log('🚀 Playwright로 태블릿 뷰포트 테스트 시작...');
  
  const browser = await playwright.chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
  });
  
  const context = await browser.newContext({
    viewport: { width: 768, height: 1024 },
    deviceScaleFactor: 2,
    userAgent: 'Mozilla/5.0 (iPad; CPU OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1'
  });
  
  const page = await context.newPage();
  
  try {
    // 탑마케팅 홈페이지 접속
    console.log('📱 태블릿 뷰포트 (768x1024)로 이벤트 페이지 접속...');
    await page.goto('https://www.topmktx.com/events', { 
      waitUntil: 'networkidle',
      timeout: 30000 
    });
    
    // 페이지 로딩 대기
    await page.waitForTimeout(3000);
    
    // 스크린샷 촬영
    await page.screenshot({ 
      path: '/var/www/html/topmkt/events_tablet_fixed.png',
      fullPage: true,
      type: 'png'
    });
    
    console.log('✅ 태블릿 스크린샷 저장 완료: events_tablet_fixed.png');
    
    // 터치 타겟 크기 검증
    const touchTargets = await page.evaluate(() => {
      const elements = [
        ...document.querySelectorAll('.nav-btn'),
        ...document.querySelectorAll('.view-btn'),
        ...document.querySelectorAll('.create-event-btn'),
        ...document.querySelectorAll('.event-item'),
        ...document.querySelectorAll('.more-events')
      ];
      
      return elements.map((el, index) => {
        const rect = el.getBoundingClientRect();
        return {
          selector: el.className,
          index: index,
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