import { chromium } from "playwright";

(async () => {
  console.log("🚀 푸터 최적화 검증 테스트...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext();
    const page = await context.newPage();
    
    // DevLogin 및 캐시 무시
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    // 강의 페이지에서 새로운 푸터 확인 (캐시 무시)
    const timestamp = Date.now();
    await page.goto(`https://www.topmktx.com/lectures?footer_check=${timestamp}&v=${timestamp}`, {
      waitUntil: 'networkidle'
    });
    await page.waitForTimeout(3000);
    
    // 모바일 사이즈에서 확인
    await page.setViewportSize({ width: 375, height: 812 });
    await page.waitForTimeout(2000);
    
    const footerAnalysis = await page.evaluate(() => {
      // 푸터 요소들 찾기
      const footer = document.querySelector('footer');
      const mobileFooter = document.querySelector('.footer-mobile');
      const desktopFooter = document.querySelector('.footer-desktop');
      
      if (!footer) {
        return { error: 'footer 요소를 찾을 수 없습니다.' };
      }
      
      // 스타일 확인
      const footerStyles = window.getComputedStyle(footer);
      const mobileStyles = mobileFooter ? window.getComputedStyle(mobileFooter) : null;
      const desktopStyles = desktopFooter ? window.getComputedStyle(desktopFooter) : null;
      
      // 푸터 구조 확인
      const footerRect = footer.getBoundingClientRect();
      
      // 실제 표시되는 요소들
      const visibleLinks = Array.from(footer.querySelectorAll('a')).filter(link => {
        const rect = link.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
      });
      
      return {
        footer: {
          exists: true,
          className: footer.className,
          rect: {
            width: Math.round(footerRect.width),
            height: Math.round(footerRect.height),
            y: Math.round(footerRect.y)
          },
          styles: {
            background: footerStyles.background,
            padding: footerStyles.padding
          }
        },
        mobileFooter: {
          exists: !!mobileFooter,
          display: mobileStyles?.display || 'none',
          className: mobileFooter?.className || ''
        },
        desktopFooter: {
          exists: !!desktopFooter,
          display: desktopStyles?.display || 'none',
          className: desktopFooter?.className || ''
        },
        visibleLinks: visibleLinks.map(link => ({
          text: link.textContent.trim(),
          rect: {
            x: Math.round(link.getBoundingClientRect().x),
            y: Math.round(link.getBoundingClientRect().y),
            width: Math.round(link.getBoundingClientRect().width),
            height: Math.round(link.getBoundingClientRect().height)
          }
        })),
        mediaQueryTest: {
          windowWidth: window.innerWidth,
          expectedMobile: window.innerWidth <= 768
        }
      };
    });
    
    console.log("📊 푸터 분석 결과:");
    console.log(`   푸터 클래스: ${footerAnalysis.footer.className}`);
    console.log(`   푸터 크기: ${footerAnalysis.footer.rect.width}x${footerAnalysis.footer.rect.height}`);
    console.log(`   모바일 푸터 존재: ${footerAnalysis.mobileFooter.exists ? '✅' : '❌'}`);
    console.log(`   모바일 푸터 표시: ${footerAnalysis.mobileFooter.display}`);
    console.log(`   데스크톱 푸터 존재: ${footerAnalysis.desktopFooter.exists ? '✅' : '❌'}`);
    console.log(`   데스크톱 푸터 표시: ${footerAnalysis.desktopFooter.display}`);
    console.log(`   화면 너비: ${footerAnalysis.mediaQueryTest.windowWidth}px`);
    console.log(`   모바일 예상: ${footerAnalysis.mediaQueryTest.expectedMobile ? '✅' : '❌'}`);
    
    console.log("\\n🔗 실제 표시 링크들:");
    footerAnalysis.visibleLinks.forEach((link, i) => {
      console.log(`   ${i+1}. "${link.text}" - 위치: (${link.rect.x}, ${link.rect.y}), 크기: ${link.rect.width}x${link.rect.height}`);
    });
    
    // CSS 내용 확인
    const cssCheck = await page.evaluate(() => {
      const styles = Array.from(document.styleSheets);
      let hasModernFooterCSS = false;
      let hasMediaQuery = false;
      
      try {
        for (const sheet of styles) {
          if (sheet.cssRules) {
            for (const rule of sheet.cssRules) {
              if (rule.selectorText?.includes('.modern-footer')) {
                hasModernFooterCSS = true;
              }
              if (rule.media && rule.media.mediaText.includes('max-width: 768px')) {
                hasMediaQuery = true;
              }
            }
          }
        }
      } catch (e) {
        // CORS 오류 무시
      }
      
      return { hasModernFooterCSS, hasMediaQuery };
    });
    
    console.log("\\n🎨 CSS 확인:");
    console.log(`   modern-footer CSS: ${cssCheck.hasModernFooterCSS ? '✅' : '❌'}`);
    console.log(`   미디어 쿼리: ${cssCheck.hasMediaQuery ? '✅' : '❌'}`);
    
    // 스크린샷 촬영
    await page.screenshot({ 
      path: "/var/www/html/topmkt/footer_verification_mobile.png",
      fullPage: true
    });
    
    // 데스크톱에서도 확인
    await page.setViewportSize({ width: 1200, height: 800 });
    await page.waitForTimeout(2000);
    
    const desktopAnalysis = await page.evaluate(() => {
      const mobileFooter = document.querySelector('.footer-mobile');
      const desktopFooter = document.querySelector('.footer-desktop');
      
      return {
        mobile: mobileFooter ? window.getComputedStyle(mobileFooter).display : 'none',
        desktop: desktopFooter ? window.getComputedStyle(desktopFooter).display : 'none',
        windowWidth: window.innerWidth
      };
    });
    
    console.log("\\n💻 데스크톱 (1200px) 확인:");
    console.log(`   모바일 푸터: ${desktopAnalysis.mobile}`);
    console.log(`   데스크톱 푸터: ${desktopAnalysis.desktop}`);
    
    await page.screenshot({ 
      path: "/var/www/html/topmkt/footer_verification_desktop.png",
      fullPage: false,
      clip: { x: 0, y: 600, width: 1200, height: 400 }
    });
    
    // 최종 결론
    console.log("\\n🎯 최종 결론:");
    if (footerAnalysis.mobileFooter.exists && footerAnalysis.desktopFooter.exists) {
      if (footerAnalysis.footer.rect.height < 250) {
        console.log("🎉 푸터 최적화 성공!");
        console.log("   ✅ 모바일/데스크톱 푸터 분리 구현");
        console.log("   ✅ 푸터 높이 최적화 (250px 미만)");
      } else {
        console.log("⚠️ 푸터 높이가 여전히 큼 (250px 이상)");
      }
    } else {
      console.log("❌ 푸터 구조에 문제가 있습니다.");
      console.log("   현재 footer.php 파일이 제대로 로드되지 않았을 수 있습니다.");
    }
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();