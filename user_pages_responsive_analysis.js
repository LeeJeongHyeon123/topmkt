import { chromium } from "playwright";
import fs from 'fs';
import path from 'path';

(async () => {
  console.log("🔍 UltraThink: 탑마케팅 유저 페이지 반응형 UI/UX 분석 시작...");
  
  const browser = await chromium.launch({ 
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  });
  
  try {
    // 테스트할 유저 페이지들 (우선순위별)
    const userPages = [
      { name: "홈페이지", url: "/", critical: true },
      { name: "커뮤니티", url: "/community", critical: true },
      { name: "강의일정", url: "/lectures", critical: true },
      { name: "행사일정", url: "/events", critical: true },
      { name: "공지사항", url: "/notices", critical: true },
      { name: "채팅", url: "/chat", critical: true },
      { name: "내프로필", url: "/profile", critical: true },
      { name: "프로필편집", url: "/profile/edit", critical: false },
      { name: "기업회원정보", url: "/corp/info", critical: false },
      { name: "로그인", url: "/auth/login", critical: false },
      { name: "회원가입", url: "/auth/signup", critical: false },
      { name: "이용약관", url: "/terms", critical: false },
      { name: "개인정보처리방침", url: "/privacy", critical: false }
    ];
    
    // 테스트할 화면 크기들
    const viewports = [
      { name: "모바일", width: 375, height: 812, type: "mobile" },
      { name: "태블릿", width: 768, height: 1024, type: "tablet" }
    ];
    
    // 결과 저장 배열
    const analysisResults = [];
    
    // 스크린샷 저장 디렉토리 생성
    const screenshotDir = '/var/www/html/topmkt/screenshots/responsive_analysis';
    if (!fs.existsSync(screenshotDir)) {
      fs.mkdirSync(screenshotDir, { recursive: true });
    }
    
    // 🚀 병렬 처리 함수
    const analyzePageViewport = async (userPage, viewport) => {
      const context = await browser.newContext();
      const page = await context.newPage();
      
      try {
        // DevLogin으로 로그인
        await page.goto("https://www.topmktx.com/dev/login_helper.php?user_id=4");
        await page.waitForTimeout(2000);
        
        // 뷰포트 설정
        await page.setViewportSize({ width: viewport.width, height: viewport.height });
        
        // 페이지 이동
        await page.goto(`https://www.topmktx.com${userPage.url}?analysis_test=${Date.now()}`);
        await page.waitForTimeout(2000);
        
        // 페이지 분석
        const analysis = await page.evaluate((pageInfo) => {
          const results = {
            url: window.location.href,
            viewport: pageInfo.viewport,
            issues: [],
            metrics: {},
            elements: {}
          };
          
          // 화면 크기 정보
          results.metrics.windowWidth = window.innerWidth;
          results.metrics.windowHeight = window.innerHeight;
          results.metrics.documentWidth = document.documentElement.scrollWidth;
          results.metrics.documentHeight = document.documentElement.scrollHeight;
          
          // 가로 스크롤 체크
          if (document.documentElement.scrollWidth > window.innerWidth) {
            results.issues.push({
              type: "horizontal_scroll",
              severity: "high", 
              message: `가로 스크롤 발생 (${document.documentElement.scrollWidth}px > ${window.innerWidth}px)`
            });
          }
          
          // 오버플로우 요소 체크
          const allElements = document.querySelectorAll('*');
          let overflowElements = 0;
          allElements.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
              overflowElements++;
            }
          });
          
          if (overflowElements > 5) {
            results.issues.push({
              type: "element_overflow",
              severity: "medium",
              message: `${overflowElements}개 요소가 화면 밖으로 벗어남`
            });
          }
          
          // 폰트 크기 체크 (모바일에서 너무 작은 텍스트)
          if (pageInfo.viewport.type === 'mobile') {
            const textElements = document.querySelectorAll('p, span, div, a, button, label');
            let smallTextCount = 0;
            textElements.forEach(el => {
              const fontSize = parseFloat(window.getComputedStyle(el).fontSize);
              if (fontSize < 14 && el.textContent.trim().length > 0) {
                smallTextCount++;
              }
            });
            
            if (smallTextCount > 10) {
              results.issues.push({
                type: "small_text",
                severity: "medium",
                message: `${smallTextCount}개 요소의 폰트가 너무 작음 (14px 미만)`
              });
            }
          }
          
          // 버튼/링크 터치 크기 체크 (모바일)
          if (pageInfo.viewport.type === 'mobile') {
            const interactiveElements = document.querySelectorAll('button, a, input[type="button"], input[type="submit"]');
            let smallButtonCount = 0;
            interactiveElements.forEach(el => {
              const rect = el.getBoundingClientRect();
              if ((rect.width < 44 || rect.height < 44) && rect.width > 0 && rect.height > 0) {
                smallButtonCount++;
              }
            });
            
            if (smallButtonCount > 0) {
              results.issues.push({
                type: "small_touch_targets",
                severity: "high",
                message: `${smallButtonCount}개 터치 요소가 너무 작음 (44px 미만)`
              });
            }
          }
          
          // 특정 요소들 크기 측정
          const header = document.querySelector('header, .header, nav');
          if (header) {
            const headerRect = header.getBoundingClientRect();
            results.elements.header = {
              width: headerRect.width,
              height: headerRect.height,
              overflowing: headerRect.right > window.innerWidth
            };
            
            if (headerRect.right > window.innerWidth) {
              results.issues.push({
                type: "header_overflow",
                severity: "high",
                message: "헤더가 화면 밖으로 벗어남"
              });
            }
          }
          
          const footer = document.querySelector('footer, .footer');
          if (footer) {
            const footerRect = footer.getBoundingClientRect();
            results.elements.footer = {
              width: footerRect.width,
              height: footerRect.height,
              overflowing: footerRect.right > window.innerWidth
            };
            
            if (footerRect.right > window.innerWidth) {
              results.issues.push({
                type: "footer_overflow",
                severity: "medium",
                message: "푸터가 화면 밖으로 벗어남"
              });
            }
          }
          
          return results;
        }, { viewport, url: userPage.url });
        
        // 스크린샷 촬영
        const screenshotPath = path.join(screenshotDir, `${userPage.name.replace(/[^a-zA-Z0-9]/g, '_')}_${viewport.name}.png`);
        await page.screenshot({ 
          path: screenshotPath,
          fullPage: true
        });
        
        // 결과 반환
        return {
          page: userPage.name,
          url: userPage.url,
          viewport: viewport.name,
          critical: userPage.critical,
          analysis: analysis,
          screenshotPath: screenshotPath,
          issueCount: analysis.issues.length,
          severity: analysis.issues.length === 0 ? 'good' : 
                   analysis.issues.some(issue => issue.severity === 'high') ? 'critical' :
                   analysis.issues.some(issue => issue.severity === 'medium') ? 'warning' : 'info'
        };
        
      } catch (error) {
        return {
          page: userPage.name,
          url: userPage.url,
          viewport: viewport.name,
          critical: userPage.critical,
          error: error.message,
          severity: 'error'
        };
      } finally {
        await context.close();
      }
    };
    
    console.log("\\n🚀 병렬 분석 시작... (최대 26개 동시 작업)");
    
    // 모든 조합을 병렬로 실행
    const allTasks = [];
    for (const userPage of userPages) {
      for (const viewport of viewports) {
        allTasks.push(analyzePageViewport(userPage, viewport));
      }
    }
    
    // 병렬 실행 (최대 8개씩 동시 실행)
    const batchSize = 8;
    for (let i = 0; i < allTasks.length; i += batchSize) {
      const batch = allTasks.slice(i, i + batchSize);
      console.log(`\\n📦 배치 ${Math.floor(i/batchSize) + 1}/${Math.ceil(allTasks.length/batchSize)} 실행 중... (${batch.length}개 작업)`);
      
      const batchResults = await Promise.all(batch);
      analysisResults.push(...batchResults);
      
      // 각 결과 출력
      batchResults.forEach(result => {
        if (result.error) {
          console.log(`  ❌ ${result.page} (${result.viewport}): ${result.error}`);
        } else {
          console.log(`  ${result.issueCount === 0 ? '✅' : '⚠️'} ${result.page} (${result.viewport}): ${result.issueCount}개 문제`);
        }
      });
    }
    
    // 결과 요약 및 우선순위 설정
    console.log("\\n📋 UltraThink 분석 결과 요약:");
    
    const criticalIssues = analysisResults.filter(r => r.critical && r.severity === 'critical');
    const warningIssues = analysisResults.filter(r => r.critical && r.severity === 'warning');
    const goodPages = analysisResults.filter(r => r.severity === 'good');
    
    console.log(`\\n🔴 긴급 수정 필요 (핵심 페이지): ${criticalIssues.length}개`);
    criticalIssues.forEach(issue => {
      console.log(`   - ${issue.page} (${issue.viewport}): ${issue.issueCount}개 문제`);
    });
    
    console.log(`\\n🟡 개선 권장 (핵심 페이지): ${warningIssues.length}개`);
    warningIssues.forEach(issue => {
      console.log(`   - ${issue.page} (${issue.viewport}): ${issue.issueCount}개 문제`);
    });
    
    console.log(`\\n✅ 상태 양호: ${goodPages.length}개`);
    
    // 상세 결과 JSON 저장
    const detailedResults = {
      timestamp: new Date().toISOString(),
      summary: {
        totalPages: userPages.length,
        totalTests: analysisResults.length,
        criticalIssues: criticalIssues.length,
        warningIssues: warningIssues.length,
        goodPages: goodPages.length
      },
      results: analysisResults
    };
    
    fs.writeFileSync(
      '/var/www/html/topmkt/responsive_analysis_results.json',
      JSON.stringify(detailedResults, null, 2)
    );
    
    console.log("\\n💾 상세 분석 결과가 responsive_analysis_results.json에 저장되었습니다.");
    console.log(`📸 스크린샷이 ${screenshotDir}/ 에 저장되었습니다.`);
    
    // 개선 우선순위 제안
    console.log("\\n🎯 UltraThink 개선 우선순위 제안:");
    console.log("1. 🔴 긴급: 가로 스크롤 및 요소 오버플로우 문제");
    console.log("2. 🟡 중요: 터치 요소 크기 및 폰트 크기 문제");
    console.log("3. 🟢 권장: UI 일관성 및 사용자 경험 개선");
    
  } catch (error) {
    console.error("❌ 분석 오류:", error);
  } finally {
    await browser.close();
  }
})();