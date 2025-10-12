// 🔥 모바일 캘린더 v2.8 테스트 스크립트
const { chromium } = require('playwright');

async function testMobileCalendar() {
    console.log('🔥 모바일 캘린더 v2.8 반응형 테스트 시작...');

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 375, height: 667 }, // iPhone SE 크기
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15'
    });

    const page = await context.newPage();

    try {
        // 캘린더 페이지 접속
        console.log('📱 모바일 사이즈로 캘린더 페이지 접속...');
        await page.goto('https://www.topmktx.com/events?view=calendar', {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        // 2초 대기 (CSS 적용 시간)
        await page.waitForTimeout(2000);

        // 캘린더 요소 확인
        const calendarView = await page.$('.calendar-view');
        console.log('📅 .calendar-view 요소 존재:', !!calendarView);

        if (calendarView) {
            // 캘린더 박스 크기 측정
            const box = await calendarView.boundingBox();
            console.log('📏 캘린더 박스 정보:', {
                width: box?.width,
                height: box?.height,
                x: box?.x,
                y: box?.y
            });

            // 화면 밖으로 밀려나지 않았는지 확인
            const isVisible = box && box.x >= 0 && box.x + box.width <= 375;
            console.log('👀 캘린더 화면 내 표시:', isVisible);

            // 캘린더 CSS 스타일 확인
            const styles = await page.evaluate(() => {
                const calendar = document.querySelector('.calendar-view');
                if (!calendar) return null;

                const computedStyle = window.getComputedStyle(calendar);
                return {
                    width: computedStyle.width,
                    maxWidth: computedStyle.maxWidth,
                    minWidth: computedStyle.minWidth,
                    margin: computedStyle.margin,
                    overflow: computedStyle.overflow,
                    overflowX: computedStyle.overflowX,
                    display: computedStyle.display,
                    visibility: computedStyle.visibility
                };
            });
            console.log('🎨 캘린더 CSS 스타일:', styles);
        }

        // 수평 스크롤 확인
        const hasHorizontalScroll = await page.evaluate(() => {
            return document.documentElement.scrollWidth > document.documentElement.clientWidth;
        });
        console.log('↔️ 수평 스크롤 존재:', hasHorizontalScroll);

        // 스크린샷 촬영
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const screenshotPath = `/var/www/html/topmkt/mobile-calendar-v28-${timestamp}.png`;
        await page.screenshot({
            path: screenshotPath,
            fullPage: true
        });
        console.log('📸 스크린샷 저장:', screenshotPath);

        // 결과 요약
        console.log('\n=== 🔥 v2.8 테스트 결과 요약 ===');
        console.log('✅ 캘린더 표시:', !!calendarView);
        console.log('✅ 화면 내 위치:', !hasHorizontalScroll ? '성공' : '실패 (수평 스크롤 존재)');
        console.log('📱 모바일 크기: 375px × 667px');
        console.log(`📸 스크린샷: ${screenshotPath}`);

    } catch (error) {
        console.error('❌ 테스트 중 오류:', error);
    } finally {
        await browser.close();
    }
}

testMobileCalendar();