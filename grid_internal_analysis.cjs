/**
 * 🧠 Ultra Think Phase 3: Grid 내부 요소들의 실제 크기와 위치 분석
 * 1fr이 768px로 계산되는 정확한 메커니즘 파악
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🧠 Ultra Think Phase 3: Grid 내부 요소 분석');

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

        const gridInternalAnalysis = await page.evaluate(() => {
            const layout = document.querySelector('.events-layout');
            if (!layout) return { error: 'events-layout not found' };

            // Grid Container 분석
            const layoutRect = layout.getBoundingClientRect();
            const layoutStyles = window.getComputedStyle(layout);

            const gridInfo = {
                container: {
                    boundingRect: {
                        width: layoutRect.width,
                        height: layoutRect.height,
                        left: layoutRect.left,
                        right: layoutRect.right,
                        top: layoutRect.top,
                        bottom: layoutRect.bottom
                    },
                    client: {
                        clientWidth: layout.clientWidth,
                        clientHeight: layout.clientHeight,
                        scrollWidth: layout.scrollWidth,
                        scrollHeight: layout.scrollHeight
                    },
                    styles: {
                        display: layoutStyles.display,
                        gridTemplateColumns: layoutStyles.gridTemplateColumns,
                        gridTemplateRows: layoutStyles.gridTemplateRows,
                        gap: layoutStyles.gap,
                        columnGap: layoutStyles.columnGap,
                        rowGap: layoutStyles.rowGap,
                        width: layoutStyles.width,
                        maxWidth: layoutStyles.maxWidth,
                        padding: layoutStyles.padding,
                        margin: layoutStyles.margin,
                        boxSizing: layoutStyles.boxSizing
                    }
                }
            };

            // Grid 자식 요소들 분석
            const children = Array.from(layout.children);
            gridInfo.children = children.map((child, index) => {
                const childRect = child.getBoundingClientRect();
                const childStyles = window.getComputedStyle(child);

                return {
                    index,
                    element: {
                        tagName: child.tagName,
                        className: child.className,
                        id: child.id
                    },
                    boundingRect: {
                        width: childRect.width,
                        height: childRect.height,
                        left: childRect.left,
                        right: childRect.right,
                        top: childRect.top,
                        bottom: childRect.bottom
                    },
                    client: {
                        clientWidth: child.clientWidth,
                        clientHeight: child.clientHeight,
                        scrollWidth: child.scrollWidth,
                        scrollHeight: child.scrollHeight
                    },
                    styles: {
                        width: childStyles.width,
                        maxWidth: childStyles.maxWidth,
                        minWidth: childStyles.minWidth,
                        gridColumn: childStyles.gridColumn,
                        gridColumnStart: childStyles.gridColumnStart,
                        gridColumnEnd: childStyles.gridColumnEnd,
                        gridRow: childStyles.gridRow,
                        gridRowStart: childStyles.gridRowStart,
                        gridRowEnd: childStyles.gridRowEnd,
                        gridArea: childStyles.gridArea,
                        margin: childStyles.margin,
                        padding: childStyles.padding,
                        boxSizing: childStyles.boxSizing,
                        position: childStyles.position
                    },
                    // Container를 기준으로 한 상대 위치
                    relativePosition: {
                        left: childRect.left - layoutRect.left,
                        right: childRect.right - layoutRect.left,
                        width: childRect.width
                    }
                };
            });

            // Grid Track 크기 계산 시도
            const trackAnalysis = {
                containerAvailableWidth: layout.clientWidth,
                explicitTracks: layoutStyles.gridTemplateColumns,

                // 실제 자식 요소들의 위치로부터 track 크기 역추적
                inferredTracks: null
            };

            if (gridInfo.children.length >= 2) {
                const firstChild = gridInfo.children[0];
                const secondChild = gridInfo.children[1];

                // 첫 번째 자식이 첫 번째 트랙, 두 번째 자식이 두 번째 트랙이라고 가정
                const firstTrackWidth = firstChild.boundingRect.width;
                const secondTrackWidth = secondChild.boundingRect.width;
                const gap = parseFloat(layoutStyles.columnGap) || 0;

                trackAnalysis.inferredTracks = {
                    firstTrack: firstTrackWidth,
                    secondTrack: secondTrackWidth,
                    gap: gap,
                    total: firstTrackWidth + secondTrackWidth + gap,
                    matchesContainer: Math.abs((firstTrackWidth + secondTrackWidth + gap) - layout.clientWidth) < 1
                };
            }

            // 오버플로우 분석
            const overflowAnalysis = {
                containerOverflows: layout.scrollWidth > layout.clientWidth,
                containerOverflowAmount: layout.scrollWidth - layout.clientWidth,
                childrenExceedContainer: gridInfo.children.some(child =>
                    child.relativePosition.right > layout.clientWidth
                ),
                rightmostChild: gridInfo.children.reduce((max, child) =>
                    child.relativePosition.right > max ? child.relativePosition.right : max, 0
                )
            };

            // 최종 진단
            const diagnosis = {
                gridSystemWorking: layoutStyles.display === 'grid',
                explicitTemplateMatches: layoutStyles.gridTemplateColumns === '1fr',
                computedTemplateValue: layoutStyles.gridTemplateColumns,

                // 1fr이 768px로 계산되는 이유 추론
                frCalculationIssue: {
                    expected1frWidth: layout.clientWidth, // 738px 예상
                    actual1frWidth: parseFloat(layoutStyles.gridTemplateColumns), // 실제 값
                    discrepancy: parseFloat(layoutStyles.gridTemplateColumns) !== layout.clientWidth
                },

                // 실제 문제
                rootCause: null
            };

            // 근본 원인 추론
            if (overflowAnalysis.containerOverflows) {
                diagnosis.rootCause = 'Container 오버플로우로 인한 스크롤 발생';
            } else if (diagnosis.frCalculationIssue.discrepancy) {
                diagnosis.rootCause = '1fr 계산 메커니즘 이상';
            } else if (!trackAnalysis.inferredTracks?.matchesContainer) {
                diagnosis.rootCause = 'Grid track 크기와 컨테이너 크기 불일치';
            } else {
                diagnosis.rootCause = '명확하지 않음 - 추가 분석 필요';
            }

            return {
                gridInfo,
                trackAnalysis,
                overflowAnalysis,
                diagnosis,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                timestamp: Date.now()
            };
        });

        if (gridInternalAnalysis.error) {
            console.log('❌', gridInternalAnalysis.error);
            return;
        }

        // 결과 출력
        const analysis = gridInternalAnalysis;

        console.log('\n📊 Grid Container 상세 정보:');
        const container = analysis.gridInfo.container;
        console.log(`   크기: ${container.boundingRect.width}px × ${container.boundingRect.height}px`);
        console.log(`   클라이언트: ${container.client.clientWidth}px × ${container.client.clientHeight}px`);
        console.log(`   스크롤: ${container.client.scrollWidth}px × ${container.client.scrollHeight}px`);
        console.log(`   CSS 속성:`);
        console.log(`      display: ${container.styles.display}`);
        console.log(`      grid-template-columns: ${container.styles.gridTemplateColumns}`);
        console.log(`      gap: ${container.styles.gap}`);
        console.log(`      width: ${container.styles.width}`);

        console.log('\n👥 Grid 자식 요소들:');
        analysis.gridInfo.children.forEach((child, index) => {
            console.log(`   ${index + 1}. ${child.element.tagName}.${child.element.className}`);
            console.log(`      절대 위치: ${child.boundingRect.left}px ~ ${child.boundingRect.right}px (너비: ${child.boundingRect.width}px)`);
            console.log(`      상대 위치: ${child.relativePosition.left}px ~ ${child.relativePosition.right}px`);
            console.log(`      Grid 위치: column ${child.styles.gridColumnStart}-${child.styles.gridColumnEnd}`);
            console.log(`      CSS 너비: ${child.styles.width}, 최대: ${child.styles.maxWidth}`);

            if (child.relativePosition.right > container.client.clientWidth) {
                console.log(`      ⚠️  컨테이너를 벗어남! (+${child.relativePosition.right - container.client.clientWidth}px)`);
            }
        });

        console.log('\n🧮 Track 분석:');
        const track = analysis.trackAnalysis;
        console.log(`   컨테이너 사용가능 너비: ${track.containerAvailableWidth}px`);
        console.log(`   명시적 트랙: ${track.explicitTracks}`);

        if (track.inferredTracks) {
            const inferred = track.inferredTracks;
            console.log(`   역추적된 트랙 크기:`);
            console.log(`      첫 번째 트랙: ${inferred.firstTrack}px`);
            console.log(`      두 번째 트랙: ${inferred.secondTrack}px`);
            console.log(`      Gap: ${inferred.gap}px`);
            console.log(`      총합: ${inferred.total}px`);
            console.log(`      컨테이너와 일치: ${inferred.matchesContainer ? '✅ 예' : '❌ 아니오'}`);

            if (!inferred.matchesContainer) {
                console.log(`      ⚠️  불일치량: ${inferred.total - track.containerAvailableWidth}px`);
            }
        }

        console.log('\n📈 오버플로우 분석:');
        const overflow = analysis.overflowAnalysis;
        console.log(`   컨테이너 오버플로우: ${overflow.containerOverflows ? '❌ 있음' : '✅ 없음'}`);
        if (overflow.containerOverflows) {
            console.log(`   오버플로우 크기: ${overflow.containerOverflowAmount}px`);
        }
        console.log(`   자식이 컨테이너 초과: ${overflow.childrenExceedContainer ? '❌ 있음' : '✅ 없음'}`);
        console.log(`   가장 오른쪽 자식 위치: ${overflow.rightmostChild}px`);

        console.log('\n🔍 최종 진단:');
        const diag = analysis.diagnosis;
        console.log(`   Grid 시스템 작동: ${diag.gridSystemWorking ? '✅ 정상' : '❌ 비정상'}`);
        console.log(`   템플릿 명시: ${diag.explicitTemplateMatches ? '✅ 1fr 설정됨' : '❌ 1fr 아님'}`);
        console.log(`   계산된 템플릿 값: ${diag.computedTemplateValue}`);

        const frIssue = diag.frCalculationIssue;
        console.log(`   1fr 계산 문제:`);
        console.log(`      예상 1fr 너비: ${frIssue.expected1frWidth}px`);
        console.log(`      실제 계산된 값: ${frIssue.actual1frWidth}px`);
        console.log(`      불일치 여부: ${frIssue.discrepancy ? '❌ 있음' : '✅ 없음'}`);

        console.log(`\n🎯 추정 근본 원인: ${diag.rootCause}`);

        // 해결책 제시
        console.log('\n💡 해결책:');
        if (overflow.containerOverflows && overflow.containerOverflowAmount > 0) {
            console.log(`   1. 컨테이너 오버플로우 ${overflow.containerOverflowAmount}px 해결 필요`);
            console.log('   2. 768px에서 강제 1컬럼 적용 (가장 확실한 방법)');
            console.log('   3. 또는 사이드바 너비/Gap 조정으로 오버플로우 제거');
        }

        if (frIssue.discrepancy) {
            console.log('   4. 1fr 계산 메커니즘 문제 → CSS 미디어 쿼리 강화 또는 JavaScript 대체');
        }

        console.log('\n🎯 Phase 3 완료: Grid 내부 분석 완료');

    } catch (error) {
        console.error('❌ 분석 중 오류 발생:', error.message);
    } finally {
        await browser.close();
    }
})();