import { chromium } from "playwright";

(async () => {
  console.log("🎯 최종 햄버거 메뉴 완전 테스트...");
  
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
    
    // JavaScript 오류 캡처
    const jsErrors = [];
    page.on('pageerror', error => {
      jsErrors.push(error.message);
    });
    
    // DevLogin Helper로 사용자 ID 4 로그인
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    // 메인 페이지로 이동
    await page.goto("https://www.topmktx.com/");
    await page.waitForTimeout(3000);
    
    // 햄버거 메뉴 완전 분석
    const hamburgerAnalysis = await page.evaluate(() => {
      const hamburger = document.getElementById("mobile-hamburger");
      const headerLeft = document.querySelector(".header-left");
      
      return {
        hamburger: hamburger ? {
          exists: true,
          visible: window.getComputedStyle(hamburger).display !== 'none',
          rect: hamburger.getBoundingClientRect(),
          styles: {
            display: window.getComputedStyle(hamburger).display,
            visibility: window.getComputedStyle(hamburger).visibility,
            opacity: window.getComputedStyle(hamburger).opacity,
            background: window.getComputedStyle(hamburger).background,
            border: window.getComputedStyle(hamburger).border,
            zIndex: window.getComputedStyle(hamburger).zIndex
          },
          clickable: hamburger.offsetWidth > 0 && hamburger.offsetHeight > 0
        } : { exists: false },
        headerLayout: headerLeft ? {
          rect: headerLeft.getBoundingClientRect(),
          flexDirection: window.getComputedStyle(headerLeft).flexDirection,
          justifyContent: window.getComputedStyle(headerLeft).justifyContent,
          alignItems: window.getComputedStyle(headerLeft).alignItems
        } : null
      };
    });
    
    console.log("🍔 최종 햄버거 메뉴 분석:");
    console.log(JSON.stringify(hamburgerAnalysis, null, 2));
    
    console.log(`\n✅ JavaScript 오류: ${jsErrors.length}개`);
    if (jsErrors.length > 0) {
      jsErrors.forEach((error, i) => {
        console.log(`  ${i+1}. ${error}`);
      });
    }
    
    // 스크린샷 촬영 (햄버거 메뉴 영역)
    await page.screenshot({ 
      path: "/var/www/html/topmkt/final_hamburger_mobile.png",
      fullPage: false,
      clip: { x: 300, y: 0, width: 90, height: 70 }
    });
    
    // 햄버거 메뉴 클릭 테스트
    if (hamburgerAnalysis.hamburger.exists && hamburgerAnalysis.hamburger.clickable) {
      console.log("🖱️ 햄버거 메뉴 클릭 테스트...");
      
      await page.click("#mobile-hamburger");
      await page.waitForTimeout(1000);
      
      // 모달이 열렸는지 확인
      const modalStatus = await page.evaluate(() => {
        const modal = document.getElementById("mobileMenuModal");
        return modal ? {
          exists: true,
          active: modal.classList.contains('active'),
          display: window.getComputedStyle(modal).display,
          visibility: window.getComputedStyle(modal).visibility
        } : { exists: false };
      });
      
      console.log("📱 모달 상태:", JSON.stringify(modalStatus, null, 2));
      
      // 전체 페이지 스크린샷 (모달 열린 상태)
      await page.screenshot({ 
        path: "/var/www/html/topmkt/final_hamburger_modal_opened.png",
        fullPage: false
      });
      
      console.log("✅ 햄버거 메뉴 클릭 테스트 완료!");
    } else {
      console.log("❌ 햄버거 메뉴를 클릭할 수 없습니다.");
    }
    
    console.log("🎉 최종 테스트 완료!");
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();