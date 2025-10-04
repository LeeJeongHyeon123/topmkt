const { chromium } = require('playwright');

(async () => {
  console.log('🔍 채팅 페이지 HTML 구조 분석 시작...\n');

  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage({
    viewport: { width: 1920, height: 1080 }
  });

  try {
    // 1. DevLoginHelper로 자동 로그인
    console.log('🔐 DevLoginHelper 자동 로그인...');
    await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4', {
      waitUntil: 'networkidle'
    });

    // 2. 채팅 페이지 접속
    console.log('💬 채팅 페이지 접속...');
    await page.goto('https://www.topmktx.com/chat', {
      waitUntil: 'networkidle'
    });

    console.log(`현재 URL: ${page.url()}`);

    // 3. 페이지 로딩 완료까지 대기
    await page.waitForTimeout(3000);

    // 4. 전체 body HTML 구조 분석
    console.log('\n📄 전체 body HTML 구조 분석:');
    const bodyStructure = await page.evaluate(() => {
      function analyzeElement(element, depth = 0) {
        const indent = '  '.repeat(depth);
        const tagName = element.tagName.toLowerCase();
        const id = element.id ? ` id="${element.id}"` : '';
        const className = element.className ? ` class="${element.className}"` : '';
        const textContent = element.childNodes.length === 1 && element.childNodes[0].nodeType === 3
          ? ` [${element.textContent.trim().substring(0, 30)}${element.textContent.trim().length > 30 ? '...' : ''}]`
          : '';

        let result = `${indent}<${tagName}${id}${className}>${textContent}\n`;

        if (depth < 4) { // 깊이 제한
          for (const child of element.children) {
            result += analyzeElement(child, depth + 1);
          }
        }

        return result;
      }

      return analyzeElement(document.body);
    });

    console.log(bodyStructure.substring(0, 2000) + '...');

    // 5. 채팅 관련 요소들 찾기
    console.log('\n🏠 채팅 관련 요소들 검색:');

    const selectors = [
      '#chat-room-list',
      '.chat-room-list',
      '.room-list',
      '.chat-container',
      '.chat-content',
      '.room-item',
      '.chat-room-item',
      '.room',
      '.chat-room',
      '[class*="room"]',
      '[id*="chat"]',
      '[class*="chat"]'
    ];

    for (const selector of selectors) {
      const count = await page.locator(selector).count();
      if (count > 0) {
        console.log(`✅ ${selector}: ${count}개 발견`);

        // 첫 번째 요소의 HTML 내용 출력
        const html = await page.locator(selector).first().innerHTML();
        console.log(`   HTML 내용 (처음 200자): ${html.substring(0, 200)}...`);
      } else {
        console.log(`❌ ${selector}: 없음`);
      }
    }

    // 6. 채팅방 이름이 포함된 텍스트 요소 찾기
    console.log('\n💬 채팅방 이름 텍스트 검색:');

    const chatRoomNames = await page.evaluate(() => {
      const textNodes = [];
      const walker = document.createTreeWalker(
        document.body,
        NodeFilter.SHOW_TEXT,
        null,
        false
      );

      let node;
      while (node = walker.nextNode()) {
        const text = node.textContent.trim();
        if (text.includes('우리집탄이') || text.includes('마마맘') || text.includes('안..') || text.includes('우리...')) {
          const parent = node.parentElement;
          const tagName = parent.tagName.toLowerCase();
          const className = parent.className;
          const id = parent.id;

          textNodes.push({
            text: text,
            tagName: tagName,
            className: className,
            id: id,
            outerHTML: parent.outerHTML.substring(0, 200)
          });
        }
      }

      return textNodes;
    });

    console.log(`채팅방 이름이 포함된 요소 ${chatRoomNames.length}개 발견:`);
    for (const node of chatRoomNames) {
      console.log(`  - ${node.tagName}${node.className ? `.${node.className}` : ''}${node.id ? `#${node.id}` : ''}: "${node.text}"`);
      console.log(`    HTML: ${node.outerHTML}...`);
    }

    // 7. 실제 room-name 클래스 찾기
    console.log('\n🔍 실제 room-name 클래스 분석:');

    const roomNameElements = await page.locator('.room-name').count();
    console.log(`room-name 클래스 요소: ${roomNameElements}개`);

    if (roomNameElements > 0) {
      for (let i = 0; i < roomNameElements; i++) {
        const element = page.locator('.room-name').nth(i);
        const text = await element.textContent();
        const isVisible = await element.isVisible();
        const boundingBox = await element.boundingBox();

        console.log(`  Room ${i + 1}:`);
        console.log(`    텍스트: "${text}"`);
        console.log(`    가시성: ${isVisible}`);
        console.log(`    위치: ${boundingBox ? `${boundingBox.x}, ${boundingBox.y}, ${boundingBox.width}x${boundingBox.height}` : 'null'}`);

        if (isVisible) {
          const styles = await element.evaluate((el) => {
            const computed = window.getComputedStyle(el);
            return {
              maxWidth: computed.maxWidth,
              width: computed.width,
              overflow: computed.overflow,
              textOverflow: computed.textOverflow,
              whiteSpace: computed.whiteSpace,
              clientWidth: el.clientWidth,
              scrollWidth: el.scrollWidth
            };
          });

          console.log(`    스타일:`, styles);

          const isOverflowing = styles.scrollWidth > styles.clientWidth + 2;
          console.log(`    텍스트 잘림: ${isOverflowing ? '❌ 발생' : '✅ 없음'} (${styles.scrollWidth}px vs ${styles.clientWidth}px)`);
        }
      }
    }

    // 8. 최종 스크린샷
    console.log('\n📸 분석 스크린샷 생성...');
    await page.screenshot({
      path: 'chat-structure-analysis.png',
      fullPage: true
    });
    console.log('✅ 스크린샷 저장: chat-structure-analysis.png');

  } catch (error) {
    console.log(`❌ 오류 발생: ${error.message}`);
    console.log(error.stack);
  }

  await browser.close();
  console.log('\n🏁 HTML 구조 분석 완료');

})().catch(console.error);