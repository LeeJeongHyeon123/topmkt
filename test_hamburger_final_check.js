import { chromium } from "playwright";

(async () => {
  console.log("🔍 햄버거 아이콘 최종 가시성 테스트...");
  
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
    
    // 햄버거 메뉴와 라인 요소들 상세 분석
    const hamburgerAnalysis = await page.evaluate(() => {
      const hamburger = document.getElementById("mobile-menu-toggle");
      const lines = hamburger ? hamburger.querySelectorAll(".hamburger-line") : [];
      
      return {
        hamburger: hamburger ? {
          exists: true,
          rect: hamburger.getBoundingClientRect(),
          styles: {
            display: window.getComputedStyle(hamburger).display,
            visibility: window.getComputedStyle(hamburger).visibility,
            opacity: window.getComputedStyle(hamburger).opacity,
            zIndex: window.getComputedStyle(hamburger).zIndex
          },
          html: hamburger.outerHTML
        } : { exists: false },
        lines: Array.from(lines).map((line, index) => ({
          index,
          rect: line.getBoundingClientRect(),
          styles: {
            display: window.getComputedStyle(line).display,
            background: window.getComputedStyle(line).background,
            backgroundColor: window.getComputedStyle(line).backgroundColor,
            width: window.getComputedStyle(line).width,
            height: window.getComputedStyle(line).height,
            opacity: window.getComputedStyle(line).opacity
          }
        }))
      };
    });
    
    console.log("🍔 햄버거 메뉴 상세 분석:");
    console.log(JSON.stringify(hamburgerAnalysis, null, 2));
    
    // 여러 각도에서 스크린샷
    await page.screenshot({ 
      path: "/var/www/html/topmkt/hamburger_final_check.png",
      fullPage: false,
      clip: { x: 0, y: 0, width: 390, height: 80 }
    });
    
    // 햄버거 영역만 확대
    if (hamburgerAnalysis.hamburger.exists) {
      const rect = hamburgerAnalysis.hamburger.rect;
      await page.screenshot({ 
        path: "/var/www/html/topmkt/hamburger_closeup.png",
        fullPage: false,
        clip: { 
          x: Math.max(0, rect.left - 10), 
          y: Math.max(0, rect.top - 10), 
          width: Math.min(390, rect.width + 20), 
          height: Math.min(80, rect.height + 20) 
        }
      });
    }
    
    console.log("✅ 햄버거 아이콘 분석 완료!");
    console.log("📁 생성된 파일:");
    console.log("  - hamburger_final_check.png (헤더 전체)");
    console.log("  - hamburger_closeup.png (햄버거 영역 확대)");
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();
