import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 375, height: 667 }, // iPhone SE 크기
    deviceScaleFactor: 2,
    isMobile: true,
    hasTouch: true,
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1'
  });

  const page = await context.newPage();

  try {
    console.log('🔄 커뮤니티 페이지로 이동 중...');
    await page.goto('https://www.topmktx.com/community', { waitUntil: 'networkidle' });

    // 페이지 로드 완료 대기
    await page.waitForTimeout(3000);

    console.log('📸 스크린샷 촬영 중...');
    await page.screenshot({
      path: '/var/www/html/topmkt/community_mobile_analysis.png',
      fullPage: true
    });

    // 돋보기 버튼과 글쓰기 버튼 분석
    console.log('🔍 버튼들 분석 중...');

    const buttonAnalysis = await page.evaluate(() => {
      const results = [];

      // 돋보기 버튼 찾기
      const searchButtons = document.querySelectorAll('button[onclick*="search"], .search-btn, button[title*="검색"], button[aria-label*="검색"], i.fa-search');
      searchButtons.forEach((btn, index) => {
        const rect = btn.getBoundingClientRect();
        const computedStyle = window.getComputedStyle(btn);
        results.push({
          type: '돋보기/검색 버튼',
          index: index + 1,
          element: btn.tagName,
          className: btn.className,
          position: {
            x: Math.round(rect.x),
            y: Math.round(rect.y),
            width: Math.round(rect.width),
            height: Math.round(rect.height)
          },
          styles: {
            fontSize: computedStyle.fontSize,
            padding: computedStyle.padding,
            margin: computedStyle.margin,
            backgroundColor: computedStyle.backgroundColor,
            color: computedStyle.color
          },
          text: btn.textContent?.trim() || '(텍스트 없음)',
          visible: rect.width > 0 && rect.height > 0
        });
      });

      // 글쓰기 버튼 찾기
      const writeButtons = document.querySelectorAll('button[onclick*="write"], .write-btn, button[title*="글쓰기"], button[aria-label*="글쓰기"], a[href*="write"]');
      writeButtons.forEach((btn, index) => {
        const rect = btn.getBoundingClientRect();
        const computedStyle = window.getComputedStyle(btn);
        results.push({
          type: '글쓰기 버튼',
          index: index + 1,
          element: btn.tagName,
          className: btn.className,
          position: {
            x: Math.round(rect.x),
            y: Math.round(rect.y),
            width: Math.round(rect.width),
            height: Math.round(rect.height)
          },
          styles: {
            fontSize: computedStyle.fontSize,
            padding: computedStyle.padding,
            margin: computedStyle.margin,
            backgroundColor: computedStyle.backgroundColor,
            color: computedStyle.color
          },
          text: btn.textContent?.trim() || '(텍스트 없음)',
          href: btn.href || null,
          visible: rect.width > 0 && rect.height > 0
        });
      });

      // 전체 페이지 크기 정보
      const pageInfo = {
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight
        },
        documentSize: {
          width: document.documentElement.scrollWidth,
          height: document.documentElement.scrollHeight
        }
      };

      return { buttons: results, pageInfo };
    });

    console.log('\n📊 분석 결과:');
    console.log('='.repeat(50));
    console.log(`뷰포트 크기: ${buttonAnalysis.pageInfo.viewport.width} x ${buttonAnalysis.pageInfo.viewport.height}`);
    console.log(`문서 크기: ${buttonAnalysis.pageInfo.documentSize.width} x ${buttonAnalysis.pageInfo.documentSize.height}`);
    console.log(`찾은 버튼 수: ${buttonAnalysis.buttons.length}개`);
    console.log('='.repeat(50));

    buttonAnalysis.buttons.forEach((btn, index) => {
      console.log(`\n${index + 1}. ${btn.type}`);
      console.log(`   요소: ${btn.element} (클래스: ${btn.className})`);
      console.log(`   위치: x=${btn.position.x}, y=${btn.position.y}`);
      console.log(`   크기: ${btn.position.width} x ${btn.position.height}px`);
      console.log(`   텍스트: "${btn.text}"`);
      console.log(`   표시됨: ${btn.visible ? '예' : '아니오'}`);
      console.log(`   배경색: ${btn.styles.backgroundColor}`);
      console.log(`   글자 크기: ${btn.styles.fontSize}`);
      console.log(`   패딩: ${btn.styles.padding}`);
      if (btn.href) console.log(`   링크: ${btn.href}`);
    });

    // 추가적으로 모든 버튼 요소들도 확인
    const allButtons = await page.evaluate(() => {
      const buttons = Array.from(document.querySelectorAll('button, .btn, [role="button"]'));
      return buttons.map((btn, index) => {
        const rect = btn.getBoundingClientRect();
        return {
          index: index + 1,
          tag: btn.tagName,
          className: btn.className,
          text: btn.textContent?.trim().substring(0, 50) || '(텍스트 없음)',
          position: {
            x: Math.round(rect.x),
            y: Math.round(rect.y),
            width: Math.round(rect.width),
            height: Math.round(rect.height)
          },
          visible: rect.width > 0 && rect.height > 0
        };
      }).filter(btn => btn.visible);
    });

    console.log('\n\n🔍 모든 버튼 요소들 (상위 10개):');
    console.log('='.repeat(50));
    allButtons.slice(0, 10).forEach((btn) => {
      console.log(`${btn.index}. ${btn.tag} "${btn.text}" - 위치: (${btn.position.x}, ${btn.position.y}) 크기: ${btn.position.width}x${btn.position.height}`);
    });

    console.log('\n✅ 분석 완료! 스크린샷이 저장되었습니다: community_mobile_analysis.png');

  } catch (error) {
    console.error('❌ 오류 발생:', error);
  } finally {
    await browser.close();
  }
})();