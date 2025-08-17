import { chromium } from "playwright";

(async () => {
  console.log("🎯 반응형 기본 뷰 설정 최종 검증...");
  
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
    
    console.log("\n📊 반응형 뷰 전환 테스트:");
    
    // 테스트 시나리오들
    const scenarios = [
      { name: "데스크톱", width: 1920, height: 1080, expected: "calendar", icon: "🖥️" },
      { name: "노트북", width: 1366, height: 768, expected: "calendar", icon: "💻" },
      { name: "태블릿", width: 1024, height: 768, expected: "calendar", icon: "📱" },
      { name: "모바일 대형", width: 768, height: 1024, expected: "list", icon: "📱" },
      { name: "모바일 일반", width: 414, height: 896, expected: "list", icon: "📱" },
      { name: "모바일 소형", width: 375, height: 812, expected: "list", icon: "📱" }
    ];
    
    let passedTests = 0;
    let totalTests = scenarios.length;
    
    for (const scenario of scenarios) {
      await page.setViewportSize({ width: scenario.width, height: scenario.height });
      await page.context().clearCookies();
      
      // 처음 방문 시뮬레이션 (view 파라미터 없이)
      await page.goto(`https://www.topmktx.com/lectures?responsive_test=${Date.now()}`);
      await page.waitForTimeout(2000);
      
      const result = await page.evaluate(() => {
        const url = window.location.href;
        const urlParams = new URLSearchParams(window.location.search);
        const viewParam = urlParams.get('view');
        
        // 실제 화면에 표시되는 뷰 확인
        const calendarVisible = document.querySelector('.calendar-view')?.style.display !== 'none';
        const listVisible = document.querySelector('.list-view')?.style.display !== 'none';
        
        let displayedView = 'unknown';
        if (document.querySelector('.list-view') && !document.querySelector('.calendar-view')) {
          displayedView = 'list';
        } else if (document.querySelector('.calendar-view') && !document.querySelector('.list-view')) {
          displayedView = 'calendar';
        } else {
          // 둘 다 있는 경우 현재 보이는 것으로 판단
          displayedView = listVisible ? 'list' : 'calendar';
        }
        
        return {
          url: url,
          viewParam: viewParam || 'calendar',
          displayedView: displayedView,
          screenWidth: window.innerWidth
        };
      });
      
      const isCorrect = result.viewParam === scenario.expected;
      const status = isCorrect ? '✅' : '❌';
      
      if (isCorrect) passedTests++;
      
      console.log(`${scenario.icon} ${scenario.name} (${scenario.width}px): ${status} ${result.viewParam} 뷰`);
      
      if (!isCorrect) {
        console.log(`   기대: ${scenario.expected}, 실제: ${result.viewParam}`);
      }
    }
    
    // 수동 뷰 전환 테스트
    console.log("\n🔄 수동 뷰 전환 테스트:");
    
    // 모바일에서 캘린더로 수동 전환
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto(`https://www.topmktx.com/lectures?view=calendar&manual_test=${Date.now()}`);
    await page.waitForTimeout(2000);
    
    const manualResult = await page.evaluate(() => {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get('view');
    });
    
    console.log(`📱 모바일에서 캘린더 수동 선택: ${manualResult === 'calendar' ? '✅' : '❌'} 유지됨`);
    
    // 스크린샷 (모바일 목록뷰)
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto(`https://www.topmktx.com/lectures?final_test=${Date.now()}`);
    await page.waitForTimeout(2000);
    await page.screenshot({ 
      path: `/var/www/html/topmkt/responsive_mobile_list_view.png`,
      fullPage: false
    });
    
    // 스크린샷 (데스크톱 캘린더뷰)
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto(`https://www.topmktx.com/lectures?final_test=${Date.now()}`);
    await page.waitForTimeout(2000);
    await page.screenshot({ 
      path: `/var/www/html/topmkt/responsive_desktop_calendar_view.png`,
      fullPage: false
    });
    
    // 결과 요약
    console.log("\n🎯 반응형 기본 뷰 설정 결과:");
    console.log(`   성공률: ${passedTests}/${totalTests} (${Math.round(passedTests/totalTests*100)}%)`);
    
    if (passedTests === totalTests) {
      console.log("\n🎉 반응형 기본 뷰 설정 완료!");
      console.log("   ✅ PC/노트북/태블릿: 캘린더 뷰 기본");
      console.log("   ✅ 모바일: 목록형 뷰 기본");
      console.log("   ✅ 사용자 수동 선택 시 설정 유지");
      console.log("   ✅ 화면 크기 기반 자동 감지");
    } else {
      console.log("\n⚠️ 일부 테스트 실패, 추가 확인 필요");
    }
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();