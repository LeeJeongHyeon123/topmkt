import { chromium } from "playwright";

(async () => {
  console.log("🎯 최종 정렬 및 반응형 테스트...");
  
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
    
    // 테스트 1: 모바일 사이즈 (375px)
    console.log("\\n📱 모바일 테스트 (375px):");
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto(`https://www.topmktx.com/?mobile_test=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    const mobileTest = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      const logo = document.querySelector('.logo');
      
      if (!hamburger || !logo) {
        return { success: false, message: '요소를 찾을 수 없습니다.' };
      }
      
      const hamburgerRect = hamburger.getBoundingClientRect();
      const logoRect = logo.getBoundingClientRect();
      const hamburgerCenterY = hamburgerRect.y + hamburgerRect.height / 2;
      const logoCenterY = logoRect.y + logoRect.height / 2;
      
      return {
        success: true,
        hamburger: {
          visible: window.getComputedStyle(hamburger).display !== 'none',
          position: { x: Math.round(hamburgerRect.x), y: Math.round(hamburgerRect.y) },
          centerY: Math.round(hamburgerCenterY)
        },
        logo: {
          position: { x: Math.round(logoRect.x), y: Math.round(logoRect.y) },
          centerY: Math.round(logoCenterY)
        },
        alignment: {
          centerYDiff: Math.abs(hamburgerCenterY - logoCenterY),
          aligned: Math.abs(hamburgerCenterY - logoCenterY) <= 1
        }
      };
    });
    
    if (mobileTest.success) {
      console.log(`   햄버거 표시: ${mobileTest.hamburger.visible ? '✅' : '❌'}`);
      console.log(`   햄버거 위치: (${mobileTest.hamburger.position.x}, ${mobileTest.hamburger.position.y})`);
      console.log(`   햄버거 중심: y=${mobileTest.hamburger.centerY}`);
      console.log(`   로고 중심: y=${mobileTest.logo.centerY}`);
      console.log(`   정렬 상태: ${mobileTest.alignment.aligned ? '✅ 완벽 정렬' : `❌ 차이 ${mobileTest.alignment.centerYDiff}px`}`);
    }
    
    // 테스트 2: 태블릿 사이즈 (768px)
    console.log("\\n📱 태블릿 테스트 (768px):");
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.waitForTimeout(2000);
    
    const tabletTest = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      return {
        visible: hamburger ? window.getComputedStyle(hamburger).display !== 'none' : false,
        mediaQueryMatch: window.matchMedia('(max-width: 768px)').matches
      };
    });
    
    console.log(`   햄버거 표시: ${tabletTest.visible ? '✅' : '❌'}`);
    console.log(`   미디어 쿼리: max-width 768px = ${tabletTest.mediaQueryMatch}`);
    
    // 테스트 3: PC 사이즈 (1024px)
    console.log("\\n💻 PC 테스트 (1024px):");
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.waitForTimeout(2000);
    
    const pcTest = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      const styles = hamburger ? window.getComputedStyle(hamburger) : null;
      
      return {
        exists: !!hamburger,
        visible: hamburger ? styles.display !== 'none' : false,
        opacity: hamburger ? styles.opacity : '0',
        position: hamburger ? styles.position : 'static',
        left: hamburger ? styles.left : 'auto',
        mediaQueryMatch: window.matchMedia('(min-width: 769px)').matches
      };
    });
    
    console.log(`   햄버거 존재: ${pcTest.exists ? '예' : '아니오'}`);
    console.log(`   햄버거 표시: ${pcTest.visible ? '❌ 보임 (문제!)' : '✅ 숨김'}`);
    console.log(`   opacity: ${pcTest.opacity}`);
    console.log(`   position: ${pcTest.position}`);
    console.log(`   left: ${pcTest.left}`);
    console.log(`   미디어 쿼리: min-width 769px = ${pcTest.mediaQueryMatch}`);
    
    // 테스트 4: 노트북 사이즈 (1440px)
    console.log("\\n💻 노트북 테스트 (1440px):");
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.waitForTimeout(2000);
    
    const laptopTest = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      return {
        visible: hamburger ? window.getComputedStyle(hamburger).display !== 'none' : false
      };
    });
    
    console.log(`   햄버거 표시: ${laptopTest.visible ? '❌ 보임 (문제!)' : '✅ 숨김'}`);
    
    // 최종 결과
    console.log("\\n🎯 최종 결과:");
    const allPassed = mobileTest.success && 
                     mobileTest.hamburger.visible && 
                     mobileTest.alignment.aligned && 
                     tabletTest.visible && 
                     !pcTest.visible && 
                     !laptopTest.visible;
    
    if (allPassed) {
      console.log("🎉 모든 테스트 통과! 완벽한 정렬 및 반응형 동작");
    } else {
      console.log("⚠️ 일부 테스트 실패. 추가 조정 필요");
    }
    
  } catch (error) {
    console.error("❌ 오류:", error);
  } finally {
    await browser.close();
  }
})();