const { chromium } = require('playwright');

async function testLecturesUI() {
  console.log('🎭 플레이라이트로 lectures/3 UI 테스트 시작...');
  
  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 1200, height: 800 },
      userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    });
    
    const page = await context.newPage();
    
    // 1. 로그인 (DevLoginHelper 사용)
    console.log('🔐 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    // 로그인 상태 확인
    const loginStatus = await page.evaluate(() => {
      return {
        hasSession: document.cookie.includes('PHPSESSID'),
        currentUserId: window.userId || null,
        isLoggedIn: !!document.querySelector('.user-menu') || !!document.querySelector('.nav-auth .user-avatar')
      };
    });
    
    console.log('📋 로그인 상태:', loginStatus);
    
    // 2. lectures/3 페이지로 이동
    console.log('📅 lectures/3 페이지 이동...');
    await page.goto('https://www.topmktx.com/lectures/3', { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);
    
    // 3. 페이지 로드 상태 확인
    const pageStatus = await page.evaluate(() => {
      return {
        title: document.title,
        url: window.location.href,
        readyState: document.readyState,
        hasContent: !!document.querySelector('.lecture-detail-container'),
        hasHeader: !!document.querySelector('header'),
        hasFooter: !!document.querySelector('footer'),
        cssLoaded: Array.from(document.styleSheets).length,
        jsLoaded: Array.from(document.scripts).filter(s => s.src).length,
        imagesLoaded: Array.from(document.images).filter(img => img.complete).length,
        totalImages: document.images.length
      };
    });
    
    console.log('📊 페이지 상태:', pageStatus);
    
    // 4. CSS 캐스케이드 분석
    console.log('🎨 CSS 캐스케이드 분석 중...');
    const cssAnalysis = await page.evaluate(() => {
      const styles = {};
      
      // 주요 요소들의 computed style 확인
      const selectors = [
        '.lecture-detail-container',
        '.lecture-header',
        '.lecture-banner',
        '.lecture-category',
        '.lecture-title',
        '.lecture-subtitle',
        '.lecture-meta-basic',
        '.lecture-actions',
        '.lecture-content',
        '.lecture-sidebar'
      ];
      
      selectors.forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
          const computed = window.getComputedStyle(element);
          styles[selector] = {
            display: computed.display,
            visibility: computed.visibility,
            opacity: computed.opacity,
            position: computed.position,
            width: computed.width,
            height: computed.height,
            backgroundColor: computed.backgroundColor,
            color: computed.color,
            fontSize: computed.fontSize,
            margin: computed.margin,
            padding: computed.padding
          };
        } else {
          styles[selector] = 'ELEMENT_NOT_FOUND';
        }
      });
      
      return styles;
    });
    
    console.log('🎨 CSS 분석 결과:');
    Object.entries(cssAnalysis).forEach(([selector, style]) => {
      if (style === 'ELEMENT_NOT_FOUND') {
        console.log(`❌ ${selector}: 요소를 찾을 수 없음`);
      } else {
        console.log(`✅ ${selector}: display=${style.display}, visibility=${style.visibility}, opacity=${style.opacity}`);
      }
    });
    
    // 5. 스크린샷 촬영
    console.log('📸 스크린샷 촬영 중...');
    await page.screenshot({ 
      path: '/var/www/html/topmkt/lectures_ui_test.png', 
      fullPage: true 
    });
    console.log('✅ 스크린샷 저장됨: /var/www/html/topmkt/lectures_ui_test.png');
    
    // 6. 콘솔 에러 확인
    const consoleMessages = [];
    page.on('console', msg => {
      consoleMessages.push({
        type: msg.type(),
        text: msg.text(),
        location: msg.location()
      });
    });
    
    await page.waitForTimeout(2000);
    
    console.log('📝 콘솔 메시지:');
    consoleMessages.slice(-10).forEach(msg => {
      console.log(`[${msg.type.toUpperCase()}] ${msg.text}`);
    });
    
    // 7. 네트워크 요청 분석
    console.log('🌐 네트워크 요청 분석:');
    const failedRequests = [];
    
    page.on('response', response => {
      const url = response.url();
      const status = response.status();
      
      if (status >= 400) {
        failedRequests.push({ url, status });
      }
      
      // CSS/JS 파일 요청 로깅
      if (url.includes('.css') || url.includes('.js')) {
        console.log(`📄 ${status === 200 ? '✅' : '❌'} ${url.split('/').pop()}: ${status}`);
      }
    });
    
    if (failedRequests.length > 0) {
      console.log('❌ 실패한 요청들:');
      failedRequests.forEach(req => {
        console.log(`  ${req.status}: ${req.url}`);
      });
    }
    
    // 8. 최종 결과 요약
    console.log('\n📋 === 테스트 결과 요약 ===');
    console.log(`페이지 타이틀: ${pageStatus.title}`);
    console.log(`컨텐츠 존재: ${pageStatus.hasContent ? '✅' : '❌'}`);
    console.log(`헤더 존재: ${pageStatus.hasHeader ? '✅' : '❌'}`);
    console.log(`푸터 존재: ${pageStatus.hasFooter ? '✅' : '❌'}`);
    console.log(`CSS 파일 수: ${pageStatus.cssLoaded}`);
    console.log(`JS 파일 수: ${pageStatus.jsLoaded}`);
    console.log(`이미지 로드: ${pageStatus.imagesLoaded}/${pageStatus.totalImages}`);
    
    // 주요 요소 존재 확인
    const criticalElements = [
      '.lecture-detail-container',
      '.lecture-header', 
      '.lecture-banner',
      '.lecture-title',
      '.lecture-meta-basic',
      '.lecture-content',
      '.lecture-sidebar'
    ];
    
    let missingElements = 0;
    criticalElements.forEach(selector => {
      if (cssAnalysis[selector] === 'ELEMENT_NOT_FOUND') {
        console.log(`❌ 누락된 요소: ${selector}`);
        missingElements++;
      }
    });
    
    if (missingElements === 0) {
      console.log('✅ 모든 주요 요소가 정상 로드됨');
    }
    
    // CSS 적용 상태 확인
    const brokenStyles = Object.entries(cssAnalysis).filter(([selector, style]) => {
      return style !== 'ELEMENT_NOT_FOUND' && 
             (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity) === 0);
    });
    
    if (brokenStyles.length > 0) {
      console.log('⚠️ CSS 문제 의심 요소들:');
      brokenStyles.forEach(([selector, style]) => {
        console.log(`  ${selector}: display=${style.display}, visibility=${style.visibility}, opacity=${style.opacity}`);
      });
    } else {
      console.log('✅ CSS 스타일이 정상 적용됨');
    }
    
  } catch (error) {
    console.error('❌ 테스트 중 오류 발생:', error);
  } finally {
    await browser.close();
  }
}

testLecturesUI().catch(console.error);
