/**
 * 🧠 Ultra Think Phase 2: Grid Container 부모 요소 크기 제한 분석
 * Grid가 1fr을 768px로 계산하는 근본 원인 파악
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🧠 Ultra Think Phase 2: Container 계층 구조 분석');

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

        const hierarchyAnalysis = await page.evaluate(() => {
            // Grid Container부터 시작해서 부모 요소들을 거슬러 올라가며 분석
            const layout = document.querySelector('.events-layout');
            if (!layout) return { error: 'events-layout not found' };

            const hierarchy = [];
            let currentElement = layout;

            // 최대 10레벨까지 부모 요소 분석
            for (let level = 0; level < 10 && currentElement; level++) {
                const rect = currentElement.getBoundingClientRect();
                const computed = window.getComputedStyle(currentElement);

                const elementInfo = {
                    level,
                    tagName: currentElement.tagName,
                    className: currentElement.className,
                    id: currentElement.id,

                    // 크기 정보
                    clientWidth: currentElement.clientWidth,
                    scrollWidth: currentElement.scrollWidth,
                    offsetWidth: currentElement.offsetWidth,
                    boundingWidth: rect.width,

                    // CSS 크기 속성
                    width: computed.width,
                    maxWidth: computed.maxWidth,
                    minWidth: computed.minWidth,

                    // 박스 모델
                    paddingLeft: computed.paddingLeft,
                    paddingRight: computed.paddingRight,
                    marginLeft: computed.marginLeft,
                    marginRight: computed.marginRight,
                    borderLeftWidth: computed.borderLeftWidth,
                    borderRightWidth: computed.borderRightWidth,

                    // 레이아웃 정보
                    display: computed.display,
                    position: computed.position,
                    overflow: computed.overflow,
                    overflowX: computed.overflowX,
                    boxSizing: computed.boxSizing,

                    // 제약 조건
                    hasWidthConstraint: computed.width !== 'auto' && !computed.width.includes('%'),
                    hasMaxWidthConstraint: computed.maxWidth !== 'none',
                    hasOverflowHidden: computed.overflowX === 'hidden',

                    // 계산된 사용 가능 공간
                    availableWidth: currentElement.clientWidth -
                                   parseFloat(computed.paddingLeft) -
                                   parseFloat(computed.paddingRight)
                };

                hierarchy.push(elementInfo);
                currentElement = currentElement.parentElement;
            }

            // Grid 특화 분석
            const gridAnalysis = {
                container: hierarchy[0], // .events-layout
                parent: hierarchy[1],    // .events-container

                // Grid 계산 시뮬레이션
                expectedSingleColumn: hierarchy[0].availableWidth,
                expectedTwoColumn: {
                    firstColumn: hierarchy[0].availableWidth - 320 - 20, // width - sidebar - gap
                    secondColumn: 320,
                    gap: 20,
                    total: hierarchy[0].availableWidth
                }
            };

            // 문제 진단
            const diagnosis = {
                containerTooSmall: hierarchy[0].availableWidth < 800, // 320 + 480 minimum
                parentConstraints: [],
                bottleneck: null
            };

            // 크기 제약이 있는 부모 요소 찾기
            hierarchy.forEach((element, index) => {
                if (element.hasWidthConstraint || element.hasMaxWidthConstraint || element.hasOverflowHidden) {
                    diagnosis.parentConstraints.push({
                        level: index,
                        element: element.tagName + (element.className ? '.' + element.className : ''),
                        constraint: element.hasWidthConstraint ? `width: ${element.width}` :
                                   element.hasMaxWidthConstraint ? `max-width: ${element.maxWidth}` :
                                   'overflow-x: hidden',
                        availableWidth: element.availableWidth
                    });
                }
            });

            // 가장 제한적인 요소 찾기 (병목 지점)
            const widthConstraints = hierarchy.map(el => el.availableWidth).filter(w => w > 0);
            const minWidth = Math.min(...widthConstraints);
            const bottleneckIndex = hierarchy.findIndex(el => el.availableWidth === minWidth);

            if (bottleneckIndex !== -1) {
                diagnosis.bottleneck = {
                    level: bottleneckIndex,
                    element: hierarchy[bottleneckIndex].tagName +
                            (hierarchy[bottleneckIndex].className ? '.' + hierarchy[bottleneckIndex].className : ''),
                    availableWidth: minWidth,
                    reason: hierarchy[bottleneckIndex].hasWidthConstraint ? 'width 제한' :
                           hierarchy[bottleneckIndex].hasMaxWidthConstraint ? 'max-width 제한' :
                           hierarchy[bottleneckIndex].hasOverflowHidden ? 'overflow hidden' :
                           '패딩/여백'
                };
            }

            return {
                hierarchy,
                gridAnalysis,
                diagnosis,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };
        });

        if (hierarchyAnalysis.error) {
            console.log('❌', hierarchyAnalysis.error);
            return;
        }

        // 결과 출력
        console.log('\n📏 Container 계층 구조 (Grid → 부모 순서):');
        hierarchyAnalysis.hierarchy.forEach((element, index) => {
            const constraint = element.hasWidthConstraint || element.hasMaxWidthConstraint || element.hasOverflowHidden;
            const constraintIcon = constraint ? '⚠️' : '✅';

            console.log(`   ${index}. ${constraintIcon} ${element.tagName}${element.className ? '.' + element.className : ''}`);
            console.log(`      사용가능 너비: ${element.availableWidth}px (client: ${element.clientWidth}px)`);
            console.log(`      CSS width: ${element.width}, max-width: ${element.maxWidth}`);
            console.log(`      패딩: ${element.paddingLeft} | ${element.paddingRight}`);
            console.log(`      overflow-x: ${element.overflowX}, box-sizing: ${element.boxSizing}`);

            if (constraint) {
                const reasons = [];
                if (element.hasWidthConstraint) reasons.push(`고정 너비 (${element.width})`);
                if (element.hasMaxWidthConstraint) reasons.push(`최대 너비 제한 (${element.maxWidth})`);
                if (element.hasOverflowHidden) reasons.push('가로 스크롤 숨김');
                console.log(`      🚫 제약 조건: ${reasons.join(', ')}`);
            }
        });

        console.log('\n🧮 Grid 계산 분석:');
        const grid = hierarchyAnalysis.gridAnalysis;
        console.log(`   컨테이너 사용가능 너비: ${grid.container.availableWidth}px`);
        console.log(`   1컬럼 모드 예상 너비: ${grid.expectedSingleColumn}px`);
        console.log(`   2컬럼 모드 계산:`);
        console.log(`      첫 번째 컬럼: ${grid.expectedTwoColumn.firstColumn}px`);
        console.log(`      두 번째 컬럼: ${grid.expectedTwoColumn.secondColumn}px`);
        console.log(`      Gap: ${grid.expectedTwoColumn.gap}px`);
        console.log(`      전체 필요 너비: ${grid.expectedTwoColumn.total}px`);

        console.log('\n🔍 문제 진단:');
        const diag = hierarchyAnalysis.diagnosis;
        console.log(`   컨테이너가 2컬럼에 부족: ${diag.containerTooSmall ? '❌ 예' : '✅ 아니오'}`);
        console.log(`   크기 제약 요소 수: ${diag.parentConstraints.length}개`);

        if (diag.parentConstraints.length > 0) {
            console.log('\n🚫 크기 제약 요소들:');
            diag.parentConstraints.forEach((constraint, index) => {
                console.log(`   ${index + 1}. Level ${constraint.level}: ${constraint.element}`);
                console.log(`      제약: ${constraint.constraint}`);
                console.log(`      사용가능: ${constraint.availableWidth}px`);
            });
        }

        if (diag.bottleneck) {
            console.log('\n🎯 핵심 병목 지점:');
            console.log(`   요소: ${diag.bottleneck.element} (Level ${diag.bottleneck.level})`);
            console.log(`   사용가능 너비: ${diag.bottleneck.availableWidth}px`);
            console.log(`   제한 원인: ${diag.bottleneck.reason}`);

            if (diag.bottleneck.availableWidth < 800) {
                console.log('   💡 해결책: 이 요소의 크기 제한을 완화하거나 768px에서 1컬럼으로 전환 필요');
            }
        }

        // 근본 원인 분석
        console.log('\n🧠 근본 원인 분석:');
        if (grid.container.availableWidth < 800) {
            console.log(`   ❌ 문제 확인: 사용가능 너비 ${grid.container.availableWidth}px < 필요 너비 800px`);
            console.log('   📋 해결 방향:');
            console.log('      1. 768px에서 1컬럼 Grid 강제 적용 (현재 시도 중)');
            console.log('      2. 사이드바 너비 줄이기 (320px → 280px)');
            console.log('      3. Gap 줄이기 (20px → 10px)');
            console.log('      4. Container 패딩 줄이기');
        } else {
            console.log('   ✅ 크기는 충분하지만 다른 원인 존재');
        }

        console.log('\n🎯 Phase 2 완료: Container 계층 분석 완료');

    } catch (error) {
        console.error('❌ 분석 중 오류 발생:', error.message);
    } finally {
        await browser.close();
    }
})();