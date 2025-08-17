import { chromium } from "playwright";

(async () => {
  console.log("🔥 ULTRA THINK: 실제 시각적 겹침 문제 진단...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 375, height: 812 },
      userAgent: "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1"
    });
    
    const page = await context.newPage();
    
    // DevLogin
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    // 메인 페이지 (캐시 우회)
    await page.goto(`https://www.topmktx.com/?nocache=${Date.now()}`);
    await page.waitForTimeout(3000);
    await page.waitForLoadState('networkidle');
    
    // 헤더 영역의 모든 요소들과 그 위치, 겹침 상황 분석
    const overlapAnalysis = await page.evaluate(() => {
      const header = document.querySelector('.main-header');
      const headerContent = document.querySelector('.header-content');
      const headerLeft = document.querySelector('.header-left');
      const navAuth = document.querySelector('.nav-auth');
      const hamburger = document.getElementById('mobile-hamburger');
      
      const getDetailedInfo = (element, name) => {
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
            left: Math.round(rect.left),
            right: Math.round(rect.right),
            top: Math.round(rect.top),
            bottom: Math.round(rect.bottom)
          },
          styles: {
            display: styles.display,
            visibility: styles.visibility,
            opacity: styles.opacity,
            position: styles.position,
            zIndex: styles.zIndex,
            left: styles.left,
            right: styles.right,
            top: styles.top,
            bottom: styles.bottom,
            transform: styles.transform,
            marginLeft: styles.marginLeft,
            marginRight: styles.marginRight,
            order: styles.order
          }
        };
      };
      
      const headerInfo = getDetailedInfo(header, 'header');
      const headerContentInfo = getDetailedInfo(headerContent, 'headerContent');
      const headerLeftInfo = getDetailedInfo(headerLeft, 'headerLeft');
      const navAuthInfo = getDetailedInfo(navAuth, 'navAuth');
      const hamburgerInfo = getDetailedInfo(hamburger, 'hamburger');
      
      // 겹침 분석
      const overlapCheck = {
        headerLeftVsHamburger: false,
        navAuthVsHamburger: false,
        details: []
      };
      
      if (headerLeftInfo.exists && hamburgerInfo.exists) {
        const leftRight = headerLeftInfo.rect.right;
        const hamburgerLeft = hamburgerInfo.rect.left;
        
        if (leftRight > hamburgerLeft) {
          overlapCheck.headerLeftVsHamburger = true;
          overlapCheck.details.push(`headerLeft 우측(${leftRight}) > hamburger 좌측(${hamburgerLeft}) - 겹침!`);
        }
      }
      
      if (navAuthInfo.exists && hamburgerInfo.exists) {
        const navLeft = navAuthInfo.rect.left;
        const hamburgerRight = hamburgerInfo.rect.right;
        
        if (navLeft < hamburgerRight) {
          overlapCheck.navAuthVsHamburger = true;
          overlapCheck.details.push(`navAuth 좌측(${navLeft}) < hamburger 우측(${hamburgerRight}) - 겹침!`);
        }
      }
      
      return {
        screenInfo: {
          width: window.innerWidth,
          height: window.innerHeight
        },
        elements: {
          header: headerInfo,
          headerContent: headerContentInfo,
          headerLeft: headerLeftInfo,
          navAuth: navAuthInfo,
          hamburger: hamburgerInfo
        },
        overlapCheck
      };
    });
    
    console.log("📱 화면 정보:", overlapAnalysis.screenInfo);
    
    console.log("\\n🔍 헤더 요소들 상세 분석:");
    Object.entries(overlapAnalysis.elements).forEach(([key, info]) => {
      if (info.exists) {
        console.log(`\\n📦 ${info.name}:`);
        console.log(`   위치: (${info.rect.x}, ${info.rect.y}) 크기: ${info.rect.width}x${info.rect.height}`);
        console.log(`   영역: left:${info.rect.left} → right:${info.rect.right}, top:${info.rect.top} → bottom:${info.rect.bottom}`);
        console.log(`   스타일: display=${info.styles.display}, position=${info.styles.position}, zIndex=${info.styles.zIndex}`);
        console.log(`   순서: order=${info.styles.order}, marginLeft=${info.styles.marginLeft}, marginRight=${info.styles.marginRight}`);
      } else {
        console.log(`\\n❌ ${key}: 존재하지 않음`);
      }
    });
    
    console.log("\\n⚠️ 겹침 분석 결과:");
    console.log(`headerLeft vs hamburger 겹침: ${overlapAnalysis.overlapCheck.headerLeftVsHamburger}`);
    console.log(`navAuth vs hamburger 겹침: ${overlapAnalysis.overlapCheck.navAuthVsHamburger}`);
    
    if (overlapAnalysis.overlapCheck.details.length > 0) {
      console.log("\\n🚨 겹침 상세 정보:");
      overlapAnalysis.overlapCheck.details.forEach(detail => {
        console.log(`   - ${detail}`);
      });
    }
    
    // 헤더 영역 전체 스크린샷
    await page.screenshot({ 
      path: "/var/www/html/topmkt/visual_overlap_diagnosis.png",
      fullPage: false,
      clip: { x: 0, y: 0, width: 375, height: 80 }
    });
    
    console.log("\\n📸 시각적 진단 스크린샷 촬영 완료");
    
    // 햄버거 아이콘이 실제로 어떤 요소에 가려져 있는지 확인
    const elementAtHamburgerPosition = await page.evaluate(() => {
      const hamburger = document.getElementById('mobile-hamburger');
      if (!hamburger) return null;
      
      const rect = hamburger.getBoundingClientRect();
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 2;
      
      // 햄버거 위치에서 실제로 클릭되는 요소 확인
      const elementAtCenter = document.elementFromPoint(centerX, centerY);
      
      return {
        hamburgerCenter: { x: centerX, y: centerY },
        elementAtCenter: elementAtCenter ? {
          tagName: elementAtCenter.tagName,
          id: elementAtCenter.id,
          className: elementAtCenter.className,
          innerHTML: elementAtCenter.innerHTML.length > 50 ? elementAtCenter.innerHTML.substring(0, 50) + '...' : elementAtCenter.innerHTML
        } : null
      };
    });
    
    console.log("\\n🎯 햄버거 위치에서 실제 클릭되는 요소:");
    if (elementAtHamburgerPosition.elementAtCenter) {
      console.log(`   중심점: (${elementAtHamburgerPosition.hamburgerCenter.x}, ${elementAtHamburgerPosition.hamburgerCenter.y})`);
      console.log(`   실제 요소: ${elementAtHamburgerPosition.elementAtCenter.tagName}#${elementAtHamburgerPosition.elementAtCenter.id}.${elementAtHamburgerPosition.elementAtCenter.className}`);
      
      if (elementAtHamburgerPosition.elementAtCenter.id !== 'mobile-hamburger') {
        console.log("🚨 햄버거 아이콘이 다른 요소에 가려져 있습니다!");
      }
    }
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();