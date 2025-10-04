/**
 * Grid 템플릿이 768px로 나오는 문제를 정확히 분석
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🔍 Grid 768px 문제 정밀 분석');

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

        const detailedAnalysis = await page.evaluate(() => {
            const layout = document.querySelector('.events-layout');
            if (!layout) return { error: 'layout not found' };

            const computed = window.getComputedStyle(layout);

            // Grid 관련 모든 속성 수집
            const gridProperties = {
                display: computed.display,
                gridTemplateColumns: computed.gridTemplateColumns,
                gridTemplateRows: computed.gridTemplateRows,
                gridAutoColumns: computed.gridAutoColumns,
                gridAutoRows: computed.gridAutoRows,
                gap: computed.gap,
                columnGap: computed.columnGap,
                rowGap: computed.rowGap,
                width: computed.width,
                maxWidth: computed.maxWidth,
                minWidth: computed.minWidth
            };

            // 실제 컬럼 정보 추출
            const gridTrackSizes = {
                columnCount: layout.style.gridTemplateColumns ? layout.style.gridTemplateColumns.split(' ').length : 'auto',
                explicitColumns: computed.gridTemplateColumns,
                actualColumnWidths: []
            };

            // Grid 자식들의 위치 정보
            const children = Array.from(layout.children);
            const childrenPositions = children.map(child => {
                const rect = child.getBoundingClientRect();
                const childComputed = window.getComputedStyle(child);
                return {
                    className: child.className,
                    left: rect.left,
                    right: rect.right,
                    width: rect.width,
                    gridColumn: childComputed.gridColumn,
                    gridColumnStart: childComputed.gridColumnStart,
                    gridColumnEnd: childComputed.gridColumnEnd
                };
            });

            // 미디어 쿼리 적용 상태 확인
            const mediaQueries = {
                width768: window.matchMedia('(max-width: 768px)').matches,
                width1024: window.matchMedia('(max-width: 1024px)').matches,
                width1199: window.matchMedia('(max-width: 1199px)').matches,
                currentWidth: window.innerWidth
            };

            // CSS 규칙들 중에서 events-layout 관련 규칙 찾기
            const stylesheets = Array.from(document.styleSheets);
            const relevantRules = [];

            stylesheets.forEach(sheet => {
                try {
                    const rules = Array.from(sheet.cssRules || sheet.rules || []);
                    rules.forEach(rule => {
                        if (rule.selectorText && rule.selectorText.includes('events-layout')) {
                            const ruleInfo = {
                                selector: rule.selectorText,
                                gridTemplateColumns: rule.style.gridTemplateColumns || 'not set',
                                mediaQuery: rule.parentRule ? rule.parentRule.conditionText : 'none'
                            };
                            relevantRules.push(ruleInfo);
                        }
                    });
                } catch (e) {
                    // CORS 등으로 접근 불가한 스타일시트 무시
                }
            });

            return {
                gridProperties,
                gridTrackSizes,
                childrenPositions,
                mediaQueries,
                relevantRules,
                layoutRect: layout.getBoundingClientRect()
            };
        });

        if (detailedAnalysis.error) {
            console.log('❌ Layout을 찾을 수 없습니다.');
            return;
        }

        console.log('\n📐 Grid 속성 상세 분석:');
        Object.entries(detailedAnalysis.gridProperties).forEach(([key, value]) => {
            console.log(`   ${key}: ${value}`);
        });

        console.log('\n🎯 Grid 트랙 분석:');
        console.log(`   명시적 컬럼: ${detailedAnalysis.gridTrackSizes.explicitColumns}`);
        console.log(`   컬럼 개수: ${detailedAnalysis.gridTrackSizes.columnCount}`);

        console.log('\n👥 자식 요소들 위치:');
        detailedAnalysis.childrenPositions.forEach((child, index) => {
            console.log(`   ${index + 1}. ${child.className}:`);
            console.log(`      위치: left ${child.left}px, right ${child.right}px (너비: ${child.width}px)`);
            console.log(`      Grid 컬럼: ${child.gridColumn} (start: ${child.gridColumnStart}, end: ${child.gridColumnEnd})`);
            if (child.right > 768) {
                console.log(`      ⚠️  화면을 벗어남! (${child.right}px > 768px)`);
            }
        });

        console.log('\n📱 미디어 쿼리 상태:');
        Object.entries(detailedAnalysis.mediaQueries).forEach(([key, value]) => {
            console.log(`   ${key}: ${value}`);
        });

        console.log('\n📋 관련 CSS 규칙들:');
        detailedAnalysis.relevantRules.forEach((rule, index) => {
            console.log(`   ${index + 1}. ${rule.selector}`);
            console.log(`      grid-template-columns: ${rule.gridTemplateColumns}`);
            console.log(`      미디어 쿼리: ${rule.mediaQuery}`);
        });

        // Grid template columns가 768px로 나오는 이유 분석
        const templateColumns = detailedAnalysis.gridProperties.gridTemplateColumns;
        if (templateColumns === '768px') {
            console.log('\n❌ 문제 발견: Grid template columns가 768px (고정값)');
            console.log('   예상: "1fr" (유연한 너비)');
            console.log('   실제: "768px" (고정 너비)');
            console.log('   → 이는 CSS 미디어 쿼리가 적용되지 않았음을 의미');
        } else if (templateColumns.includes('1fr')) {
            console.log('\n✅ Grid template columns 정상: ' + templateColumns);
        }

    } catch (error) {
        console.error('❌ 분석 중 오류:', error.message);
    } finally {
        await browser.close();
    }
})();