const { chromium } = require('playwright');

async function testEventsPages() {
    console.log('🎯 이벤트 페이지 컴포넌트화 완료 후 테스트 시작...');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    });

    const page = await context.newPage();

    try {
        // 1. 캘린더 뷰 테스트
        console.log('\n1️⃣ 캘린더 뷰 테스트 중...');
        await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=calendar', {
            waitUntil: 'networkidle'
        });

        // 페이지 로드 대기
        await page.waitForTimeout(3000);

        // 캘린더 뷰 헤더 영역 스크린샷
        await page.screenshot({
            path: 'events-calendar-header-test.png',
            clip: { x: 0, y: 0, width: 1440, height: 400 },
            fullPage: false
        });

        // 캘린더 뷰 전체 스크린샷
        await page.screenshot({
            path: 'events-calendar-full-test.png',
            fullPage: true
        });

        // 헤더 텍스트 확인
        const calendarHeaderText = await page.textContent('.events-header h2').catch(() => null);
        console.log('📅 캘린더 뷰 헤더 텍스트:', calendarHeaderText);

        // 컨트롤 버튼 확인
        const calendarControls = await page.$$eval('.calendar-controls .btn', buttons =>
            buttons.map(btn => ({
                text: btn.textContent.trim(),
                classes: btn.className
            }))
        ).catch(() => []);
        console.log('🎮 캘린더 뷰 컨트롤 버튼들:', calendarControls);

        // 2. 목록 뷰로 전환 테스트
        console.log('\n2️⃣ 목록 뷰로 전환 테스트 중...');

        // 목록 뷰 버튼 클릭
        const listViewBtn = page.locator('#listViewBtn');
        if (await listViewBtn.isVisible()) {
            await listViewBtn.click();
            await page.waitForTimeout(2000);
        } else {
            // 직접 URL로 이동
            await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=list', {
                waitUntil: 'networkidle'
            });
        }

        await page.waitForTimeout(3000);

        // 목록 뷰 헤더 영역 스크린샷
        await page.screenshot({
            path: 'events-list-header-test.png',
            clip: { x: 0, y: 0, width: 1440, height: 400 },
            fullPage: false
        });

        // 목록 뷰 전체 스크린샷
        await page.screenshot({
            path: 'events-list-full-test.png',
            fullPage: true
        });

        // 헤더 텍스트 확인
        const listHeaderText = await page.textContent('.events-header h2').catch(() => null);
        console.log('📋 목록 뷰 헤더 텍스트:', listHeaderText);

        // 컨트롤 버튼 확인
        const listControls = await page.$$eval('.calendar-controls .btn', buttons =>
            buttons.map(btn => ({
                text: btn.textContent.trim(),
                classes: btn.className
            }))
        ).catch(() => []);
        console.log('🎮 목록 뷰 컨트롤 버튼들:', listControls);

        // 3. 캘린더 뷰로 다시 전환 테스트
        console.log('\n3️⃣ 캘린더 뷰로 재전환 테스트 중...');

        const calendarViewBtn = page.locator('#calendarViewBtn');
        if (await calendarViewBtn.isVisible()) {
            await calendarViewBtn.click();
            await page.waitForTimeout(2000);
        }

        // 4. 모바일 반응형 테스트
        console.log('\n4️⃣ 모바일 반응형 테스트 중...');

        // 모바일 뷰포트로 변경
        await page.setViewportSize({ width: 375, height: 812 });
        await page.waitForTimeout(2000);

        // 모바일 캘린더 뷰 스크린샷
        await page.screenshot({
            path: 'events-mobile-calendar-test.png',
            fullPage: true
        });

        // 모바일 목록 뷰로 전환
        await page.goto('https://www.topmktx.com/events?year=2025&month=9&view=list', {
            waitUntil: 'networkidle'
        });
        await page.waitForTimeout(3000);

        // 모바일 목록 뷰 스크린샷
        await page.screenshot({
            path: 'events-mobile-list-test.png',
            fullPage: true
        });

        // 5. 헤더 스타일 검증
        console.log('\n5️⃣ 헤더 스타일 검증 중...');

        const headerStyles = await page.evaluate(() => {
            const header = document.querySelector('.events-header');
            if (!header) return null;

            const styles = window.getComputedStyle(header);
            return {
                background: styles.background,
                backgroundImage: styles.backgroundImage,
                color: styles.color,
                textAlign: styles.textAlign
            };
        });

        console.log('🎨 헤더 스타일:', headerStyles);

        console.log('\n✅ 이벤트 페이지 테스트 완료!');
        console.log('\n📊 테스트 결과 요약:');
        console.log(`- 캘린더 뷰 헤더: ${calendarHeaderText || 'N/A'}`);
        console.log(`- 목록 뷰 헤더: ${listHeaderText || 'N/A'}`);
        console.log(`- 헤더 텍스트 일치: ${calendarHeaderText === listHeaderText ? '✅' : '❌'}`);
        console.log(`- 스크린샷 생성: 6개 파일 생성됨`);

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error);
    } finally {
        await browser.close();
    }
}

testEventsPages();