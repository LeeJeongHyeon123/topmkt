import { chromium } from "playwright";

(async () => {
  console.log("🔍 커뮤니티 페이지 버튼 디버깅...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext();
    const page = await context.newPage();
    
    // DevLogin으로 로그인
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    // 뷰포트를 모바일로 설정
    await page.setViewportSize({ width: 375, height: 812 });
    
    // 커뮤니티 페이지 이동
    await page.goto("https://www.topmktx.com/community");
    await page.waitForTimeout(3000);
    
    // 모든 버튼과 클릭 가능한 요소들 분석
    const buttonAnalysis = await page.evaluate(() => {
      const buttons = document.querySelectorAll('.btn, button, a[role="button"], input[type="button"], input[type="submit"]');
      const analysis = [];
      
      buttons.forEach((btn, index) => {
        const rect = btn.getBoundingClientRect();
        const computedStyle = window.getComputedStyle(btn);
        
        if (rect.width > 0 && rect.height > 0) {
          analysis.push({
            index: index,
            tagName: btn.tagName,
            className: btn.className,
            text: btn.textContent.trim().substring(0, 30),
            width: Math.round(rect.width),
            height: Math.round(rect.height),
            isSmall: rect.height < 44,
            fontSize: computedStyle.fontSize,
            padding: computedStyle.padding,
            display: computedStyle.display
          });
        }
      });
      
      return analysis;
    });
    
    console.log("\\n📊 버튼 분석 결과:");
    buttonAnalysis.forEach(btn => {
      const status = btn.isSmall ? '❌' : '✅';
      console.log(`${status} [${btn.index}] ${btn.tagName}.${btn.className}`);
      console.log(`    크기: ${btn.width}x${btn.height}px`);
      console.log(`    텍스트: "${btn.text}"`);
      console.log(`    스타일: fontSize=${btn.fontSize}, padding=${btn.padding}`);
      console.log("");
    });
    
    // 작은 버튼들만 필터링
    const smallButtons = buttonAnalysis.filter(btn => btn.isSmall);
    console.log(`\\n🔴 44px 미만인 버튼: ${smallButtons.length}개`);
    smallButtons.forEach(btn => {
      console.log(`- [${btn.index}] ${btn.text} (${btn.width}x${btn.height}px)`);
    });
    
  } catch (error) {
    console.error("❌ 디버깅 오류:", error);
  } finally {
    await browser.close();
  }
})();