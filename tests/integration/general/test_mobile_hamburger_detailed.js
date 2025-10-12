import { chromium } from 'playwright';

(async () => {
  console.log('🔍 모바일 햄버거 메뉴 상세 분석 시작...');
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 390, height: 844 },
      userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1'
    });
    
    const page = await context.newPage();
    
    // 1. DevLogin Helper로 사용자 ID 4 로그인
    console.log('📱 1단계: DevLogin Helper로 로그인...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    // 메인 페이지로 이동
    console.log('🏠 2단계: 메인 페이지로 이동...');
    await page.goto('https://www.topmktx.com/');
    await page.waitForTimeout(3000);
    
    // 현재 화면 스크린샷
    await page.screenshot({ 
      path: '/var/www/html/topmkt/mobile_header_detailed.png',
      fullPage: false
    });
    
    // 3. 햄버거 메뉴 상세 분석
    console.log('🔍 3단계: 햄버거 메뉴 상세 분석...');
    
    const hamburgerInfo = await page.evaluate(() => {
      const hamburger = document.querySelector('#mobile-menu-toggle');
      if (!hamburger) return { exists: false };
      
      const rect = hamburger.getBoundingClientRect();
      const styles = window.getComputedStyle(hamburger);
      
      return {
        exists: true,
        visible: rect.width > 0 && rect.height > 0,
        position: {
          top: rect.top,
          left: rect.left,
          width: rect.width,
          height: rect.height
        },
        styles: {
          display: styles.display,
          visibility: styles.visibility,
          opacity: styles.opacity,
          position: styles.position,
          zIndex: styles.zIndex,
          backgroundColor: styles.backgroundColor,
          color: styles.color,
          border: styles.border
        },
        innerHTML: hamburger.innerHTML,
        classList: Array.from(hamburger.classList),
        attributes: Array.from(hamburger.attributes).map(attr => ({
          name: attr.name,
          value: attr.value
        }))
      };
    });
    
    console.log('🍔 햄버거 메뉴 상세 정보:');
    console.log(JSON.stringify(hamburgerInfo, null, 2));
    
    // 4. 헤더 전체 구조 분석
    console.log('📋 4단계: 헤더 전체 구조 분석...');
    
    const headerInfo = await page.evaluate(() => {
      const header = document.querySelector('header');
      if (!header) return { exists: false };
      
      // 모든 자식 요소들 분석
      const elements = [];
      const collectElements = (element, depth = 0) => {
        const rect = element.getBoundingClientRect();
        const styles = window.getComputedStyle(element);
        
        elements.push({
          tagName: element.tagName,
          id: element.id,
          classList: Array.from(element.classList),
          depth: depth,
          visible: rect.width > 0 && rect.height > 0,
          position: {
            top: rect.top,
            left: rect.left,
            width: rect.width,
            height: rect.height
          },
          display: styles.display
        });
        
        Array.from(element.children).forEach(child => {
          if (depth < 3) { // 3레벨까지만
            collectElements(child, depth + 1);
          }
        });
      };
      
      collectElements(header);
      return { exists: true, elements };
    });
    
    console.log('📄 헤더 구조 요소들:');
    headerInfo.elements.forEach(el => {
      const indent = '  '.repeat(el.depth);
      console.log(`${indent}${el.tagName}${el.id ? `#${el.id}` : ''}${el.classList.length ? `.${el.classList.join('.')}` : ''} - ${el.visible ? 'VISIBLE' : 'HIDDEN'} (${el.display})`);
    });
    
    // 5. 우측 영역 분석
    console.log('➡️ 5단계: 헤더 우측 영역 분석...');
    
    const rightAreaInfo = await page.evaluate(() => {
      const rightSelectors = [
        '.header-right',
        '.nav-right', 
        '.user-menu',
        '.user-info',
        '.profile-section'
      ];
      
      const results = {};
      rightSelectors.forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
          const rect = element.getBoundingClientRect();
          results[selector] = {
            exists: true,
            visible: rect.width > 0 && rect.height > 0,
            innerHTML: element.innerHTML.substring(0, 200) + '...'
          };
        } else {
          results[selector] = { exists: false };
        }
      });
      
      return results;
    });
    
    console.log('📍 우측 영역 요소들:');
    Object.entries(rightAreaInfo).forEach(([selector, info]) => {
      console.log(`  ${selector}: ${info.exists ? (info.visible ? 'VISIBLE' : 'HIDDEN') : 'NOT FOUND'}`);
    });
    
    // 6. 미디어 쿼리 및 반응형 확인
    console.log('📱 6단계: 반응형 CSS 확인...');
    
    const responsiveInfo = await page.evaluate(() => {
      const mediaQueries = [
        '(max-width: 768px)',
        '(max-width: 480px)',
        '(min-width: 769px)'
      ];
      
      const results = {};
      mediaQueries.forEach(query => {
        results[query] = window.matchMedia(query).matches;
      });
      
      return results;
    });
    
    console.log('📱 미디어 쿼리 상태:');
    Object.entries(responsiveInfo).forEach(([query, matches]) => {
      console.log(`  ${query}: ${matches ? 'ACTIVE' : 'INACTIVE'}`);
    });
    
    // 7. CSS 파일 로딩 확인
    console.log('🎨 7단계: CSS 파일 로딩 확인...');
    
    const cssInfo = await page.evaluate(() => {
      const stylesheets = Array.from(document.styleSheets);
      return stylesheets.map(sheet => ({
        href: sheet.href,
        title: sheet.title,
        disabled: sheet.disabled,
        rules: sheet.cssRules ? sheet.cssRules.length : 'Cannot access'
      }));
    });
    
    console.log('🎨 로드된 CSS 파일들:');
    cssInfo.forEach(css => {
      console.log(`  ${css.href || 'inline'} - ${css.disabled ? 'DISABLED' : 'ENABLED'} (${css.rules} rules)`);
    });
    
    // 8. 클릭 테스트
    console.log('🖱️ 8단계: 햄버거 메뉴 클릭 테스트...');
    
    try {
      // 햄버거 메뉴 클릭 전 상태
      const beforeClick = await page.evaluate(() => {
        const nav = document.querySelector('#main-nav');
        return nav ? window.getComputedStyle(nav).display : 'not found';
      });
      console.log(`📋 클릭 전 메인 네비게이션 상태: ${beforeClick}`);
      
      // 햄버거 메뉴 클릭
      await page.click('#mobile-menu-toggle');
      await page.waitForTimeout(500);
      
      // 클릭 후 상태
      const afterClick = await page.evaluate(() => {
        const nav = document.querySelector('#main-nav');
        return nav ? window.getComputedStyle(nav).display : 'not found';
      });
      console.log(`📋 클릭 후 메인 네비게이션 상태: ${afterClick}`);
      
      // 클릭 후 스크린샷
      await page.screenshot({ 
        path: '/var/www/html/topmkt/mobile_hamburger_clicked.png',
        fullPage: false
      });
      
    } catch (error) {
      console.log('❌ 햄버거 메뉴 클릭 오류:', error.message);
    }
    
    console.log('✅ 모바일 햄버거 메뉴 상세 분석 완료!');
    console.log('📁 생성된 스크린샷:');
    console.log('  - mobile_header_detailed.png (현재 상태)');
    console.log('  - mobile_hamburger_clicked.png (클릭 후)');
    
  } catch (error) {
    console.error('❌ 오류 발생:', error);
  } finally {
    await browser.close();
  }
})();