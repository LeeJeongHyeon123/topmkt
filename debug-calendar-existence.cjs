// 🔍 캘린더 요소 존재 여부 디버깅
const { chromium } = require('playwright');

async function debugCalendarExistence() {
    console.log('🔍 캘린더 요소 존재 여부 디버깅 시작...');

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

        // DOM 구조 전체 분석
        const domStructure = await page.evaluate(() => {
            const container = document.querySelector('.events-container');
            if (!container) return { error: '.events-container 없음' };

            return {
                containerExists: !!container,
                containerHTML: container.innerHTML.substring(0, 1000) + '...',
                calendarViewExists: !!document.querySelector('.calendar-view'),
                calendarTableExists: !!document.querySelector('.calendar-table'),
                calendarHeaderExists: !!document.querySelector('.calendar-header'),
                calendarBodyExists: !!document.querySelector('.calendar-body'),
                listViewExists: !!document.querySelector('.list-view'),
                allClasses: Array.from(document.querySelectorAll('[class]')).map(el => el.className).slice(0, 20)
            };
        });

        console.log('📊 DOM 구조 분석:', JSON.stringify(domStructure, null, 2));

        // 캘린더 관련 모든 요소 검색
        const calendarElements = await page.evaluate(() => {
            const elements = document.querySelectorAll('*');
            const calendarRelated = [];

            elements.forEach((el, index) => {
                if (el.className && typeof el.className === 'string') {
                    if (el.className.includes('calendar') ||
                        el.className.includes('Calendar') ||
                        el.id && el.id.includes('calendar')) {
                        calendarRelated.push({
                            index,
                            tagName: el.tagName,
                            className: el.className,
                            id: el.id,
                            display: window.getComputedStyle(el).display,
                            visibility: window.getComputedStyle(el).visibility,
                            opacity: window.getComputedStyle(el).opacity
                        });
                    }
                }
            });

            return calendarRelated;
        });

        console.log('🔍 캘린더 관련 요소들:', JSON.stringify(calendarElements, null, 2));

        // PHP 뷰 변수 확인
        const phpVars = await page.evaluate(() => {
            const bodyDataView = document.body.getAttribute('data-view');
            const htmlDataView = document.documentElement.getAttribute('data-view');
            return {
                bodyDataView,
                htmlDataView,
                currentURL: window.location.href
            };
        });

        console.log('🔧 PHP 뷰 변수:', JSON.stringify(phpVars, null, 2));

    } catch (error) {
        console.error('❌ 디버깅 중 오류:', error);
    } finally {
        await browser.close();
    }
}

debugCalendarExistence();