import { chromium } from "playwright";

(async () => {
  console.log("📱 공통 푸터 UI 반응형 분석...");
  
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
    
    // 다양한 화면 크기에서 푸터 확인
    const screenSizes = [
      { name: "모바일 소형", width: 320, height: 568 },
      { name: "모바일 중형", width: 375, height: 812 },
      { name: "모바일 대형", width: 414, height: 896 },
      { name: "태블릿", width: 768, height: 1024 },
      { name: "데스크톱", width: 1200, height: 800 }
    ];
    
    for (const size of screenSizes) {
      console.log(`\n📱 ${size.name} (${size.width}x${size.height}) 분석:`);
      
      await page.setViewportSize({ width: size.width, height: size.height });
      await page.goto(`https://www.topmktx.com/?footer_test=${Date.now()}&size=${size.width}`);
      await page.waitForTimeout(3000);
      
      const footerAnalysis = await page.evaluate(() => {
        const footer = document.querySelector('footer');
        const footerContent = footer ? footer.querySelector('.footer-content, .container') : null;
        const footerLinks = footer ? footer.querySelectorAll('a') : [];
        const footerText = footer ? footer.textContent : '';
        
        if (!footer) {
          return { error: '푸터를 찾을 수 없습니다.' };
        }
        
        const footerRect = footer.getBoundingClientRect();
        const footerStyles = window.getComputedStyle(footer);
        
        // 푸터 내부 요소들 분석
        const links = Array.from(footerLinks).map(link => ({
          text: link.textContent.trim(),
          href: link.href,
          rect: {
            x: Math.round(link.getBoundingClientRect().x),
            y: Math.round(link.getBoundingClientRect().y),
            width: Math.round(link.getBoundingClientRect().width),
            height: Math.round(link.getBoundingClientRect().height)
          }
        }));
        
        // 텍스트 길이 및 줄바꿈 확인
        const textLines = footerText.split('\n').filter(line => line.trim());
        
        return {
          footer: {
            rect: {
              x: Math.round(footerRect.x),
              y: Math.round(footerRect.y),
              width: Math.round(footerRect.width),
              height: Math.round(footerRect.height)
            },
            styles: {
              display: footerStyles.display,
              flexDirection: footerStyles.flexDirection,
              justifyContent: footerStyles.justifyContent,
              alignItems: footerStyles.alignItems,
              padding: footerStyles.padding,
              margin: footerStyles.margin,
              fontSize: footerStyles.fontSize,
              lineHeight: footerStyles.lineHeight
            },
            text: footerText.substring(0, 200) + (footerText.length > 200 ? '...' : ''),
            textLines: textLines.length
          },
          links: links,
          issues: {
            tooTall: footerRect.height > window.innerHeight * 0.3,
            overflowX: footerRect.width > window.innerWidth,
            linksOverlapping: links.some((link, i) => 
              links.some((other, j) => 
                i !== j && 
                Math.abs(link.rect.y - other.rect.y) < 10 && 
                Math.abs(link.rect.x - other.rect.x) < link.rect.width
              )
            ),
            textTooSmall: parseFloat(footerStyles.fontSize) < 14,
            tooManyLines: textLines.length > 5
          }
        };
      });
      
      if (footerAnalysis.error) {
        console.log("❌", footerAnalysis.error);
        continue;
      }
      
      console.log("📊 푸터 정보:");
      console.log(`   크기: ${footerAnalysis.footer.rect.width}x${footerAnalysis.footer.rect.height}`);
      console.log(`   위치: (${footerAnalysis.footer.rect.x}, ${footerAnalysis.footer.rect.y})`);
      console.log(`   레이아웃: ${footerAnalysis.footer.styles.display}, flex-direction: ${footerAnalysis.footer.styles.flexDirection}`);
      console.log(`   패딩: ${footerAnalysis.footer.styles.padding}`);
      console.log(`   텍스트 줄 수: ${footerAnalysis.footer.textLines}`);
      console.log(`   링크 개수: ${footerAnalysis.links.length}`);
      
      console.log("🔍 문제점 분석:");
      Object.entries(footerAnalysis.issues).forEach(([issue, hasIssue]) => {
        const emoji = hasIssue ? "❌" : "✅";
        const descriptions = {
          tooTall: "푸터 높이가 너무 높음 (화면의 30% 이상)",
          overflowX: "가로 스크롤 발생",
          linksOverlapping: "링크들이 겹쳐서 표시됨",
          textTooSmall: "텍스트가 너무 작음 (14px 미만)",
          tooManyLines: "텍스트 줄이 너무 많음 (5줄 이상)"
        };
        console.log(`   ${emoji} ${descriptions[issue]}`);
      });
      
      if (footerAnalysis.links.length > 0) {
        console.log("🔗 링크 배치:");
        footerAnalysis.links.forEach((link, i) => {
          console.log(`   ${i+1}. "${link.text}" - 위치: (${link.rect.x}, ${link.rect.y}), 크기: ${link.rect.width}x${link.rect.height}`);
        });
      }
      
      // 스크린샷 촬영
      await page.screenshot({ 
        path: `/var/www/html/topmkt/footer_${size.name.replace(' ', '_')}_${size.width}px.png`,
        fullPage: true
      });
    }
    
    // 전체 페이지 구조에서 푸터 위치 확인
    console.log("\n📄 전체 페이지에서의 푸터 위치 분석:");
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto(`https://www.topmktx.com/?full_page_test=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    const pageStructure = await page.evaluate(() => {
      const body = document.body;
      const header = document.querySelector('header');
      const main = document.querySelector('main, .main-content');
      const footer = document.querySelector('footer');
      
      const getElementInfo = (element, name) => {
        if (!element) return { name, exists: false };
        const rect = element.getBoundingClientRect();
        return {
          name,
          exists: true,
          rect: {
            y: Math.round(rect.y),
            height: Math.round(rect.height),
            bottom: Math.round(rect.bottom)
          }
        };
      };
      
      return {
        body: getElementInfo(body, 'body'),
        header: getElementInfo(header, 'header'),
        main: getElementInfo(main, 'main'),
        footer: getElementInfo(footer, 'footer'),
        windowHeight: window.innerHeight,
        documentHeight: document.documentElement.scrollHeight
      };
    });
    
    console.log("📋 페이지 구조:");
    ['header', 'main', 'footer'].forEach(section => {
      const info = pageStructure[section];
      if (info.exists) {
        const percentage = Math.round((info.rect.height / pageStructure.windowHeight) * 100);
        console.log(`   ${section}: y=${info.rect.y}, 높이=${info.rect.height}px (화면의 ${percentage}%)`);
      } else {
        console.log(`   ${section}: ❌ 요소 없음`);
      }
    });
    
    console.log(`\n📏 전체 높이 비교:`);
    console.log(`   뷰포트: ${pageStructure.windowHeight}px`);
    console.log(`   문서 전체: ${pageStructure.documentHeight}px`);
    
    if (pageStructure.footer.exists) {
      const footerPosition = pageStructure.footer.rect.y;
      const expectedPosition = pageStructure.windowHeight - pageStructure.footer.rect.height;
      const isSticky = Math.abs(footerPosition - expectedPosition) < 50;
      
      console.log(`   푸터 위치: y=${footerPosition} (${isSticky ? '✅ 하단 고정' : '❌ 위치 이상'})`);
    }
    
    console.log("\n📸 모든 스크린샷 촬영 완료!");
    
  } catch (error) {
    console.error("❌ 분석 오류:", error);
  } finally {
    await browser.close();
  }
})();