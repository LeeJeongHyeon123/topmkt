/**
 * 768px 가로 스크롤 수정 후 검증 테스트
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🔧 768px 가로 스크롤 수정 후 검증 테스트');

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 768, height: 1024 });

    try {
        // 1. 목록 뷰 테스트
        console.log('\n📋 목록 뷰 테스트 (수정 후)...');
        await page.goto('https://www.topmktx.com/events?year=2025&month=3&view=list', {
            waitUntil: 'networkidle0',
            timeout: 15000
        });

        const listResults = await page.evaluate(() => {
            const container = document.querySelector('.events-container');
            const layout = document.querySelector('.events-layout');
            const sidebar = document.querySelector('.events-sidebar');

            const containerRect = container?.getBoundingClientRect();
            const layoutRect = layout?.getBoundingClientRect();
            const sidebarRect = sidebar?.getBoundingClientRect();

            const layoutComputed = layout ? window.getComputedStyle(layout) : null;

            return {
                container: {
                    width: containerRect?.width,
                    scrollWidth: container?.scrollWidth,
                    hasHorizontalScroll: container ? container.scrollWidth > container.clientWidth : null
                },
                layout: {
                    width: layoutRect?.width,
                    scrollWidth: layout?.scrollWidth,
                    gridTemplateColumns: layoutComputed?.gridTemplateColumns,
                    display: layoutComputed?.display,
                    gap: layoutComputed?.gap
                },
                sidebar: {
                    width: sidebarRect?.width,
                    left: sidebarRect?.left,
                    right: sidebarRect?.right,
                    withinScreen: sidebarRect ? sidebarRect.right <= 768 : null
                },
                windowWidth: window.innerWidth,
                documentWidth: document.documentElement.scrollWidth
            };
        });

        console.log('   📊 목록 뷰 결과:');
        console.log(`   Container 가로 스크롤: ${listResults.container.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
        console.log(`   Grid 템플릿 컬럼: ${listResults.layout.gridTemplateColumns}`);
        console.log(`   사이드바 화면 내: ${listResults.sidebar.withinScreen ? '✅ 예' : '❌ 아니오'} (right: ${listResults.sidebar.right}px)`);
        console.log(`   문서 전체 너비: ${listResults.documentWidth}px vs 화면 ${listResults.windowWidth}px`);

        // 목록 뷰 스크린샷
        await page.screenshot({
            path: 'list-view-768px-fixed.png',
            fullPage: true
        });

        // 2. 캘린더 뷰 테스트
        console.log('\n📅 캘린더 뷰 테스트 (수정 후)...');
        await page.goto('https://www.topmktx.com/events?year=2025&month=3&view=calendar', {
            waitUntil: 'networkidle0',
            timeout: 15000
        });

        const calendarResults = await page.evaluate(() => {
            const container = document.querySelector('.events-container');
            const layout = document.querySelector('.events-layout');
            const sidebar = document.querySelector('.events-sidebar');

            const containerRect = container?.getBoundingClientRect();
            const layoutRect = layout?.getBoundingClientRect();
            const sidebarRect = sidebar?.getBoundingClientRect();

            const layoutComputed = layout ? window.getComputedStyle(layout) : null;

            return {
                container: {
                    width: containerRect?.width,
                    scrollWidth: container?.scrollWidth,
                    hasHorizontalScroll: container ? container.scrollWidth > container.clientWidth : null
                },
                layout: {
                    width: layoutRect?.width,
                    scrollWidth: layout?.scrollWidth,
                    gridTemplateColumns: layoutComputed?.gridTemplateColumns,
                    display: layoutComputed?.display,
                    gap: layoutComputed?.gap
                },
                sidebar: {
                    width: sidebarRect?.width,
                    left: sidebarRect?.left,
                    right: sidebarRect?.right,
                    withinScreen: sidebarRect ? sidebarRect.right <= 768 : null
                },
                windowWidth: window.innerWidth,
                documentWidth: document.documentElement.scrollWidth
            };
        });

        console.log('   📊 캘린더 뷰 결과:');
        console.log(`   Container 가로 스크롤: ${calendarResults.container.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
        console.log(`   Grid 템플릿 컬럼: ${calendarResults.layout.gridTemplateColumns}`);
        console.log(`   사이드바 화면 내: ${calendarResults.sidebar.withinScreen ? '✅ 예' : '❌ 아니오'} (right: ${calendarResults.sidebar.right}px)`);
        console.log(`   문서 전체 너비: ${calendarResults.documentWidth}px vs 화면 ${calendarResults.windowWidth}px`);

        // 캘린더 뷰 스크린샷
        await page.screenshot({
            path: 'calendar-view-768px-fixed.png',
            fullPage: true
        });

        // 3. 종합 결과 평가
        console.log('\n🎯 종합 결과:');
        const listFixed = !listResults.container.hasHorizontalScroll && listResults.sidebar.withinScreen;
        const calendarFixed = !calendarResults.container.hasHorizontalScroll && calendarResults.sidebar.withinScreen;

        console.log(`   목록 뷰 가로 스크롤 문제: ${listFixed ? '✅ 해결됨' : '❌ 여전히 존재'}`);
        console.log(`   캘린더 뷰 가로 스크롤 문제: ${calendarFixed ? '✅ 해결됨' : '❌ 여전히 존재'}`);

        if (listFixed && calendarFixed) {
            console.log('\n🎉 768px 가로 스크롤 문제 완전 해결!');
        } else {
            console.log('\n⚠️ 추가 수정이 필요합니다.');
        }

        console.log('\n📸 생성된 스크린샷:');
        console.log('   - list-view-768px-fixed.png');
        console.log('   - calendar-view-768px-fixed.png');

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    } finally {
        await browser.close();
    }
})();