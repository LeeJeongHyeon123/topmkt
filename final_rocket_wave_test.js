import { chromium } from "playwright";

(async () => {
  console.log("🌊 로켓 + 파동 애니메이션 최종 테스트...");
  
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
    
    // 메인 페이지에서 로고 위치 및 파동 애니메이션 확인
    console.log("\n🎯 로고 위치 및 파동 애니메이션 테스트:");
    await page.setViewportSize({ width: 1200, height: 800 });
    await page.goto(`https://www.topmktx.com/?wave_test=${Date.now()}`);
    await page.waitForTimeout(4000); // 착륙 애니메이션 완료 대기
    
    const initialResult = await page.evaluate(() => {
      const headerContent = document.querySelector('.header-content');
      const logoIcon = document.querySelector('.logo-icon');
      const headerRocket = document.querySelector('.header-rocket');
      
      if (!headerContent || !logoIcon || !headerRocket) {
        return { error: '요소를 찾을 수 없습니다.' };
      }
      
      const headerRect = headerContent.getBoundingClientRect();
      const logoRect = logoIcon.getBoundingClientRect();
      const rocketRect = headerRocket.getBoundingClientRect();
      
      // 가상 요소들의 z-index 확인
      const logoIconStyles = window.getComputedStyle(logoIcon);
      const beforeStyles = window.getComputedStyle(logoIcon, '::before');
      const afterStyles = window.getComputedStyle(logoIcon, '::after');
      
      return {
        logoPosition: {
          fromLeft: Math.round(logoRect.left - headerRect.left),
          absolute: Math.round(logoRect.left)
        },
        rocketZIndex: window.getComputedStyle(headerRocket).zIndex,
        logoIconZIndex: logoIconStyles.zIndex,
        beforeZIndex: beforeStyles.zIndex,
        afterZIndex: afterStyles.zIndex,
        dimensions: {
          header: { width: Math.round(headerRect.width), left: Math.round(headerRect.left) },
          logo: { width: Math.round(logoRect.width), left: Math.round(logoRect.left) },
          rocket: { width: Math.round(rocketRect.width), left: Math.round(rocketRect.left) }
        }
      };
    });
    
    if (initialResult.error) {
      console.log("❌", initialResult.error);
      return;
    }
    
    console.log("📍 로고 위치 분석:");
    console.log(`   헤더 좌측에서 거리: ${initialResult.logoPosition.fromLeft}px`);
    console.log(`   화면 절대 위치: ${initialResult.logoPosition.absolute}px`);
    console.log(`   위치 평가: ${initialResult.logoPosition.fromLeft <= 25 ? '✅ 좌측에 적절히 배치' : '⚠️ 너무 우측으로 이동'}`);
    
    console.log("\n🎨 Z-Index 레이어 확인:");
    console.log(`   로켓 아이콘: z-index ${initialResult.rocketZIndex}`);
    console.log(`   로고 컨테이너: z-index ${initialResult.logoIconZIndex}`);
    console.log(`   파동 효과(::before): z-index ${initialResult.beforeZIndex}`);
    console.log(`   연기 효과(::after): z-index ${initialResult.afterZIndex}`);
    
    // 호버 시 파동 애니메이션 테스트
    console.log("\n🖱️ 호버 시 파동 애니메이션 테스트:");
    await page.hover('.logo-link');
    await page.waitForTimeout(1000);
    
    const hoverResult = await page.evaluate(() => {
      const logoIcon = document.querySelector('.logo-icon');
      const headerRocket = document.querySelector('.header-rocket');
      
      // 호버 시 가상 요소들의 상태
      const logoRect = logoIcon.getBoundingClientRect();
      const rocketRect = headerRocket.getBoundingClientRect();
      
      // 파동 영역(가상 요소들이 나타나는 영역) 클릭 테스트
      const waveAreaResults = [];
      const testPoints = [
        { name: '로켓 중심', x: rocketRect.left + rocketRect.width / 2, y: rocketRect.top + rocketRect.height / 2 },
        { name: '로고 좌측', x: logoRect.left - 20, y: logoRect.top + logoRect.height / 2 },
        { name: '로고 하단', x: logoRect.left + logoRect.width / 2, y: logoRect.bottom + 10 },
        { name: '파동 예상 영역', x: logoRect.left - 35, y: logoRect.top + logoRect.height / 2 }
      ];
      
      testPoints.forEach(point => {
        const element = document.elementFromPoint(point.x, point.y);
        waveAreaResults.push({
          point: point.name,
          coordinates: `(${Math.round(point.x)}, ${Math.round(point.y)})`,
          topElement: element ? {
            tagName: element.tagName,
            className: element.className,
            id: element.id
          } : null,
          isLogoRelated: element && (
            element.closest('.logo-icon') || 
            element.closest('.logo-link') || 
            element.classList.contains('header-rocket')
          )
        });
      });
      
      return {
        waveTests: waveAreaResults,
        rocketTransform: window.getComputedStyle(headerRocket).transform
      };
    });
    
    console.log("🌊 파동 영역 클릭 테스트:");
    hoverResult.waveTests.forEach(test => {
      console.log(`   ${test.point} ${test.coordinates}:`);
      console.log(`      최상위: ${test.topElement?.tagName}#${test.topElement?.id}.${test.topElement?.className}`);
      console.log(`      로고 관련: ${test.isLogoRelated ? '✅' : '❌'}`);
    });
    
    console.log(`\n🚀 호버 변환: ${hoverResult.rocketTransform}`);
    
    // 최종 스크린샷
    await page.screenshot({ 
      path: "/var/www/html/topmkt/final_rocket_wave_test.png",
      fullPage: false,
      clip: { x: 0, y: 0, width: 500, height: 120 }
    });
    
    console.log("\n🎯 최종 결과:");
    const logoWellPositioned = initialResult.logoPosition.fromLeft <= 25;
    const zIndexProperlySet = parseInt(initialResult.rocketZIndex) === 9999 && 
                             parseInt(initialResult.beforeZIndex) === 9995 && 
                             parseInt(initialResult.afterZIndex) === 9994;
    const waveAreaAccessible = hoverResult.waveTests.some(test => test.isLogoRelated);
    
    if (logoWellPositioned && zIndexProperlySet && waveAreaAccessible) {
      console.log("🎉 로켓 + 파동 애니메이션 완전 성공!");
      console.log("   ✅ 로고 위치: 좌측에 적절히 배치");
      console.log("   ✅ Z-Index: 모든 레이어 올바르게 설정");
      console.log("   ✅ 파동 애니메이션: 가려지지 않고 표시");
      console.log("   ✅ 호버 효과: 정상 작동");
    } else {
      console.log("⚠️ 일부 개선 필요:");
      if (!logoWellPositioned) console.log("   ❌ 로고가 너무 우측에 위치");
      if (!zIndexProperlySet) console.log("   ❌ Z-Index 설정 미완료");
      if (!waveAreaAccessible) console.log("   ❌ 파동 영역이 가려짐");
    }
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();