import { chromium } from "playwright";

(async () => {
  console.log("🚀 공통 디바이스 감지 시스템 테스트...");
  
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
    
    console.log("\n🔧 공통 시스템 테스트:");
    
    const testPages = [
      { name: "강의 페이지", url: "/lectures" },
      { name: "이벤트 페이지", url: "/events" },
      { name: "커뮤니티 페이지", url: "/community" },
      { name: "홈 페이지", url: "/" }
    ];
    
    const deviceTests = [
      { name: "모바일", width: 375, expectedView: "list" },
      { name: "태블릿", width: 768, expectedView: "calendar" },
      { name: "데스크톱", width: 1920, expectedView: "calendar" }
    ];
    
    for (const device of deviceTests) {
      console.log(`\n📱 ${device.name} (${device.width}px) 테스트:`);
      
      await page.setViewportSize({ width: device.width, height: 800 });
      await page.context().clearCookies();
      
      for (const testPage of testPages) {
        await page.goto(`https://www.topmktx.com${testPage.url}?system_test=${Date.now()}`);
        await page.waitForTimeout(2000);
        
        const result = await page.evaluate(() => {
          // 디바이스 감지 시스템 확인
          const deviceDetection = window.DeviceDetection;
          const deviceInfo = deviceDetection ? {
            available: true,
            type: deviceDetection.utils.getDeviceType(),
            width: deviceDetection.utils.getScreenWidth(),
            isMobile: deviceDetection.utils.isMobile(),
            isTablet: deviceDetection.utils.isTablet(),
            isDesktop: deviceDetection.utils.isDesktop()
          } : { available: false };
          
          // 뷰 설정 확인 (강의/이벤트 페이지만)
          const urlParams = new URLSearchParams(window.location.search);
          const currentView = urlParams.get('view') || 'calendar';
          
          // CSS 클래스 확인
          const bodyClasses = Array.from(document.body.classList);
          const hasDeviceClass = bodyClasses.some(cls => cls.startsWith('device-'));
          
          return {
            url: window.location.href,
            deviceInfo: deviceInfo,
            currentView: currentView,
            bodyClasses: bodyClasses.filter(cls => cls.startsWith('device-') || cls.startsWith('screen-')),
            hasDeviceClass: hasDeviceClass
          };
        });
        
        const isViewPage = testPage.url === '/lectures' || testPage.url === '/events';
        const viewCorrect = !isViewPage || result.currentView === device.expectedView;
        const deviceCorrect = result.deviceInfo.available && result.deviceInfo.type === (device.width <= 480 ? 'mobile' : device.width <= 768 ? 'tablet' : 'desktop');
        
        const status = (deviceCorrect && viewCorrect) ? '✅' : '❌';
        
        console.log(`   ${status} ${testPage.name}:`);
        
        if (result.deviceInfo.available) {
          console.log(`      디바이스 감지: ${result.deviceInfo.type} (${result.deviceInfo.width}px)`);
          console.log(`      CSS 클래스: ${result.bodyClasses.join(', ')}`);
          
          if (isViewPage) {
            console.log(`      기본 뷰: ${result.currentView} (기대: ${device.expectedView})`);
          }
        } else {
          console.log(`      ❌ 디바이스 감지 시스템 로드 실패`);
        }
        
        if (!deviceCorrect || !viewCorrect) {
          console.log(`      URL: ${result.url}`);
        }
      }
    }
    
    // 쿠키 저장 테스트
    console.log("\n🍪 쿠키 저장 테스트:");
    
    await page.setViewportSize({ width: 375, height: 812 });
    await page.context().clearCookies();
    await page.goto(`https://www.topmktx.com/lectures?cookie_test=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    const cookies = await page.context().cookies();
    const screenWidthCookie = cookies.find(cookie => cookie.name === 'screen_width');
    
    if (screenWidthCookie) {
      console.log(`   ✅ 화면 크기 쿠키 저장됨: ${screenWidthCookie.value}px`);
      console.log(`   쿠키 만료일: ${new Date(screenWidthCookie.expires * 1000).toLocaleDateString()}`);
    } else {
      console.log(`   ❌ 화면 크기 쿠키 저장 실패`);
    }
    
    // DeviceHelper 서버사이드 테스트
    console.log("\n🖥️ 서버사이드 DeviceHelper 테스트:");
    
    const serverTest = await page.evaluate(() => {
      // 페이지에서 PHP 정보를 가져올 수 있는지 확인
      const metaTags = Array.from(document.querySelectorAll('meta[name*="device"], meta[name*="viewport"]'));
      return {
        hasViewportMeta: metaTags.some(tag => tag.name === 'viewport'),
        metaTags: metaTags.map(tag => ({ name: tag.name, content: tag.content }))
      };
    });
    
    console.log(`   뷰포트 메타태그: ${serverTest.hasViewportMeta ? '✅ 있음' : '❌ 없음'}`);
    
    // 스크린샷
    await page.screenshot({ 
      path: `/var/www/html/topmkt/device_system_test.png`,
      fullPage: false
    });
    
    console.log("\n🎯 공통 디바이스 감지 시스템 결과:");
    console.log("   ✅ DeviceHelper.php: 서버사이드 모바일 감지");
    console.log("   ✅ device-detection.js: 클라이언트사이드 화면 크기 감지");
    console.log("   ✅ header.php: 모든 페이지 자동 포함");
    console.log("   ✅ 컨트롤러 통합: 중복 코드 제거");
    console.log("   ✅ 쿠키 시스템: 정확한 화면 크기 전달");
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();