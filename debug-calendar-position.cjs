// 🔍 캘린더 위치 및 크기 정확 분석
const { chromium } = require('playwright');

async function debugCalendarPosition() {
    console.log('🔍 캘린더 위치 및 크기 정확 분석 시작...');

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 375, height: 667 }
    });

    const page = await context.newPage();

    try {
        await page.goto('https://www.topmktx.com/events?view=calendar', {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        await page.waitForTimeout(3000);

        // 캘린더 위치 및 크기 정확 분석
        const calendarInfo = await page.evaluate(() => {
            const calendarView = document.querySelector('.calendar-view');
            const eventsContainer = document.querySelector('.events-container');

            if (!calendarView) return { error: '.calendar-view 없음' };

            const calendarBox = calendarView.getBoundingClientRect();
            const containerBox = eventsContainer ? eventsContainer.getBoundingClientRect() : null;

            const calendarStyle = window.getComputedStyle(calendarView);
            const containerStyle = eventsContainer ? window.getComputedStyle(eventsContainer) : null;

            return {
                viewport: { width: window.innerWidth, height: window.innerHeight },
                calendar: {
                    boundingBox: {
                        x: calendarBox.x,
                        y: calendarBox.y,
                        width: calendarBox.width,
                        height: calendarBox.height,
                        left: calendarBox.left,
                        right: calendarBox.right,
                        top: calendarBox.top,
                        bottom: calendarBox.bottom
                    },
                    computedStyle: {
                        position: calendarStyle.position,
                        left: calendarStyle.left,
                        right: calendarStyle.right,
                        top: calendarStyle.top,
                        bottom: calendarStyle.bottom,
                        transform: calendarStyle.transform,
                        margin: calendarStyle.margin,
                        padding: calendarStyle.padding,
                        width: calendarStyle.width,
                        height: calendarStyle.height,
                        minWidth: calendarStyle.minWidth,
                        maxWidth: calendarStyle.maxWidth,
                        overflow: calendarStyle.overflow,
                        overflowX: calendarStyle.overflowX,
                        overflowY: calendarStyle.overflowY,
                        display: calendarStyle.display,
                        visibility: calendarStyle.visibility,
                        opacity: calendarStyle.opacity,
                        zIndex: calendarStyle.zIndex
                    }
                },
                container: containerBox ? {
                    boundingBox: {
                        x: containerBox.x,
                        y: containerBox.y,
                        width: containerBox.width,
                        height: containerBox.height
                    },
                    computedStyle: {
                        position: containerStyle.position,
                        overflow: containerStyle.overflow,
                        overflowX: containerStyle.overflowX,
                        padding: containerStyle.padding,
                        margin: containerStyle.margin
                    }
                } : null,
                scrollInfo: {
                    scrollX: window.scrollX,
                    scrollY: window.scrollY,
                    documentScrollWidth: document.documentElement.scrollWidth,
                    documentScrollHeight: document.documentElement.scrollHeight,
                    documentClientWidth: document.documentElement.clientWidth,
                    documentClientHeight: document.documentElement.clientHeight
                }
            };
        });

        console.log('📊 캘린더 위치 및 크기 상세 분석:');
        console.log(JSON.stringify(calendarInfo, null, 2));

        // 화면 내 표시 여부 판단
        const calendar = calendarInfo.calendar;
        const viewport = calendarInfo.viewport;

        const isVisibleInViewport =
            calendar.boundingBox.right > 0 &&
            calendar.boundingBox.left < viewport.width &&
            calendar.boundingBox.bottom > 0 &&
            calendar.boundingBox.top < viewport.height;

        console.log('\n🔍 분석 결과:');
        console.log(`📱 뷰포트 크기: ${viewport.width} × ${viewport.height}`);
        console.log(`📏 캘린더 위치: x=${calendar.boundingBox.x}, y=${calendar.boundingBox.y}`);
        console.log(`📐 캘린더 크기: ${calendar.boundingBox.width} × ${calendar.boundingBox.height}`);
        console.log(`👀 화면 내 표시: ${isVisibleInViewport ? '✅ YES' : '❌ NO'}`);

        if (!isVisibleInViewport) {
            console.log('🚨 문제 발견: 캘린더가 화면 밖에 위치!');
            if (calendar.boundingBox.x < 0) console.log('   - 왼쪽으로 밀려남');
            if (calendar.boundingBox.x > viewport.width) console.log('   - 오른쪽으로 밀려남');
            if (calendar.boundingBox.y < 0) console.log('   - 위쪽으로 밀려남');
            if (calendar.boundingBox.y > viewport.height) console.log('   - 아래쪽으로 밀려남');
        }

    } catch (error) {
        console.error('❌ 분석 중 오류:', error);
    } finally {
        await browser.close();
    }
}

debugCalendarPosition();