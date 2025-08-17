import { chromium } from 'playwright';

(async () => {
  console.log('🔍 햄버거 메뉴 가시성 정확한 분석 시작...');
  
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
    
    // DevLogin Helper로 사용자 ID 4 로그인
    console.log('📱 로그인 중...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
    await page.waitForTimeout(2000);
    
    // 메인 페이지로 이동
    console.log('🏠 메인 페이지로 이동...');
    await page.goto('https://www.topmktx.com/');
    await page.waitForTimeout(3000);
    
    // 1. 햄버거 메뉴 완전 분석
    console.log('🔍 1단계: 햄버거 메뉴 상세 분석...');
    
    const hamburgerAnalysis = await page.evaluate(() => {
      const hamburger = document.querySelector('#mobile-menu-toggle');
      
      if (!hamburger) {
        return { 
          exists: false, 
          message: 'Hamburger element not found' 
        };
      }
      
      const rect = hamburger.getBoundingClientRect();
      const styles = window.getComputedStyle(hamburger);
      const isInViewport = rect.right > 0 && rect.left < window.innerWidth && 
                          rect.bottom > 0 && rect.top < window.innerHeight;
      
      return {
        exists: true,
        isInViewport,
        position: {
          top: rect.top,
          left: rect.left,
          right: rect.right,
          bottom: rect.bottom,
          width: rect.width,
          height: rect.height
        },
        viewportSize: {
          width: window.innerWidth,
          height: window.innerHeight
        },
        styles: {
          display: styles.display,
          visibility: styles.visibility,
          opacity: styles.opacity,
          position: styles.position,
          zIndex: styles.zIndex,
          overflow: styles.overflow,
          transform: styles.transform,
          margin: styles.margin,
          padding: styles.padding,
          right: styles.right,
          left: styles.left,
          order: styles.order
        },
        computedVisible: rect.width > 0 && rect.height > 0 && 
                        styles.display !== 'none' && 
                        styles.visibility !== 'hidden' && 
                        parseFloat(styles.opacity) > 0,
        parentInfo: hamburger.parentElement ? {
          tagName: hamburger.parentElement.tagName,
          className: hamburger.parentElement.className,
          rect: hamburger.parentElement.getBoundingClientRect(),
          styles: {
            display: window.getComputedStyle(hamburger.parentElement).display,
            overflow: window.getComputedStyle(hamburger.parentElement).overflow
          }
        } : null
      };
    });
    
    console.log('🔍 햄버거 메뉴 분석 결과:');
    console.log(JSON.stringify(hamburgerAnalysis, null, 2));
    
    // 2. 헤더 전체 레이아웃 분석
    console.log('📋 2단계: 헤더 레이아웃 분석...');
    
    const headerAnalysis = await page.evaluate(() => {
      const headerContent = document.querySelector('.header-content');
      
      if (!headerContent) {
        return { exists: false };
      }
      
      const rect = headerContent.getBoundingClientRect();
      const styles = window.getComputedStyle(headerContent);
      
      // 모든 직계 자식 요소들 분석
      const children = Array.from(headerContent.children).map(child => {
        const childRect = child.getBoundingClientRect();
        const childStyles = window.getComputedStyle(child);
        
        return {
          tagName: child.tagName,
          id: child.id,
          className: child.className,
          position: {
            left: childRect.left,
            right: childRect.right,
            width: childRect.width
          },
          styles: {
            display: childStyles.display,
            order: childStyles.order,
            flex: childStyles.flex,
            marginLeft: childStyles.marginLeft,
            marginRight: childStyles.marginRight
          },
          isVisible: childRect.width > 0 && childRect.height > 0
        };
      });
      
      return {
        exists: true,
        rect: {
          left: rect.left,
          right: rect.right,
          width: rect.width
        },
        styles: {
          display: styles.display,
          justifyContent: styles.justifyContent,
          alignItems: styles.alignItems,
          overflow: styles.overflow
        },
        children
      };
    });
    
    console.log('📋 헤더 레이아웃 분석:');
    console.log(JSON.stringify(headerAnalysis, null, 2));
    
    // 3. CSS 미디어 쿼리 확인
    console.log('📱 3단계: 미디어 쿼리 확인...');
    
    const mediaQueryCheck = await page.evaluate(() => {
      const queries = [
        { query: '(max-width: 768px)', description: 'Mobile' },
        { query: '(max-width: 480px)', description: 'Small Mobile' },
        { query: '(min-width: 769px)', description: 'Desktop' }
      ];
      
      return queries.map(({ query, description }) => ({
        query,
        description,
        matches: window.matchMedia(query).matches
      }));
    });
    
    console.log('📱 미디어 쿼리 상태:');
    mediaQueryCheck.forEach(({ query, description, matches }) => {
      console.log(`  ${description} ${query}: ${matches ? '✅ ACTIVE' : '❌ INACTIVE'}`);
    });
    
    // 4. 오른쪽 끝 요소들 확인
    console.log('➡️ 4단계: 헤더 오른쪽 끝 요소 확인...');
    
    const rightmostElement = await page.evaluate(() => {
      const headerContent = document.querySelector('.header-content');
      if (!headerContent) return null;
      
      const allElements = Array.from(headerContent.querySelectorAll('*'));
      let rightmost = null;
      let maxRight = 0;
      
      allElements.forEach(el => {
        const rect = el.getBoundingClientRect();
        if (rect.right > maxRight) {
          maxRight = rect.right;
          rightmost = {
            tagName: el.tagName,
            id: el.id,
            className: el.className,
            right: rect.right,
            left: rect.left,
            width: rect.width,
            textContent: el.textContent?.substring(0, 50) + '...'
          };
        }
      });
      
      return {
        viewportWidth: window.innerWidth,
        rightmostElement: rightmost,
        isHamburgerRightmost: rightmost?.id === 'mobile-menu-toggle'
      };
    });
    
    console.log('➡️ 가장 오른쪽 요소:');
    console.log(JSON.stringify(rightmostElement, null, 2));
    
    // 5. 스크린샷으로 시각적 확인
    console.log('📷 5단계: 시각적 확인 스크린샷...');
    
    await page.screenshot({ 
      path: '/var/www/html/topmkt/hamburger_visibility_test.png',
      fullPage: false,
      clip: { x: 0, y: 0, width: 390, height: 100 } // 헤더 영역만
    });
    
    // 6. 가능한 문제 진단
    console.log('🔍 6단계: 문제 진단...');
    
    if (hamburgerAnalysis.exists) {
      const issues = [];
      
      if (!hamburgerAnalysis.isInViewport) {
        issues.push('❌ 햄버거 메뉴가 뷰포트 밖에 위치');
      }
      
      if (!hamburgerAnalysis.computedVisible) {
        issues.push('❌ CSS 스타일에 의해 햄버거 메뉴가 숨겨짐');
      }
      
      if (hamburgerAnalysis.position.right > hamburgerAnalysis.viewportSize.width) {
        issues.push('❌ 햄버거 메뉴가 화면 너비를 초과하여 위치');
      }
      
      if (hamburgerAnalysis.position.left < 0) {
        issues.push('❌ 햄버거 메뉴가 화면 왼쪽 밖에 위치');
      }
      
      if (issues.length === 0) {
        console.log('✅ 햄버거 메뉴가 정상적으로 보여야 합니다');
      } else {
        console.log('🚨 발견된 문제들:');
        issues.forEach(issue => console.log(`  ${issue}`));
      }
    } else {
      console.log('❌ 햄버거 메뉴 요소가 존재하지 않습니다');
    }
    
    console.log('✅ 햄버거 메뉴 가시성 분석 완료!');
    console.log('📁 생성된 스크린샷: hamburger_visibility_test.png');
    
  } catch (error) {
    console.error('❌ 오류 발생:', error);
  } finally {
    await browser.close();
  }
})();