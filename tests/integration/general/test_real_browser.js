import { chromium } from "playwright";

(async () => {
  console.log("🔍 실제 브라우저에서 햄버거 아이콘 확인...");
  
  const browser = await chromium.launch({ 
    headless: false,  // 헤드리스 끄고 실제 브라우저로
    args: ["--no-sandbox", "--disable-setuid-sandbox"],
    slowMo: 1000
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 390, height: 844 },
      userAgent: "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1"
    });
    
    const page = await context.newPage();
    
    // DevLogin Helper로 사용자 ID 4 로그인
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(3000);
    
    // 메인 페이지로 이동
    await page.goto("https://www.topmktx.com/");
    await page.waitForTimeout(5000);
    
    console.log("📱 페이지 로드 완료. 5초 후 확인...");
    await page.waitForTimeout(5000);
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    // 브라우저를 닫지 않고 유지
    console.log("브라우저를 수동으로 확인하세요.");
  }
})();
