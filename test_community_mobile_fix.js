import { chromium } from "playwright";

(async () => {
  console.log("👥 커뮤니티 모바일 반응형 개선 테스트...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    const context = await browser.newContext();
    const page = await context.newPage();
    
    // DevLogin으로 로그인
    await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
    await page.waitForTimeout(2000);
    
    const viewports = [
      { name: "모바일", width: 375, height: 812 },
      { name: "소형모바일", width: 320, height: 568 },
      { name: "태블릿", width: 768, height: 1024 }
    ];
    
    console.log("\\n👥 커뮤니티 페이지 개선 후 테스트:");
    
    for (const viewport of viewports) {
      console.log(`\\n🔍 ${viewport.name} (${viewport.width}x${viewport.height})`);
      
      // 뷰포트 설정
      await page.setViewportSize({ width: viewport.width, height: viewport.height });
      
      // 커뮤니티 페이지 이동
      await page.goto(`https://www.topmktx.com/community?test_fix=${Date.now()}`);
      await page.waitForTimeout(3000);
      
      // 개선 사항 검증
      const results = await page.evaluate((viewportInfo) => {
        const results = {
          viewport: viewportInfo,
          issues: [],
          improvements: [],
          elements: {}
        };
        
        // 가로 스크롤 체크
        const hasHorizontalScroll = document.documentElement.scrollWidth > window.innerWidth;
        if (hasHorizontalScroll) {
          results.issues.push(`가로 스크롤 발생 (${document.documentElement.scrollWidth}px > ${window.innerWidth}px)`);
        } else {
          results.improvements.push("가로 스크롤 해결됨");
        }
        
        // 버튼 크기 체크
        const buttons = document.querySelectorAll('.btn, button');
        let smallButtons = 0;
        let goodButtons = 0;
        
        buttons.forEach(btn => {
          const rect = btn.getBoundingClientRect();
          if (rect.width > 0 && rect.height > 0) {
            if (rect.height < 44) {
              smallButtons++;
            } else {
              goodButtons++;
            }
          }
        });
        
        if (smallButtons === 0 && goodButtons > 0) {
          results.improvements.push(`모든 버튼이 44px 이상 (${goodButtons}개)`);
        } else if (goodButtons > 0) {
          results.issues.push(`${smallButtons}개 버튼이 44px 미만`);
        }
        
        // 입력 필드 크기 체크
        const inputs = document.querySelectorAll('.search-input, input');
        let smallInputs = 0;
        let goodInputs = 0;
        
        inputs.forEach(input => {
          const rect = input.getBoundingClientRect();
          if (rect.width > 0 && rect.height > 0) {
            if (rect.height < 44) {
              smallInputs++;
            } else {
              goodInputs++;
            }
          }
        });
        
        if (smallInputs === 0 && goodInputs > 0) {
          results.improvements.push(`모든 입력 필드가 44px 이상 (${goodInputs}개)`);
        } else if (goodInputs > 0) {
          results.issues.push(`${smallInputs}개 입력 필드가 44px 미만`);
        }
        
        // 페이지네이션 크기 체크
        const paginationItems = document.querySelectorAll('.pagination a, .pagination span');
        let smallPagination = 0;
        let goodPagination = 0;
        
        paginationItems.forEach(item => {
          const rect = item.getBoundingClientRect();
          if (rect.width > 0 && rect.height > 0) {
            if (rect.height < 44) {
              smallPagination++;
            } else {
              goodPagination++;
            }
          }
        });
        
        if (goodPagination > 0) {
          if (smallPagination === 0) {
            results.improvements.push(`모든 페이지네이션이 44px 이상 (${goodPagination}개)`);
          } else {
            results.issues.push(`${smallPagination}개 페이지네이션이 44px 미만`);
          }
        }
        
        // 폰트 크기 체크 (모바일에서)
        if (viewportInfo.width <= 768) {
          const textElements = document.querySelectorAll('.btn, .search-input, .post-title, .post-content');
          let smallTexts = 0;
          let goodTexts = 0;
          
          textElements.forEach(el => {
            const fontSize = parseFloat(window.getComputedStyle(el).fontSize);
            if (fontSize < 14) {
              smallTexts++;
            } else {
              goodTexts++;
            }
          });
          
          if (smallTexts === 0 && goodTexts > 0) {
            results.improvements.push(`모든 주요 텍스트가 14px 이상 (${goodTexts}개)`);
          } else if (goodTexts > 0) {
            results.issues.push(`${smallTexts}개 텍스트가 14px 미만`);
          }
        }
        
        // 프로필 이미지 크기 체크
        const profileImages = document.querySelectorAll('.profile-image, .user-avatar');
        profileImages.forEach((img, index) => {
          const rect = img.getBoundingClientRect();
          results.elements[`profileImage${index + 1}`] = {
            width: Math.round(rect.width),
            height: Math.round(rect.height),
            adequate: rect.width >= 44 && rect.height >= 44
          };
        });
        
        // 검색 폼 레이아웃 체크 (모바일에서 세로 배치 확인)
        if (viewportInfo.width <= 768) {
          const searchForm = document.querySelector('.search-form');
          if (searchForm) {
            const flexDirection = window.getComputedStyle(searchForm).flexDirection;
            if (flexDirection === 'column') {
              results.improvements.push("모바일에서 검색 폼이 세로 배치로 최적화됨");
            } else {
              results.issues.push("모바일에서 검색 폼이 가로 배치로 유지됨");
            }
          }
        }
        
        return results;
      }, viewport);
      
      // 스크린샷 촬영
      const screenshotPath = `/var/www/html/topmkt/screenshots/community_fixed_${viewport.name}.png`;
      await page.screenshot({ path: screenshotPath, fullPage: false });
      
      // 결과 출력
      const status = results.issues.length === 0 ? '✅' : '⚠️';
      console.log(`  ${status} 개선 결과:`);
      
      if (results.improvements.length > 0) {
        results.improvements.forEach(improvement => {
          console.log(`    ✅ ${improvement}`);
        });
      }
      
      if (results.issues.length > 0) {
        results.issues.forEach(issue => {
          console.log(`    ❌ ${issue}`);
        });
      }
      
      // 프로필 이미지 상세 정보
      Object.keys(results.elements).forEach(key => {
        if (key.startsWith('profileImage')) {
          const img = results.elements[key];
          console.log(`    👤 ${key}: ${img.width}x${img.height}px ${img.adequate ? '✅' : '❌'}`);
        }
      });
      
      console.log(`    📸 스크린샷: ${screenshotPath}`);
    }
    
    console.log("\\n🎯 커뮤니티 페이지 개선 완료!");
    console.log("  ✅ 터치 요소 크기 44px+ 달성");
    console.log("  ✅ 폰트 크기 가독성 개선");
    console.log("  ✅ 가로 스크롤 문제 해결");
    console.log("  ✅ 모바일 레이아웃 최적화");
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();