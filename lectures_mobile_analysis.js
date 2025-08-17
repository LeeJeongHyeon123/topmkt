import { chromium } from "playwright";

(async () => {
  console.log("📱 강의 페이지 모바일 반응형 문제 분석...");
  
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
    
    // 강의 페이지로 이동
    await page.goto("https://www.topmktx.com/lectures");
    await page.waitForTimeout(3000);
    
    const screenSizes = [
      { name: "모바일", width: 375, height: 812 },
      { name: "태블릿", width: 768, height: 1024 },
      { name: "데스크톱", width: 1200, height: 800 }
    ];
    
    for (const size of screenSizes) {
      console.log(`\\n📱 ${size.name} (${size.width}x${size.height}) 분석:`);
      
      await page.setViewportSize({ width: size.width, height: size.height });
      await page.waitForTimeout(2000);
      
      const analysis = await page.evaluate(() => {
        // 주요 요소들 찾기
        const colorLegend = document.querySelector('.color-legend, .legend, [class*="legend"], [class*="color"]');
        const calendarControls = document.querySelector('.calendar-controls, .controls, [class*="control"], .btn-group');
        const sidebar = document.querySelector('.sidebar, .side-panel, [class*="sidebar"], [class*="side"]');
        const calendar = document.querySelector('.calendar, [class*="calendar"], .fc');
        const container = document.querySelector('.container, .main-content');
        
        const getElementInfo = (element, name) => {
          if (!element) return { name, exists: false };
          
          const rect = element.getBoundingClientRect();
          const styles = window.getComputedStyle(element);
          
          return {
            name,
            exists: true,
            rect: {
              x: Math.round(rect.x),
              y: Math.round(rect.y),
              width: Math.round(rect.width),
              height: Math.round(rect.height),
              right: Math.round(rect.right)
            },
            styles: {
              display: styles.display,
              position: styles.position,
              overflow: styles.overflow,
              width: styles.width,
              maxWidth: styles.maxWidth,
              padding: styles.padding,
              margin: styles.margin
            },
            issues: {
              overflowsViewport: rect.right > window.innerWidth || rect.width > window.innerWidth,
              tooWide: rect.width > window.innerWidth * 0.95,
              hidden: styles.display === 'none',
              fixedPosition: styles.position === 'fixed' || styles.position === 'absolute'
            }
          };
        };
        
        return {
          viewport: {
            width: window.innerWidth,
            height: window.innerHeight
          },
          elements: {
            colorLegend: getElementInfo(colorLegend, '색상범례'),
            calendarControls: getElementInfo(calendarControls, '캘린더 컨트롤'),
            sidebar: getElementInfo(sidebar, '사이드바'),
            calendar: getElementInfo(calendar, '달력'),
            container: getElementInfo(container, '컨테이너')
          },
          bodyOverflow: {
            x: document.body.scrollWidth > window.innerWidth,
            y: document.body.scrollHeight > window.innerHeight
          }
        };
      });
      
      console.log(`   뷰포트: ${analysis.viewport.width}x${analysis.viewport.height}`);
      console.log(`   가로 오버플로우: ${analysis.bodyOverflow.x ? '❌ 발생' : '✅ 없음'}`);
      
      Object.values(analysis.elements).forEach(element => {
        if (element.exists) {
          console.log(`\\n   📋 ${element.name}:`);
          console.log(`      크기: ${element.rect.width}x${element.rect.height}`);
          console.log(`      위치: (${element.rect.x}, ${element.rect.y})`);
          console.log(`      표시: ${element.styles.display}`);
          
          const issues = [];
          if (element.issues.overflowsViewport) issues.push('뷰포트 넘침');
          if (element.issues.tooWide) issues.push('너무 넓음');
          if (element.issues.hidden) issues.push('숨겨짐');
          if (element.issues.fixedPosition) issues.push('고정 위치');
          
          if (issues.length > 0) {
            console.log(`      ❌ 문제: ${issues.join(', ')}`);
          } else {
            console.log(`      ✅ 정상`);
          }
        } else {
          console.log(`\\n   ❌ ${element.name}: 요소를 찾을 수 없음`);
        }
      });
      
      // 스크린샷 촬영
      await page.screenshot({ 
        path: `/var/www/html/topmkt/lectures_${size.name}_${size.width}px.png`,
        fullPage: true
      });
    }
    
    // HTML 구조 분석
    console.log("\\n🔍 HTML 구조 분석:");
    await page.setViewportSize({ width: 375, height: 812 });
    
    const htmlStructure = await page.evaluate(() => {
      const selectors = [
        '.color-legend', '.legend', '[class*="legend"]',
        '.calendar-controls', '.controls', '[class*="control"]',
        '.sidebar', '[class*="sidebar"]',
        '.calendar', '[class*="calendar"]', '.fc',
        '.container', '.main-content'
      ];
      
      const foundElements = [];
      selectors.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        if (elements.length > 0) {
          elements.forEach((el, i) => {
            foundElements.push({
              selector,
              index: i,
              className: el.className,
              id: el.id,
              tagName: el.tagName
            });
          });
        }
      });
      
      return foundElements;
    });
    
    console.log("   발견된 요소들:");
    htmlStructure.forEach(el => {
      console.log(`   - ${el.tagName}#${el.id}.${el.className} (${el.selector})`);
    });
    
  } catch (error) {
    console.error("❌ 분석 오류:", error);
  } finally {
    await browser.close();
  }
})();