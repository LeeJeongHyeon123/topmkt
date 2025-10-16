const { chromium } = require('playwright');

async function testRealBrowserScenario() {
  console.log('🌐 실제 브라우저 시나리오 테스트 시작...');
  
  // 실제 브라우저처럼 설정
  const browser = await chromium.launch({
    headless: false, // 실제 브라우저처럼 보이게 함
    args: [
      '--no-sandbox', 
      '--disable-setuid-sandbox',
      '--disable-web-security', // CORS 문제 방지
      '--disable-features=VizDisplayCompositor'
    ]
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 1200, height: 800 },
      userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
      // 실제 브라우저처럼 쿠키와 캐시 활성화
      storageState: undefined,
      permissions: ['geolocation'],
      geolocation: { latitude: 37.5665, longitude: 126.9780 },
      locale: 'ko-KR',
      timezoneId: 'Asia/Seoul'
    });
    
    const page = await context.newPage();
    
    // 콘솔 메시지 캡처
    page.on('console', msg => {
      const type = msg.type();
      const text = msg.text();
      
      if (type === 'error') {
        console.log(`❌ CONSOLE ERROR: ${text}`);
      } else if (type === 'warning') {
        console.log(`⚠️ CONSOLE WARN: ${text}`);
      } else if (text.includes('CSS') || text.includes('스타일') || text.includes('lecture')) {
        console.log(`🎨 CONSOLE: ${text}`);
      }
    });
    
    // 페이지 로드
    console.log('📄 lectures/3 페이지 로드 중...');
    await page.goto('https://www.topmktx.com/lectures/3', { 
      waitUntil: 'networkidle',
      timeout: 30000 
    });
    
    await page.waitForTimeout(5000); // 추가 대기
    
    // CSS 적용 상태 확인
    console.log('\n🎨 실제 브라우저 CSS 상태 확인:');
    const cssStatus = await page.evaluate(() => {
      const results = {
        styleSheetsCount: document.styleSheets.length,
        lectureElements: {},
        computedStyles: {}
      };
      
      // 스타일시트 수 확인
      Array.from(document.styleSheets).forEach((sheet, index) => {
        try {
          const url = sheet.href ? new URL(sheet.href).pathname : 'inline';
          const rules = sheet.cssRules ? sheet.cssRules.length : 0;
          results[`sheet_${index}`] = `${url}: ${rules} rules`;
        } catch (e) {
          results[`sheet_${index}`] = `error: ${e.message}`;
        }
      });
      
      // 주요 요소 존재 및 스타일 확인
      const selectors = [
        '.lecture-detail-container',
        '.lecture-header', 
        '.lecture-banner',
        '.lecture-title',
        '.lecture-content',
        '.lecture-sidebar'
      ];
      
      selectors.forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
          const rect = element.getBoundingClientRect();
          const computed = window.getComputedStyle(element);
          
          results.lectureElements[selector] = {
            exists: true,
            dimensions: `${rect.width}x${rect.height}`,
            position: `${rect.left}, ${rect.top}`,
            display: computed.display,
            visibility: computed.visibility,
            opacity: computed.opacity
          };
          
          if (selector === '.lecture-banner') {
            results.computedStyles.bannerBg = computed.background;
          }
          if (selector === '.lecture-title') {
            results.computedStyles.titleSize = computed.fontSize;
            results.computedStyles.titleColor = computed.color;
          }
        } else {
          results.lectureElements[selector] = { exists: false };
        }
      });
      
      // body 스타일 확인
      const body = document.body;
      const bodyComputed = window.getComputedStyle(body);
      results.bodyStyle = {
        backgroundColor: bodyComputed.backgroundColor,
        margin: bodyComputed.margin,
        padding: bodyComputed.padding
      };
      
      return results;
    });
    
    console.log(`📄 스타일시트 수: ${cssStatus.styleSheetsCount}`);
    console.log('\n🏗️ 강의 요소 상태:');
    Object.entries(cssStatus.lectureElements).forEach(([selector, info]) => {
      if (info.exists) {
        console.log(`✅ ${selector}: ${info.dimensions} (${info.position}) - ${info.display}`);
      } else {
        console.log(`❌ ${selector}: 요소 없음`);
      }
    });
    
    console.log('\n🎨 계산된 스타일:');
    if (cssStatus.computedStyles.bannerBg) {
      console.log(`   └─ 배너 배경: ${cssStatus.computedStyles.bannerBg}`);
    }
    if (cssStatus.computedStyles.titleSize) {
      console.log(`   └─ 제목 크기: ${cssStatus.computedStyles.titleSize}`);
    }
    if (cssStatus.computedStyles.titleColor) {
      console.log(`   └─ 제목 색상: ${cssStatus.computedStyles.titleColor}`);
    }
    
    console.log(`   └─ 바디 배경: ${cssStatus.bodyStyle.backgroundColor}`);
    
    // 스크린샷 촬영
    console.log('\n📸 실제 브라우저 스크린샷 촬영...');
    await page.screenshot({ 
      path: '/var/www/html/topmkt/lectures_real_browser.png', 
      fullPage: true 
    });
    console.log('✅ 스크린샷 저장됨: /var/www/html/topmkt/lectures_real_browser.png');
    
    // CSS 디버깅 스크립트 실행
    console.log('\n🔍 CSS 디버깅 스크립트 실행...');
    try {
      await page.evaluate(() => {
        if (window.runCSSDebug) {
          window.runCSSDebug();
        } else {
          console.log('⚠️ CSS 디버깅 스크립트가 로드되지 않음');
        }
      });
    } catch (e) {
      console.log('❌ CSS 디버깅 실행 실패:', e.message);
    }
    
    // 잠시 대기해서 디버깅 결과 확인
    await page.waitForTimeout(3000);
    
    console.log('\n✅ 실제 브라우저 테스트 완료');
    
  } catch (error) {
    console.error('❌ 실제 브라우저 테스트 실패:', error);
  } finally {
    await browser.close();
  }
}

testRealBrowserScenario().catch(console.error);
