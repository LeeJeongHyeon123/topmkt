import { chromium } from "playwright";

(async () => {
  console.log("📝 강의 목록 간격 및 구분선 분석...");
  
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
    
    // 목록형 뷰로 이동
    await page.goto(`https://www.topmktx.com/lectures?year=2025&month=6&view=list&spacing_test=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    console.log("\n📱 모바일 (375px) 강의 목록 간격 분석:");
    await page.setViewportSize({ width: 375, height: 812 });
    await page.waitForTimeout(2000);
    
    const spacingAnalysis = await page.evaluate(() => {
      const listItems = document.querySelectorAll('.lecture-list-item');
      const analysis = [];
      
      for (let i = 0; i < Math.min(listItems.length, 5); i++) {
        const item = listItems[i];
        const rect = item.getBoundingClientRect();
        const styles = window.getComputedStyle(item);
        
        // 다음 아이템과의 간격 계산
        let gapToNext = 0;
        if (i < listItems.length - 1) {
          const nextItem = listItems[i + 1];
          const nextRect = nextItem.getBoundingClientRect();
          gapToNext = nextRect.top - rect.bottom;
        }
        
        analysis.push({
          index: i + 1,
          height: Math.round(rect.height),
          top: Math.round(rect.top),
          bottom: Math.round(rect.bottom),
          padding: styles.padding,
          borderBottom: styles.borderBottom,
          backgroundColor: styles.backgroundColor,
          gapToNext: Math.round(gapToNext),
          boxShadow: styles.boxShadow
        });
      }
      
      return {
        totalItems: listItems.length,
        analysis,
        containerStyles: window.getComputedStyle(document.querySelector('.list-view'))
      };
    });
    
    console.log(`   총 강의 항목: ${spacingAnalysis.totalItems}개`);
    console.log(`   컨테이너 배경: ${spacingAnalysis.containerStyles.backgroundColor}`);
    console.log(`   컨테이너 테두리: ${spacingAnalysis.containerStyles.border}`);
    
    spacingAnalysis.analysis.forEach(item => {
      console.log(`\n   📋 강의 ${item.index}:`);
      console.log(`      높이: ${item.height}px`);
      console.log(`      패딩: ${item.padding}`);
      console.log(`      배경색: ${item.backgroundColor}`);
      console.log(`      하단 테두리: ${item.borderBottom}`);
      console.log(`      그림자: ${item.boxShadow === 'none' ? '없음' : '있음'}`);
      
      if (item.gapToNext !== undefined) {
        if (item.gapToNext <= 1) {
          console.log(`      ❌ 다음 항목과의 간격: ${item.gapToNext}px (구분이 어려움)`);
        } else {
          console.log(`      ✅ 다음 항목과의 간격: ${item.gapToNext}px`);
        }
      }
    });
    
    // 스크린샷 (현재 상태)
    await page.screenshot({ 
      path: `/var/www/html/topmkt/lectures_list_spacing_before.png`,
      fullPage: true
    });
    
    console.log("\n📊 구분선 개선 필요사항:");
    console.log("   1. 강의 항목 간 명확한 시각적 분리");
    console.log("   2. 적절한 간격과 구분선");
    console.log("   3. 호버 효과 개선");
    console.log("   4. 카드형 디자인 또는 그림자 효과");
    
  } catch (error) {
    console.error("❌ 분석 오류:", error);
  } finally {
    await browser.close();
  }
})();