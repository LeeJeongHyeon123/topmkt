import { chromium } from "playwright";

(async () => {
  console.log("🚀 Z-Index 로켓 애니메이션 테스트...");
  
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
    
    // 메인 페이지
    console.log("\n🎯 Z-Index 레이어 확인 테스트:");
    await page.setViewportSize({ width: 1200, height: 800 });
    await page.goto(`https://www.topmktx.com/?zindex_test=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    const zIndexResult = await page.evaluate(() => {
      const headerRocket = document.querySelector('.header-rocket');
      const logoIcon = document.querySelector('.logo-icon');
      const logoLink = document.querySelector('.logo-link');
      const headerLeft = document.querySelector('.header-left');
      const headerContent = document.querySelector('.header-content');
      const mainHeader = document.querySelector('.main-header');
      
      const getZIndex = (element) => {
        if (!element) return 'N/A';
        const styles = window.getComputedStyle(element);
        return styles.zIndex;
      };
      
      const getPosition = (element) => {
        if (!element) return 'N/A';
        const styles = window.getComputedStyle(element);
        return styles.position;
      };
      
      return {
        headerRocket: {
          zIndex: getZIndex(headerRocket),
          position: getPosition(headerRocket),
          exists: !!headerRocket
        },
        logoIcon: {
          zIndex: getZIndex(logoIcon),
          position: getPosition(logoIcon),
          exists: !!logoIcon
        },
        logoLink: {
          zIndex: getZIndex(logoLink),
          position: getPosition(logoLink),
          exists: !!logoLink
        },
        headerLeft: {
          zIndex: getZIndex(headerLeft),
          position: getPosition(headerLeft),
          exists: !!headerLeft
        },
        headerContent: {
          zIndex: getZIndex(headerContent),
          position: getPosition(headerContent),
          exists: !!headerContent
        },
        mainHeader: {
          zIndex: getZIndex(mainHeader),
          position: getPosition(mainHeader),
          exists: !!mainHeader
        }
      };
    });
    
    console.log("📊 Z-Index 레이어 분석:");
    Object.entries(zIndexResult).forEach(([element, data]) => {
      if (data.exists) {
        console.log(`   ${element}:`);
        console.log(`      z-index: ${data.zIndex}`);
        console.log(`      position: ${data.position}`);
      } else {
        console.log(`   ${element}: ❌ 요소 없음`);
      }
    });
    
    // 로켓 애니메이션 레이어 순서 확인
    const layerOrder = Object.entries(zIndexResult)
      .filter(([_, data]) => data.exists && data.zIndex !== 'auto')
      .sort(([_, a], [__, b]) => parseInt(b.zIndex) - parseInt(a.zIndex));
    
    console.log("\n🏆 Z-Index 순서 (높은 순서대로):");
    layerOrder.forEach(([element, data], index) => {
      const rank = index + 1;
      const badge = rank === 1 ? "🥇" : rank === 2 ? "🥈" : rank === 3 ? "🥉" : `${rank}위`;
      console.log(`   ${badge} ${element}: z-index ${data.zIndex}`);
    });
    
    // 호버 애니메이션 테스트
    console.log("\n🖱️ 호버 애니메이션 Z-Index 테스트:");
    await page.hover('.logo-link');
    await page.waitForTimeout(500);
    
    const hoverResult = await page.evaluate(() => {
      const headerRocket = document.querySelector('.header-rocket');
      const rect = headerRocket.getBoundingClientRect();
      const styles = window.getComputedStyle(headerRocket);
      
      // 로켓 중심점에서 클릭 테스트 (실제로는 elementFromPoint로 확인)
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 2;
      const topElement = document.elementFromPoint(centerX, centerY);
      
      return {
        rocketVisible: rect.width > 0 && rect.height > 0,
        rocketZIndex: styles.zIndex,
        topElementAtRocketCenter: topElement ? {
          tagName: topElement.tagName,
          className: topElement.className,
          id: topElement.id
        } : null,
        isRocketOnTop: topElement && (topElement.classList.contains('header-rocket') || topElement.closest('.header-rocket'))
      };
    });
    
    console.log("🎯 호버 상태 분석:");
    console.log(`   로켓 표시: ${hoverResult.rocketVisible ? '✅' : '❌'}`);
    console.log(`   로켓 z-index: ${hoverResult.rocketZIndex}`);
    console.log(`   로켓 중심점의 최상위 요소: ${hoverResult.topElementAtRocketCenter?.tagName}.${hoverResult.topElementAtRocketCenter?.className}#${hoverResult.topElementAtRocketCenter?.id}`);
    console.log(`   로켓이 최상위: ${hoverResult.isRocketOnTop ? '✅' : '❌'}`);
    
    // 최종 스크린샷
    await page.screenshot({ 
      path: "/var/www/html/topmkt/z_index_rocket_fixed.png",
      fullPage: false,
      clip: { x: 0, y: 0, width: 400, height: 100 }
    });
    
    console.log("\n🎯 최종 Z-Index 검증:");
    const rocketZIndex = parseInt(zIndexResult.headerRocket.zIndex);
    const otherElements = Object.entries(zIndexResult)
      .filter(([key, _]) => key !== 'headerRocket')
      .map(([_, data]) => parseInt(data.zIndex) || 0)
      .filter(z => !isNaN(z));
    
    const maxOtherZIndex = Math.max(...otherElements, 0);
    const isRocketHighest = rocketZIndex > maxOtherZIndex;
    
    if (isRocketHighest && hoverResult.isRocketOnTop) {
      console.log("🎉 Z-Index 로켓 애니메이션 완전 해결!");
      console.log(`   ✅ 로켓 z-index (${rocketZIndex}) > 다른 요소들 (최대 ${maxOtherZIndex})`);
      console.log("   ✅ 호버 시에도 로켓이 최상위 표시");
      console.log("   ✅ 더 이상 다른 요소에 가려지지 않음");
    } else {
      console.log("⚠️ Z-Index 추가 조정 필요");
      if (!isRocketHighest) {
        console.log(`   ❌ 로켓 z-index (${rocketZIndex}) ≤ 다른 요소 (최대 ${maxOtherZIndex})`);
      }
      if (!hoverResult.isRocketOnTop) {
        console.log("   ❌ 호버 시 로켓이 가려짐");
      }
    }
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();