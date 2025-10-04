// 🔍 스크롤 원인 자동 분석 도구
const { chromium } = require('playwright');

async function scrollDebugger() {
    console.log('🔍 스크롤 원인 자동 분석 시작...');

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 375, height: 667 }
    });

    const page = await context.newPage();

    try {
        await page.goto('https://www.topmktx.com/events?view=calendar&t=' + Date.now(), {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        await page.waitForTimeout(3000);

        // 스크롤 및 오버플로우 분석
        const scrollAnalysis = await page.evaluate(() => {
            const viewport = {
                width: window.innerWidth,
                height: window.innerHeight
            };

            const document_size = {
                scrollWidth: document.documentElement.scrollWidth,
                scrollHeight: document.documentElement.scrollHeight,
                clientWidth: document.documentElement.clientWidth,
                clientHeight: document.documentElement.clientHeight
            };

            const body_size = {
                scrollWidth: document.body.scrollWidth,
                scrollHeight: document.body.scrollHeight,
                clientWidth: document.body.clientWidth,
                clientHeight: document.body.clientHeight
            };

            // 스크롤 존재 여부
            const has_horizontal_scroll = document.documentElement.scrollWidth > document.documentElement.clientWidth;
            const has_vertical_scroll = document.documentElement.scrollHeight > document.documentElement.clientHeight;

            // 큰 요소들 찾기
            const all_elements = Array.from(document.querySelectorAll('*'));
            const oversized_elements = [];

            all_elements.forEach((el, index) => {
                const rect = el.getBoundingClientRect();
                const computedStyle = window.getComputedStyle(el);

                // 뷰포트를 벗어나는 요소들 찾기
                if (rect.width > viewport.width || rect.height > viewport.height ||
                    rect.right > viewport.width || rect.bottom > viewport.height ||
                    rect.left < 0 || rect.top < 0) {

                    oversized_elements.push({
                        tagName: el.tagName,
                        className: el.className,
                        id: el.id,
                        boundingBox: {
                            x: rect.x,
                            y: rect.y,
                            width: rect.width,
                            height: rect.height,
                            left: rect.left,
                            right: rect.right,
                            top: rect.top,
                            bottom: rect.bottom
                        },
                        computedStyle: {
                            width: computedStyle.width,
                            height: computedStyle.height,
                            position: computedStyle.position,
                            overflow: computedStyle.overflow,
                            overflowX: computedStyle.overflowX,
                            overflowY: computedStyle.overflowY
                        },
                        causes_horizontal_overflow: rect.right > viewport.width || rect.width > viewport.width,
                        causes_vertical_overflow: rect.bottom > viewport.height || rect.height > viewport.height
                    });
                }
            });

            // 특별히 캘린더 요소 확인
            const calendar = document.querySelector('.calendar-view');
            let calendar_info = null;
            if (calendar) {
                const rect = calendar.getBoundingClientRect();
                const style = window.getComputedStyle(calendar);
                calendar_info = {
                    boundingBox: rect,
                    computedStyle: {
                        position: style.position,
                        width: style.width,
                        height: style.height,
                        top: style.top,
                        left: style.left,
                        overflow: style.overflow,
                        overflowX: style.overflowX,
                        overflowY: style.overflowY
                    },
                    causes_scroll: rect.right > viewport.width || rect.bottom > viewport.height || rect.left < 0 || rect.top < 0
                };
            }

            return {
                viewport,
                document_size,
                body_size,
                has_horizontal_scroll,
                has_vertical_scroll,
                scroll_amount: {
                    horizontal: document.documentElement.scrollWidth - document.documentElement.clientWidth,
                    vertical: document.documentElement.scrollHeight - document.documentElement.clientHeight
                },
                oversized_elements: oversized_elements.slice(0, 10), // 상위 10개만
                calendar_info,
                total_oversized_count: oversized_elements.length
            };
        });

        console.log('📊 스크롤 자동 분석 결과:');
        console.log('='.repeat(80));

        console.log('\n📱 뷰포트 정보:');
        console.log(`크기: ${scrollAnalysis.viewport.width} × ${scrollAnalysis.viewport.height}`);

        console.log('\n📄 문서 크기:');
        console.log(`스크롤 크기: ${scrollAnalysis.document_size.scrollWidth} × ${scrollAnalysis.document_size.scrollHeight}`);
        console.log(`클라이언트 크기: ${scrollAnalysis.document_size.clientWidth} × ${scrollAnalysis.document_size.clientHeight}`);

        console.log('\n🔍 스크롤 상태:');
        console.log(`가로 스크롤: ${scrollAnalysis.has_horizontal_scroll ? '❌ 존재' : '✅ 없음'} (${scrollAnalysis.scroll_amount.horizontal}px 초과)`);
        console.log(`세로 스크롤: ${scrollAnalysis.has_vertical_scroll ? '❌ 존재' : '✅ 없음'} (${scrollAnalysis.scroll_amount.vertical}px 초과)`);

        if (scrollAnalysis.calendar_info) {
            console.log('\n📅 캘린더 정보:');
            console.log(`위치: x=${scrollAnalysis.calendar_info.boundingBox.x}, y=${scrollAnalysis.calendar_info.boundingBox.y}`);
            console.log(`크기: ${scrollAnalysis.calendar_info.boundingBox.width} × ${scrollAnalysis.calendar_info.boundingBox.height}`);
            console.log(`범위: left=${scrollAnalysis.calendar_info.boundingBox.left}, right=${scrollAnalysis.calendar_info.boundingBox.right}, top=${scrollAnalysis.calendar_info.boundingBox.top}, bottom=${scrollAnalysis.calendar_info.boundingBox.bottom}`);
            console.log(`스크롤 원인: ${scrollAnalysis.calendar_info.causes_scroll ? '❌ YES' : '✅ NO'}`);
        }

        console.log('\n🚨 스크롤 원인 요소들:');
        scrollAnalysis.oversized_elements.forEach((el, index) => {
            console.log(`${index + 1}. ${el.tagName}${el.className ? '.' + el.className.split(' ')[0] : ''}${el.id ? '#' + el.id : ''}`);
            console.log(`   위치: x=${el.boundingBox.x.toFixed(1)}, y=${el.boundingBox.y.toFixed(1)}`);
            console.log(`   크기: ${el.boundingBox.width.toFixed(1)} × ${el.boundingBox.height.toFixed(1)}`);
            console.log(`   범위: right=${el.boundingBox.right.toFixed(1)}, bottom=${el.boundingBox.bottom.toFixed(1)}`);
            console.log(`   가로 오버플로우: ${el.causes_horizontal_overflow ? '❌' : '✅'}`);
            console.log(`   세로 오버플로우: ${el.causes_vertical_overflow ? '❌' : '✅'}`);
            console.log(`   position: ${el.computedStyle.position}`);
            console.log('');
        });

        console.log(`\n📊 총 ${scrollAnalysis.total_oversized_count}개 요소가 뷰포트를 벗어남`);

        // 스크린샷
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const screenshotPath = `/var/www/html/topmkt/scroll-debug-${timestamp}.png`;
        await page.screenshot({
            path: screenshotPath,
            fullPage: true
        });
        console.log(`\n📸 스크린샷: ${screenshotPath}`);

    } catch (error) {
        console.error('❌ 스크롤 분석 중 오류:', error);
    } finally {
        await browser.close();
    }
}

scrollDebugger();