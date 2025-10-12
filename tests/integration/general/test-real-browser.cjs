// 🔥 실제 브라우저에서 수정 사항 테스트
const { chromium } = require('playwright');

async function testRealBrowser() {
    console.log('🔥 실제 브라우저에서 수정 사항 테스트 시작...');

    const browser = await chromium.launch({
        headless: true  // 🔥 헤드리스 모드
    });

    const context = await browser.newContext({
        viewport: { width: 375, height: 667 }
    });

    const page = await context.newPage();

    try {
        // 캐시 무효화
        await page.goto('https://www.topmktx.com/events?view=calendar&t=' + Date.now(), {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        console.log('⏳ 5초 대기 중...');
        await page.waitForTimeout(5000);

        // 캘린더 위치 확인
        const calendarInfo = await page.evaluate(() => {
            const calendar = document.querySelector('.calendar-view');
            if (!calendar) return { error: 'calendar not found' };

            const box = calendar.getBoundingClientRect();
            const style = window.getComputedStyle(calendar);

            return {
                position: {
                    x: box.x,
                    y: box.y,
                    width: box.width,
                    height: box.height
                },
                style: {
                    width: style.width,
                    maxWidth: style.maxWidth,
                    margin: style.margin,
                    marginLeft: style.marginLeft,
                    marginRight: style.marginRight
                },
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                visible: box.x >= 0 && box.x + box.width <= window.innerWidth
            };
        });

        console.log('📊 실제 브라우저 테스트 결과:');
        console.log(JSON.stringify(calendarInfo, null, 2));

        // 스크린샷
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const screenshotPath = `/var/www/html/topmkt/real-browser-test-${timestamp}.png`;
        await page.screenshot({
            path: screenshotPath,
            fullPage: true
        });
        console.log('📸 실제 브라우저 스크린샷:', screenshotPath);

        console.log('\n🔍 결과 요약:');
        console.log(`✅ 캘린더 발견: ${!calendarInfo.error}`);
        console.log(`📍 위치: x=${calendarInfo.position?.x}, y=${calendarInfo.position?.y}`);
        console.log(`📐 크기: ${calendarInfo.position?.width} × ${calendarInfo.position?.height}`);
        console.log(`👀 화면 내 표시: ${calendarInfo.visible ? '✅ YES' : '❌ NO'}`);

    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
    } finally {
        setTimeout(() => {
            browser.close();
        }, 5000); // 5초 후 브라우저 닫기
    }
}

testRealBrowser();