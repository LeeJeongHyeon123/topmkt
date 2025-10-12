const playwright = require('playwright');

/**
 * 공지사항 상세 페이지 헤더 겹침 수정 간단 검증
 */

async function testNoticeHeaderFix() {
    console.log('🔍 공지사항 헤더 겹침 수정 검증...\n');

    const browser = await playwright.chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext();
        const page = await context.newPage();
        
        // 공지사항 상세 페이지로 이동
        console.log('📄 공지사항 상세 페이지 접속 중...');
        
        await page.goto('https://www.topmktx.com/notices/19', { 
            waitUntil: 'domcontentloaded',
            timeout: 30000 
        });

        console.log('✅ 페이지 로딩 완료');

        // 데스크톱 뷰포트로 설정
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.waitForTimeout(2000);

        // 헤더와 제목 위치 확인
        const headerInfo = await page.evaluate(() => {
            const header = document.querySelector('.main-header');
            const container = document.querySelector('.detail-container');
            const title = document.querySelector('.notice-title');
            
            if (!header || !container || !title) {
                return { error: '필요한 요소를 찾을 수 없습니다.' };
            }
            
            const headerRect = header.getBoundingClientRect();
            const titleRect = title.getBoundingClientRect();
            const containerStyles = window.getComputedStyle(container);
            
            return {
                headerHeight: headerRect.height,
                headerBottom: headerRect.bottom,
                titleTop: titleRect.top,
                clearance: titleRect.top - headerRect.bottom,
                containerMarginTop: containerStyles.marginTop,
                containerPaddingTop: containerStyles.paddingTop
            };
        });

        if (headerInfo.error) {
            console.log('❌', headerInfo.error);
            return;
        }

        console.log('\n📊 데스크톱 뷰포트 측정 결과:');
        console.log(`   헤더 높이: ${headerInfo.headerHeight}px`);
        console.log(`   헤더 하단: ${headerInfo.headerBottom}px`);
        console.log(`   제목 상단: ${headerInfo.titleTop}px`);
        console.log(`   여유 공간: ${headerInfo.clearance}px`);
        console.log(`   컨테이너 margin-top: ${headerInfo.containerMarginTop}`);
        console.log(`   컨테이너 padding-top: ${headerInfo.containerPaddingTop}`);

        // 모바일 뷰포트 테스트
        await page.setViewportSize({ width: 375, height: 667 });
        await page.waitForTimeout(1000);

        const mobileInfo = await page.evaluate(() => {
            const header = document.querySelector('.main-header');
            const title = document.querySelector('.notice-title');
            
            if (!header || !title) {
                return { error: '요소를 찾을 수 없습니다.' };
            }
            
            const headerRect = header.getBoundingClientRect();
            const titleRect = title.getBoundingClientRect();
            
            return {
                headerHeight: headerRect.height,
                titleTop: titleRect.top,
                clearance: titleRect.top - headerRect.bottom
            };
        });

        console.log('\n📱 모바일 뷰포트 측정 결과:');
        console.log(`   헤더 높이: ${mobileInfo.headerHeight}px`);
        console.log(`   제목 상단: ${mobileInfo.titleTop}px`);
        console.log(`   여유 공간: ${mobileInfo.clearance}px`);

        // 스크린샷 촬영
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.waitForTimeout(500);
        await page.screenshot({ 
            path: '/var/www/html/topmkt/notice_header_after_fix.png',
            clip: { x: 0, y: 0, width: 1920, height: 600 }
        });

        console.log('\n📸 스크린샷 저장: notice_header_after_fix.png');

        // 결과 판정
        const desktopOK = headerInfo.clearance >= 10;
        const mobileOK = mobileInfo.clearance >= 5;
        
        console.log('\n🎯 헤더 겹침 수정 결과:');
        console.log(`   데스크톱: ${desktopOK ? '✅ 문제 해결됨' : '❌ 여전히 문제 있음'} (${headerInfo.clearance}px)`);
        console.log(`   모바일: ${mobileOK ? '✅ 문제 해결됨' : '❌ 여전히 문제 있음'} (${mobileInfo.clearance}px)`);
        
        if (desktopOK && mobileOK) {
            console.log('\n🎉 헤더 겹침 문제가 완전히 해결되었습니다!');
        } else {
            console.log('\n⚠️  추가 조정이 필요합니다.');
        }

    } catch (error) {
        console.error('❌ 테스트 실행 오류:', error.message);
    } finally {
        await browser.close();
        console.log('\n🔚 테스트 완료');
    }
}

testNoticeHeaderFix().catch(console.error);