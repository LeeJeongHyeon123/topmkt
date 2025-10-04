// 🌟 ULTRA THINK 모드 최종 테스트 - 간단한 레이아웃 검증
const { chromium } = require('playwright');

async function testUltraThinkLayout() {
    console.log('🚀 ULTRA THINK 모드 최종 테스트 시작');

    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();

    // 3가지 뷰포트 테스트
    const viewports = [
        { name: '데스크톱', width: 1920, height: 1080 },
        { name: '태블릿', width: 768, height: 1024 },
        { name: '모바일', width: 375, height: 812 }
    ];

    const results = [];

    for (const viewport of viewports) {
        console.log(`\n📱 ${viewport.name} 테스트 시작 (${viewport.width}x${viewport.height})`);

        await page.setViewportSize({ width: viewport.width, height: viewport.height });

        try {
            // 캘린더 뷰 접속
            await page.goto('https://www.topmktx.com/events?view=calendar', {
                waitUntil: 'networkidle',
                timeout: 15000
            });

            // 캘린더 요소 확인
            const calendarView = await page.locator('.calendar-view').first();
            const calendarVisible = await calendarView.isVisible();

            // 사이드바 확인
            const sidebar = await page.locator('.events-sidebar').first();
            const sidebarVisible = await sidebar.isVisible();

            // 가로 스크롤 확인
            const hasHorizontalScroll = await page.evaluate(() => {
                return document.documentElement.scrollWidth > document.documentElement.clientWidth;
            });

            // 캘린더 위치 확인
            let calendarPosition = { x: 0, y: 0 };
            if (calendarVisible) {
                const bbox = await calendarView.boundingBox();
                calendarPosition = { x: bbox.x, y: bbox.y };
            }

            // 컨테이너 중앙 정렬 확인
            const containerCentered = await page.evaluate(() => {
                const container = document.querySelector('.events-container');
                if (!container) return false;
                const containerRect = container.getBoundingClientRect();
                const windowWidth = window.innerWidth;
                const leftMargin = containerRect.x;
                const rightMargin = windowWidth - (containerRect.x + containerRect.width);
                return Math.abs(leftMargin - rightMargin) < 20; // 20px 오차 허용
            });

            const testResult = {
                viewport: viewport.name,
                캘린더표시: calendarVisible ? '✅' : '❌',
                사이드바표시: sidebarVisible ? '✅' : '❌',
                가로스크롤: hasHorizontalScroll ? '❌ 있음' : '✅ 없음',
                캘린더위치: `x:${Math.round(calendarPosition.x)}, y:${Math.round(calendarPosition.y)}`,
                중앙정렬: containerCentered ? '✅' : '❌',
                전체평가: calendarVisible && !hasHorizontalScroll && containerCentered ? '🎉 성공' : '⚠️ 문제'
            };

            results.push(testResult);

            // 스크린샷 저장
            await page.screenshot({
                path: `ultra-think-final-${viewport.name.toLowerCase()}.png`,
                fullPage: false
            });

            console.log('✅ 테스트 완료:', testResult);

        } catch (error) {
            console.error(`❌ ${viewport.name} 테스트 실패:`, error.message);
            results.push({
                viewport: viewport.name,
                전체평가: '❌ 오류',
                오류: error.message
            });
        }
    }

    await browser.close();

    // 최종 결과 출력
    console.log('\n🏆 ULTRA THINK 모드 최종 테스트 결과');
    console.log('='.repeat(80));
    results.forEach(result => {
        console.log(`\n📱 ${result.viewport}:`);
        Object.entries(result).forEach(([key, value]) => {
            if (key !== 'viewport') {
                console.log(`   ${key}: ${value}`);
            }
        });
    });

    const successCount = results.filter(r => r.전체평가?.includes('성공')).length;
    console.log(`\n🎯 성공률: ${successCount}/${results.length} (${Math.round(successCount/results.length*100)}%)`);

    if (successCount === results.length) {
        console.log('🌟 ULTRA THINK 모드 구현 완료! 모든 테스트 통과!');
    } else {
        console.log('⚠️ 일부 개선이 필요합니다.');
    }
}

testUltraThinkLayout().catch(console.error);