/**
 * 실제 사용 가능한 공간과 Grid 계산 분석
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🔍 실제 사용 가능 공간 분석');

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 768, height: 1024 });

    try {
        await page.goto('https://www.topmktx.com/events?year=2025&month=3&view=list', {
            waitUntil: 'networkidle0'
        });

        const spaceAnalysis = await page.evaluate(() => {
            // 전체 계층 구조 분석
            const body = document.body;
            const container = document.querySelector('.events-container');
            const layout = document.querySelector('.events-layout');

            const bodyRect = body.getBoundingClientRect();
            const containerRect = container?.getBoundingClientRect();
            const layoutRect = layout?.getBoundingClientRect();

            const bodyStyles = window.getComputedStyle(body);
            const containerStyles = container ? window.getComputedStyle(container) : null;
            const layoutStyles = layout ? window.getComputedStyle(layout) : null;

            // 실제 1fr이 얼마인지 계산
            const containerPaddingLeft = container ? parseFloat(containerStyles.paddingLeft) : 0;
            const containerPaddingRight = container ? parseFloat(containerStyles.paddingRight) : 0;
            const layoutGap = layout ? parseFloat(layoutStyles.gap) : 0;

            const availableWidth = containerRect ?
                containerRect.width - containerPaddingLeft - containerPaddingRight : 0;

            // 2컬럼 Grid에서 1fr의 실제 크기 계산
            // 1fr + 320px + gap = available width
            // 1fr = available width - 320px - gap
            const calculatedFirstColumn = availableWidth - 320 - layoutGap;

            return {
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                body: {
                    width: bodyRect.width,
                    scrollWidth: body.scrollWidth,
                    hasHorizontalScroll: body.scrollWidth > bodyRect.width
                },
                container: {
                    width: containerRect?.width,
                    scrollWidth: container?.scrollWidth,
                    paddingLeft: containerPaddingLeft,
                    paddingRight: containerPaddingRight,
                    availableWidth: availableWidth,
                    hasHorizontalScroll: container ? container.scrollWidth > container.clientWidth : null
                },
                layout: {
                    width: layoutRect?.width,
                    scrollWidth: layout?.scrollWidth,
                    gridTemplateColumns: layoutStyles?.gridTemplateColumns,
                    gap: layoutGap,
                    calculatedFirstColumn: calculatedFirstColumn
                },
                problem: {
                    isFirstColumnTooWide: calculatedFirstColumn > availableWidth - 320 - layoutGap,
                    actualOverflow: layout?.scrollWidth - layoutRect?.width,
                    shouldBeSingleColumn: availableWidth < 800  // 320 사이드바 + 480 최소 컨텐츠
                }
            };
        });

        console.log('\n📊 공간 분석 결과:');
        console.log(`   뷰포트: ${spaceAnalysis.viewport.width}px × ${spaceAnalysis.viewport.height}px`);
        console.log(`   Body 너비: ${spaceAnalysis.body.width}px (스크롤: ${spaceAnalysis.body.scrollWidth}px)`);
        console.log(`   Container 너비: ${spaceAnalysis.container.width}px (사용가능: ${spaceAnalysis.container.availableWidth}px)`);
        console.log(`   Layout 너비: ${spaceAnalysis.layout.width}px (스크롤: ${spaceAnalysis.layout.scrollWidth}px)`);

        console.log('\n🧮 Grid 계산:');
        console.log(`   Grid 템플릿: ${spaceAnalysis.layout.gridTemplateColumns}`);
        console.log(`   Gap: ${spaceAnalysis.layout.gap}px`);
        console.log(`   1fr 계산값: ${spaceAnalysis.layout.calculatedFirstColumn}px`);
        console.log(`   실제 첫 번째 컬럼 필요 공간: ${spaceAnalysis.layout.calculatedFirstColumn + spaceAnalysis.layout.gap + 320}px`);

        console.log('\n⚠️  문제 분석:');
        console.log(`   첫 번째 컬럼 너무 넓음: ${spaceAnalysis.problem.isFirstColumnTooWide ? '예' : '아니오'}`);
        console.log(`   실제 오버플로우: ${spaceAnalysis.problem.actualOverflow}px`);
        console.log(`   단일 컬럼 필요: ${spaceAnalysis.problem.shouldBeSingleColumn ? '예' : '아니오'}`);

        // 가로 스크롤 발생 위치 정확히 파악
        console.log('\n📍 스크롤 발생 분석:');
        console.log(`   Body 가로 스크롤: ${spaceAnalysis.body.hasHorizontalScroll ? '있음' : '없음'}`);
        console.log(`   Container 가로 스크롤: ${spaceAnalysis.container.hasHorizontalScroll ? '있음' : '없음'}`);

        if (spaceAnalysis.container.availableWidth < 640) {
            console.log('\n💡 해결책: 768px 이하에서는 반드시 단일 컬럼이 필요');
            console.log(`   현재 사용 가능 너비 (${spaceAnalysis.container.availableWidth}px)로는 2컬럼 불가능`);
        }

    } catch (error) {
        console.error('❌ 분석 중 오류:', error.message);
    } finally {
        await browser.close();
    }
})();