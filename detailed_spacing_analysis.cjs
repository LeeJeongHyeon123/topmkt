const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    console.log('=== 🔍 상세 여백 분석 테스트 ===\n');

    // 데스크톱 뷰포트 설정
    await page.setViewportSize({ width: 1920, height: 1080 });

    // DevLoginHelper로 로그인
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(2000);

    // 채팅 페이지로 이동
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    await page.waitForTimeout(3000);

    // 상세 분석
    const detailedAnalysis = await page.evaluate(() => {
      const chatContainer = document.querySelector('.chat-container');
      const chatHeader = document.querySelector('.chat-header');
      const chatLayout = document.querySelector('.chat-layout');
      const chatSidebar = document.querySelector('.chat-sidebar');

      const getElementInfo = (element, name) => {
        if (!element) return { name, exists: false };

        const rect = element.getBoundingClientRect();
        const styles = window.getComputedStyle(element);

        return {
          name,
          exists: true,
          left: rect.left,
          right: rect.right,
          width: rect.width,
          padding: styles.padding,
          paddingLeft: parseInt(styles.paddingLeft) || 0,
          paddingRight: parseInt(styles.paddingRight) || 0,
          margin: styles.margin,
          marginLeft: parseInt(styles.marginLeft) || 0,
          marginRight: parseInt(styles.marginRight) || 0,
          position: styles.position,
          display: styles.display,
          boxSizing: styles.boxSizing
        };
      };

      return {
        container: getElementInfo(chatContainer, 'chat-container'),
        header: getElementInfo(chatHeader, 'chat-header'),
        layout: getElementInfo(chatLayout, 'chat-layout'),
        sidebar: getElementInfo(chatSidebar, 'chat-sidebar')
      };
    });

    console.log('📊 상세 요소 분석:');

    Object.entries(detailedAnalysis).forEach(([key, info]) => {
      if (info.exists) {
        console.log(`\n🔹 ${info.name}:`);
        console.log(`   Left: ${Math.round(info.left)}px`);
        console.log(`   Width: ${Math.round(info.width)}px`);
        console.log(`   Padding: ${info.padding} (Left: ${info.paddingLeft}px, Right: ${info.paddingRight}px)`);
        console.log(`   Margin: ${info.margin} (Left: ${info.marginLeft}px, Right: ${info.marginRight}px)`);
        console.log(`   Position: ${info.position}, Display: ${info.display}, Box-sizing: ${info.boxSizing}`);
      } else {
        console.log(`\n❌ ${info.name}: 요소를 찾을 수 없음`);
      }
    });

    // 실제 콘텐츠 영역 계산
    console.log('\n📐 실제 콘텐츠 영역 계산:');

    const header = detailedAnalysis.header;
    const layout = detailedAnalysis.layout;

    if (header.exists && layout.exists) {
      const headerContentStart = header.left + header.paddingLeft;
      const headerContentEnd = header.right - header.paddingRight;
      const layoutContentStart = layout.left + layout.paddingLeft;
      const layoutContentEnd = layout.right - layout.paddingRight;

      console.log(`   Header 콘텐츠 시작: ${Math.round(headerContentStart)}px`);
      console.log(`   Layout 콘텐츠 시작: ${Math.round(layoutContentStart)}px`);
      console.log(`   콘텐츠 시작점 차이: ${Math.round(Math.abs(headerContentStart - layoutContentStart))}px`);

      console.log(`   Header 콘텐츠 끝: ${Math.round(headerContentEnd)}px`);
      console.log(`   Layout 콘텐츠 끝: ${Math.round(layoutContentEnd)}px`);
      console.log(`   콘텐츠 끝점 차이: ${Math.round(Math.abs(headerContentEnd - layoutContentEnd))}px`);
    }

    // 스크린샷
    await page.screenshot({ path: 'detailed-spacing-analysis.png', fullPage: false });
    console.log('\n📸 상세 분석 스크린샷: detailed-spacing-analysis.png');

    // 문제 진단
    console.log('\n🔍 문제 진단:');

    if (header.exists && layout.exists) {
      const leftDiff = Math.abs(header.left - layout.left);
      const paddingMatch = header.paddingLeft === layout.paddingLeft && header.paddingRight === layout.paddingRight;

      if (leftDiff > 5) {
        console.log(`❌ 요소 시작 위치 차이: ${Math.round(leftDiff)}px`);
        console.log(`   - Header Left: ${Math.round(header.left)}px`);
        console.log(`   - Layout Left: ${Math.round(layout.left)}px`);
      }

      if (!paddingMatch) {
        console.log(`❌ 패딩 불일치:`);
        console.log(`   - Header 좌우 패딩: ${header.paddingLeft}px / ${header.paddingRight}px`);
        console.log(`   - Layout 좌우 패딩: ${layout.paddingLeft}px / ${layout.paddingRight}px`);
      } else {
        console.log(`✅ 패딩 일치: ${header.paddingLeft}px / ${header.paddingRight}px`);
      }

      // 부모 컨테이너 영향 분석
      if (detailedAnalysis.container.exists) {
        console.log(`\n📦 부모 컨테이너 분석:`);
        console.log(`   - Container Left: ${Math.round(detailedAnalysis.container.left)}px`);
        console.log(`   - Container 패딩: ${detailedAnalysis.container.padding}`);

        const headerRelativeToContainer = header.left - detailedAnalysis.container.left;
        const layoutRelativeToContainer = layout.left - detailedAnalysis.container.left;

        console.log(`   - Header 상대위치: ${Math.round(headerRelativeToContainer)}px`);
        console.log(`   - Layout 상대위치: ${Math.round(layoutRelativeToContainer)}px`);
        console.log(`   - 상대위치 차이: ${Math.round(Math.abs(headerRelativeToContainer - layoutRelativeToContainer))}px`);
      }
    }

  } catch (error) {
    console.error('❌ 분석 중 오류 발생:', error.message);
  } finally {
    await page.close();
    await browser.close();
    console.log('\n✅ 상세 여백 분석 완료');
  }
})();