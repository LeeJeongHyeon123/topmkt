const playwright = require('playwright');

/**
 * 실제 존재하는 ID로 헤더 겹침 수정 최종 검증
 */

async function testHeaderFixFinal() {
    console.log('🔍 헤더 겹침 수정 최종 검증 (실제 ID)...\n');

    const browser = await playwright.chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext();
        const page = await context.newPage();
        
        const testPages = [
            {
                name: '공지사항',
                url: 'https://www.topmktx.com/notices/19',
                containerSelector: '.detail-container'
            },
            {
                name: '강의',
                url: 'https://www.topmktx.com/lectures/199',
                containerSelector: '.lecture-detail-container'
            },
            {
                name: '이벤트',
                url: 'https://www.topmktx.com/events/199',
                containerSelector: '.event-detail-container'
            }
        ];

        const results = [];

        for (const testPage of testPages) {
            console.log(`📄 ${testPage.name} 상세 페이지 테스트...`);
            
            try {
                await page.goto(testPage.url, { 
                    waitUntil: 'domcontentloaded',
                    timeout: 15000 
                });

                await page.waitForTimeout(2000); // 페이지 렌더링 대기

                // 데스크톱 뷰포트
                await page.setViewportSize({ width: 1920, height: 1080 });
                await page.waitForTimeout(1000);

                const info = await page.evaluate((containerSelector) => {
                    const header = document.querySelector('.main-header');
                    const container = document.querySelector(containerSelector);
                    
                    if (!header || !container) {
                        return { 
                            error: `요소를 찾을 수 없습니다. 헤더: ${!!header}, 컨테이너: ${!!container}` 
                        };
                    }
                    
                    const headerRect = header.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();
                    const containerStyles = window.getComputedStyle(container);
                    
                    return {
                        headerHeight: headerRect.height,
                        headerBottom: headerRect.bottom,
                        containerTop: containerRect.top,
                        clearance: containerRect.top - headerRect.bottom,
                        marginTop: containerStyles.marginTop,
                        paddingTop: containerStyles.paddingTop
                    };
                }, testPage.containerSelector);

                if (info.error) {
                    console.log(`   ❌ ${info.error}`);
                    results.push({ name: testPage.name, status: 'error', error: info.error });
                    continue;
                }

                // 모바일 뷰포트
                await page.setViewportSize({ width: 375, height: 667 });
                await page.waitForTimeout(1000);

                const mobileInfo = await page.evaluate((containerSelector) => {
                    const header = document.querySelector('.main-header');
                    const container = document.querySelector(containerSelector);
                    
                    if (!header || !container) {
                        return { error: '모바일에서 요소를 찾을 수 없습니다.' };
                    }
                    
                    const headerRect = header.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();
                    
                    return {
                        headerHeight: headerRect.height,
                        clearance: containerRect.top - headerRect.bottom
                    };
                }, testPage.containerSelector);

                console.log(`   CSS margin-top: ${info.marginTop}`);
                console.log(`   CSS padding-top: ${info.paddingTop}`);
                console.log(`   데스크톱 여유 공간: ${info.clearance.toFixed(1)}px`);
                console.log(`   모바일 여유 공간: ${mobileInfo.clearance.toFixed(1)}px`);
                
                const desktopOK = info.clearance >= 10;
                const mobileOK = mobileInfo.clearance >= 5;
                
                console.log(`   결과: 데스크톱 ${desktopOK ? '✅' : '❌'} | 모바일 ${mobileOK ? '✅' : '❌'}`);
                
                results.push({
                    name: testPage.name,
                    status: 'success',
                    desktopClearance: info.clearance,
                    mobileClearance: mobileInfo.clearance,
                    desktopOK,
                    mobileOK,
                    marginTop: info.marginTop
                });

            } catch (error) {
                console.log(`   ❌ 오류: ${error.message}`);
                results.push({ name: testPage.name, status: 'error', error: error.message });
            }
            
            console.log('');
        }

        // 종합 결과
        console.log('📊 헤더 겹침 수정 최종 결과:');
        console.log('='.repeat(50));
        
        let allSuccess = true;
        
        results.forEach(result => {
            if (result.status === 'success') {
                const status = result.desktopOK && result.mobileOK ? '✅ 완전 해결' : '⚠️  부분 해결';
                console.log(`${result.name}: ${status} (${result.marginTop})`);
                console.log(`   데스크톱: ${result.desktopClearance.toFixed(1)}px | 모바일: ${result.mobileClearance.toFixed(1)}px`);
                
                if (!result.desktopOK || !result.mobileOK) allSuccess = false;
            } else {
                console.log(`${result.name}: ❌ 테스트 실패 - ${result.error}`);
                allSuccess = false;
            }
        });
        
        console.log('='.repeat(50));
        
        if (allSuccess) {
            console.log('🎉 모든 상세 페이지의 헤더 겹침 문제가 완전히 해결되었습니다!');
            console.log('✨ 사용자가 보고한 "detail-container 제목이 짤리는" 문제가 수정되었습니다.');
        } else {
            console.log('⚠️  일부 페이지에서 추가 조정이 필요합니다.');
        }

        // 스크린샷 저장
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.goto('https://www.topmktx.com/notices/19', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        await page.screenshot({ 
            path: '/var/www/html/topmkt/header_fix_verification.png',
            clip: { x: 0, y: 0, width: 1920, height: 400 }
        });
        console.log('\n📸 검증 스크린샷 저장: header_fix_verification.png');

    } catch (error) {
        console.error('❌ 테스트 실행 오류:', error.message);
    } finally {
        await browser.close();
        console.log('\n🔚 최종 검증 완료');
    }
}

testHeaderFixFinal().catch(console.error);