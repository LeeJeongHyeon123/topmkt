import { chromium } from "playwright";

(async () => {
  console.log("🔍 헤더 레이아웃 겹침 문제 정밀 분석...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 390, height: 844 },
      userAgent: "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1"
    });
    
    const page = await context.newPage();
    
    // DevLogin Helper로 사용자 ID 4 로그인
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    // 메인 페이지로 이동
    await page.goto("https://www.topmktx.com/");
    await page.waitForTimeout(3000);
    
    console.log("📐 헤더 요소들 위치 분석...");
    
    // 헤더 요소들의 정확한 위치와 크기 확인
    const layoutAnalysis = await page.evaluate(() => {
      const headerContent = document.querySelector(".header-content");
      const headerLeft = document.querySelector(".header-left");
      const navAuth = document.querySelector(".nav-auth");
      const hamburger = document.getElementById("mobile-menu-toggle");
      
      return {
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight
        },
        headerContent: headerContent ? {
          rect: headerContent.getBoundingClientRect(),
          overflow: window.getComputedStyle(headerContent).overflow,
          justifyContent: window.getComputedStyle(headerContent).justifyContent
        } : null,
        headerLeft: headerLeft ? {
          rect: headerLeft.getBoundingClientRect(),
          zIndex: window.getComputedStyle(headerLeft).zIndex,
          flex: window.getComputedStyle(headerLeft).flex
        } : null,
        navAuth: navAuth ? {
          rect: navAuth.getBoundingClientRect(),
          zIndex: window.getComputedStyle(navAuth).zIndex,
          flex: window.getComputedStyle(navAuth).flex
        } : null,
        hamburger: hamburger ? {
          rect: hamburger.getBoundingClientRect(),
          zIndex: window.getComputedStyle(hamburger).zIndex,
          display: window.getComputedStyle(hamburger).display,
          position: window.getComputedStyle(hamburger).position
        } : null
      };
    });
    
    console.log("📊 헤더 레이아웃 분석 결과:");
    console.log("  뷰포트:", layoutAnalysis.viewport);
    if (layoutAnalysis.headerContent) {
      console.log("  헤더 컨테이너:", {
        width: layoutAnalysis.headerContent.rect.width,
        overflow: layoutAnalysis.headerContent.overflow,
        justifyContent: layoutAnalysis.headerContent.justifyContent
      });
    }
    if (layoutAnalysis.headerLeft) {
      console.log("  로고 영역:", {
        left: layoutAnalysis.headerLeft.rect.left,
        right: layoutAnalysis.headerLeft.rect.right,
        width: layoutAnalysis.headerLeft.rect.width,
        flex: layoutAnalysis.headerLeft.flex
      });
    }
    if (layoutAnalysis.hamburger) {
      console.log("  햄버거 메뉴:", {
        left: layoutAnalysis.hamburger.rect.left,
        right: layoutAnalysis.hamburger.rect.right,
        width: layoutAnalysis.hamburger.rect.width,
        display: layoutAnalysis.hamburger.display
      });
    }
    
    // 겹침 확인
    const isOverlapping = layoutAnalysis.headerLeft && layoutAnalysis.hamburger && 
      layoutAnalysis.headerLeft.rect.right > layoutAnalysis.hamburger.rect.left;
    
    console.log("🚨 겹침 상태:", isOverlapping ? "YES - 로고가 햄버거를 덮고 있음!" : "NO");
    
    // 스크린샷으로 확인
    await page.screenshot({ 
      path: "/var/www/html/topmkt/header_layout_debug.png",
      fullPage: false,
      clip: { x: 0, y: 0, width: 390, height: 80 }
    });
    
    console.log("✅ 헤더 레이아웃 분석 완료! header_layout_debug.png 생성됨");
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();
