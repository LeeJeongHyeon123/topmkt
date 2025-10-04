// 🔥 최종 모바일 캘린더 테스트
const { chromium } = require('playwright');

async function finalMobileTest() {
    console.log('🔥 최종 모바일 캘린더 테스트 시작...');

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

        await page.waitForTimeout(5000);

        // 강제 스크롤을 통해 캘린더 찾기 시도
        await page.evaluate(() => {
            // 수평 스크롤로 캘린더 찾기
            window.scrollTo(400, 0);
        });

        await page.waitForTimeout(1000);

        // 스크린샷 촬영
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const screenshotPath = `/var/www/html/topmkt/final-mobile-test-${timestamp}.png`;
        await page.screenshot({
            path: screenshotPath,
            fullPage: true
        });

        console.log('📸 전체 스크린샷 저장:', screenshotPath);

        // 캘린더 위치 확인
        const calendarInfo = await page.evaluate(() => {
            const calendar = document.querySelector('.calendar-view');
            if (!calendar) return null;

            const box = calendar.getBoundingClientRect();
            return {
                x: box.x,
                y: box.y,
                width: box.width,
                height: box.height,
                visible: box.x >= 0 && box.x < window.innerWidth
            };
        });

        console.log('📊 캘린더 정보:', calendarInfo);

        if (calendarInfo && !calendarInfo.visible) {
            console.log('🔧 캘린더가 화면 밖에 있음 - 수평 스크롤 시도');

            // 캘린더 위치로 스크롤
            await page.evaluate((x) => {
                window.scrollTo(x - 100, 0);
            }, calendarInfo.x);

            await page.waitForTimeout(500);

            // 스크롤 후 스크린샷
            const scrolledScreenshotPath = `/var/www/html/topmkt/scrolled-mobile-test-${timestamp}.png`;
            await page.screenshot({
                path: scrolledScreenshotPath,
                fullPage: true
            });

            console.log('📸 스크롤 후 스크린샷 저장:', scrolledScreenshotPath);
        }

    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
    } finally {
        await browser.close();
    }
}

finalMobileTest();