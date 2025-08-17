import { chromium } from "playwright";

(async () => {
  console.log("🎯 ULTRA FINAL: 햄버거 아이콘 클릭 가능성 최종 테스트...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 375, height: 812 },
      userAgent: "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1"
    });
    
    const page = await context.newPage();
    
    // DevLogin
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    // 메인 페이지 (최강 캐시 우회)
    await page.goto(`https://www.topmktx.com/?click_test=${Date.now()}&ultra=${Math.random()}&pointer_fix=${Date.now()}`);
    await page.waitForTimeout(4000);
    await page.waitForLoadState('networkidle');
    
    // 최종 클릭 테스트
    const clickabilityTest = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      
      if (!hamburger) {
        return { exists: false, error: '햄버거 아이콘이 존재하지 않습니다.' };
      }
      
      const rect = hamburger.getBoundingClientRect();
      
      // 다양한 지점에서 클릭 테스트
      const testPoints = [
        { name: '중심', x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 },
        { name: '좌상단', x: rect.left + 5, y: rect.top + 5 },
        { name: '우상단', x: rect.right - 5, y: rect.top + 5 },
        { name: '좌하단', x: rect.left + 5, y: rect.bottom - 5 },
        { name: '우하단', x: rect.right - 5, y: rect.bottom - 5 }
      ];
      
      const results = testPoints.map(point => {
        const elementAtPoint = document.elementFromPoint(point.x, point.y);
        return {
          point: point.name,
          coordinates: `(${Math.round(point.x)}, ${Math.round(point.y)})`,
          element: elementAtPoint ? {
            tagName: elementAtPoint.tagName,
            id: elementAtPoint.id,
            className: elementAtPoint.className
          } : null,
          isHamburger: elementAtPoint && elementAtPoint.id === 'mobile-hamburger',
          isHamburgerChild: elementAtPoint && elementAtPoint.closest('#mobile-hamburger') !== null
        };
      });
      
      return {
        exists: true,
        hamburgerInfo: {
          position: {
            x: Math.round(rect.x),
            y: Math.round(rect.y),
            width: Math.round(rect.width),
            height: Math.round(rect.height),
            left: Math.round(rect.left),
            right: Math.round(rect.right),
            top: Math.round(rect.top),
            bottom: Math.round(rect.bottom)
          },
          styles: {
            display: window.getComputedStyle(hamburger).display,
            position: window.getComputedStyle(hamburger).position,
            zIndex: window.getComputedStyle(hamburger).zIndex,
            pointerEvents: window.getComputedStyle(hamburger).pointerEvents
          }
        },
        clickTests: results,
        anyClickable: results.some(r => r.isHamburger || r.isHamburgerChild)
      };
    });
    
    console.log("🎯 최종 클릭 가능성 테스트 결과:");
    
    if (!clickabilityTest.exists) {
      console.log("❌", clickabilityTest.error);
      return;
    }
    
    const { hamburgerInfo, clickTests, anyClickable } = clickabilityTest;
    
    console.log("\\n📱 햄버거 아이콘 정보:");
    console.log(`   위치: (${hamburgerInfo.position.x}, ${hamburgerInfo.position.y})`);
    console.log(`   크기: ${hamburgerInfo.position.width}x${hamburgerInfo.position.height}`);
    console.log(`   영역: left:${hamburgerInfo.position.left} → right:${hamburgerInfo.position.right}`);
    console.log(`   영역: top:${hamburgerInfo.position.top} → bottom:${hamburgerInfo.position.bottom}`);
    console.log(`   스타일: display=${hamburgerInfo.styles.display}, position=${hamburgerInfo.styles.position}`);
    console.log(`   z-index: ${hamburgerInfo.styles.zIndex}, pointer-events: ${hamburgerInfo.styles.pointerEvents}`);
    
    console.log("\\n🎯 다중 지점 클릭 테스트:");
    clickTests.forEach(test => {
      const status = test.isHamburger ? '✅ 햄버거' : test.isHamburgerChild ? '✅ 햄버거 자식' : '❌ 다른 요소';
      console.log(`   ${test.point} ${test.coordinates}: ${status}`);
      if (test.element && !test.isHamburger && !test.isHamburgerChild) {
        console.log(`      → ${test.element.tagName}#${test.element.id}.${test.element.className}`);
      }
    });
    
    console.log(`\\n🎯 전체 클릭 가능성: ${anyClickable ? '✅ 가능' : '❌ 불가능'}`);
    
    if (anyClickable) {
      console.log("\\n🖱️ 실제 클릭 테스트 진행...");
      
      try {
        // 가장 확실한 중심점 클릭
        const centerX = hamburgerInfo.position.left + hamburgerInfo.position.width / 2;
        const centerY = hamburgerInfo.position.top + hamburgerInfo.position.height / 2;
        
        await page.mouse.click(centerX, centerY);
        await page.waitForTimeout(1000);
        
        const modalResult = await page.evaluate(() => {
          const modal = document.getElementById('mobileMenuModal');
          const hamburger = document.getElementById('mobile-hamburger');
          return {
            modalActive: modal ? modal.classList.contains('active') : false,
            hamburgerActive: hamburger ? hamburger.classList.contains('active') : false,
            modalDisplay: modal ? window.getComputedStyle(modal).display : 'none'
          };
        });
        
        console.log("📱 클릭 결과:");
        console.log(`   모달 활성화: ${modalResult.modalActive ? '✅' : '❌'}`);
        console.log(`   햄버거 X자 변환: ${modalResult.hamburgerActive ? '✅' : '❌'}`);
        console.log(`   모달 표시: ${modalResult.modalDisplay}`);
        
        if (modalResult.modalActive && modalResult.hamburgerActive) {
          console.log("\\n🎉 햄버거 메뉴 완벽하게 작동합니다!");
        } else {
          console.log("\\n⚠️ 클릭은 됐지만 기능이 완전하지 않습니다.");
        }
        
      } catch (clickError) {
        console.error("❌ 클릭 실행 실패:", clickError.message);
      }
    }
    
    // 최종 스크린샷
    await page.screenshot({ 
      path: "/var/www/html/topmkt/ultra_final_click_test.png",
      fullPage: false,
      clip: { x: 300, y: 0, width: 75, height: 80 }
    });
    
    console.log("\\n📸 최종 테스트 스크린샷 촬영 완료!");
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();