const { chromium } = require('playwright');

async function testLecturesEventsAccess() {
    console.log('🧪 강의/행사 페이지 접근 테스트 시작...');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    try {
        const page = await browser.newPage();

        // 1. 강의 페이지 테스트
        console.log('📚 강의 페이지 접근 테스트...');
        const lecturesResponse = await page.goto('https://www.topmktx.com/lectures', {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });

        console.log(`강의 페이지 상태: ${lecturesResponse.status()}`);

        if (lecturesResponse.status() === 200) {
            await page.waitForTimeout(2000);

            // 강의 목록 확인
            const lectureCount = await page.evaluate(() => {
                const lectureItems = document.querySelectorAll('.lecture-item, .event-item, .calendar-event');
                return lectureItems.length;
            });

            console.log(`📊 표시된 강의 개수: ${lectureCount}개`);
        }

        // 2. 행사 페이지 테스트
        console.log('🎉 행사 페이지 접근 테스트...');
        const eventsResponse = await page.goto('https://www.topmktx.com/events', {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });

        console.log(`행사 페이지 상태: ${eventsResponse.status()}`);

        if (eventsResponse.status() === 200) {
            await page.waitForTimeout(2000);

            // 행사 목록 확인
            const eventCount = await page.evaluate(() => {
                const eventItems = document.querySelectorAll('.lecture-item, .event-item, .calendar-event');
                return eventItems.length;
            });

            console.log(`📊 표시된 행사 개수: ${eventCount}개`);
        }

        // 스크린샷 저장
        await page.screenshot({
            path: 'lectures-events-test.png',
            fullPage: true
        });
        console.log('📸 스크린샷 저장: lectures-events-test.png');

        // 결과 분석
        const isLecturesWorking = lecturesResponse.status() === 200;
        const isEventsWorking = eventsResponse.status() === 200;

        console.log('\n📊 종합 테스트 결과:');
        console.log(`- 강의 페이지: ${isLecturesWorking ? '✅ 정상' : '❌ 오류'} (${lecturesResponse.status()})`);
        console.log(`- 행사 페이지: ${isEventsWorking ? '✅ 정상' : '❌ 오류'} (${eventsResponse.status()})`);

        if (isLecturesWorking && isEventsWorking) {
            console.log('🎉 강의/행사 페이지 복구 성공!');
        } else {
            console.log('❌ 일부 페이지에 문제가 있습니다.');
        }

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    } finally {
        await browser.close();
        console.log('🏁 테스트 완료');
    }
}

testLecturesEventsAccess();