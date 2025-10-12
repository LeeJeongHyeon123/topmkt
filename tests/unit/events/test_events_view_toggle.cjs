const { chromium } = require('playwright');

async function testViewToggle() {
    console.log('🔄 이벤트 페이지 뷰 전환 기능 테스트 시작...');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });

    const page = await context.newPage();

    try {
        // 1. 캘린더 뷰에서 시작
        console.log('\n1️⃣ 캘린더 뷰에서 시작...');
        await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
            waitUntil: 'networkidle'
        });
        await page.waitForTimeout(3000);

        // 캘린더 뷰 상태 확인
        const calendarActive = await page.$('#calendarViewBtn.active') !== null;
        const listActive = await page.$('#listViewBtn.active') !== null;
        console.log(`📅 캘린더 뷰 버튼 활성화: ${calendarActive ? '✅' : '❌'}`);
        console.log(`📋 목록 뷰 버튼 활성화: ${listActive ? '✅' : '❌'}`);

        // 캘린더가 표시되는지 확인
        const calendarVisible = await page.$('.calendar-table') !== null;
        console.log(`📅 캘린더 테이블 표시: ${calendarVisible ? '✅' : '❌'}`);

        // 2. 목록 뷰로 전환
        console.log('\n2️⃣ 목록 뷰로 전환 테스트...');

        const listViewBtn = await page.$('#listViewBtn');
        if (listViewBtn) {
            await listViewBtn.click();
            await page.waitForTimeout(2000);

            // URL 변경 확인
            const currentUrl = page.url();
            console.log(`🔗 현재 URL: ${currentUrl}`);
            console.log(`📋 목록 뷰 URL 포함: ${currentUrl.includes('view=list') ? '✅' : '❌'}`);

            // 목록 뷰 상태 확인
            const calendarActiveAfter = await page.$('#calendarViewBtn.active') !== null;
            const listActiveAfter = await page.$('#listViewBtn.active') !== null;
            console.log(`📅 캘린더 버튼 비활성화: ${!calendarActiveAfter ? '✅' : '❌'}`);
            console.log(`📋 목록 버튼 활성화: ${listActiveAfter ? '✅' : '❌'}`);

            // 목록이 표시되는지 확인
            const listVisible = await page.$('.events-list') !== null;
            console.log(`📋 이벤트 목록 표시: ${listVisible ? '✅' : '❌'}`);

            // 목록 뷰 스크린샷
            await page.screenshot({
                path: 'events-view-toggle-list.png',
                fullPage: false,
                clip: { x: 0, y: 200, width: 1440, height: 500 }
            });

        } else {
            console.log('❌ 목록 뷰 버튼을 찾을 수 없습니다.');
        }

        // 3. 다시 캘린더 뷰로 전환
        console.log('\n3️⃣ 캘린더 뷰로 재전환 테스트...');

        const calendarViewBtn = await page.$('#calendarViewBtn');
        if (calendarViewBtn) {
            await calendarViewBtn.click();
            await page.waitForTimeout(2000);

            // URL 변경 확인
            const currentUrl = page.url();
            console.log(`🔗 현재 URL: ${currentUrl}`);
            console.log(`📅 캘린더 뷰 URL 포함: ${currentUrl.includes('view=calendar') ? '✅' : '❌'}`);

            // 캘린더 뷰 상태 확인
            const calendarActiveFinal = await page.$('#calendarViewBtn.active') !== null;
            const listActiveFinal = await page.$('#listViewBtn.active') !== null;
            console.log(`📅 캘린더 버튼 활성화: ${calendarActiveFinal ? '✅' : '❌'}`);
            console.log(`📋 목록 버튼 비활성화: ${!listActiveFinal ? '✅' : '❌'}`);

            // 캘린더가 다시 표시되는지 확인
            const calendarVisibleFinal = await page.$('.calendar-table') !== null;
            console.log(`📅 캘린더 테이블 재표시: ${calendarVisibleFinal ? '✅' : '❌'}`);

            // 캘린더 뷰 스크린샷
            await page.screenshot({
                path: 'events-view-toggle-calendar.png',
                fullPage: false,
                clip: { x: 0, y: 200, width: 1440, height: 500 }
            });

        } else {
            console.log('❌ 캘린더 뷰 버튼을 찾을 수 없습니다.');
        }

        // 4. 버튼 스타일 확인
        console.log('\n4️⃣ 버튼 스타일 확인...');

        const buttonStyles = await page.evaluate(() => {
            const calendarBtn = document.querySelector('#calendarViewBtn');
            const listBtn = document.querySelector('#listViewBtn');

            if (!calendarBtn || !listBtn) return null;

            const calendarStyles = window.getComputedStyle(calendarBtn);
            const listStyles = window.getComputedStyle(listBtn);

            return {
                calendar: {
                    backgroundColor: calendarStyles.backgroundColor,
                    color: calendarStyles.color,
                    hasActiveClass: calendarBtn.classList.contains('active')
                },
                list: {
                    backgroundColor: listStyles.backgroundColor,
                    color: listStyles.color,
                    hasActiveClass: listBtn.classList.contains('active')
                }
            };
        });

        if (buttonStyles) {
            console.log('🎨 캘린더 버튼 스타일:', buttonStyles.calendar);
            console.log('🎨 목록 버튼 스타일:', buttonStyles.list);
        }

        console.log('\n✅ 뷰 전환 기능 테스트 완료!');

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
    } finally {
        await browser.close();
    }
}

testViewToggle();