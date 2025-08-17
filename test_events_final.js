import playwright from 'playwright';

(async () => {
  console.log('🚀 Playwright로 최종 터치 타겟 테스트 시작...');
  
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
    // 캐시 무시하고 접속
    console.log('📱 캐시 무시하고 이벤트 페이지 접속...');
    await page.goto('https://www.topmktx.com/events?nocache=' + Date.now(), { 
      waitUntil: 'networkidle',
      timeout: 30000 
    });
    
    // 페이지 로딩 대기
    await page.waitForTimeout(5000);
    
    // 강제 새로고침
    await page.reload({ waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);
    
    // 스크린샷 촬영
    await page.screenshot({ 
      path: '/var/www/html/topmkt/events_mobile_final.png',
      fullPage: true,
      type: 'png'
    });
    
    console.log('✅ 최종 모바일 스크린샷 저장 완료: events_mobile_final.png');
    
    // CSS 스타일 실제 적용 확인
    const actualStyles = await page.evaluate(() => {
      const navBtns = document.querySelectorAll('.nav-btn');
      const viewBtns = document.querySelectorAll('.view-btn');
      
      const getElementInfo = (el) => {
        const rect = el.getBoundingClientRect();
        const styles = window.getComputedStyle(el);
        return {
          className: el.className,
          width: rect.width,
          height: rect.height,
          minWidth: styles.minWidth,
          minHeight: styles.minHeight,
          padding: styles.padding,
          boxSizing: styles.boxSizing
        };
      };
      
      return {
        navBtns: Array.from(navBtns).map(getElementInfo),
        viewBtns: Array.from(viewBtns).map(getElementInfo)
      };
    });
    
    console.log('🎯 실제 CSS 스타일 적용 확인:');
    console.log('네비게이션 버튼:');
    actualStyles.navBtns.forEach((btn, i) => {
      console.log(`  nav-btn ${i+1}: ${Math.round(btn.width)}x${Math.round(btn.height)}px (min: ${btn.minWidth}x${btn.minHeight}, padding: ${btn.padding})`);
    });
    
    console.log('뷰 전환 버튼:');
    actualStyles.viewBtns.forEach((btn, i) => {
      console.log(`  view-btn ${i+1}: ${Math.round(btn.width)}x${Math.round(btn.height)}px (min: ${btn.minWidth}x${btn.minHeight}, padding: ${btn.padding})`);
    });
    
    // 터치 가능 영역 체크
    const touchAreas = await page.evaluate(() => {
      const elements = [
        ...document.querySelectorAll('.nav-btn'),
        ...document.querySelectorAll('.view-btn'),
        ...document.querySelectorAll('.create-event-btn')
      ];
      
      return elements.map(el => {
        const rect = el.getBoundingClientRect();
        const area = rect.width * rect.height;
        const meetsStandard = rect.width >= 44 && rect.height >= 44;
        return {
          selector: el.className,
          width: Math.round(rect.width),
          height: Math.round(rect.height),
          area: Math.round(area),
          meetsStandard: meetsStandard
        };
      });
    });
    
    console.log('📏 터치 영역 최종 검증:');
    touchAreas.forEach(area => {
      console.log(`  ${area.selector}: ${area.width}x${area.height}px (면적: ${area.area}px²) ${area.meetsStandard ? '✅' : '❌'}`);
    });
    
    const passedCount = touchAreas.filter(area => area.meetsStandard).length;
    const totalCount = touchAreas.length;
    console.log(`\n📊 터치 타겟 기준 통과율: ${passedCount}/${totalCount} (${Math.round(passedCount/totalCount*100)}%)`);
    
  } catch (error) {
    console.error('❌ 에러:', error.message);
  } finally {
    await browser.close();
  }
})();