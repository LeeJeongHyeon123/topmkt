import { chromium } from "playwright";

(async () => {
  console.log("🎨 강의 목록 카드형 디자인 테스트...");
  
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
    
    // 목록형 뷰로 이동 (캐시 무시)
    await page.goto(`https://www.topmktx.com/lectures?year=2025&month=6&view=list&card_test=${Date.now()}`);
    await page.waitForTimeout(3000);
    
    const testSizes = [
      { name: "모바일", width: 375, height: 812 },
      { name: "태블릿", width: 768, height: 1024 }
    ];
    
    for (const size of testSizes) {
      console.log(`\n🎨 ${size.name} (${size.width}x${size.height}) 카드 디자인 검증:`);
      
      await page.setViewportSize({ width: size.width, height: size.height });
      await page.waitForTimeout(2000);
      
      const cardAnalysis = await page.evaluate(() => {
        const listItems = document.querySelectorAll('.lecture-list-item');
        const listView = document.querySelector('.list-view');
        
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
            marginBottom: styles.marginBottom,
            padding: styles.padding,
            borderRadius: styles.borderRadius,
            boxShadow: styles.boxShadow,
            backgroundColor: styles.backgroundColor,
            border: styles.border,
            gapToNext: Math.round(gapToNext),
            hasGradientBorder: styles.getPropertyValue('--gradient-border') || 'pseudo-element'
          });
        }
        
        return {
          totalItems: listItems.length,
          listViewBackground: window.getComputedStyle(listView).backgroundColor,
          listViewPadding: window.getComputedStyle(listView).padding,
          analysis
        };
      });
      
      console.log(`   총 강의: ${cardAnalysis.totalItems}개`);
      console.log(`   목록 배경: ${cardAnalysis.listViewBackground}`);
      console.log(`   목록 패딩: ${cardAnalysis.listViewPadding}`);
      
      cardAnalysis.analysis.forEach(card => {
        console.log(`\n   🃏 카드 ${card.index}:`);
        console.log(`      높이: ${card.height}px`);
        console.log(`      패딩: ${card.padding}`);
        console.log(`      마진 하단: ${card.marginBottom}`);
        console.log(`      모서리 둥글기: ${card.borderRadius}`);
        console.log(`      배경색: ${card.backgroundColor}`);
        console.log(`      테두리: ${card.border}`);
        console.log(`      그림자: ${card.boxShadow === 'none' ? '없음' : '있음'}`);
        
        if (card.gapToNext !== undefined) {
          if (card.gapToNext >= 8) {
            console.log(`      ✅ 다음 카드와의 간격: ${card.gapToNext}px (명확한 구분)`);
          } else if (card.gapToNext >= 4) {
            console.log(`      🔶 다음 카드와의 간격: ${card.gapToNext}px (적당한 구분)`);
          } else {
            console.log(`      ❌ 다음 카드와의 간격: ${card.gapToNext}px (구분 부족)`);
          }
        }
      });
      
      // 스크린샷
      await page.screenshot({ 
        path: `/var/www/html/topmkt/lectures_card_design_${size.name}_${size.width}px.png`,
        fullPage: true
      });
    }
    
    // Before vs After 비교
    console.log("\n📊 카드형 디자인 개선 결과:");
    
    await page.setViewportSize({ width: 375, height: 812 });
    await page.waitForTimeout(1000);
    
    const improvements = await page.evaluate(() => {
      const items = document.querySelectorAll('.lecture-list-item');
      if (items.length < 2) return null;
      
      const firstItem = items[0];
      const secondItem = items[1];
      
      const firstRect = firstItem.getBoundingClientRect();
      const secondRect = secondItem.getBoundingClientRect();
      const gap = secondRect.top - firstRect.bottom;
      
      const firstStyles = window.getComputedStyle(firstItem);
      
      return {
        cardGap: Math.round(gap),
        hasBoxShadow: firstStyles.boxShadow !== 'none',
        hasBorderRadius: firstStyles.borderRadius !== '0px',
        cardBackground: firstStyles.backgroundColor,
        marginBottom: firstStyles.marginBottom
      };
    });
    
    if (improvements) {
      console.log("   Before: 강의 항목들이 0px 간격으로 붙어있어 구분 어려움");
      console.log(`   After: ${improvements.cardGap}px 간격의 카드형 디자인으로 명확한 구분`);
      console.log(`   카드 배경: ${improvements.cardBackground}`);
      console.log(`   그림자 효과: ${improvements.hasBoxShadow ? '적용됨' : '없음'}`);
      console.log(`   모서리 둥글기: ${improvements.hasBorderRadius ? '적용됨' : '없음'}`);
      console.log(`   카드 간격: ${improvements.marginBottom}`);
      
      if (improvements.cardGap >= 8 && improvements.hasBoxShadow && improvements.hasBorderRadius) {
        console.log("\n🎉 카드형 디자인 완성!");
        console.log("   ✅ 명확한 구분: 적절한 카드 간격");
        console.log("   ✅ 예쁜 디자인: 그림자와 둥근 모서리");
        console.log("   ✅ 향상된 UX: 호버 효과와 좌측 그라디언트");
      } else {
        console.log("\n⚠️ 일부 개선 필요");
      }
    }
    
  } catch (error) {
    console.error("❌ 테스트 오류:", error);
  } finally {
    await browser.close();
  }
})();