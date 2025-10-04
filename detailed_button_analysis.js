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

    // 상단 부분만 스크린샷 (헤더와 검색 부분)
    console.log('📸 상단 부분 스크린샷 촬영 중...');
    await page.screenshot({
      path: '/var/www/html/topmkt/community_mobile_header.png',
      clip: { x: 0, y: 0, width: 375, height: 400 }
    });

    // 상세 버튼 분석
    const detailedAnalysis = await page.evaluate(() => {
      const results = {
        header: {},
        searchArea: {},
        writeButton: {},
        layout: {}
      };

      // 헤더 영역 분석
      const header = document.querySelector('header, .header, .main-header');
      if (header) {
        const headerRect = header.getBoundingClientRect();
        results.header = {
          element: header.tagName,
          className: header.className,
          position: {
            x: Math.round(headerRect.x),
            y: Math.round(headerRect.y),
            width: Math.round(headerRect.width),
            height: Math.round(headerRect.height)
          },
          styles: {
            backgroundColor: window.getComputedStyle(header).backgroundColor,
            position: window.getComputedStyle(header).position,
            zIndex: window.getComputedStyle(header).zIndex
          }
        };
      }

      // 검색 영역 분석
      const searchContainer = document.querySelector('.search-container, .search-section, .search-area');
      if (searchContainer) {
        const searchRect = searchContainer.getBoundingClientRect();
        results.searchArea = {
          element: searchContainer.tagName,
          className: searchContainer.className,
          position: {
            x: Math.round(searchRect.x),
            y: Math.round(searchRect.y),
            width: Math.round(searchRect.width),
            height: Math.round(searchRect.height)
          }
        };
      }

      // 돋보기 버튼 상세 분석
      const searchButton = document.querySelector('.search-btn');
      if (searchButton) {
        const btnRect = searchButton.getBoundingClientRect();
        const btnStyle = window.getComputedStyle(searchButton);
        results.searchButton = {
          element: searchButton.tagName,
          className: searchButton.className,
          position: {
            x: Math.round(btnRect.x),
            y: Math.round(btnRect.y),
            width: Math.round(btnRect.width),
            height: Math.round(btnRect.height)
          },
          styles: {
            position: btnStyle.position,
            left: btnStyle.left,
            top: btnStyle.top,
            margin: btnStyle.margin,
            padding: btnStyle.padding,
            backgroundColor: btnStyle.backgroundColor,
            border: btnStyle.border,
            borderRadius: btnStyle.borderRadius,
            fontSize: btnStyle.fontSize,
            color: btnStyle.color,
            cursor: btnStyle.cursor,
            display: btnStyle.display,
            zIndex: btnStyle.zIndex
          },
          parent: {
            tag: searchButton.parentElement?.tagName,
            className: searchButton.parentElement?.className
          }
        };
      }

      // 글쓰기 버튼 분석
      const writeButton = document.querySelector('a[href*="write"], .write-btn, [onclick*="write"]');
      if (writeButton) {
        const writeRect = writeButton.getBoundingClientRect();
        const writeStyle = window.getComputedStyle(writeButton);
        results.writeButton = {
          element: writeButton.tagName,
          className: writeButton.className,
          text: writeButton.textContent?.trim(),
          href: writeButton.href,
          position: {
            x: Math.round(writeRect.x),
            y: Math.round(writeRect.y),
            width: Math.round(writeRect.width),
            height: Math.round(writeRect.height)
          },
          styles: {
            position: writeStyle.position,
            left: writeStyle.left,
            top: writeStyle.top,
            margin: writeStyle.margin,
            padding: writeStyle.padding,
            backgroundColor: writeStyle.backgroundColor,
            border: writeStyle.border,
            borderRadius: writeStyle.borderRadius,
            fontSize: writeStyle.fontSize,
            color: writeStyle.color,
            textAlign: writeStyle.textAlign,
            display: writeStyle.display
          }
        };
      }

      // 레이아웃 문제점 분석
      results.layout = {
        viewportWidth: window.innerWidth,
        contentOverflow: document.documentElement.scrollWidth > window.innerWidth,
        searchButtonFromEdge: results.searchButton ? results.searchButton.position.x : 0,
        writeButtonFromEdge: results.writeButton ? results.writeButton.position.x : 0,
        buttonsAlignment: {
          searchY: results.searchButton ? results.searchButton.position.y : 0,
          writeY: results.writeButton ? results.writeButton.position.y : 0,
          verticalGap: results.writeButton && results.searchButton ?
            Math.abs(results.writeButton.position.y - (results.searchButton.position.y + results.searchButton.position.height)) : 0
        }
      };

      return results;
    });

    console.log('\n📊 상세 분석 결과:');
    console.log('='.repeat(60));

    if (detailedAnalysis.header.element) {
      console.log('\n🏠 헤더 정보:');
      console.log(`   위치: (${detailedAnalysis.header.position.x}, ${detailedAnalysis.header.position.y})`);
      console.log(`   크기: ${detailedAnalysis.header.position.width} x ${detailedAnalysis.header.position.height}px`);
      console.log(`   배경색: ${detailedAnalysis.header.styles.backgroundColor}`);
    }

    if (detailedAnalysis.searchButton) {
      console.log('\n🔍 돋보기 버튼 상세 정보:');
      console.log(`   위치: (${detailedAnalysis.searchButton.position.x}, ${detailedAnalysis.searchButton.position.y})`);
      console.log(`   크기: ${detailedAnalysis.searchButton.position.width} x ${detailedAnalysis.searchButton.position.height}px`);
      console.log(`   왼쪽 여백: ${detailedAnalysis.searchButton.position.x}px`);
      console.log(`   CSS 위치: ${detailedAnalysis.searchButton.styles.position}`);
      console.log(`   패딩: ${detailedAnalysis.searchButton.styles.padding}`);
      console.log(`   마진: ${detailedAnalysis.searchButton.styles.margin}`);
      console.log(`   배경색: ${detailedAnalysis.searchButton.styles.backgroundColor}`);
      console.log(`   테두리: ${detailedAnalysis.searchButton.styles.border}`);
    }

    if (detailedAnalysis.writeButton) {
      console.log('\n✏️ 글쓰기 버튼 상세 정보:');
      console.log(`   텍스트: "${detailedAnalysis.writeButton.text}"`);
      console.log(`   위치: (${detailedAnalysis.writeButton.position.x}, ${detailedAnalysis.writeButton.position.y})`);
      console.log(`   크기: ${detailedAnalysis.writeButton.position.width} x ${detailedAnalysis.writeButton.position.height}px`);
      console.log(`   왼쪽 여백: ${detailedAnalysis.writeButton.position.x}px`);
      console.log(`   CSS 위치: ${detailedAnalysis.writeButton.styles.position}`);
      console.log(`   패딩: ${detailedAnalysis.writeButton.styles.padding}`);
      console.log(`   마진: ${detailedAnalysis.writeButton.styles.margin}`);
      console.log(`   배경색: ${detailedAnalysis.writeButton.styles.backgroundColor}`);
      console.log(`   글자색: ${detailedAnalysis.writeButton.styles.color}`);
    }

    console.log('\n📐 레이아웃 분석:');
    console.log(`   뷰포트 너비: ${detailedAnalysis.layout.viewportWidth}px`);
    console.log(`   콘텐츠 오버플로우: ${detailedAnalysis.layout.contentOverflow ? '예' : '아니오'}`);
    console.log(`   돋보기 버튼 간격: ${detailedAnalysis.layout.buttonsAlignment.verticalGap}px`);

    // 문제점 진단
    console.log('\n⚠️ 발견된 문제점들:');
    const issues = [];

    if (detailedAnalysis.layout.searchButtonFromEdge < 8) {
      issues.push('돋보기 버튼이 화면 가장자리에 너무 가깝습니다');
    }

    if (detailedAnalysis.layout.writeButtonFromEdge < 8) {
      issues.push('글쓰기 버튼이 화면 가장자리에 너무 가깝습니다');
    }

    if (detailedAnalysis.layout.buttonsAlignment.verticalGap > 20) {
      issues.push('돋보기 버튼과 글쓰기 버튼 사이 간격이 너무 큽니다');
    }

    if (detailedAnalysis.searchButton && detailedAnalysis.searchButton.position.width < 44) {
      issues.push('돋보기 버튼이 모바일 터치 기준(44px)보다 작습니다');
    }

    if (detailedAnalysis.writeButton && detailedAnalysis.writeButton.position.height < 44) {
      issues.push('글쓰기 버튼이 모바일 터치 기준(44px)보다 작습니다');
    }

    issues.forEach((issue, index) => {
      console.log(`   ${index + 1}. ${issue}`);
    });

    if (issues.length === 0) {
      console.log('   문제점이 발견되지 않았습니다.');
    }

    console.log('\n✅ 상세 분석 완료! 스크린샷: community_mobile_header.png');

  } catch (error) {
    console.error('❌ 오류 발생:', error);
  } finally {
    await browser.close();
  }
})();