/**
 * 🧠 Ultra Think: Flexbox 전환 후 최종 검증
 * Grid → Flexbox 전환이 768px 가로 스크롤 문제를 해결했는지 확인
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🧠 Ultra Think: Flexbox 해결책 최종 검증');

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 768, height: 1024 });

    try {
        await page.goto('https://www.topmktx.com/events?year=2025&month=3&view=list', {
            waitUntil: 'networkidle0',
            timeout: 15000
        });

        const flexboxResults = await page.evaluate(() => {
            const container = document.querySelector('.events-container');
            const layout = document.querySelector('.events-layout');
            const main = document.querySelector('.events-main');
            const sidebar = document.querySelector('.events-sidebar');

            // Container 분석
            const containerRect = container?.getBoundingClientRect();
            const containerStyles = container ? window.getComputedStyle(container) : null;

            // Layout 분석 (Grid → Flexbox 전환)
            const layoutRect = layout?.getBoundingClientRect();
            const layoutStyles = layout ? window.getComputedStyle(layout) : null;

            // 자식 요소들 분석
            const mainRect = main?.getBoundingClientRect();
            const mainStyles = main ? window.getComputedStyle(main) : null;

            const sidebarRect = sidebar?.getBoundingClientRect();
            const sidebarStyles = sidebar ? window.getComputedStyle(sidebar) : null;

            // 핵심 검증 포인트들
            const verification = {
                // Layout이 Flexbox로 전환되었는가?
                isFlexbox: layoutStyles?.display === 'flex',
                flexDirection: layoutStyles?.flexDirection,

                // Container 오버플로우 해결되었는가?
                containerOverflow: container ? container.scrollWidth > container.clientWidth : null,
                containerScrollWidth: container?.scrollWidth,
                containerClientWidth: container?.clientWidth,

                // Layout 오버플로우 해결되었는가?
                layoutOverflow: layout ? layout.scrollWidth > layout.clientWidth : null,
                layoutScrollWidth: layout?.scrollWidth,
                layoutClientWidth: layout?.clientWidth,

                // 자식 요소들이 화면을 벗어나지 않는가?
                mainWithinContainer: mainRect ? mainRect.right <= (containerRect?.right || 0) : null,
                sidebarWithinContainer: sidebarRect ? sidebarRect.right <= (containerRect?.right || 0) : null,

                // 실제 크기들
                containerWidth: containerRect?.width,
                layoutWidth: layoutRect?.width,
                mainWidth: mainRect?.width,
                sidebarWidth: sidebarRect?.width,

                // 위치 정보
                mainPosition: {
                    left: mainRect?.left,
                    right: mainRect?.right,
                    width: mainRect?.width
                },
                sidebarPosition: {
                    left: sidebarRect?.left,
                    right: sidebarRect?.right,
                    width: sidebarRect?.width
                },

                // 뷰포트 정보
                viewportWidth: window.innerWidth,
                documentScrollWidth: document.documentElement.scrollWidth
            };

            return verification;
        });

        // 결과 출력
        console.log('\n🧠 Flexbox 전환 검증 결과:');

        console.log('\n📊 레이아웃 시스템:');
        console.log(`   Display: ${flexboxResults.isFlexbox ? '✅ Flexbox' : '❌ Grid/기타'}`);
        console.log(`   Flex Direction: ${flexboxResults.flexDirection || 'not set'}`);

        console.log('\n📏 크기 정보:');
        console.log(`   뷰포트: ${flexboxResults.viewportWidth}px`);
        console.log(`   Container: ${flexboxResults.containerWidth}px`);
        console.log(`   Layout: ${flexboxResults.layoutWidth}px`);
        console.log(`   Main: ${flexboxResults.mainWidth}px`);
        console.log(`   Sidebar: ${flexboxResults.sidebarWidth}px`);

        console.log('\n🔄 오버플로우 검사:');
        const containerOK = !flexboxResults.containerOverflow;
        const layoutOK = !flexboxResults.layoutOverflow;

        console.log(`   Container 오버플로우: ${containerOK ? '✅ 해결됨' : '❌ 여전히 존재'}`);
        if (flexboxResults.containerOverflow) {
            console.log(`      스크롤 너비: ${flexboxResults.containerScrollWidth}px vs 클라이언트: ${flexboxResults.containerClientWidth}px`);
        }

        console.log(`   Layout 오버플로우: ${layoutOK ? '✅ 해결됨' : '❌ 여전히 존재'}`);
        if (flexboxResults.layoutOverflow) {
            console.log(`      스크롤 너비: ${flexboxResults.layoutScrollWidth}px vs 클라이언트: ${flexboxResults.layoutClientWidth}px`);
        }

        console.log('\n📍 위치 검사:');
        const mainOK = flexboxResults.mainWithinContainer;
        const sidebarOK = flexboxResults.sidebarWithinContainer;

        console.log(`   Main 컨테이너 내: ${mainOK ? '✅ 예' : '❌ 벗어남'}`);
        console.log(`      위치: ${flexboxResults.mainPosition.left}px ~ ${flexboxResults.mainPosition.right}px`);

        console.log(`   Sidebar 컨테이너 내: ${sidebarOK ? '✅ 예' : '❌ 벗어남'}`);
        console.log(`      위치: ${flexboxResults.sidebarPosition.left}px ~ ${flexboxResults.sidebarPosition.right}px`);

        // 최종 결과
        console.log('\n🎯 최종 검증 결과:');
        const allChecksPass = flexboxResults.isFlexbox &&
                             containerOK &&
                             layoutOK &&
                             mainOK &&
                             sidebarOK;

        if (allChecksPass) {
            console.log('✅ 완전 성공: 768px 가로 스크롤 문제 완전 해결!');
            console.log('🎉 Ultra Think 근본 해결 완료!');

            // 성공 스크린샷
            await page.screenshot({
                path: 'flexbox-solution-success-768px.png',
                fullPage: true
            });
            console.log('📸 성공 스크린샷: flexbox-solution-success-768px.png');
        } else {
            console.log('❌ 부분 성공 또는 추가 문제 존재:');

            if (!flexboxResults.isFlexbox) console.log('   - Flexbox 전환 실패');
            if (!containerOK) console.log('   - Container 오버플로우 지속');
            if (!layoutOK) console.log('   - Layout 오버플로우 지속');
            if (!mainOK) console.log('   - Main 요소가 여전히 컨테이너 초과');
            if (!sidebarOK) console.log('   - Sidebar 요소가 여전히 컨테이너 초과');

            console.log('\n💡 추가 조치 필요:');
            if (!flexboxResults.isFlexbox) {
                console.log('   1. CSS 캐시 완전 삭제 또는 더 강한 CSS Specificity');
            }
            if (flexboxResults.containerOverflow || flexboxResults.layoutOverflow) {
                console.log('   2. 패딩/마진 조정 또는 사이드바 크기 축소');
            }

            // 디버그 스크린샷
            await page.screenshot({
                path: 'flexbox-solution-debug-768px.png',
                fullPage: true
            });
            console.log('📸 디버그 스크린샷: flexbox-solution-debug-768px.png');
        }

        console.log('\n🧠 Ultra Think Phase 완료');

    } catch (error) {
        console.error('❌ 검증 중 오류 발생:', error.message);
    } finally {
        await browser.close();
    }
})();