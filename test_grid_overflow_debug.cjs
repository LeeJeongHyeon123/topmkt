/**
 * events-layout Grid 시스템 상세 분석
 * 768px에서 가로 스크롤 원인 정확히 파악
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🔍 Grid 시스템 상세 분석 시작');

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

        // 1. events-layout Grid 상세 분석
        const gridInfo = await page.evaluate(() => {
            const layout = document.querySelector('.events-layout');
            if (!layout) return null;

            const rect = layout.getBoundingClientRect();
            const computed = window.getComputedStyle(layout);

            // Grid 관련 정보
            const gridTemplateColumns = computed.gridTemplateColumns;
            const gridGap = computed.gridGap;
            const gap = computed.gap;
            const columnGap = computed.columnGap;
            const rowGap = computed.rowGap;

            // Grid 자식들 정보
            const children = Array.from(layout.children);
            const childrenInfo = children.map(child => {
                const childRect = child.getBoundingClientRect();
                const childComputed = window.getComputedStyle(child);
                return {
                    className: child.className,
                    width: childRect.width,
                    computedWidth: childComputed.width,
                    computedMinWidth: childComputed.minWidth,
                    computedMaxWidth: childComputed.maxWidth,
                    gridColumn: childComputed.gridColumn,
                    gridRow: childComputed.gridRow,
                    margin: childComputed.margin,
                    padding: childComputed.padding
                };
            });

            return {
                width: rect.width,
                scrollWidth: layout.scrollWidth,
                computedWidth: computed.width,
                gridTemplateColumns,
                gridGap,
                gap,
                columnGap,
                rowGap,
                display: computed.display,
                boxSizing: computed.boxSizing,
                padding: computed.padding,
                margin: computed.margin,
                childrenInfo
            };
        });

        if (gridInfo) {
            console.log('\n📐 events-layout Grid 분석:');
            console.log(`   실제 너비: ${gridInfo.width}px`);
            console.log(`   스크롤 너비: ${gridInfo.scrollWidth}px`);
            console.log(`   CSS 너비: ${gridInfo.computedWidth}`);
            console.log(`   Grid 템플릿 컬럼: ${gridInfo.gridTemplateColumns}`);
            console.log(`   Grid Gap: ${gridInfo.gap} (컬럼: ${gridInfo.columnGap}, 행: ${gridInfo.rowGap})`);
            console.log(`   패딩: ${gridInfo.padding}`);
            console.log(`   마진: ${gridInfo.margin}`);

            if (gridInfo.scrollWidth > gridInfo.width) {
                console.log(`   ⚠️  Grid 내용이 넘침! (${gridInfo.scrollWidth}px > ${gridInfo.width}px)`);
            }

            console.log('\n🧩 Grid 자식 요소들:');
            gridInfo.childrenInfo.forEach((child, index) => {
                console.log(`   ${index + 1}. ${child.className}:`);
                console.log(`      너비: ${child.width}px (CSS: ${child.computedWidth})`);
                console.log(`      Min/Max: ${child.computedMinWidth} / ${child.computedMaxWidth}`);
                console.log(`      Grid Column: ${child.gridColumn}`);
                console.log(`      마진: ${child.margin}, 패딩: ${child.padding}`);
            });
        }

        // 2. 사이드바 상세 분석 (Grid 자식 중 하나)
        const sidebarInfo = await page.evaluate(() => {
            const sidebar = document.querySelector('.events-sidebar');
            if (!sidebar) return null;

            const rect = sidebar.getBoundingClientRect();
            const computed = window.getComputedStyle(sidebar);

            return {
                width: rect.width,
                computedWidth: computed.width,
                computedMinWidth: computed.minWidth,
                computedMaxWidth: computed.maxWidth,
                margin: computed.margin,
                padding: computed.padding,
                boxSizing: computed.boxSizing,
                position: computed.position,
                left: rect.left,
                right: rect.right
            };
        });

        if (sidebarInfo) {
            console.log('\n📋 events-sidebar 상세 분석:');
            console.log(`   실제 너비: ${sidebarInfo.width}px`);
            console.log(`   CSS 너비: ${sidebarInfo.computedWidth}`);
            console.log(`   Min/Max 너비: ${sidebarInfo.computedMinWidth} / ${sidebarInfo.computedMaxWidth}`);
            console.log(`   박스 사이징: ${sidebarInfo.boxSizing}`);
            console.log(`   위치: left ${sidebarInfo.left}px, right ${sidebarInfo.right}px`);
            console.log(`   마진: ${sidebarInfo.margin}`);
            console.log(`   패딩: ${sidebarInfo.padding}`);

            if (sidebarInfo.right > 768) {
                console.log(`   ⚠️  사이드바가 화면을 벗어남! (right: ${sidebarInfo.right}px > 768px)`);
            }
        }

        // 3. events-list-content 분석
        const listContentInfo = await page.evaluate(() => {
            const content = document.querySelector('.events-list-content');
            if (!content) return null;

            const rect = content.getBoundingClientRect();
            const computed = window.getComputedStyle(content);

            return {
                width: rect.width,
                computedWidth: computed.width,
                computedMinWidth: computed.minWidth,
                computedMaxWidth: computed.maxWidth,
                left: rect.left,
                right: rect.right,
                margin: computed.margin,
                padding: computed.padding
            };
        });

        if (listContentInfo) {
            console.log('\n📝 events-list-content 분석:');
            console.log(`   실제 너비: ${listContentInfo.width}px`);
            console.log(`   CSS 너비: ${listContentInfo.computedWidth}`);
            console.log(`   위치: left ${listContentInfo.left}px, right ${listContentInfo.right}px`);

            if (listContentInfo.right > 768) {
                console.log(`   ⚠️  리스트 컨텐츠가 화면을 벗어남! (right: ${listContentInfo.right}px > 768px)`);
            }
        }

        // 4. 현재 적용된 CSS 미디어 쿼리 확인
        const mediaQueryInfo = await page.evaluate(() => {
            // 768px 관련 미디어 쿼리 체크
            const is768 = window.matchMedia('(max-width: 768px)').matches;
            const is1199 = window.matchMedia('(max-width: 1199px)').matches;
            const is1024 = window.matchMedia('(max-width: 1024px)').matches;

            return {
                is768,
                is1199,
                is1024,
                windowWidth: window.innerWidth
            };
        });

        console.log('\n📱 미디어 쿼리 상태:');
        console.log(`   윈도우 너비: ${mediaQueryInfo.windowWidth}px`);
        console.log(`   @media (max-width: 768px): ${mediaQueryInfo.is768 ? '✅ 적용' : '❌ 미적용'}`);
        console.log(`   @media (max-width: 1024px): ${mediaQueryInfo.is1024 ? '✅ 적용' : '❌ 미적용'}`);
        console.log(`   @media (max-width: 1199px): ${mediaQueryInfo.is1199 ? '✅ 적용' : '❌ 미적용'}`);

        console.log('\n🎯 Grid 시스템 분석 완료!');

    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    } finally {
        await browser.close();
    }
})();