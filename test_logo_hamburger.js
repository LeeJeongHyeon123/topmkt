import { chromium } from "playwright";

(async () => {
  console.log("🔍 로고 영역 햄버거 메뉴 테스트...");
  
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
    
    // 새로운 햄버거 메뉴 분석
    const hamburgerAnalysis = await page.evaluate(() => {
      const mobileHamburger = document.getElementById("mobile-hamburger");
      const headerLeft = document.querySelector(".header-left");
      
      return {
        mobileHamburger: mobileHamburger ? {
          exists: true,
          rect: mobileHamburger.getBoundingClientRect(),
          styles: {
            display: window.getComputedStyle(mobileHamburger).display,
            visibility: window.getComputedStyle(mobileHamburger).visibility,
            opacity: window.getComputedStyle(mobileHamburger).opacity
          },
          html: mobileHamburger.outerHTML
        } : { exists: false },
        headerLeft: headerLeft ? {
          rect: headerLeft.getBoundingClientRect(),
          children: Array.from(headerLeft.children).map(child => ({
            tagName: child.tagName,
            className: child.className,
            id: child.id,
            rect: child.getBoundingClientRect()
          }))
        } : null
      };
    });
    
    console.log("🍔 로고 영역 햄버거 메뉴 분석:");
    console.log(JSON.stringify(hamburgerAnalysis, null, 2));
    
    // 스크린샷 촬영
    await page.screenshot({ 
      path: "/var/www/html/topmkt/hamburger_logo_area.png",
      fullPage: false,
      clip: { x: 0, y: 0, width: 390, height: 80 }
    });
    
    // 햄버거 메뉴 클릭 테스트
    if (hamburgerAnalysis.mobileHamburger.exists) {
      console.log("🖱️ 햄버거 메뉴 클릭 테스트...");
      await page.click("#mobile-hamburger");
      await page.waitForTimeout(1000);
      
      // 메뉴 열린 상태 스크린샷
      await page.screenshot({ 
        path: "/var/www/html/topmkt/hamburger_menu_opened_new.png",
        fullPage: false
      });
    }
    
    console.log("✅ 로고 영역 햄버거 메뉴 테스트 완료!");
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();
