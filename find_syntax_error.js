import { chromium } from "playwright";

(async () => {
  console.log("🔍 1727 라인 JavaScript 구문 오류 정확한 위치 찾기...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 390, height: 844 }
    });
    
    const page = await context.newPage();
    
    // DevLogin Helper로 사용자 ID 4 로그인
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    // 메인 페이지로 이동
    await page.goto("https://www.topmktx.com/");
    await page.waitForTimeout(3000);
    
    // 페이지 소스에서 1727라인 근처 추출
    const pageSource = await page.content();
    const lines = pageSource.split('\n');
    
    console.log("🔍 1727라인 근처 코드:");
    for (let i = Math.max(0, 1722); i < Math.min(lines.length, 1732); i++) {
      console.log(`${i+1}: ${lines[i]}`);
    }
    
    // JavaScript 오류를 발생시키는 정확한 코드 찾기
    const errorContext = await page.evaluate(() => {
      const scripts = Array.from(document.scripts);
      return scripts.map((script, index) => ({
        index,
        src: script.src || 'inline',
        content: script.innerHTML.substring(0, 500) + '...'
      }));
    });
    
    console.log("\n📜 페이지의 스크립트들:");
    errorContext.forEach(script => {
      console.log(`스크립트 ${script.index}: ${script.src}`);
      if (script.src === 'inline') {
        console.log(`내용: ${script.content}`);
      }
    });
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();