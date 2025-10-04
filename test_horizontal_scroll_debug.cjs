/**
 * 768px 화면에서 events-container 가로 스크롤 문제 진단
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🔍 768px 화면 가로 스크롤 문제 진단 시작');

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();

    // 768px 화면으로 설정 (태블릿 세로 모드)
    await page.setViewport({ width: 768, height: 1024 });

    try {
        console.log('\n📱 768px 뷰포트로 이벤트 목록 페이지 접속...');
        await page.goto('https://www.topmktx.com/events?year=2025&month=3&view=list', {
            waitUntil: 'networkidle0',
            timeout: 15000
        });

        // 1. events-container의 현재 크기 및 스크롤 정보 확인
        const containerInfo = await page.evaluate(() => {
            const container = document.querySelector('.events-container');
            if (!container) return null;

            const rect = container.getBoundingClientRect();
            const computed = window.getComputedStyle(container);

            return {
                width: rect.width,
                height: rect.height,
                scrollWidth: container.scrollWidth,
                scrollHeight: container.scrollHeight,
                hasHorizontalScroll: container.scrollWidth > container.clientWidth,
                hasVerticalScroll: container.scrollHeight > container.clientHeight,
                computedWidth: computed.width,
                computedMaxWidth: computed.maxWidth,
                computedMinWidth: computed.minWidth,
                padding: computed.padding,
                margin: computed.margin,
                boxSizing: computed.boxSizing,
                overflow: computed.overflow,
                overflowX: computed.overflowX,
                overflowY: computed.overflowY
            };
        });

        if (containerInfo) {
            console.log('\n📊 events-container 분석 결과:');
            console.log(`   실제 너비: ${containerInfo.width}px`);
            console.log(`   스크롤 너비: ${containerInfo.scrollWidth}px`);
            console.log(`   가로 스크롤 발생: ${containerInfo.hasHorizontalScroll ? '❌ YES' : '✅ NO'}`);
            console.log(`   CSS 너비: ${containerInfo.computedWidth}`);
            console.log(`   CSS 최대너비: ${containerInfo.computedMaxWidth}`);
            console.log(`   박스 사이징: ${containerInfo.boxSizing}`);
            console.log(`   오버플로우: ${containerInfo.overflow} (X: ${containerInfo.overflowX})`);
            console.log(`   패딩: ${containerInfo.padding}`);
            console.log(`   마진: ${containerInfo.margin}`);
        } else {
            console.log('❌ .events-container 요소를 찾을 수 없습니다.');
            return;
        }

        // 2. 자식 요소들의 너비 분석
        const childrenInfo = await page.evaluate(() => {
            const container = document.querySelector('.events-container');
            if (!container) return null;

            const children = Array.from(container.children);
            return children.map(child => {
                const rect = child.getBoundingClientRect();
                const computed = window.getComputedStyle(child);
                return {
                    tagName: child.tagName,
                    className: child.className,
                    width: rect.width,
                    computedWidth: computed.width,
                    computedMinWidth: computed.minWidth,
                    computedMaxWidth: computed.maxWidth,
                    margin: computed.margin,
                    padding: computed.padding,
                    boxSizing: computed.boxSizing,
                    position: computed.position,
                    display: computed.display
                };
            });
        });

        if (childrenInfo && childrenInfo.length > 0) {
            console.log('\n🔍 자식 요소들 분석:');
            childrenInfo.forEach((child, index) => {
                console.log(`   ${index + 1}. ${child.tagName}.${child.className}:`);
                console.log(`      너비: ${child.width}px (CSS: ${child.computedWidth})`);
                console.log(`      최소/최대: ${child.computedMinWidth} / ${child.computedMaxWidth}`);
                console.log(`      박스사이징: ${child.boxSizing}, 디스플레이: ${child.display}`);
                if (child.width > 768) {
                    console.log(`      ⚠️  화면보다 넓음! (${child.width}px > 768px)`);
                }
            });
        }

        // 3. 전체 body와 html의 오버플로우 상태 확인
        const documentInfo = await page.evaluate(() => {
            const body = document.body;
            const html = document.documentElement;
            const bodyStyles = window.getComputedStyle(body);
            const htmlStyles = window.getComputedStyle(html);

            return {
                bodyOverflowX: bodyStyles.overflowX,
                bodyWidth: body.scrollWidth,
                htmlOverflowX: htmlStyles.overflowX,
                htmlWidth: html.scrollWidth,
                windowWidth: window.innerWidth,
                documentWidth: document.documentElement.clientWidth
            };
        });

        console.log('\n🌐 전체 문서 분석:');
        console.log(`   윈도우 너비: ${documentInfo.windowWidth}px`);
        console.log(`   문서 너비: ${documentInfo.documentWidth}px`);
        console.log(`   Body 스크롤 너비: ${documentInfo.bodyWidth}px`);
        console.log(`   HTML 스크롤 너비: ${documentInfo.htmlWidth}px`);
        console.log(`   Body 오버플로우-X: ${documentInfo.bodyOverflowX}`);
        console.log(`   HTML 오버플로우-X: ${documentInfo.htmlOverflowX}`);

        // 4. 768px보다 넓은 모든 요소 찾기
        const wideElements = await page.evaluate(() => {
            const allElements = document.querySelectorAll('*');
            const wideElements = [];

            allElements.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.width > 768) {
                    const computed = window.getComputedStyle(el);
                    wideElements.push({
                        tagName: el.tagName,
                        className: el.className,
                        id: el.id,
                        width: rect.width,
                        computedWidth: computed.width,
                        computedMinWidth: computed.minWidth,
                        display: computed.display,
                        position: computed.position
                    });
                }
            });

            return wideElements;
        });

        if (wideElements.length > 0) {
            console.log('\n⚠️  768px보다 넓은 요소들:');
            wideElements.forEach((el, index) => {
                console.log(`   ${index + 1}. ${el.tagName}${el.className ? '.' + el.className : ''}${el.id ? '#' + el.id : ''}:`);
                console.log(`      실제 너비: ${el.width}px (CSS: ${el.computedWidth})`);
                console.log(`      디스플레이: ${el.display}, 포지션: ${el.position}`);
            });
        } else {
            console.log('\n✅ 768px보다 넓은 요소가 없습니다.');
        }

        // 5. 스크린샷 촬영
        await page.screenshot({
            path: 'horizontal-scroll-debug-768px.png',
            fullPage: true
        });

        console.log('\n📸 스크린샷 저장: horizontal-scroll-debug-768px.png');
        console.log('🎯 문제 요소 식별 완료!');

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    } finally {
        await browser.close();
    }
})();