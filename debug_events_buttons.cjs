const { chromium } = require('playwright');

async function debugEventsButtons() {
    console.log('🔍 이벤트 페이지 버튼 디버깅...');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });

    const page = await context.newPage();

    try {
        // 캘린더 뷰 페이지 로드
        await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
            waitUntil: 'networkidle'
        });
        await page.waitForTimeout(3000);

        // 페이지 내 모든 버튼 요소 찾기
        const allButtons = await page.$$eval('button, .btn, input[type="button"]', elements =>
            elements.map(el => ({
                tagName: el.tagName,
                id: el.id || 'no-id',
                className: el.className || 'no-class',
                textContent: el.textContent?.trim() || 'no-text',
                outerHTML: el.outerHTML.substring(0, 100) + (el.outerHTML.length > 100 ? '...' : '')
            }))
        );

        console.log('\n📋 페이지 내 모든 버튼 요소:');
        allButtons.forEach((btn, index) => {
            console.log(`${index + 1}. [${btn.tagName}] ID: ${btn.id}, Class: ${btn.className}`);
            console.log(`   Text: "${btn.textContent}"`);
            console.log(`   HTML: ${btn.outerHTML}`);
            console.log('');
        });

        // 특정 클래스 패턴 검색
        const calendarButtons = await page.$$eval('*[class*="calendar"], *[class*="Calendar"]', elements =>
            elements.map(el => ({
                tagName: el.tagName,
                id: el.id || 'no-id',
                className: el.className || 'no-class',
                textContent: el.textContent?.trim() || 'no-text'
            }))
        );

        console.log('\n📅 캘린더 관련 요소들:');
        calendarButtons.forEach((btn, index) => {
            console.log(`${index + 1}. [${btn.tagName}] ID: ${btn.id}, Class: ${btn.className}, Text: "${btn.textContent}"`);
        });

        // 목록 관련 요소 검색
        const listButtons = await page.$$eval('*[class*="list"], *[class*="List"]', elements =>
            elements.map(el => ({
                tagName: el.tagName,
                id: el.id || 'no-id',
                className: el.className || 'no-class',
                textContent: el.textContent?.trim() || 'no-text'
            }))
        );

        console.log('\n📋 목록 관련 요소들:');
        listButtons.forEach((btn, index) => {
            console.log(`${index + 1}. [${btn.tagName}] ID: ${btn.id}, Class: ${btn.className}, Text: "${btn.textContent}"`);
        });

        // 뷰 관련 요소 검색
        const viewButtons = await page.$$eval('*[class*="view"], *[class*="View"]', elements =>
            elements.map(el => ({
                tagName: el.tagName,
                id: el.id || 'no-id',
                className: el.className || 'no-class',
                textContent: el.textContent?.trim() || 'no-text'
            }))
        );

        console.log('\n👁️ 뷰 관련 요소들:');
        viewButtons.forEach((btn, index) => {
            console.log(`${index + 1}. [${btn.tagName}] ID: ${btn.id}, Class: ${btn.className}, Text: "${btn.textContent}"`);
        });

        // 페이지 HTML 일부 저장
        const pageContent = await page.content();
        const calendarControlsMatch = pageContent.match(/<div[^>]*calendar-controls[^>]*>[\s\S]*?<\/div>/i);

        if (calendarControlsMatch) {
            console.log('\n🎮 캘린더 컨트롤 HTML:');
            console.log(calendarControlsMatch[0]);
        }

    } catch (error) {
        console.error('❌ 디버깅 중 오류:', error);
    } finally {
        await browser.close();
    }
}

debugEventsButtons();