import playwright from 'playwright';

(async () => {
  console.log('🚀 Playwright로 소형 모바일 뷰포트 테스트 시작...');
  
  const browser = await playwright.chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
  });
  
  const context = await browser.newContext({
    viewport: { width: 320, height: 568 },
    deviceScaleFactor: 2,
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1'
  });
  
  const page = await context.newPage();
  
  try {
    // 탑마케팅 홈페이지 접속
    console.log('📱 소형 모바일 뷰포트 (320x568)로 이벤트 페이지 접속...');
    await page.goto('https://www.topmktx.com/events', { 
      waitUntil: 'networkidle',
      timeout: 30000 
    });
    
    // 페이지 로딩 대기
    await page.waitForTimeout(3000);
    
    // 스크린샷 촬영
    await page.screenshot({ 
      path: '/var/www/html/topmkt/events_small_fixed.png',
      fullPage: true,
      type: 'png'
    });
    
    console.log('✅ 소형 모바일 스크린샷 저장 완료: events_small_fixed.png');
    
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
          area: rect.width * rect.height,
          fontSize: window.getComputedStyle(el).fontSize
        };
      });
    });
    
    console.log('🎯 터치 타겟 크기 검증:');
    touchTargets.forEach(target => {
      const meetsStandard = target.width >= 44 && target.height >= 44;
      console.log(`  ${target.selector}: ${Math.round(target.width)}x${Math.round(target.height)}px ${meetsStandard ? '✅' : '❌'} (font: ${target.fontSize})`);
    });
    
    // 버튼 텍스트 2줄 여부 확인
    const buttonTextWrapping = await page.evaluate(() => {
      const buttons = document.querySelectorAll('.create-event-btn, .view-btn');
      return Array.from(buttons).map(btn => {
        const height = btn.getBoundingClientRect().height;
        const lineHeight = parseFloat(window.getComputedStyle(btn).lineHeight);
        const hasWrapping = height > lineHeight * 1.5;
        return {
          text: btn.textContent.trim(),
          height: height,
          lineHeight: lineHeight,
          hasWrapping: hasWrapping
        };
      });
    });
    
    console.log('📝 버튼 텍스트 줄바꿈 검사:');
    buttonTextWrapping.forEach(btn => {
      console.log(`  "${btn.text}": ${btn.hasWrapping ? '❌ 2줄' : '✅ 1줄'} (height: ${Math.round(btn.height)}px)`);
    });
    
  } catch (error) {
    console.error('❌ 에러:', error.message);
  } finally {
    await browser.close();
  }
})();