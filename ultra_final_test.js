import { chromium } from "playwright";

(async () => {
  console.log("🔥 ULTRA THINK: 최종 햄버거 아이콘 테스트...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext({
      viewport: { width: 375, height: 812 },
      userAgent: "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
      deviceScaleFactor: 3
    });
    
    const page = await context.newPage();
    
    // 완전한 캐시 우회
    await page.route('**/*', route => {
      const headers = route.request().headers();
      headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
      headers['Pragma'] = 'no-cache';
      headers['Expires'] = '0';
      route.continue({ headers });
    });
    
    console.log("🔑 DevLogin Helper로 로그인...");
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    console.log("🏠 메인 페이지로 이동 (캐시 완전 우회)...");
    const cacheBreaker = Date.now();
    await page.goto(`https://www.topmktx.com/?v=${cacheBreaker}&nocache=${cacheBreaker}`);
    await page.waitForTimeout(3000);
    await page.waitForLoadState('networkidle');
    
    // 햄버거 요소들 완전 분석
    const hamburgerAnalysis = await page.evaluate(() => {
      const allElements = [];
      
      // 모든 햄버거 관련 요소 찾기
      const hamburgerElements = document.querySelectorAll('*[id*="hamburger"], *[class*="hamburger"], *[id*="mobile"]');
      
      hamburgerElements.forEach(el => {
        const rect = el.getBoundingClientRect();
        const styles = window.getComputedStyle(el);
        
        allElements.push({
          tagName: el.tagName,
          id: el.id,
          className: el.className,
          rect: {
            x: rect.x,
            y: rect.y,
            width: rect.width,
            height: rect.height,
            visible: rect.width > 0 && rect.height > 0
          },
          styles: {
            display: styles.display,
            visibility: styles.visibility,
            opacity: styles.opacity,
            zIndex: styles.zIndex,
            position: styles.position
          },
          clickable: el.offsetWidth > 0 && el.offsetHeight > 0,
          innerHTML: el.innerHTML.length > 100 ? el.innerHTML.substring(0, 100) + '...' : el.innerHTML
        });
      });
      
      return {
        screenWidth: window.innerWidth,
        screenHeight: window.innerHeight,
        mediaQuery: {
          maxWidth768: window.matchMedia('(max-width: 768px)').matches,
          minWidth769: window.matchMedia('(min-width: 769px)').matches
        },
        totalElements: allElements.length,
        elements: allElements
      };
    });
    
    console.log("🔍 ULTRA 분석 결과:");
    console.log(`화면 크기: ${hamburgerAnalysis.screenWidth}x${hamburgerAnalysis.screenHeight}`);
    console.log(`미디어 쿼리: max-width 768px = ${hamburgerAnalysis.mediaQuery.maxWidth768}`);
    console.log(`전체 햄버거 관련 요소: ${hamburgerAnalysis.totalElements}개`);
    
    hamburgerAnalysis.elements.forEach((el, i) => {
      console.log(`\\n${i+1}. ${el.tagName}#${el.id}.${el.className}:`);
      console.log(`   위치: (${el.rect.x}, ${el.rect.y}) 크기: ${el.rect.width}x${el.rect.height}`);
      console.log(`   보임: ${el.rect.visible}, 클릭가능: ${el.clickable}`);
      console.log(`   스타일: display=${el.styles.display}, visibility=${el.styles.visibility}, opacity=${el.styles.opacity}`);
    });
    
    // mobile-hamburger 버튼만 확인
    const mainHamburger = hamburgerAnalysis.elements.find(el => el.id === 'mobile-hamburger');
    
    if (mainHamburger && mainHamburger.clickable) {
      console.log("\\n🍔 메인 햄버거 버튼 클릭 테스트...");
      
      try {
        await page.click('#mobile-hamburger');
        await page.waitForTimeout(1000);
        
        const modalStatus = await page.evaluate(() => {
          const modal = document.getElementById('mobileMenuModal');
          return modal ? {
            exists: true,
            active: modal.classList.contains('active'),
            display: window.getComputedStyle(modal).display,
            visibility: window.getComputedStyle(modal).visibility,
            opacity: window.getComputedStyle(modal).opacity
          } : { exists: false };
        });
        
        console.log("📱 모달 상태:", JSON.stringify(modalStatus, null, 2));
        
        if (modalStatus.active) {
          console.log("✅ 햄버거 메뉴 완벽 작동!");
        } else {
          console.log("❌ 햄버거 메뉴 클릭했지만 모달이 열리지 않음");
        }
        
      } catch (error) {
        console.error("❌ 햄버거 버튼 클릭 실패:", error.message);
      }
    } else {
      console.log("❌ 메인 햄버거 버튼을 찾을 수 없거나 클릭할 수 없음");
    }
    
    // 스크린샷 촬영
    await page.screenshot({ 
      path: "/var/www/html/topmkt/ultra_final_result.png",
      fullPage: false,
      clip: { x: 300, y: 0, width: 75, height: 70 }
    });
    
    console.log("\\n🎯 최종 결과:");
    if (mainHamburger) {
      console.log(`✅ 햄버거 아이콘 존재: ${mainHamburger.rect.width}x${mainHamburger.rect.height}`);
      console.log(`✅ 표시 상태: ${mainHamburger.styles.display}, 가시성: ${mainHamburger.styles.visibility}`);
      console.log(`✅ 클릭 가능: ${mainHamburger.clickable}`);
    } else {
      console.log("❌ 햄버거 아이콘 없음");
    }
    
  } catch (error) {
    console.error("❌ 오류 발생:", error);
  } finally {
    await browser.close();
  }
})();