const { chromium } = require('playwright');

async function testLecturesNetwork() {
  console.log('🌐 플레이라이트로 lectures/3 네트워크 분석 시작...');
  
  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 1200, height: 800 }
    });
    
    const page = await context.newPage();
    
    // 네트워크 요청 모니터링
    const requests = [];
    const responses = [];
    
    page.on('request', request => {
      requests.push({
        url: request.url(),
        method: request.method(),
        resourceType: request.resourceType(),
        headers: request.headers()
      });
    });
    
    page.on('response', response => {
      responses.push({
        url: response.url(),
        status: response.status(),
        contentType: response.headers()['content-type'] || '',
        size: response.headers()['content-length'] || '0'
      });
    });
    
    // 로그인
    console.log('🔐 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    // lectures/3 페이지 이동
    console.log('📅 lectures/3 페이지 이동...');
    await page.goto('https://www.topmktx.com/lectures/3', { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);
    
    // CSS 요청 분석
    console.log('\n🎨 CSS 파일 요청 분석:');
    const cssRequests = responses.filter(r => 
      r.url.includes('.css') && 
      (r.status === 200 || r.status >= 300)
    );
    
    cssRequests.forEach(req => {
      const filename = req.url.split('/').pop().split('?')[0];
      const statusIcon = req.status === 200 ? '✅' : '❌';
      console.log(`${statusIcon} ${filename}: ${req.status} (${req.size} bytes)`);
      
      if (req.status !== 200) {
        console.log(`   URL: ${req.url}`);
      }
    });
    
    // JS 요청 분석
    console.log('\n📜 JavaScript 파일 요청 분석:');
    const jsRequests = responses.filter(r => 
      r.url.includes('.js') && 
      (r.status === 200 || r.status >= 300)
    );
    
    jsRequests.forEach(req => {
      const filename = req.url.split('/').pop().split('?')[0];
      const statusIcon = req.status === 200 ? '✅' : '❌';
      console.log(`${statusIcon} ${filename}: ${req.status} (${req.size} bytes)`);
      
      if (req.status !== 200) {
        console.log(`   URL: ${req.url}`);
      }
    });
    
    // 실패한 요청들
    console.log('\n❌ 실패한 요청들:');
    const failedRequests = responses.filter(r => r.status >= 400);
    
    if (failedRequests.length === 0) {
      console.log('✅ 실패한 요청 없음');
    } else {
      failedRequests.forEach(req => {
        console.log(`❌ ${req.status}: ${req.url}`);
      });
    }
    
    // 리다이렉트된 요청들
    console.log('\n🔄 리다이렉트된 요청들:');
    const redirectedRequests = responses.filter(r => r.status >= 300 && r.status < 400);
    
    if (redirectedRequests.length === 0) {
      console.log('✅ 리다이렉트된 요청 없음');
    } else {
      redirectedRequests.forEach(req => {
        console.log(`🔄 ${req.status}: ${req.url}`);
      });
    }
    
    // CSS 콘텐츠 확인
    console.log('\n🎨 CSS 콘텐츠 검증:');
    const cssContent = await page.evaluate(() => {
      const results = {};
      
      // 주요 CSS 파일들 확인
      const cssUrls = [
        '/assets/css/php/base.css.php',
        '/assets/css/php/layout.css.php', 
        '/assets/css/php/buttons.css.php',
        '/assets/css/main.css',
        '/assets/css/badges.css',
        '/assets/css/search-filter.css'
      ];
      
      // 스타일시트 확인
      Array.from(document.styleSheets).forEach((sheet, index) => {
        if (sheet.href) {
          const url = new URL(sheet.href);
          const pathname = url.pathname;
          
          if (cssUrls.some(cssUrl => pathname.includes(cssUrl.split('/').pop().split('.')[0]))) {
            try {
              const rules = sheet.cssRules ? sheet.cssRules.length : 0;
              results[pathname] = {
                loaded: true,
                rules: rules,
                disabled: sheet.disabled
              };
            } catch (e) {
              results[pathname] = {
                loaded: false,
                error: e.message
              };
            }
          }
        }
      });
      
      return results;
    });
    
    Object.entries(cssContent).forEach(([url, info]) => {
      const filename = url.split('/').pop();
      if (info.loaded) {
        console.log(`✅ ${filename}: ${info.rules}개 규칙 로드됨`);
      } else {
        console.log(`❌ ${filename}: 로드 실패 - ${info.error}`);
      }
    });
    
    // 스타일 적용 확인
    console.log('\n🎯 스타일 적용 확인:');
    const styleCheck = await page.evaluate(() => {
      const checks = {};
      
      // 주요 요소들의 스타일 확인
      const elements = [
        { selector: '.lecture-detail-container', property: 'display' },
        { selector: '.lecture-header', property: 'background' },
        { selector: '.lecture-banner', property: 'background', expected: /linear-gradient/ },
        { selector: '.lecture-title', property: 'font-size' },
        { selector: '.lecture-category', property: 'background' },
        { selector: 'body', property: 'background-color' },
        { selector: 'header', property: 'background' }
      ];
      
      elements.forEach(({ selector, property, expected }) => {
        const element = document.querySelector(selector);
        if (element) {
          const value = window.getComputedStyle(element)[property];
          const isExpected = expected ? expected.test(value) : value !== 'none' && value !== '';
          checks[selector] = {
            property,
            value,
            applied: isExpected
          };
        } else {
          checks[selector] = { error: 'ELEMENT_NOT_FOUND' };
        }
      });
      
      return checks;
    });
    
    Object.entries(styleCheck).forEach(([selector, info]) => {
      if (info.error) {
        console.log(`❌ ${selector}: 요소를 찾을 수 없음`);
      } else {
        const status = info.applied ? '✅' : '⚠️';
        console.log(`${status} ${selector} ${info.property}: ${info.value}`);
      }
    });
    
  } catch (error) {
    console.error('❌ 테스트 중 오류 발생:', error);
  } finally {
    await browser.close();
  }
}

testLecturesNetwork().catch(console.error);
