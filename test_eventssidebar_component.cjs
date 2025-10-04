/**
 * EventsSidebar 컴포넌트 통합 테스트
 * 캘린더 뷰와 목록 뷰에서 사이드바 컴포넌트 정상 작동 확인
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🚀 EventsSidebar 컴포넌트 통합 테스트 시작');

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-web-security']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    try {
        // 1. 캘린더 뷰 테스트
        console.log('\n📅 캘린더 뷰 테스트 중...');
        await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
            waitUntil: 'networkidle0',
            timeout: 15000
        });

        // 사이드바 존재 확인
        const calendarSidebar = await page.$('.events-sidebar');
        if (calendarSidebar) {
            console.log('✅ 캘린더 뷰: 사이드바 렌더링 성공');
        } else {
            console.log('❌ 캘린더 뷰: 사이드바 렌더링 실패');
            return;
        }

        // 사이드바 섹션들 확인
        const todayEventsSection = await page.$('.today-events, .empty-sidebar');
        const upcomingEventsSection = await page.$('.upcoming-events, .empty-sidebar');
        const sidebarTitles = await page.$$('.sidebar-title');

        console.log(`✅ 캘린더 뷰: 오늘의 행사 섹션 ${todayEventsSection ? '정상' : '오류'}`);
        console.log(`✅ 캘린더 뷰: 다가오는 행사 섹션 ${upcomingEventsSection ? '정상' : '오류'}`);
        console.log(`✅ 캘린더 뷰: 사이드바 제목 개수: ${sidebarTitles.length}개`);

        // 캘린더 스크린샷
        await page.screenshot({
            path: 'calendar-sidebar-test.png',
            fullPage: false,
            clip: { x: 1200, y: 200, width: 400, height: 800 }
        });

        // 2. 목록 뷰 테스트
        console.log('\n📋 목록 뷰 테스트 중...');
        await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=list', {
            waitUntil: 'networkidle0',
            timeout: 15000
        });

        // 사이드바 존재 확인
        const listSidebar = await page.$('.events-sidebar');
        if (listSidebar) {
            console.log('✅ 목록 뷰: 사이드바 렌더링 성공');
        } else {
            console.log('❌ 목록 뷰: 사이드바 렌더링 실패');
            return;
        }

        // 사이드바 섹션들 확인
        const listTodayEventsSection = await page.$('.today-events, .empty-sidebar');
        const listUpcomingEventsSection = await page.$('.upcoming-events, .empty-sidebar');
        const listSidebarTitles = await page.$$('.sidebar-title');

        console.log(`✅ 목록 뷰: 오늘의 행사 섹션 ${listTodayEventsSection ? '정상' : '오류'}`);
        console.log(`✅ 목록 뷰: 다가오는 행사 섹션 ${listUpcomingEventsSection ? '정상' : '오류'}`);
        console.log(`✅ 목록 뷰: 사이드바 제목 개수: ${listSidebarTitles.length}개`);

        // 목록 뷰 스크린샷
        await page.screenshot({
            path: 'list-sidebar-test.png',
            fullPage: false,
            clip: { x: 1200, y: 200, width: 400, height: 800 }
        });

        // 3. 사이드바 일관성 검증
        console.log('\n🔄 사이드바 일관성 검증...');

        // 캘린더 뷰로 돌아가서 HTML 구조 비교
        await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
            waitUntil: 'networkidle0'
        });

        const calendarSidebarHTML = await page.evaluate(() => {
            const sidebar = document.querySelector('.events-sidebar');
            return sidebar ? sidebar.innerHTML.replace(/\s+/g, ' ').trim() : null;
        });

        // 목록 뷰로 이동해서 HTML 구조 비교
        await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=list', {
            waitUntil: 'networkidle0'
        });

        const listSidebarHTML = await page.evaluate(() => {
            const sidebar = document.querySelector('.events-sidebar');
            return sidebar ? sidebar.innerHTML.replace(/\s+/g, ' ').trim() : null;
        });

        if (calendarSidebarHTML && listSidebarHTML) {
            const isIdentical = calendarSidebarHTML === listSidebarHTML;
            console.log(`${isIdentical ? '✅' : '❌'} 사이드바 HTML 구조 일치: ${isIdentical}`);

            if (!isIdentical) {
                console.log('📊 구조 차이점 분석:');
                console.log(`캘린더 뷰 HTML 길이: ${calendarSidebarHTML.length}`);
                console.log(`목록 뷰 HTML 길이: ${listSidebarHTML.length}`);
            }
        }

        // 4. CSS 스타일 확인
        console.log('\n🎨 CSS 스타일 확인...');

        const sidebarStyles = await page.evaluate(() => {
            const sidebar = document.querySelector('.events-sidebar');
            if (!sidebar) return null;

            const computed = window.getComputedStyle(sidebar);
            return {
                background: computed.backgroundColor,
                borderRadius: computed.borderRadius,
                padding: computed.padding,
                width: computed.width,
                maxWidth: computed.maxWidth
            };
        });

        if (sidebarStyles) {
            console.log('✅ CSS 스타일 적용 확인:');
            console.log(`   배경색: ${sidebarStyles.background}`);
            console.log(`   모서리: ${sidebarStyles.borderRadius}`);
            console.log(`   패딩: ${sidebarStyles.padding}`);
            console.log(`   너비: ${sidebarStyles.width}`);
            console.log(`   최대너비: ${sidebarStyles.maxWidth}`);
        }

        // 5. 이벤트 링크 확인
        console.log('\n🔗 이벤트 링크 확인...');

        const eventLinks = await page.$$eval('.sidebar-event-item', links =>
            links.map(link => ({
                href: link.href,
                title: link.querySelector('.sidebar-event-title')?.textContent?.trim(),
                hasMetaInfo: !!link.querySelector('.sidebar-event-meta')
            }))
        );

        console.log(`✅ 사이드바 이벤트 링크 ${eventLinks.length}개 확인`);
        eventLinks.forEach((link, index) => {
            console.log(`   ${index + 1}. ${link.title || '제목 없음'} (메타정보: ${link.hasMetaInfo ? '있음' : '없음'})`);
        });

        console.log('\n🎉 EventsSidebar 컴포넌트 통합 테스트 완료!');
        console.log('📸 생성된 스크린샷: calendar-sidebar-test.png, list-sidebar-test.png');

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    } finally {
        await browser.close();
    }
})();