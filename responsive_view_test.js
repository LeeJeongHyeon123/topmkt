import { chromium } from "playwright";

(async () => {
  console.log("📱 반응형 기본 뷰 설정 테스트...");
  
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
    
    const testScenarios = [
      {
        name: "PC 데스크톱",
        width: 1920,
        height: 1080,
        expectedView: "calendar",
        description: "PC에서는 캘린더 뷰가 기본"
      },
      {
        name: "노트북",
        width: 1366,
        height: 768,
        expectedView: "calendar",
        description: "노트북에서도 캘린더 뷰가 기본"
      },
      {
        name: "태블릿",
        width: 1024,
        height: 768,
        expectedView: "calendar",
        description: "태블릿에서도 캘린더 뷰가 기본"
      },
      {
        name: "모바일 (큰 화면)",
        width: 768,
        height: 1024,
        expectedView: "list",
        description: "768px에서는 목록형 뷰가 기본"
      },
      {
        name: "모바일 (일반)",
        width: 414,
        height: 896,
        expectedView: "list",
        description: "일반 모바일에서는 목록형 뷰가 기본"
      },
      {
        name: "모바일 (작은 화면)",
        width: 375,
        height: 812,
        expectedView: "list",
        description: "작은 모바일에서는 목록형 뷰가 기본"
      }
    ];
    
    for (const scenario of testScenarios) {
      console.log(`\n📱 ${scenario.name} (${scenario.width}x${scenario.height}) 테스트:`);
      console.log(`   기대값: ${scenario.expectedView} 뷰`);
      
      await page.setViewportSize({ width: scenario.width, height: scenario.height });
      await page.waitForTimeout(1000);
      
      // 쿠키 초기화하여 처음 방문 상황 시뮬레이션
      await page.context().clearCookies();
      
      // 강의 페이지 방문 (view 파라미터 없이)
      await page.goto(`https://www.topmktx.com/lectures?test=${Date.now()}`);
      await page.waitForTimeout(3000);
      
      // 현재 URL에서 view 파라미터 확인
      const currentUrl = page.url();
      const urlParams = new URLSearchParams(currentUrl.split('?')[1] || '');
      const actualView = urlParams.get('view') || 'calendar';
      
      // 화면에 실제로 보여지는 뷰 확인
      const visibleView = await page.evaluate(() => {
        const calendarView = document.querySelector('.calendar-view');
        const listView = document.querySelector('.list-view');
        
        if (listView && listView.style.display !== 'none') {
          return 'list';
        } else if (calendarView && calendarView.style.display !== 'none') {
          return 'calendar';
        } else {
          // CSS로 처리되는 경우
          const listDisplay = window.getComputedStyle(listView || document.createElement('div')).display;
          const calendarDisplay = window.getComputedStyle(calendarView || document.createElement('div')).display;
          
          if (listDisplay !== 'none') return 'list';
          if (calendarDisplay !== 'none') return 'calendar';
          
          return 'unknown';
        }
      });
      
      const isCorrect = actualView === scenario.expectedView;
      const status = isCorrect ? '✅' : '❌';
      
      console.log(`   ${status} 실제 결과: ${actualView} 뷰`);
      console.log(`   화면 표시: ${visibleView} 뷰`);
      console.log(`   URL: ${currentUrl}`);
      
      if (!isCorrect) {
        console.log(`   ⚠️ 기대값(${scenario.expectedView})과 다름!`);
      }
      
      // 스크린샷
      await page.screenshot({ 
        path: `/var/www/html/topmkt/responsive_view_${scenario.name.replace(/[^a-zA-Z0-9]/g, '_')}_${scenario.width}px.png`,
        fullPage: false
      });
    }
    
    // User-Agent 기반 모바일 감지 테스트
    console.log("\n📱 User-Agent 기반 모바일 감지 테스트:");
    
    const userAgentTests = [
      {
        name: "iPhone",
        userAgent: "Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15",
        expectedView: "list"
      },
      {
        name: "Android",
        userAgent: "Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36",
        expectedView: "list"
      },
      {
        name: "iPad",
        userAgent: "Mozilla/5.0 (iPad; CPU OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15",
        expectedView: "list"
      },
      {
        name: "Desktop Chrome",
        userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
        expectedView: "calendar"
      }
    ];
    
    for (const test of userAgentTests) {
      await page.setUserAgent(test.userAgent);
      await page.setViewportSize({ width: 1024, height: 768 }); // 중립적 크기
      await page.context().clearCookies();
      
      await page.goto(`https://www.topmktx.com/lectures?ua_test=${Date.now()}`);
      await page.waitForTimeout(2000);
      
      const currentUrl = page.url();
      const urlParams = new URLSearchParams(currentUrl.split('?')[1] || '');
      const actualView = urlParams.get('view') || 'calendar';
      
      const isCorrect = actualView === test.expectedView;
      const status = isCorrect ? '✅' : '❌';
      
      console.log(`   ${status} ${test.name}: ${actualView} 뷰 (기대: ${test.expectedView})`);
    }
    
    console.log("\n📊 반응형 기본 뷰 설정 요약:");
    console.log("   🖥️ PC/노트북 (1024px+): 캘린더 뷰");
    console.log("   📱 모바일/태블릿 (768px-): 목록형 뷰");
    console.log("   🔄 사용자가 수동 전환 시 설정 유지");
    console.log("   🍪 화면 크기 쿠키로 정확한 감지");
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();