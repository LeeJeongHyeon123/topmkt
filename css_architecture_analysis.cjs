/**
 * 🧠 Ultra Think: CSS 아키텍처 완전 분석
 * 모든 CSS 파일의 로드 순서, 충돌 상황, 우선순위 체계 분석
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🧠 Ultra Think: CSS 아키텍처 완전 분석 시작');

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 768, height: 1024 });

    try {
        console.log('\n📊 Phase 1: CSS 파일 구조 및 로드 순서 분석');

        await page.goto('https://www.topmktx.com/events?year=2025&month=3&view=list', {
            waitUntil: 'networkidle0',
            timeout: 15000
        });

        const cssAnalysis = await page.evaluate(() => {
            // 1. 모든 CSS 파일 수집
            const stylesheets = Array.from(document.styleSheets);
            const cssFiles = [];

            stylesheets.forEach((sheet, index) => {
                try {
                    const href = sheet.href || '인라인 CSS';
                    const disabled = sheet.disabled;
                    const media = sheet.media.mediaText || 'all';

                    // 규칙 수 계산
                    let ruleCount = 0;
                    try {
                        ruleCount = sheet.cssRules ? sheet.cssRules.length : 0;
                    } catch (e) {
                        ruleCount = 'CORS 차단';
                    }

                    cssFiles.push({
                        index,
                        href,
                        disabled,
                        media,
                        ruleCount,
                        type: href.includes('http') ? 'external' : 'inline'
                    });
                } catch (e) {
                    cssFiles.push({
                        index,
                        href: 'ERROR: ' + e.message,
                        disabled: true,
                        media: 'unknown',
                        ruleCount: 0,
                        type: 'error'
                    });
                }
            });

            // 2. events-layout 관련 CSS 규칙 수집
            const eventsLayoutRules = [];

            stylesheets.forEach((sheet, sheetIndex) => {
                try {
                    const rules = Array.from(sheet.cssRules || []);

                    rules.forEach((rule, ruleIndex) => {
                        // 일반 CSS 규칙
                        if (rule.selectorText && rule.selectorText.includes('events-layout')) {
                            eventsLayoutRules.push({
                                sheetIndex,
                                sheetHref: sheet.href || '인라인',
                                ruleIndex,
                                selector: rule.selectorText,
                                gridTemplateColumns: rule.style.gridTemplateColumns || 'not set',
                                mediaQuery: 'none',
                                parentRule: null,
                                specificity: calculateSpecificity(rule.selectorText)
                            });
                        }

                        // 미디어 쿼리 내부 규칙
                        if (rule.type === CSSRule.MEDIA_RULE) {
                            const mediaRules = Array.from(rule.cssRules || []);
                            mediaRules.forEach((mediaRule, mediaRuleIndex) => {
                                if (mediaRule.selectorText && mediaRule.selectorText.includes('events-layout')) {
                                    eventsLayoutRules.push({
                                        sheetIndex,
                                        sheetHref: sheet.href || '인라인',
                                        ruleIndex: `${ruleIndex}.${mediaRuleIndex}`,
                                        selector: mediaRule.selectorText,
                                        gridTemplateColumns: mediaRule.style.gridTemplateColumns || 'not set',
                                        mediaQuery: rule.conditionText || rule.media.mediaText,
                                        parentRule: 'media',
                                        specificity: calculateSpecificity(mediaRule.selectorText)
                                    });
                                }
                            });
                        }
                    });
                } catch (e) {
                    console.error('CSS 규칙 분석 오류:', e.message);
                }
            });

            // CSS Specificity 계산 함수 (간단 버전)
            function calculateSpecificity(selector) {
                if (!selector) return 0;

                const ids = (selector.match(/#/g) || []).length;
                const classes = (selector.match(/\./g) || []).length;
                const elements = (selector.match(/[a-z]/g) || []).length - classes;

                return ids * 100 + classes * 10 + elements;
            }

            // 3. 현재 적용된 최종 CSS 값
            const layout = document.querySelector('.events-layout');
            const finalStyles = layout ? window.getComputedStyle(layout) : null;

            return {
                cssFiles,
                eventsLayoutRules,
                finalStyles: finalStyles ? {
                    gridTemplateColumns: finalStyles.gridTemplateColumns,
                    display: finalStyles.display,
                    gap: finalStyles.gap,
                    width: finalStyles.width,
                    maxWidth: finalStyles.maxWidth
                } : null,
                mediaQueryStatus: {
                    width768: window.matchMedia('(max-width: 768px)').matches,
                    width1024: window.matchMedia('(max-width: 1024px)').matches,
                    currentWidth: window.innerWidth
                }
            };
        });

        // 결과 출력
        console.log('\n📄 로드된 CSS 파일들:');
        cssAnalysis.cssFiles.forEach((file, index) => {
            const status = file.disabled ? '❌ 비활성' : '✅ 활성';
            const type = file.type === 'external' ? '🌐 외부' : '📝 인라인';
            console.log(`   ${index + 1}. ${type} ${status}`);
            console.log(`      경로: ${file.href}`);
            console.log(`      미디어: ${file.media}`);
            console.log(`      규칙 수: ${file.ruleCount}`);
        });

        console.log('\n🎯 events-layout 관련 CSS 규칙들:');
        cssAnalysis.eventsLayoutRules
            .sort((a, b) => {
                // 우선순위: sheetIndex → specificity → ruleIndex
                if (a.sheetIndex !== b.sheetIndex) return a.sheetIndex - b.sheetIndex;
                if (a.specificity !== b.specificity) return b.specificity - a.specificity;
                return parseFloat(a.ruleIndex) - parseFloat(b.ruleIndex);
            })
            .forEach((rule, index) => {
                const mediaStatus = rule.mediaQuery === 'none' ? '일반' :
                    (rule.mediaQuery.includes('768') ? '768px 미디어쿼리' : rule.mediaQuery);
                console.log(`   ${index + 1}. [Sheet ${rule.sheetIndex}] ${rule.selector}`);
                console.log(`      파일: ${rule.sheetHref.split('/').pop()}`);
                console.log(`      grid-template-columns: ${rule.gridTemplateColumns}`);
                console.log(`      미디어쿼리: ${mediaStatus}`);
                console.log(`      우선순위: ${rule.specificity}`);
            });

        console.log('\n🎨 최종 적용된 스타일:');
        if (cssAnalysis.finalStyles) {
            Object.entries(cssAnalysis.finalStyles).forEach(([prop, value]) => {
                console.log(`   ${prop}: ${value}`);
            });
        }

        console.log('\n📱 미디어 쿼리 상태:');
        Object.entries(cssAnalysis.mediaQueryStatus).forEach(([key, value]) => {
            console.log(`   ${key}: ${value}`);
        });

        // 문제 진단
        console.log('\n🔍 문제 진단:');

        const has768MediaQuery = cssAnalysis.eventsLayoutRules.some(rule =>
            rule.mediaQuery && rule.mediaQuery.includes('768') && rule.gridTemplateColumns !== 'not set'
        );

        const finalGridColumns = cssAnalysis.finalStyles?.gridTemplateColumns;
        const shouldBe1fr = cssAnalysis.mediaQueryStatus.width768;

        console.log(`   768px 미디어쿼리 존재: ${has768MediaQuery ? '✅ 있음' : '❌ 없음'}`);
        console.log(`   768px 조건 충족: ${shouldBe1fr ? '✅ 예' : '❌ 아니오'}`);
        console.log(`   최종 grid-template-columns: ${finalGridColumns}`);
        console.log(`   예상값: 1fr, 실제값: ${finalGridColumns}`);

        if (shouldBe1fr && finalGridColumns !== '748px' && finalGridColumns !== '1fr') {
            console.log('   ❌ 문제 확인: 768px 조건에서 1컬럼이 적용되지 않음');

            // 우선순위 높은 규칙 찾기
            const conflictingRules = cssAnalysis.eventsLayoutRules
                .filter(rule => rule.mediaQuery === 'none' && rule.gridTemplateColumns.includes('320px'))
                .sort((a, b) => b.specificity - a.specificity);

            if (conflictingRules.length > 0) {
                console.log('   🎯 충돌 원인: 미디어쿼리보다 높은 우선순위 규칙');
                conflictingRules.slice(0, 3).forEach((rule, index) => {
                    console.log(`      ${index + 1}. ${rule.selector} (우선순위: ${rule.specificity})`);
                });
            }
        } else if (shouldBe1fr && (finalGridColumns === '748px' || finalGridColumns === '1fr')) {
            console.log('   ✅ 문제 없음: 768px에서 정상적으로 1컬럼 적용됨');
        }

        console.log('\n🎯 Phase 1 완료: CSS 구조 분석 완료');

    } catch (error) {
        console.error('❌ 분석 중 오류 발생:', error.message);
    } finally {
        await browser.close();
    }
})();