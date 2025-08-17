import { chromium } from "playwright";

(async () => {
  console.log("📋 강의 페이지 목록형 뷰 우측 잘림 문제 분석...");
  
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
    
    // 목록형 뷰로 직접 이동 (사용자가 제공한 URL)
    await page.goto("https://www.topmktx.com/lectures?year=2025&month=6&view=list");
    await page.waitForTimeout(3000);
    
    const testSizes = [
      { name: "모바일", width: 375, height: 812, description: "iPhone 13 Pro" },
      { name: "태블릿", width: 768, height: 1024, description: "iPad" }
    ];
    
    for (const size of testSizes) {
      console.log(`\n📱 ${size.name} (${size.width}x${size.height}) 목록형 뷰 분석:`);
      
      await page.setViewportSize({ width: size.width, height: size.height });
      await page.waitForTimeout(2000);
      
      const listAnalysis = await page.evaluate(() => {
        // 목록형 뷰 관련 요소들 찾기
        const listView = document.querySelector('.list-view, [class*="list"], .lecture-list, .lectures-list');
        const listItems = document.querySelectorAll('.lecture-item, [class*="lecture"], .list-item, [class*="item"]');
        const container = document.querySelector('.lectures-container, .container, .main-content');
        
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
              width: Math.round(rect.width),
              height: Math.round(rect.height)
            },
            styles: {
              overflow: styles.overflow,
              overflowX: styles.overflowX,
              width: styles.width,
              maxWidth: styles.maxWidth,
              padding: styles.padding,
              margin: styles.margin,
              boxSizing: styles.boxSizing
            },
            issues: {
              overflowsViewport: rect.right > window.innerWidth,
              exceedsViewport: rect.width > window.innerWidth,
              rightClipped: rect.right > window.innerWidth,
              leftOffset: rect.left,
              rightOffset: window.innerWidth - rect.right
            }
          };
        };
        
        const listItemsAnalysis = Array.from(listItems).slice(0, 3).map((item, index) => 
          analyzeElement(item, `강의 아이템 ${index + 1}`)
        );
        
        return {
          viewport: { width: window.innerWidth, height: window.innerHeight },
          listView: analyzeElement(listView, '목록 컨테이너'),
          container: analyzeElement(container, '메인 컨테이너'),
          listItems: listItemsAnalysis,
          hasHorizontalScroll: document.body.scrollWidth > window.innerWidth,
          bodyWidth: document.body.scrollWidth,
          viewportWidth: window.innerWidth
        };
      });
      
      console.log(`   뷰포트: ${listAnalysis.viewport.width}x${listAnalysis.viewport.height}`);
      console.log(`   Body 너비: ${listAnalysis.bodyWidth}px (뷰포트: ${listAnalysis.viewportWidth}px)`);
      console.log(`   가로 스크롤: ${listAnalysis.hasHorizontalScroll ? '❌ 발생' : '✅ 없음'}`);
      
      // 목록 컨테이너 분석
      if (listAnalysis.listView.exists) {
        const lv = listAnalysis.listView;
        console.log(`\n   📋 ${lv.name}:`);
        console.log(`      크기: ${lv.rect.width}x${lv.rect.height}`);
        console.log(`      위치: left=${lv.rect.left}px, right=${lv.rect.right}px`);
        console.log(`      CSS 너비: ${lv.styles.width}`);
        console.log(`      CSS max-width: ${lv.styles.maxWidth}`);
        console.log(`      CSS overflow: ${lv.styles.overflow}, overflowX: ${lv.styles.overflowX}`);
        
        if (lv.issues.rightClipped) {
          console.log(`      ❌ 우측 잘림! 초과: ${lv.rect.right - listAnalysis.viewportWidth}px`);
        } else {
          console.log(`      ✅ 뷰포트 내 위치 (우측 여백: ${lv.issues.rightOffset}px)`);
        }
      } else {
        console.log(`\n   ❌ 목록 컨테이너를 찾을 수 없음`);
      }
      
      // 개별 강의 아이템 분석
      console.log(`\n   📝 강의 아이템들 (총 ${listAnalysis.listItems.length}개 분석):`);
      listAnalysis.listItems.forEach(item => {
        if (item.exists) {
          console.log(`      ${item.name}: ${item.rect.width}px`);
          console.log(`         위치: left=${item.rect.left}px, right=${item.rect.right}px`);
          if (item.issues.rightClipped) {
            console.log(`         ❌ 우측 잘림! 초과: ${item.rect.right - listAnalysis.viewportWidth}px`);
          } else {
            console.log(`         ✅ 정상 위치 (우측 여백: ${item.issues.rightOffset}px)`);
          }
        }
      });
      
      // 스크린샷
      await page.screenshot({ 
        path: `/var/www/html/topmkt/lectures_list_view_${size.name}_${size.width}px.png`,
        fullPage: true
      });
    }
    
    // HTML 구조 분석
    console.log("\n🔍 목록형 뷰 HTML 구조 분석:");
    
    const htmlStructure = await page.evaluate(() => {
      const selectors = [
        '.list-view', '[class*="list"]', '.lecture-list', '.lectures-list',
        '.lecture-item', '[class*="lecture"]', '.list-item', '[class*="item"]',
        '.lectures-container', '.container', '.main-content'
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
              tagName: el.tagName,
              width: Math.round(el.getBoundingClientRect().width),
              right: Math.round(el.getBoundingClientRect().right)
            });
          });
        }
      });
      
      return {
        elements: foundElements,
        viewportWidth: window.innerWidth
      };
    });
    
    console.log("   발견된 목록 관련 요소들:");
    htmlStructure.elements.forEach(el => {
      const isClipped = el.right > htmlStructure.viewportWidth;
      const status = isClipped ? '❌' : '✅';
      console.log(`   ${status} ${el.tagName}#${el.id}.${el.className}`);
      console.log(`      선택자: ${el.selector}, 너비: ${el.width}px, right: ${el.right}px`);
    });
    
  } catch (error) {
    console.error("❌ 분석 오류:", error);
  } finally {
    await browser.close();
  }
})();