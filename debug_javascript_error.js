import { chromium } from "playwright";

(async () => {
  console.log("🐛 JavaScript 구문 오류 디버깅...");
  
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
    
    // 콘솔 로그 캡처
    const consoleMessages = [];
    page.on('console', msg => {
      consoleMessages.push({
        type: msg.type(),
        text: msg.text(),
        location: msg.location()
      });
    });
    
    // JavaScript 오류 캡처
    const jsErrors = [];
    page.on('pageerror', error => {
      jsErrors.push({
        message: error.message,
        stack: error.stack
      });
    });
    
    // DevLogin Helper로 사용자 ID 4 로그인
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    // 메인 페이지로 이동
    await page.goto("https://www.topmktx.com/");
    await page.waitForTimeout(5000);
    
    // 햄버거 메뉴 요소 확인
    const hamburgerCheck = await page.evaluate(() => {
      const hamburger = document.getElementById("mobile-hamburger");
      return {
        exists: !!hamburger,
        visible: hamburger ? window.getComputedStyle(hamburger).display !== 'none' : false,
        rect: hamburger ? hamburger.getBoundingClientRect() : null,
        styles: hamburger ? {
          display: window.getComputedStyle(hamburger).display,
          visibility: window.getComputedStyle(hamburger).visibility,
          opacity: window.getComputedStyle(hamburger).opacity,
          zIndex: window.getComputedStyle(hamburger).zIndex
        } : null
      };
    });
    
    console.log("🍔 햄버거 메뉴 상태:");
    console.log(JSON.stringify(hamburgerCheck, null, 2));
    
    console.log("\n📢 콘솔 메시지들:");
    consoleMessages.forEach((msg, i) => {
      console.log(`${i+1}. [${msg.type}] ${msg.text}`);
      if (msg.location) {
        console.log(`   위치: 라인 ${msg.location.lineNumber}:${msg.location.columnNumber}`);
      }
    });
    
    console.log("\n❌ JavaScript 오류들:");
    jsErrors.forEach((error, i) => {
      console.log(`${i+1}. ${error.message}`);
      if (error.stack) {
        console.log(`   스택: ${error.stack.substring(0, 200)}...`);
      }
    });
    
    // 스크린샷 촬영
    await page.screenshot({ 
      path: "/var/www/html/topmkt/debug_javascript_mobile.png",
      fullPage: false,
      clip: { x: 0, y: 0, width: 390, height: 150 }
    });
    
    console.log("✅ JavaScript 디버깅 완료!");
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();