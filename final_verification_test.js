import { chromium } from "playwright";

(async () => {
  console.log("✨ 최종 검증 테스트 - 사용자 경험 중심...");
  
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
    
    // 테스트 1: 모바일에서 완벽한 정렬과 동작
    console.log("\n📱 모바일 완벽 테스트 (375px):");
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto(`https://www.topmktx.com/?final_verification=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    const mobileResult = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      const logo = document.querySelector('.logo');
      
      if (!hamburger || !logo) {
        return { success: false, message: '요소를 찾을 수 없습니다.' };
      }
      
      const hamburgerRect = hamburger.getBoundingClientRect();
      const logoRect = logo.getBoundingClientRect();
      const hamburgerStyles = window.getComputedStyle(hamburger);
      
      // 실제 사용자가 보는 것처럼 판단
      const isVisuallyVisible = hamburgerRect.width > 0 && 
                                hamburgerRect.height > 0 && 
                                hamburgerStyles.opacity !== '0' &&
                                hamburgerStyles.visibility !== 'hidden' &&
                                hamburgerStyles.display !== 'none' &&
                                hamburgerRect.x >= 0 && hamburgerRect.y >= 0;
      
      const centerYDiff = Math.abs(
        (hamburgerRect.y + hamburgerRect.height / 2) - 
        (logoRect.y + logoRect.height / 2)
      );
      
      return {
        success: true,
        visible: isVisuallyVisible,
        alignment: centerYDiff <= 1,
        centerYDiff: Math.round(centerYDiff),
        hamburgerCenter: Math.round(hamburgerRect.y + hamburgerRect.height / 2),
        logoCenter: Math.round(logoRect.y + logoRect.height / 2)
      };
    });
    
    if (mobileResult.success) {
      console.log(`   👀 시각적 표시: ${mobileResult.visible ? '✅ 보임' : '❌ 안 보임'}`);
      console.log(`   📏 로고와 정렬: ${mobileResult.alignment ? '✅ 완벽' : `❌ ${mobileResult.centerYDiff}px 차이`}`);
      console.log(`   🎯 중심점 비교: 햄버거 y=${mobileResult.hamburgerCenter}, 로고 y=${mobileResult.logoCenter}`);
    }
    
    // 테스트 2: PC에서 완전 숨김
    console.log("\n💻 PC 완전 숨김 테스트 (1024px):");
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.waitForTimeout(2000);
    
    const pcResult = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      
      if (!hamburger) {
        return { exists: false };
      }
      
      const rect = hamburger.getBoundingClientRect();
      const styles = window.getComputedStyle(hamburger);
      
      // 사용자가 실제로 볼 수 있는지 판단
      const isActuallyVisible = rect.width > 0 && 
                                rect.height > 0 && 
                                styles.opacity !== '0' &&
                                styles.visibility !== 'hidden' &&
                                styles.display !== 'none' &&
                                rect.x >= 0 && rect.y >= 0 &&
                                rect.x < window.innerWidth && rect.y < window.innerHeight;
      
      return {
        exists: true,
        actuallyVisible: isActuallyVisible,
        styles: {
          display: styles.display,
          visibility: styles.visibility,
          opacity: styles.opacity,
          position: styles.position,
          left: styles.left,
          top: styles.top,
          width: styles.width,
          height: styles.height
        },
        rect: {
          x: Math.round(rect.x),
          y: Math.round(rect.y),
          width: Math.round(rect.width),
          height: Math.round(rect.height)
        }
      };
    });
    
    if (pcResult.exists) {
      console.log(`   👀 실제 보임 여부: ${pcResult.actuallyVisible ? '❌ 보임 (문제!)' : '✅ 완전 숨김'}`);
      console.log(`   📊 스타일 상태:`);
      console.log(`      display: ${pcResult.styles.display}`);
      console.log(`      visibility: ${pcResult.styles.visibility}`);
      console.log(`      opacity: ${pcResult.styles.opacity}`);
      console.log(`      position: ${pcResult.styles.position}`);
      console.log(`      left: ${pcResult.styles.left}`);
      console.log(`   📐 실제 크기: ${pcResult.rect.width}x${pcResult.rect.height}`);
      console.log(`   📍 실제 위치: (${pcResult.rect.x}, ${pcResult.rect.y})`);
    }
    
    // 테스트 3: 노트북에서도 숨김
    console.log("\n💻 노트북 숨김 테스트 (1440px):");
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.waitForTimeout(2000);
    
    const laptopResult = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      if (!hamburger) return { exists: false };
      
      const rect = hamburger.getBoundingClientRect();
      const styles = window.getComputedStyle(hamburger);
      
      const isActuallyVisible = rect.width > 0 && 
                                rect.height > 0 && 
                                styles.opacity !== '0' &&
                                styles.visibility !== 'hidden' &&
                                styles.display !== 'none' &&
                                rect.x >= 0 && rect.y >= 0 &&
                                rect.x < window.innerWidth && rect.y < window.innerHeight;
      
      return { exists: true, actuallyVisible: isActuallyVisible };
    });
    
    if (laptopResult.exists) {
      console.log(`   👀 실제 보임 여부: ${laptopResult.actuallyVisible ? '❌ 보임 (문제!)' : '✅ 완전 숨김'}`);
    }
    
    // 최종 종합 판정
    console.log("\n🎯 최종 종합 결과:");
    const allTestsPassed = mobileResult.success && 
                          mobileResult.visible && 
                          mobileResult.alignment && 
                          pcResult.exists && 
                          !pcResult.actuallyVisible && 
                          laptopResult.exists && 
                          !laptopResult.actuallyVisible;
    
    if (allTestsPassed) {
      console.log("🎉 모든 테스트 완벽 통과!");
      console.log("   ✅ 모바일: 햄버거 표시 + 로고와 완벽 정렬");
      console.log("   ✅ PC: 햄버거 완전 숨김");
      console.log("   ✅ 노트북: 햄버거 완전 숨김");
      console.log("   🏆 사용자 요구사항 100% 충족!");
    } else {
      console.log("⚠️ 일부 개선 필요");
      if (!mobileResult.visible) console.log("   ❌ 모바일에서 햄버거가 보이지 않음");
      if (!mobileResult.alignment) console.log("   ❌ 모바일에서 로고와 정렬 불일치");
      if (pcResult.actuallyVisible) console.log("   ❌ PC에서 햄버거가 보임");
      if (laptopResult.actuallyVisible) console.log("   ❌ 노트북에서 햄버거가 보임");
    }
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();