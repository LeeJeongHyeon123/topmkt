import { chromium } from "playwright";

(async () => {
  console.log("🔍 강의 페이지 여백 문제 디버깅...");
  
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
    
    // 모바일에서 강의 페이지
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto(`https://www.topmktx.com/lectures?margin_debug=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    const marginAnalysis = await page.evaluate(() => {
      const sidebar = document.querySelector('.lectures-sidebar');
      const calendarView = document.querySelector('.calendar-view');
      const container = document.querySelector('.lectures-container');
      
      const analyzeElement = (element, name) => {
        if (!element) return { name, exists: false };
        
        const rect = element.getBoundingClientRect();
        const styles = window.getComputedStyle(element);
        
        return {
          name,
          exists: true,
          rect: {
            left: Math.round(rect.left),
            right: Math.round(rect.right),
            width: Math.round(rect.width)
          },
          styles: {
            marginLeft: styles.marginLeft,
            marginRight: styles.marginRight,
            paddingLeft: styles.paddingLeft,
            paddingRight: styles.paddingRight,
            width: styles.width,
            maxWidth: styles.maxWidth
          },
          calculated: {
            leftMargin: Math.round(rect.left),
            rightMargin: Math.round(window.innerWidth - rect.right),
            totalWidth: Math.round(rect.width),
            viewportWidth: window.innerWidth
          }
        };
      };
      
      return {
        viewport: window.innerWidth,
        sidebar: analyzeElement(sidebar, '사이드바'),
        calendarView: analyzeElement(calendarView, '달력'),
        container: analyzeElement(container, '컨테이너')
      };
    });
    
    console.log(`\\n📱 모바일 여백 분석 (뷰포트: ${marginAnalysis.viewport}px):`);
    
    [marginAnalysis.sidebar, marginAnalysis.calendarView, marginAnalysis.container].forEach(element => {
      if (element.exists) {
        console.log(`\\n📋 ${element.name}:`);
        console.log(`   위치: left=${element.rect.left}px, right=${element.rect.right}px`);
        console.log(`   너비: ${element.rect.width}px`);
        console.log(`   좌측 여백: ${element.calculated.leftMargin}px`);
        console.log(`   우측 여백: ${element.calculated.rightMargin}px`);
        console.log(`   CSS margin: ${element.styles.marginLeft} | ${element.styles.marginRight}`);
        console.log(`   CSS width: ${element.styles.width}`);
        console.log(`   CSS max-width: ${element.styles.maxWidth}`);
        
        if (element.calculated.rightMargin < 5) {
          console.log(`   ❌ 우측 여백 부족! (${element.calculated.rightMargin}px)`);
        } else {
          console.log(`   ✅ 우측 여백 충분 (${element.calculated.rightMargin}px)`);
        }
      }
    });
    
    // 스크린샷
    await page.screenshot({ 
      path: "/var/www/html/topmkt/lectures_margin_debug.png",
      fullPage: true
    });
    
    console.log("\\n📸 디버그 스크린샷 저장됨: lectures_margin_debug.png");
    
  } catch (error) {
    console.error("❌ 디버그 오류:", error);
  } finally {
    await browser.close();
  }
})();