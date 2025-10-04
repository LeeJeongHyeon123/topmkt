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

    // 상단 부분 스크린샷
    console.log('📸 상단 부분 스크린샷 촬영 중...');
    await page.screenshot({
      path: '/var/www/html/topmkt/community_mobile_header.png',
      clip: { x: 0, y: 0, width: 375, height: 400 }
    });

    // 안전한 버튼 분석
    const safeAnalysis = await page.evaluate(() => {
      const results = {
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight
        },
        buttons: [],
        issues: []
      };

      // 모든 클릭 가능한 요소 찾기
      const clickableElements = document.querySelectorAll('button, a, [onclick], [role="button"], .btn');

      clickableElements.forEach((element, index) => {
        try {
          const rect = element.getBoundingClientRect();
          if (rect.width > 0 && rect.height > 0) {
            const computedStyle = window.getComputedStyle(element);

            const buttonInfo = {
              index: index + 1,
              tag: element.tagName,
              className: element.className || '(없음)',
              text: (element.textContent || element.innerHTML || '').trim().substring(0, 50),
              position: {
                x: Math.round(rect.x),
                y: Math.round(rect.y),
                width: Math.round(rect.width),
                height: Math.round(rect.height)
              },
              styles: {
                position: computedStyle.position,
                backgroundColor: computedStyle.backgroundColor,
                color: computedStyle.color,
                fontSize: computedStyle.fontSize,
                padding: computedStyle.padding,
                margin: computedStyle.margin,
                border: computedStyle.border
              },
              isVisible: rect.top >= 0 && rect.top <= window.innerHeight,
              href: element.href || null,
              onclick: element.onclick ? element.onclick.toString().substring(0, 100) : null
            };

            // 버튼 유형 추정
            if (element.className.includes('search') || element.textContent.includes('검색') || element.innerHTML.includes('fa-search')) {
              buttonInfo.type = '검색/돋보기';
            } else if (element.className.includes('write') || element.textContent.includes('글쓰기') || element.href?.includes('write')) {
              buttonInfo.type = '글쓰기';
            } else if (element.className.includes('menu') || element.innerHTML.includes('fa-bars')) {
              buttonInfo.type = '메뉴';
            } else {
              buttonInfo.type = '기타';
            }

            results.buttons.push(buttonInfo);
          }
        } catch (e) {
          // 개별 요소 처리 오류는 무시
        }
      });

      // 문제점 진단
      results.buttons.forEach(button => {
        // 터치 접근성 체크 (44px 최소 크기)
        if (button.position.width < 44 || button.position.height < 44) {
          results.issues.push(`${button.type} 버튼이 터치하기 어려운 크기입니다 (${button.position.width}x${button.position.height}px, 권장: 44x44px 이상)`);
        }

        // 화면 가장자리 너무 가까이 있는지 체크
        if (button.position.x < 8) {
          results.issues.push(`${button.type} 버튼이 왼쪽 가장자리에 너무 가깝습니다 (${button.position.x}px)`);
        }

        if (button.position.x + button.position.width > results.viewport.width - 8) {
          results.issues.push(`${button.type} 버튼이 오른쪽 가장자리에 너무 가깝습니다`);
        }
      });

      // 중요한 버튼들끼리의 간격 체크
      const searchButtons = results.buttons.filter(b => b.type === '검색/돋보기');
      const writeButtons = results.buttons.filter(b => b.type === '글쓰기');

      if (searchButtons.length > 0 && writeButtons.length > 0) {
        const searchBtn = searchButtons[0];
        const writeBtn = writeButtons[0];
        const verticalGap = Math.abs(writeBtn.position.y - (searchBtn.position.y + searchBtn.position.height));

        if (verticalGap > 30) {
          results.issues.push(`검색 버튼과 글쓰기 버튼 사이 간격이 너무 큽니다 (${verticalGap}px)`);
        }
      }

      return results;
    });

    console.log('\n📊 모바일 커뮤니티 페이지 분석 결과:');
    console.log('='.repeat(60));
    console.log(`뷰포트 크기: ${safeAnalysis.viewport.width} x ${safeAnalysis.viewport.height}px`);
    console.log(`발견된 클릭 가능한 요소: ${safeAnalysis.buttons.length}개`);

    // 상단에 보이는 중요한 버튼들만 필터링
    const topButtons = safeAnalysis.buttons.filter(btn => btn.isVisible && btn.position.y < 400);

    console.log('\n🔝 상단 영역의 주요 버튼들:');
    topButtons.forEach((btn, index) => {
      console.log(`\n${index + 1}. ${btn.type} 버튼 (${btn.tag})`);
      console.log(`   클래스: ${btn.className}`);
      console.log(`   텍스트: "${btn.text}"`);
      console.log(`   위치: (${btn.position.x}, ${btn.position.y})`);
      console.log(`   크기: ${btn.position.width} x ${btn.position.height}px`);
      console.log(`   배경색: ${btn.styles.backgroundColor}`);
      console.log(`   글자 크기: ${btn.styles.fontSize}`);
      if (btn.href) console.log(`   링크: ${btn.href}`);
    });

    console.log('\n⚠️ 발견된 UI/UX 문제점들:');
    if (safeAnalysis.issues.length > 0) {
      safeAnalysis.issues.forEach((issue, index) => {
        console.log(`   ${index + 1}. ${issue}`);
      });
    } else {
      console.log('   문제점이 발견되지 않았습니다.');
    }

    // 특별히 검색과 글쓰기 버튼 집중 분석
    const searchBtn = topButtons.find(b => b.type === '검색/돋보기');
    const writeBtn = topButtons.find(b => b.type === '글쓰기');

    console.log('\n🎯 핵심 버튼 배치 분석:');

    if (searchBtn) {
      console.log(`\n🔍 검색 버튼:`);
      console.log(`   • 위치: 왼쪽에서 ${searchBtn.position.x}px, 위에서 ${searchBtn.position.y}px`);
      console.log(`   • 크기: ${searchBtn.position.width}x${searchBtn.position.height}px`);
      console.log(`   • 터치 접근성: ${searchBtn.position.width >= 44 && searchBtn.position.height >= 44 ? '✅ 양호' : '❌ 개선 필요'}`);
    }

    if (writeBtn) {
      console.log(`\n✏️ 글쓰기 버튼:`);
      console.log(`   • 위치: 왼쪽에서 ${writeBtn.position.x}px, 위에서 ${writeBtn.position.y}px`);
      console.log(`   • 크기: ${writeBtn.position.width}x${writeBtn.position.height}px`);
      console.log(`   • 터치 접근성: ${writeBtn.position.width >= 44 && writeBtn.position.height >= 44 ? '✅ 양호' : '❌ 개선 필요'}`);
      console.log(`   • 텍스트: "${writeBtn.text}"`);
    }

    if (searchBtn && writeBtn) {
      const gap = Math.abs(writeBtn.position.y - (searchBtn.position.y + searchBtn.position.height));
      console.log(`\n📏 버튼 간 간격: ${gap}px ${gap > 20 ? '(너무 큼)' : gap < 8 ? '(너무 작음)' : '(적절함)'}`);
    }

    console.log('\n✅ 분석 완료! 헤더 스크린샷: community_mobile_header.png');

  } catch (error) {
    console.error('❌ 오류 발생:', error);
  } finally {
    await browser.close();
  }
})();