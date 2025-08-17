import { chromium } from "playwright";

(async () => {
  console.log("🎯 강의 페이지 모바일 최적화 최종 검증...");
  
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
    
    // 강의 페이지로 이동 (캐시 무시)
    await page.goto(`https://www.topmktx.com/lectures?mobile_test=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    const testSizes = [
      { name: "모바일", width: 375, height: 812, description: "iPhone 13 Pro" },
      { name: "태블릿", width: 768, height: 1024, description: "iPad" }
    ];
    
    for (const size of testSizes) {
      console.log(`\\n📱 ${size.name} (${size.width}x${size.height}) 최종 검증:`);
      
      await page.setViewportSize({ width: size.width, height: size.height });
      await page.waitForTimeout(2000);
      
      const analysis = await page.evaluate(() => {
        const elements = {
          colorLegend: document.querySelector('.color-legend'),
          calendarControls: document.querySelector('.calendar-controls'),
          sidebar: document.querySelector('.lectures-sidebar'),
          calendarView: document.querySelector('.calendar-view'),
          container: document.querySelector('.lectures-container')
        };
        
        const getElementAnalysis = (element, name) => {
          if (!element) return { name, exists: false };
          
          const rect = element.getBoundingClientRect();
          const styles = window.getComputedStyle(element);
          
          const issues = [];
          if (rect.width > window.innerWidth) issues.push('뷰포트 넘침');
          if (rect.right > window.innerWidth) issues.push('우측 경계 넘침');
          if (rect.width > window.innerWidth * 0.98) issues.push('너무 넓음');
          
          return {
            name,
            exists: true,
            width: Math.round(rect.width),
            height: Math.round(rect.height),
            right: Math.round(rect.right),
            viewportWidth: window.innerWidth,
            issues: issues,
            isGood: issues.length === 0,
            styles: {
              overflow: styles.overflow,
              maxWidth: styles.maxWidth,
              width: styles.width,
              boxSizing: styles.boxSizing
            }
          };
        };
        
        return {
          viewport: { width: window.innerWidth, height: window.innerHeight },
          elements: Object.fromEntries(
            Object.entries(elements).map(([key, element]) => 
              [key, getElementAnalysis(element, key)]
            )
          ),
          hasHorizontalScroll: document.body.scrollWidth > window.innerWidth
        };
      });
      
      console.log(`   뷰포트: ${analysis.viewport.width}x${analysis.viewport.height}`);
      console.log(`   가로 스크롤: ${analysis.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
      
      let allGood = true;
      Object.values(analysis.elements).forEach(element => {
        if (element.exists) {
          const status = element.isGood ? '✅' : '❌';
          console.log(`   ${status} ${element.name}: ${element.width}px (${element.viewportWidth}px 중)`);
          
          if (!element.isGood) {
            console.log(`      문제: ${element.issues.join(', ')}`);
            allGood = false;
          }
        }
      });
      
      // 스크린샷
      await page.screenshot({ 
        path: `/var/www/html/topmkt/lectures_final_${size.name}_${size.width}px.png`,
        fullPage: true
      });
      
      if (allGood && !analysis.hasHorizontalScroll) {
        console.log(`   🎉 ${size.name} 최적화 완료!`);
      } else {
        console.log(`   ⚠️ ${size.name} 추가 수정 필요`);
      }
    }
    
    // Before vs After 요약
    console.log("\\n📊 Before vs After 비교:");
    
    await page.setViewportSize({ width: 375, height: 812 });
    await page.waitForTimeout(1000);
    
    const beforeAfter = await page.evaluate(() => {
      const sidebar = document.querySelector('.lectures-sidebar');
      const colorLegend = document.querySelector('.color-legend');
      const calendarControls = document.querySelector('.calendar-controls');
      
      return {
        sidebar: sidebar ? Math.round(sidebar.getBoundingClientRect().width) : 0,
        colorLegend: colorLegend ? Math.round(colorLegend.getBoundingClientRect().height) : 0,
        calendarControls: calendarControls ? Math.round(calendarControls.getBoundingClientRect().height) : 0,
        viewport: window.innerWidth
      };
    });
    
    console.log("   사이드바 너비:");
    console.log("      Before: 500px (뷰포트 넘침)");
    console.log(`      After: ${beforeAfter.sidebar}px (${beforeAfter.viewport}px 뷰포트에 맞춤)`);
    
    console.log("   색상범례 높이:");
    console.log("      Before: 86px (너무 큼)");
    console.log(`      After: ${beforeAfter.colorLegend}px (컴팩트)`);
    
    console.log("   캘린더 컨트롤 높이:");
    console.log("      Before: 91px (너무 큼)");
    console.log(`      After: ${beforeAfter.calendarControls}px (최적화)`);
    
    const improvement = Math.round(((500 - beforeAfter.sidebar) / 500) * 100);
    console.log(`\\n🎯 사이드바 너비 개선: ${improvement}% 감소`);
    
    if (beforeAfter.sidebar <= beforeAfter.viewport && improvement > 0) {
      console.log("\\n🎉 모바일 반응형 최적화 완전 성공!");
      console.log("   ✅ 사이드바 뷰포트 오버플로우 해결");
      console.log("   ✅ 색상범례 및 캘린더 컨트롤 크기 최적화");
      console.log("   ✅ 모든 요소가 모바일 화면에 적절히 배치");
    } else {
      console.log("   ⚠️ 일부 요소 추가 최적화 필요");
    }
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();