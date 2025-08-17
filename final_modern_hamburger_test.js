import { chromium } from "playwright";

(async () => {
  console.log("🎨 최종 모던 햄버거 메뉴 테스트...");
  
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
    await page.goto(`https://www.topmktx.com/?modern_design=${Date.now()}&fresh=${Math.random()}`);
    await page.waitForTimeout(4000);
    await page.waitForLoadState('networkidle');
    
    // 모던 햄버거 아이콘 확인
    const modernCheck = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      
      if (!hamburger) {
        return { exists: false };
      }
      
      const rect = hamburger.getBoundingClientRect();
      const styles = window.getComputedStyle(hamburger);
      
      // 중심점에서 클릭 테스트
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 2;
      const elementAtCenter = document.elementFromPoint(centerX, centerY);
      
      return {
        exists: true,
        position: {
          x: Math.round(rect.x),
          y: Math.round(rect.y),
          width: Math.round(rect.width),
          height: Math.round(rect.height)
        },
        styles: {
          display: styles.display,
          position: styles.position,
          background: styles.background,
          borderRadius: styles.borderRadius,
          boxShadow: styles.boxShadow,
          top: styles.top,
          right: styles.right,
          zIndex: styles.zIndex
        },
        clickable: hamburger.offsetWidth > 0 && hamburger.offsetHeight > 0,
        actuallyClickable: elementAtCenter && elementAtCenter.id === 'mobile-hamburger',
        spans: Array.from(hamburger.querySelectorAll('span')).length
      };
    });
    
    console.log("🎨 모던 햄버거 메뉴 상태:");
    
    if (!modernCheck.exists) {
      console.log("❌ 햄버거 아이콘이 존재하지 않습니다.");
      return;
    }
    
    console.log(`📍 위치: (${modernCheck.position.x}, ${modernCheck.position.y})`);
    console.log(`📐 크기: ${modernCheck.position.width}x${modernCheck.position.height}`);
    console.log(`🎨 배경: ${modernCheck.styles.background}`);
    console.log(`🔴 모서리: ${modernCheck.styles.borderRadius}`);
    console.log(`✨ 그림자: ${modernCheck.styles.boxShadow}`);
    console.log(`📍 고정 위치: top=${modernCheck.styles.top}, right=${modernCheck.styles.right}`);
    console.log(`🔢 z-index: ${modernCheck.styles.zIndex}`);
    console.log(`👆 클릭 가능: ${modernCheck.clickable}`);
    console.log(`🎯 실제 클릭 가능: ${modernCheck.actuallyClickable}`);
    console.log(`📏 라인 개수: ${modernCheck.spans}개`);
    
    if (modernCheck.actuallyClickable) {
      console.log("\\n🖱️ 햄버거 메뉴 클릭 테스트...");
      
      // 클릭 전 스크린샷
      await page.screenshot({ 
        path: "/var/www/html/topmkt/modern_hamburger_before_click.png",
        fullPage: false,
        clip: { x: 300, y: 0, width: 75, height: 80 }
      });
      
      try {
        await page.click('#mobile-hamburger');
        await page.waitForTimeout(500);
        
        // 애니메이션 상태 확인
        const animationCheck = await page.evaluate(() => {
          const hamburger = document.getElementById('mobile-hamburger');
          const modal = document.getElementById('mobileMenuModal');
          
          return {
            hamburgerActive: hamburger ? hamburger.classList.contains('active') : false,
            modalActive: modal ? modal.classList.contains('active') : false,
            modalDisplay: modal ? window.getComputedStyle(modal).display : 'none',
            modalVisibility: modal ? window.getComputedStyle(modal).visibility : 'hidden'
          };
        });
        
        console.log("🎭 애니메이션 상태:");
        console.log(`   햄버거 X자 변환: ${animationCheck.hamburgerActive ? '✅' : '❌'}`);
        console.log(`   모달 활성화: ${animationCheck.modalActive ? '✅' : '❌'}`);
        console.log(`   모달 표시: ${animationCheck.modalDisplay}`);
        console.log(`   모달 가시성: ${animationCheck.modalVisibility}`);
        
        // 클릭 후 스크린샷 (X자 변환 확인)
        await page.screenshot({ 
          path: "/var/www/html/topmkt/modern_hamburger_x_animation.png",
          fullPage: false,
          clip: { x: 300, y: 0, width: 75, height: 80 }
        });
        
        // 전체 모달 스크린샷
        await page.screenshot({ 
          path: "/var/www/html/topmkt/modern_hamburger_modal_opened.png",
          fullPage: false
        });
        
        if (animationCheck.hamburgerActive && animationCheck.modalActive) {
          console.log("🎉 모던 햄버거 메뉴 완벽하게 작동합니다!");
          console.log("   ✅ X자 변환 애니메이션 성공");
          console.log("   ✅ 모달 열기 성공");
        } else {
          console.log("⚠️ 일부 기능이 정상 작동하지 않습니다.");
        }
        
        // 모달 닫기 테스트
        await page.click('#mobileDropdownClose');
        await page.waitForTimeout(500);
        
        const closeCheck = await page.evaluate(() => {
          const hamburger = document.getElementById('mobile-hamburger');
          const modal = document.getElementById('mobileMenuModal');
          
          return {
            hamburgerActive: hamburger ? hamburger.classList.contains('active') : false,
            modalActive: modal ? modal.classList.contains('active') : false
          };
        });
        
        console.log("\\n🔄 닫기 테스트:");
        console.log(`   햄버거 원복: ${!closeCheck.hamburgerActive ? '✅' : '❌'}`);
        console.log(`   모달 닫힘: ${!closeCheck.modalActive ? '✅' : '❌'}`);
        
      } catch (clickError) {
        console.error("❌ 클릭 테스트 실패:", clickError.message);
      }
    } else {
      console.log("❌ 햄버거 아이콘을 클릭할 수 없습니다.");
    }
    
    console.log("\\n📸 모든 스크린샷 촬영 완료!");
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();