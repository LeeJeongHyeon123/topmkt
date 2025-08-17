import { chromium } from "playwright";

(async () => {
  console.log("📏 헤더 정렬 분석...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 375, height: 812 }
    });
    
    const page = await context.newPage();
    
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    await page.goto(`https://www.topmktx.com/?alignment_check=${Date.now()}`);
    await page.waitForTimeout(3000);
    await page.waitForLoadState('networkidle');
    
    const alignmentData = await page.evaluate(() => {
      const header = document.querySelector('.main-header');
      const headerContent = document.querySelector('.header-content');
      const logo = document.querySelector('.logo');
      const hamburger = document.getElementById('mobile-hamburger');
      
      const getElementInfo = (el, name) => {
        if (!el) return { name, exists: false };
        const rect = el.getBoundingClientRect();
        const styles = window.getComputedStyle(el);
        return {
          name,
          exists: true,
          rect: {
            x: Math.round(rect.x),
            y: Math.round(rect.y),
            width: Math.round(rect.width),
            height: Math.round(rect.height),
            top: Math.round(rect.top),
            bottom: Math.round(rect.bottom)
          },
          styles: {
            height: styles.height,
            lineHeight: styles.lineHeight,
            paddingTop: styles.paddingTop,
            paddingBottom: styles.paddingBottom,
            top: styles.top,
            transform: styles.transform
          }
        };
      };
      
      return {
        header: getElementInfo(header, 'header'),
        headerContent: getElementInfo(headerContent, 'headerContent'),
        logo: getElementInfo(logo, 'logo'),
        hamburger: getElementInfo(hamburger, 'hamburger')
      };
    });
    
    console.log("📏 헤더 요소들 정렬 분석:");
    
    Object.values(alignmentData).forEach(info => {
      if (info.exists) {
        const centerY = info.rect.y + info.rect.height / 2;
        console.log(`\\n${info.name}:`);
        console.log(`   위치: y=${info.rect.y}, 높이=${info.rect.height}`);
        console.log(`   중심점: y=${Math.round(centerY)}`);
        console.log(`   top 스타일: ${info.styles.top}`);
        if (info.name === 'hamburger') {
          console.log(`   transform: ${info.styles.transform}`);
        }
      }
    });
    
    // 정렬 권장사항 계산
    if (alignmentData.header.exists && alignmentData.logo.exists) {
      const headerCenterY = alignmentData.header.rect.y + alignmentData.header.rect.height / 2;
      const logoCenterY = alignmentData.logo.rect.y + alignmentData.logo.rect.height / 2;
      const hamburgerSize = 44; // 햄버거 크기
      const recommendedTop = headerCenterY - hamburgerSize / 2;
      
      console.log(`\\n🎯 정렬 권장사항:`);
      console.log(`   헤더 중심점: y=${Math.round(headerCenterY)}`);
      console.log(`   로고 중심점: y=${Math.round(logoCenterY)}`);
      console.log(`   햄버거 권장 top: ${Math.round(recommendedTop)}px`);
    }
    
  } catch (error) {
    console.error("❌ 오류:", error);
  } finally {
    await browser.close();
  }
})();