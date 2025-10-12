import { chromium } from "playwright";

(async () => {
  console.log("🔍 반응형 경계값 테스트 (768px 기준)...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext();
    const page = await context.newPage();
    
    // DevLogin
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    console.log("\n📏 768px 경계값 테스트:");
    
    const boundaryTests = [
      { width: 767, expected: "list", description: "767px (모바일)" },
      { width: 768, expected: "calendar", description: "768px (태블릿)" },
      { width: 769, expected: "calendar", description: "769px (태블릿+)" }
    ];
    
    for (const test of boundaryTests) {
      await page.setViewportSize({ width: test.width, height: 1024 });
      await page.context().clearCookies();
      
      // 처음 방문 시뮬레이션
      await page.goto(`https://www.topmktx.com/lectures?boundary_test=${Date.now()}`);
      await page.waitForTimeout(2000);
      
      const result = await page.evaluate(() => {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('view') || 'calendar';
      });
      
      const isCorrect = result === test.expected;
      const status = isCorrect ? '✅' : '❌';
      
      console.log(`   ${status} ${test.description}: ${result} 뷰 (기대: ${test.expected})`);
    }
    
    console.log("\n📱 최종 반응형 기준:");
    console.log("   🖥️ 768px 이상: 캘린더 뷰 (PC, 노트북, 태블릿)");
    console.log("   📱 767px 이하: 목록형 뷰 (모바일)");
    
    // 실제 디바이스 크기 테스트
    console.log("\n📱 실제 디바이스 크기 테스트:");
    
    const deviceTests = [
      { name: "iPhone SE", width: 375, expected: "list" },
      { name: "iPhone 12", width: 390, expected: "list" },
      { name: "iPhone 12 Pro Max", width: 428, expected: "list" },
      { name: "Samsung Galaxy S20", width: 360, expected: "list" },
      { name: "iPad Mini", width: 768, expected: "calendar" },
      { name: "iPad", width: 820, expected: "calendar" },
      { name: "iPad Pro", width: 1024, expected: "calendar" }
    ];
    
    let allPassed = true;
    
    for (const device of deviceTests) {
      await page.setViewportSize({ width: device.width, height: 800 });
      await page.context().clearCookies();
      
      await page.goto(`https://www.topmktx.com/lectures?device_test=${Date.now()}`);
      await page.waitForTimeout(2000);
      
      const result = await page.evaluate(() => {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('view') || 'calendar';
      });
      
      const isCorrect = result === device.expected;
      if (!isCorrect) allPassed = false;
      
      const status = isCorrect ? '✅' : '❌';
      const icon = device.expected === 'list' ? '📱' : '📊';
      
      console.log(`   ${icon} ${device.name} (${device.width}px): ${status} ${result} 뷰`);
    }
    
    if (allPassed) {
      console.log("\n🎉 반응형 기본 뷰 설정 완벽 구현!");
      console.log("   ✅ 모든 디바이스에서 적절한 기본 뷰 제공");
      console.log("   ✅ 사용자 경험 최적화");
      console.log("   ✅ 모바일에서는 카드형 목록이 더 편리");
      console.log("   ✅ PC/태블릿에서는 캘린더가 더 직관적");
    } else {
      console.log("\n⚠️ 일부 디바이스에서 조정 필요");
    }
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();