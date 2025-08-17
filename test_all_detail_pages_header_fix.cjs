const playwright = require('playwright');

/**
 * 모든 상세 페이지 헤더 겹침 수정 최종 검증
 * - 공지사항 상세 (/notices/19)
 * - 강의 상세 (존재하는 강의 찾기)
 * - 이벤트 상세 (존재하는 이벤트 찾기)
 */

async function testAllDetailPagesHeaderFix() {
    console.log('🔍 모든 상세 페이지 헤더 겹침 수정 최종 검증...\n');

    const browser = await playwright.chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext();
        const page = await context.newPage();
        
        const testPages = [
            {
                name: '공지사항 상세',
                url: 'https://www.topmktx.com/notices/19',
                containerSelector: '.detail-container',
                titleSelector: '.notice-title'
            },
            {
                name: '강의 상세',
                url: 'https://www.topmktx.com/lectures/142', // 존재할 가능성이 높은 ID
                containerSelector: '.lecture-detail-container',
                titleSelector: '.lecture-title'
            },
            {
                name: '이벤트 상세',
                url: 'https://www.topmktx.com/events/197', // 존재할 가능성이 높은 ID
                containerSelector: '.event-detail-container',
                titleSelector: '.event-title, .hero-title' // 이벤트는 다른 제목 선택자를 사용할 수 있음
            }
        ];

        const results = [];

        for (const testPage of testPages) {
            console.log(`📄 ${testPage.name} 테스트 중...`);
            
            try {
                await page.goto(testPage.url, { 
                    waitUntil: 'domcontentloaded',
                    timeout: 15000 
                });

                // 페이지 로딩 확인
                const containerExists = await page.$(testPage.containerSelector);
                if (!containerExists) {
                    console.log(`   ⚠️  컨테이너(${testPage.containerSelector})를 찾을 수 없습니다.`);
                    results.push({ name: testPage.name, status: 'container_not_found' });
                    continue;
                }

                // 데스크톱 뷰포트 테스트
                await page.setViewportSize({ width: 1920, height: 1080 });
                await page.waitForTimeout(1000);

                const desktopInfo = await page.evaluate((selectors) => {
                    const header = document.querySelector('.main-header');
                    const container = document.querySelector(selectors.container);
                    const title = document.querySelector(selectors.title);
                    
                    if (!header || !container) {
                        return { error: '필요한 요소를 찾을 수 없습니다.' };
                    }
                    
                    const headerRect = header.getBoundingClientRect();
                    const containerStyles = window.getComputedStyle(container);
                    
                    let titleInfo = { top: null, found: false };
                    if (title) {
                        const titleRect = title.getBoundingClientRect();
                        titleInfo = { top: titleRect.top, found: true };
                    }
                    
                    return {
                        headerHeight: headerRect.height,
                        headerBottom: headerRect.bottom,
                        titleTop: titleInfo.top,
                        titleFound: titleInfo.found,
                        clearance: titleInfo.found ? titleInfo.top - headerRect.bottom : null,
                        containerMarginTop: containerStyles.marginTop,
                        containerPaddingTop: containerStyles.paddingTop
                    };
                }, { container: testPage.containerSelector, title: testPage.titleSelector });

                if (desktopInfo.error) {
                    console.log(`   ❌ ${desktopInfo.error}`);
                    results.push({ name: testPage.name, status: 'error', error: desktopInfo.error });
                    continue;
                }

                // 모바일 뷰포트 테스트
                await page.setViewportSize({ width: 375, height: 667 });
                await page.waitForTimeout(1000);

                const mobileInfo = await page.evaluate((selectors) => {
                    const header = document.querySelector('.main-header');
                    const title = document.querySelector(selectors.title);
                    
                    if (!header) {
                        return { error: '헤더를 찾을 수 없습니다.' };
                    }
                    
                    const headerRect = header.getBoundingClientRect();
                    
                    let titleInfo = { top: null, found: false };
                    if (title) {
                        const titleRect = title.getBoundingClientRect();
                        titleInfo = { top: titleRect.top, found: true };
                    }
                    
                    return {
                        headerHeight: headerRect.height,
                        titleTop: titleInfo.top,
                        titleFound: titleInfo.found,
                        clearance: titleInfo.found ? titleInfo.top - headerRect.bottom : null
                    };
                }, { container: testPage.containerSelector, title: testPage.titleSelector });

                console.log(`   헤더 높이: ${desktopInfo.headerHeight}px`);
                console.log(`   컨테이너 margin-top: ${desktopInfo.containerMarginTop}`);
                console.log(`   컨테이너 padding-top: ${desktopInfo.containerPaddingTop}`);
                
                if (desktopInfo.titleFound) {
                    console.log(`   데스크톱 여유 공간: ${desktopInfo.clearance}px`);
                    console.log(`   모바일 여유 공간: ${mobileInfo.clearance}px`);
                    
                    const desktopOK = desktopInfo.clearance >= 10;
                    const mobileOK = mobileInfo.clearance >= 5;
                    
                    console.log(`   데스크톱: ${desktopOK ? '✅' : '❌'} | 모바일: ${mobileOK ? '✅' : '❌'}`);
                    
                    results.push({
                        name: testPage.name,
                        status: 'success',
                        desktopClearance: desktopInfo.clearance,
                        mobileClearance: mobileInfo.clearance,
                        desktopOK,
                        mobileOK
                    });
                } else {
                    console.log(`   ⚠️  제목 요소(${testPage.titleSelector})를 찾을 수 없지만 컨테이너 여백은 설정됨`);
                    results.push({
                        name: testPage.name,
                        status: 'title_not_found',
                        containerMarginTop: desktopInfo.containerMarginTop
                    });
                }

            } catch (error) {
                console.log(`   ❌ 오류: ${error.message}`);
                results.push({ name: testPage.name, status: 'error', error: error.message });
            }
            
            console.log('');
        }

        // 종합 결과
        console.log('📊 헤더 겹침 수정 최종 검증 결과:');
        console.log('='.repeat(50));
        
        const successCount = results.filter(r => r.status === 'success' && r.desktopOK && r.mobileOK).length;
        
        results.forEach(result => {
            if (result.status === 'success') {
                const status = result.desktopOK && result.mobileOK ? '✅ 완전 해결' : '⚠️  부분 해결';
                console.log(`${result.name}: ${status}`);
                console.log(`   데스크톱: ${result.desktopClearance}px | 모바일: ${result.mobileClearance}px`);
            } else if (result.status === 'title_not_found') {
                console.log(`${result.name}: ⚠️  제목 확인 불가 (여백 설정됨: ${result.containerMarginTop})`);
            } else {
                console.log(`${result.name}: ❌ ${result.error || result.status}`);
            }
        });
        
        console.log('='.repeat(50));
        console.log(`성공: ${successCount}/${testPages.length} 페이지`);
        
        if (successCount === testPages.length) {
            console.log('🎉 모든 상세 페이지의 헤더 겹침 문제가 완전히 해결되었습니다!');
        } else {
            console.log('⚠️  일부 페이지에서 추가 확인이 필요할 수 있습니다.');
        }

    } catch (error) {
        console.error('❌ 테스트 실행 오류:', error.message);
    } finally {
        await browser.close();
        console.log('\n🔚 최종 검증 완료');
    }
}

testAllDetailPagesHeaderFix().catch(console.error);