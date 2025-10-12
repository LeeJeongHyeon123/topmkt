const playwright = require('playwright');

/**
 * 공지사항 상세 페이지 헤더 겹침 수정 검증 테스트
 * 
 * 검증 사항:
 * 1. 데스크톱에서 제목이 헤더에 가려지지 않음
 * 2. 모바일에서 제목이 헤더에 가려지지 않음
 * 3. 모든 뷰포트에서 적절한 상단 여백 확보
 */

async function testNoticeHeaderOverlapFix() {
    console.log('🔍 공지사항 상세 페이지 헤더 겹침 수정 검증 시작...\n');

    const browser = await playwright.chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext();
        const page = await context.newPage();
        
        // 공지사항 상세 페이지로 이동 (공지사항 19)
        const noticeUrl = 'https://www.topmktx.com/notices/19';
        console.log(`📄 공지사항 상세 페이지 로딩: ${noticeUrl}`);
        
        await page.goto(noticeUrl, { 
            waitUntil: 'networkidle',
            timeout: 10000 
        });

        // 페이지 로딩 완료 대기
        await page.waitForSelector('.detail-container', { timeout: 5000 });
        console.log('✅ 페이지 로딩 완료\n');

        // 테스트 1: 데스크톱 뷰포트 (1920x1080)
        console.log('🖥️  데스크톱 뷰포트 테스트 (1920x1080)');
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.waitForTimeout(1000); // 리플로우 대기

        // 헤더와 컨테이너 위치 측정
        const desktopHeaderHeight = await page.evaluate(() => {
            const header = document.querySelector('.main-header');
            return header ? header.offsetHeight : 0;
        });

        const desktopContainerTop = await page.evaluate(() => {
            const container = document.querySelector('.detail-container');
            const rect = container.getBoundingClientRect();
            return rect.top + window.scrollY;
        });

        const desktopTitleTop = await page.evaluate(() => {
            const title = document.querySelector('.notice-title');
            const rect = title.getBoundingClientRect();
            return rect.top;
        });

        console.log(`   헤더 높이: ${desktopHeaderHeight}px`);
        console.log(`   컨테이너 상단 위치: ${desktopContainerTop}px`);
        console.log(`   제목 상단 위치: ${desktopTitleTop}px`);
        
        const desktopClearance = desktopTitleTop - desktopHeaderHeight;
        console.log(`   헤더-제목 간 여유 공간: ${desktopClearance}px`);
        
        if (desktopClearance >= 10) {
            console.log('   ✅ 데스크톱: 헤더 겹침 없음 (충분한 여유 공간)');
        } else {
            console.log('   ❌ 데스크톱: 헤더 겹침 위험 (여유 공간 부족)');
        }
        console.log('');

        // 테스트 2: 태블릿 뷰포트 (768x1024)
        console.log('📱 태블릿 뷰포트 테스트 (768x1024)');
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.waitForTimeout(1000); // 리플로우 대기

        const tabletHeaderHeight = await page.evaluate(() => {
            const header = document.querySelector('.main-header');
            return header ? header.offsetHeight : 0;
        });

        const tabletTitleTop = await page.evaluate(() => {
            const title = document.querySelector('.notice-title');
            const rect = title.getBoundingClientRect();
            return rect.top;
        });

        console.log(`   헤더 높이: ${tabletHeaderHeight}px`);
        console.log(`   제목 상단 위치: ${tabletTitleTop}px`);
        
        const tabletClearance = tabletTitleTop - tabletHeaderHeight;
        console.log(`   헤더-제목 간 여유 공간: ${tabletClearance}px`);
        
        if (tabletClearance >= 10) {
            console.log('   ✅ 태블릿: 헤더 겹침 없음 (충분한 여유 공간)');
        } else {
            console.log('   ❌ 태블릿: 헤더 겹침 위험 (여유 공간 부족)');
        }
        console.log('');

        // 테스트 3: 모바일 뷰포트 (375x667)
        console.log('📱 모바일 뷰포트 테스트 (375x667)');
        await page.setViewportSize({ width: 375, height: 667 });
        await page.waitForTimeout(1000); // 리플로우 대기

        const mobileHeaderHeight = await page.evaluate(() => {
            const header = document.querySelector('.main-header');
            return header ? header.offsetHeight : 0;
        });

        const mobileTitleTop = await page.evaluate(() => {
            const title = document.querySelector('.notice-title');
            const rect = title.getBoundingClientRect();
            return rect.top;
        });

        console.log(`   헤더 높이: ${mobileHeaderHeight}px`);
        console.log(`   제목 상단 위치: ${mobileTitleTop}px`);
        
        const mobileClearance = mobileTitleTop - mobileHeaderHeight;
        console.log(`   헤더-제목 간 여유 공간: ${mobileClearance}px`);
        
        if (mobileClearance >= 5) { // 모바일은 조금 더 관대하게
            console.log('   ✅ 모바일: 헤더 겹침 없음 (충분한 여유 공간)');
        } else {
            console.log('   ❌ 모바일: 헤더 겹침 위험 (여유 공간 부족)');
        }
        console.log('');

        // 테스트 4: CSS 스타일 검증
        console.log('🎨 CSS 스타일 적용 검증');
        const containerStyles = await page.evaluate(() => {
            const container = document.querySelector('.detail-container');
            const styles = window.getComputedStyle(container);
            return {
                marginTop: styles.marginTop,
                paddingTop: styles.paddingTop
            };
        });

        console.log(`   .detail-container margin-top: ${containerStyles.marginTop}`);
        console.log(`   .detail-container padding-top: ${containerStyles.paddingTop}`);
        
        if (parseInt(containerStyles.marginTop) >= 70) {
            console.log('   ✅ CSS: 적절한 상단 여백 설정됨');
        } else {
            console.log('   ❌ CSS: 상단 여백 부족');
        }
        console.log('');

        // 스크린샷 촬영 (각 뷰포트)
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.waitForTimeout(500);
        await page.screenshot({ 
            path: '/var/www/html/topmkt/notice_header_fix_desktop.png',
            fullPage: false
        });
        console.log('📸 데스크톱 스크린샷 저장: notice_header_fix_desktop.png');

        await page.setViewportSize({ width: 375, height: 667 });
        await page.waitForTimeout(500);
        await page.screenshot({ 
            path: '/var/www/html/topmkt/notice_header_fix_mobile.png',
            fullPage: false
        });
        console.log('📸 모바일 스크린샷 저장: notice_header_fix_mobile.png');

        // 종합 결과
        console.log('\n📊 헤더 겹침 수정 검증 결과:');
        console.log(`   데스크톱 여유 공간: ${desktopClearance}px ${desktopClearance >= 10 ? '✅' : '❌'}`);
        console.log(`   태블릿 여유 공간: ${tabletClearance}px ${tabletClearance >= 10 ? '✅' : '❌'}`);
        console.log(`   모바일 여유 공간: ${mobileClearance}px ${mobileClearance >= 5 ? '✅' : '❌'}`);
        
        const allTestsPassed = desktopClearance >= 10 && tabletClearance >= 10 && mobileClearance >= 5;
        
        if (allTestsPassed) {
            console.log('\n🎉 모든 뷰포트에서 헤더 겹침 문제가 완전히 해결되었습니다!');
        } else {
            console.log('\n⚠️  일부 뷰포트에서 여전히 조정이 필요할 수 있습니다.');
        }

    } catch (error) {
        console.error('❌ 테스트 실행 중 오류 발생:', error.message);
        
        // 오류 스크린샷
        try {
            await page.screenshot({ 
                path: '/var/www/html/topmkt/notice_header_fix_error.png',
                fullPage: true 
            });
            console.log('📸 오류 스크린샷 저장: notice_header_fix_error.png');
        } catch (screenshotError) {
            console.error('스크린샷 저장 실패:', screenshotError.message);
        }
    } finally {
        await browser.close();
        console.log('\n🔚 테스트 완료\n');
    }
}

// 테스트 실행
testNoticeHeaderOverlapFix().catch(console.error);