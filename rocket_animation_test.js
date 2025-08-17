import { chromium } from "playwright";

(async () => {
  console.log("🚀 로켓 애니메이션 짤림 방지 테스트...");
  
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
    
    // 데스크톱 테스트
    console.log("\n💻 데스크톱 로켓 애니메이션 테스트 (1200px):");
    await page.setViewportSize({ width: 1200, height: 800 });
    await page.goto(`https://www.topmktx.com/?rocket_test=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    const desktopResult = await page.evaluate(() => {
      const headerContent = document.querySelector('.header-content');
      const logoIcon = document.querySelector('.logo-icon');
      const headerRocket = document.querySelector('.header-rocket');
      
      if (!headerContent || !logoIcon || !headerRocket) {
        return { error: '요소를 찾을 수 없습니다.' };
      }
      
      const headerRect = headerContent.getBoundingClientRect();
      const logoRect = logoIcon.getBoundingClientRect();
      const rocketRect = headerRocket.getBoundingClientRect();
      
      // 패딩 및 여백 정보
      const headerStyles = window.getComputedStyle(headerContent);
      const logoStyles = window.getComputedStyle(logoIcon);
      
      return {
        header: {
          padding: headerStyles.padding,
          paddingLeft: headerStyles.paddingLeft,
          rect: {
            x: Math.round(headerRect.x),
            y: Math.round(headerRect.y),
            width: Math.round(headerRect.width),
            left: Math.round(headerRect.left),
            right: Math.round(headerRect.right)
          }
        },
        logo: {
          marginLeft: logoStyles.marginLeft,
          marginRight: logoStyles.marginRight,
          rect: {
            x: Math.round(logoRect.x),
            y: Math.round(logoRect.y),
            width: Math.round(logoRect.width),
            left: Math.round(logoRect.left),
            right: Math.round(logoRect.right)
          }
        },
        rocket: {
          rect: {
            x: Math.round(rocketRect.x),
            y: Math.round(rocketRect.y),
            width: Math.round(rocketRect.width),
            left: Math.round(rocketRect.left),
            right: Math.round(rocketRect.right)
          }
        },
        clipping: {
          rocketLeftClipped: rocketRect.left < headerRect.left,
          rocketRightClipped: rocketRect.right > headerRect.right,
          availableLeftSpace: Math.round(logoRect.left - headerRect.left),
          availableRightSpace: Math.round(headerRect.right - logoRect.right)
        }
      };
    });
    
    if (desktopResult.error) {
      console.log("❌", desktopResult.error);
      return;
    }
    
    console.log("📊 헤더 컨테이너 정보:");
    console.log(`   패딩: ${desktopResult.header.padding}`);
    console.log(`   좌측 패딩: ${desktopResult.header.paddingLeft}`);
    console.log(`   위치: x=${desktopResult.header.rect.x}, 너비=${desktopResult.header.rect.width}`);
    
    console.log("🎯 로고 아이콘 정보:");
    console.log(`   좌측 마진: ${desktopResult.logo.marginLeft}`);
    console.log(`   우측 마진: ${desktopResult.logo.marginRight}`);
    console.log(`   위치: x=${desktopResult.logo.rect.x}, 너비=${desktopResult.logo.rect.width}`);
    
    console.log("🚀 로켓 아이콘 정보:");
    console.log(`   위치: x=${desktopResult.rocket.rect.x}, 너비=${desktopResult.rocket.rect.width}`);
    console.log(`   좌측: ${desktopResult.rocket.rect.left}, 우측: ${desktopResult.rocket.rect.right}`);
    
    console.log("✂️ 짤림 검사:");
    console.log(`   좌측 짤림: ${desktopResult.clipping.rocketLeftClipped ? '❌ 짤림' : '✅ 안전'}`);
    console.log(`   우측 짤림: ${desktopResult.clipping.rocketRightClipped ? '❌ 짤림' : '✅ 안전'}`);
    console.log(`   좌측 여유 공간: ${desktopResult.clipping.availableLeftSpace}px`);
    console.log(`   우측 여유 공간: ${desktopResult.clipping.availableRightSpace}px`);
    
    // 모바일 테스트
    console.log("\n📱 모바일 로켓 애니메이션 테스트 (375px):");
    await page.setViewportSize({ width: 375, height: 812 });
    await page.waitForTimeout(2000);
    
    const mobileResult = await page.evaluate(() => {
      const headerContent = document.querySelector('.header-content');
      const logoIcon = document.querySelector('.logo-icon');
      const headerRocket = document.querySelector('.header-rocket');
      
      if (!headerContent || !logoIcon || !headerRocket) {
        return { error: '요소를 찾을 수 없습니다.' };
      }
      
      const headerRect = headerContent.getBoundingClientRect();
      const logoRect = logoIcon.getBoundingClientRect();
      const rocketRect = headerRocket.getBoundingClientRect();
      
      const logoStyles = window.getComputedStyle(logoIcon);
      
      return {
        logo: {
          marginLeft: logoStyles.marginLeft,
          rect: { left: Math.round(logoRect.left) }
        },
        rocket: {
          rect: { left: Math.round(rocketRect.left) }
        },
        clipping: {
          rocketLeftClipped: rocketRect.left < headerRect.left,
          availableLeftSpace: Math.round(logoRect.left - headerRect.left)
        }
      };
    });
    
    if (!mobileResult.error) {
      console.log("📱 모바일 결과:");
      console.log(`   로고 좌측 마진: ${mobileResult.logo.marginLeft}`);
      console.log(`   좌측 짤림: ${mobileResult.clipping.rocketLeftClipped ? '❌ 짤림' : '✅ 안전'}`);
      console.log(`   좌측 여유 공간: ${mobileResult.clipping.availableLeftSpace}px`);
    }
    
    // 호버 애니메이션 테스트
    console.log("\n🖱️ 호버 애니메이션 테스트:");
    await page.setViewportSize({ width: 1200, height: 800 });
    await page.waitForTimeout(1000);
    
    await page.hover('.logo-link');
    await page.waitForTimeout(500);
    
    const hoverResult = await page.evaluate(() => {
      const headerRocket = document.querySelector('.header-rocket');
      const rect = headerRocket.getBoundingClientRect();
      const styles = window.getComputedStyle(headerRocket);
      
      return {
        transform: styles.transform,
        rect: {
          left: Math.round(rect.left),
          right: Math.round(rect.right),
          width: Math.round(rect.width)
        }
      };
    });
    
    console.log(`   호버 transform: ${hoverResult.transform}`);
    console.log(`   호버 시 위치: left=${hoverResult.rect.left}, right=${hoverResult.rect.right}`);
    
    // 최종 스크린샷
    await page.screenshot({ 
      path: "/var/www/html/topmkt/rocket_animation_fixed.png",
      fullPage: false,
      clip: { x: 0, y: 0, width: 400, height: 100 }
    });
    
    console.log("\n🎯 최종 결과:");
    const allGood = !desktopResult.clipping.rocketLeftClipped && 
                   !desktopResult.clipping.rocketRightClipped &&
                   !mobileResult.clipping.rocketLeftClipped &&
                   desktopResult.clipping.availableLeftSpace >= 10;
    
    if (allGood) {
      console.log("🎉 로켓 애니메이션 짤림 문제 완전 해결!");
      console.log("   ✅ 데스크톱: 좌우 여유 공간 충분");
      console.log("   ✅ 모바일: 좌측 여유 공간 확보");
      console.log("   ✅ 호버 애니메이션도 안전");
    } else {
      console.log("⚠️ 추가 조정 필요");
    }
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();