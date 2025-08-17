import { chromium } from "playwright";

(async () => {
  console.log("🔥 ULTRA THINK: 최종 시각적 검증 테스트...");
  
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
    
    // 메인 페이지 (강력한 캐시 우회)
    await page.goto(`https://www.topmktx.com/?ultra_final=${Date.now()}&cache_bust=${Math.random()}`);
    await page.waitForTimeout(4000);
    await page.waitForLoadState('networkidle');
    
    // 햄버거 아이콘 최종 확인
    const finalCheck = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      
      if (!hamburger) {
        return { exists: false, message: '햄버거 아이콘이 존재하지 않습니다.' };
      }
      
      const rect = hamburger.getBoundingClientRect();
      const styles = window.getComputedStyle(hamburger);
      
      // 햄버거 위치에서 실제로 클릭되는 요소 확인
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 2;
      const elementAtCenter = document.elementFromPoint(centerX, centerY);
      
      return {
        exists: true,
        hamburgerInfo: {
          position: {
            x: Math.round(rect.x),
            y: Math.round(rect.y),
            width: Math.round(rect.width),
            height: Math.round(rect.height)
          },
          styles: {
            display: styles.display,
            visibility: styles.visibility,
            opacity: styles.opacity,
            position: styles.position,
            zIndex: styles.zIndex,
            top: styles.top,
            right: styles.right
          },
          visible: rect.width > 0 && rect.height > 0,
          clickable: hamburger.offsetWidth > 0 && hamburger.offsetHeight > 0
        },
        clickTest: {
          centerPoint: { x: centerX, y: centerY },
          elementAtCenter: elementAtCenter ? {
            tagName: elementAtCenter.tagName,
            id: elementAtCenter.id,
            className: elementAtCenter.className
          } : null,
          isHamburgerClickable: elementAtCenter && elementAtCenter.id === 'mobile-hamburger'
        }
      };
    });
    
    console.log("\\n🔍 최종 햄버거 아이콘 검증 결과:");
    
    if (!finalCheck.exists) {
      console.log("❌", finalCheck.message);
      return;
    }
    
    const { hamburgerInfo, clickTest } = finalCheck;
    
    console.log("📱 햄버거 아이콘 정보:");
    console.log(`   위치: (${hamburgerInfo.position.x}, ${hamburgerInfo.position.y})`);
    console.log(`   크기: ${hamburgerInfo.position.width}x${hamburgerInfo.position.height}`);
    console.log(`   스타일: display=${hamburgerInfo.styles.display}, position=${hamburgerInfo.styles.position}`);
    console.log(`   위치 스타일: top=${hamburgerInfo.styles.top}, right=${hamburgerInfo.styles.right}`);
    console.log(`   z-index: ${hamburgerInfo.styles.zIndex}`);
    console.log(`   가시성: visible=${hamburgerInfo.visible}, clickable=${hamburgerInfo.clickable}`);
    
    console.log("\\n🎯 클릭 테스트:");
    console.log(`   중심점: (${clickTest.centerPoint.x}, ${clickTest.centerPoint.y})`);
    
    if (clickTest.elementAtCenter) {
      console.log(`   클릭시 선택되는 요소: ${clickTest.elementAtCenter.tagName}#${clickTest.elementAtCenter.id}.${clickTest.elementAtCenter.className}`);
      
      if (clickTest.isHamburgerClickable) {
        console.log("✅ 햄버거 아이콘이 정상적으로 클릭 가능합니다!");
      } else {
        console.log("❌ 햄버거 아이콘이 다른 요소에 가려져 있습니다!");
      }
    } else {
      console.log("❌ 클릭 지점에 요소가 없습니다!");
    }
    
    // 실제 클릭 테스트
    if (clickTest.isHamburgerClickable) {
      console.log("\\n🖱️ 실제 클릭 테스트 진행...");
      
      try {
        await page.click('#mobile-hamburger');
        await page.waitForTimeout(1000);
        
        const modalTest = await page.evaluate(() => {
          const modal = document.getElementById('mobileMenuModal');
          return modal ? {
            exists: true,
            active: modal.classList.contains('active'),
            display: window.getComputedStyle(modal).display,
            visibility: window.getComputedStyle(modal).visibility
          } : { exists: false };
        });
        
        console.log("📱 모달 테스트 결과:", JSON.stringify(modalTest, null, 2));
        
        if (modalTest.active) {
          console.log("🎉 햄버거 메뉴 완벽하게 작동합니다!");
        } else {
          console.log("⚠️ 햄버거 아이콘 클릭했지만 모달이 열리지 않았습니다.");
        }
        
      } catch (clickError) {
        console.error("❌ 클릭 테스트 실패:", clickError.message);
      }
    }
    
    // 최종 스크린샷
    await page.screenshot({ 
      path: "/var/www/html/topmkt/ultra_final_visual_verification.png",
      fullPage: false,
      clip: { x: 300, y: 0, width: 75, height: 80 }
    });
    
    console.log("\\n📸 최종 검증 스크린샷 촬영 완료");
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();