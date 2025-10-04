const { chromium } = require('playwright');

async function findViewButtons() {
    console.log('🔍 뷰 전환 버튼 찾기...');

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

        // 캘린더 컨트롤 영역 내의 모든 요소 찾기
        const controlsContent = await page.evaluate(() => {
            const controlsDiv = document.querySelector('.calendar-controls');
            if (!controlsDiv) return null;

            // 모든 자식 요소 분석
            const allElements = controlsDiv.querySelectorAll('*');
            const elements = [];

            allElements.forEach(el => {
                elements.push({
                    tagName: el.tagName,
                    id: el.id || 'no-id',
                    className: el.className || 'no-class',
                    textContent: el.textContent?.trim() || 'no-text',
                    outerHTML: el.outerHTML.substring(0, 200),
                    clickable: el.onclick !== null || el.getAttribute('onclick') !== null
                });
            });

            return {
                innerHTML: controlsDiv.innerHTML,
                elements: elements
            };
        });

        if (controlsContent) {
            console.log('\n📋 캘린더 컨트롤 내부 요소들:');
            controlsContent.elements.forEach((el, index) => {
                console.log(`${index + 1}. [${el.tagName}] ID: ${el.id}, Class: ${el.className}`);
                console.log(`   Text: "${el.textContent}"`);
                console.log(`   Clickable: ${el.clickable ? '✅' : '❌'}`);
                console.log(`   HTML: ${el.outerHTML}`);
                console.log('');
            });

            console.log('\n🔍 캘린더 컨트롤 전체 HTML:');
            console.log(controlsContent.innerHTML);
        }

        // "캘린더"와 "목록" 텍스트를 포함한 요소들 찾기
        const viewElements = await page.$$eval('*', elements =>
            elements.filter(el => {
                const text = el.textContent?.trim();
                return text === '📅 캘린더' || text === '📋 목록' || text === '캘린더' || text === '목록';
            }).map(el => ({
                tagName: el.tagName,
                id: el.id || 'no-id',
                className: el.className || 'no-class',
                textContent: el.textContent?.trim(),
                outerHTML: el.outerHTML.substring(0, 200),
                parentTagName: el.parentElement?.tagName || 'no-parent',
                parentClassName: el.parentElement?.className || 'no-parent-class'
            }))
        );

        console.log('\n👁️ 뷰 관련 텍스트 요소들:');
        viewElements.forEach((el, index) => {
            console.log(`${index + 1}. [${el.tagName}] ID: ${el.id}, Class: ${el.className}`);
            console.log(`   Text: "${el.textContent}"`);
            console.log(`   Parent: [${el.parentTagName}] Class: ${el.parentClassName}`);
            console.log(`   HTML: ${el.outerHTML}`);
            console.log('');
        });

        // 클릭 가능한 요소들 찾기
        const clickableElements = await page.evaluate(() => {
            const elements = [];
            const controlsDiv = document.querySelector('.calendar-controls');
            if (!controlsDiv) return elements;

            // 클릭 이벤트가 있는 요소 찾기
            controlsDiv.addEventListener('click', function(e) {}, true);
            const allClickable = controlsDiv.querySelectorAll('*');

            allClickable.forEach(el => {
                const hasOnClick = el.onclick !== null || el.getAttribute('onclick') !== null;
                const hasEventListener = el._events !== undefined;
                const isButton = el.tagName === 'BUTTON' || el.tagName === 'A' || el.classList.contains('btn');

                if (hasOnClick || hasEventListener || isButton) {
                    elements.push({
                        tagName: el.tagName,
                        id: el.id || 'no-id',
                        className: el.className || 'no-class',
                        textContent: el.textContent?.trim() || 'no-text',
                        hasOnClick: hasOnClick,
                        isButton: isButton
                    });
                }
            });

            return elements;
        });

        console.log('\n🖱️ 클릭 가능한 요소들:');
        clickableElements.forEach((el, index) => {
            console.log(`${index + 1}. [${el.tagName}] ID: ${el.id}, Class: ${el.className}`);
            console.log(`   Text: "${el.textContent}"`);
            console.log(`   Has onClick: ${el.hasOnClick ? '✅' : '❌'}`);
            console.log(`   Is Button: ${el.isButton ? '✅' : '❌'}`);
            console.log('');
        });

    } catch (error) {
        console.error('❌ 오류:', error);
    } finally {
        await browser.close();
    }
}

findViewButtons();