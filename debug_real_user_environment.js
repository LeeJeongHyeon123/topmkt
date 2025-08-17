import { chromium } from "playwright";

(async () => {
  console.log("🔥 ULTRA THINK: 실제 사용자 환경 완전 진단...");
  
  const browser = await chromium.launch({ 
    headless: true,  // 헤드리스 모드로 실행
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    // 실제 모바일과 동일한 환경 설정
    const context = await browser.newContext({
      viewport: { width: 375, height: 812 }, // iPhone X 크기
      userAgent: "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
      deviceScaleFactor: 3
    });
    
    const page = await context.newPage();
    
    // 캐시 완전 비활성화
    await page.route('**/*', route => {
      const headers = route.request().headers();
      headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
      headers['Pragma'] = 'no-cache';
      headers['Expires'] = '0';
      route.continue({ headers });
    });
    
    // DevLogin Helper로 사용자 ID 4 로그인
    console.log("🔑 DevLogin Helper로 로그인...");
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(3000);
    
    // 메인 페이지로 이동 (캐시 우회)
    console.log("🏠 메인 페이지로 이동 (캐시 우회)...");
    await page.goto("https://www.topmktx.com/?cache_bust=" + Date.now());
    await page.waitForTimeout(5000);
    
    // 페이지 완전 로딩 대기
    await page.waitForLoadState('networkidle');
    
    console.log("📱 현재 뷰포트:", await page.viewportSize());
    
    // 전체 헤더 영역 분석
    const headerAnalysis = await page.evaluate(() => {
      const header = document.querySelector('.main-header');
      const headerContent = document.querySelector('.header-content');
      const headerLeft = document.querySelector('.header-left');
      const navAuth = document.querySelector('.nav-auth');
      const hamburger = document.getElementById('mobile-hamburger');
      
      const getElementInfo = (element, name) => {
        if (!element) return { name, exists: false };
        
        const rect = element.getBoundingClientRect();
        const styles = window.getComputedStyle(element);
        
        return {
          name,
          exists: true,
          rect: {
            x: rect.x,
            y: rect.y,
            width: rect.width,
            height: rect.height,
            visible: rect.width > 0 && rect.height > 0
          },
          styles: {
            display: styles.display,
            visibility: styles.visibility,
            opacity: styles.opacity,
            position: styles.position,
            zIndex: styles.zIndex,
            overflow: styles.overflow
          },
          innerHTML: element.innerHTML.length > 500 ? element.innerHTML.substring(0, 500) + '...' : element.innerHTML
        };
      };
      
      return {
        screenWidth: window.innerWidth,
        screenHeight: window.innerHeight,
        devicePixelRatio: window.devicePixelRatio,
        header: getElementInfo(header, 'header'),
        headerContent: getElementInfo(headerContent, 'headerContent'),
        headerLeft: getElementInfo(headerLeft, 'headerLeft'),
        navAuth: getElementInfo(navAuth, 'navAuth'),
        hamburger: getElementInfo(hamburger, 'hamburger')
      };
    });
    
    console.log("🔍 완전한 헤더 분석:");
    console.log(JSON.stringify(headerAnalysis, null, 2));
    
    // 모든 mobile-hamburger 관련 요소 찾기
    const allHamburgerElements = await page.evaluate(() => {
      const elements = document.querySelectorAll('*[id*="hamburger"], *[class*="hamburger"], *[class*="mobile"]');
      return Array.from(elements).map(el => ({
        tagName: el.tagName,
        id: el.id,
        className: el.className,
        rect: el.getBoundingClientRect(),
        display: window.getComputedStyle(el).display,
        visibility: window.getComputedStyle(el).visibility,
        innerHTML: el.innerHTML.length > 100 ? el.innerHTML.substring(0, 100) + '...' : el.innerHTML
      }));
    });
    
    console.log("🍔 모든 햄버거 관련 요소들:");
    allHamburgerElements.forEach((el, i) => {
      console.log(`${i+1}. ${el.tagName}#${el.id}.${el.className}`);
      console.log(`   크기: ${el.rect.width}x${el.rect.height} 위치: (${el.rect.x}, ${el.rect.y})`);
      console.log(`   표시: ${el.display}, 가시성: ${el.visibility}`);
    });
    
    // CSS 미디어 쿼리 확인
    const mediaQueryCheck = await page.evaluate(() => {
      return {
        matchesMaxWidth768: window.matchMedia('(max-width: 768px)').matches,
        matchesMinWidth769: window.matchMedia('(min-width: 769px)').matches,
        currentWidth: window.innerWidth
      };
    });
    
    console.log("📏 미디어 쿼리 확인:", mediaQueryCheck);
    
    // 헤더 영역 스크린샷
    await page.screenshot({ 
      path: "/var/www/html/topmkt/ultra_debug_header.png",
      fullPage: false,
      clip: { x: 0, y: 0, width: 375, height: 100 }
    });
    
    console.log("📸 헤더 스크린샷 촬영 완료");
    
    // 5초간 수동 확인 대기
    console.log("👁️ 5초간 실제 화면 확인...");
    await page.waitForTimeout(5000);
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    // 브라우저를 닫지 않고 유지하여 수동 확인 가능
    console.log("🔍 브라우저를 열어둔 상태입니다. 수동으로 확인하세요.");
  }
})();