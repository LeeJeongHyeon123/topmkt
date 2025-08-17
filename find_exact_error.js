import { chromium } from "playwright";

(async () => {
  console.log("🔍 정확한 1727라인 JavaScript 오류 찾기...");
  
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
    
    // 1727라인 정확한 코드 추출
    const errorLine = await page.evaluate(() => {
      const htmlContent = document.documentElement.innerHTML;
      const lines = htmlContent.split('\n');
      
      // 1727라인 근처 코드 확인
      const result = {};
      for (let i = Math.max(0, 1720); i < Math.min(lines.length, 1735); i++) {
        result[i+1] = lines[i];
      }
      
      return result;
    });
    
    console.log("📍 1727라인 근처 정확한 HTML/JS 코드:");
    Object.keys(errorLine).forEach(lineNum => {
      const line = errorLine[lineNum];
      if (line && line.trim()) {
        console.log(`${lineNum}: ${line}`);
      }
    });
    
    // 모든 JavaScript 문법 오류 감지
    const jsValidationResults = await page.evaluate(() => {
      const results = [];
      const scripts = Array.from(document.scripts);
      
      scripts.forEach((script, index) => {
        if (script.innerHTML && script.innerHTML.trim()) {
          try {
            // JavaScript 구문 검증
            new Function(script.innerHTML);
          } catch (e) {
            results.push({
              scriptIndex: index,
              error: e.message,
              content: script.innerHTML.substring(0, 200) + '...'
            });
          }
        }
      });
      
      return results;
    });
    
    console.log("\n❌ JavaScript 구문 오류 결과:");
    jsValidationResults.forEach(result => {
      console.log(`스크립트 ${result.scriptIndex}: ${result.error}`);
      console.log(`내용: ${result.content}`);
      console.log('---');
    });
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();