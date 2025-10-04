// 🔍 자동 CSS 디버깅 도구 - 개발자 도구처럼 CSS cascade 분석
const { chromium } = require('playwright');

async function autoCssDebugger() {
    console.log('🔍 자동 CSS 디버깅 시작...');

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 375, height: 667 }
    });

    const page = await context.newPage();

    try {
        await page.goto('https://www.topmktx.com/events?view=calendar&t=' + Date.now(), {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        await page.waitForTimeout(3000);

        // 🔥 핵심: CSS 규칙 자동 분석
        const cssAnalysis = await page.evaluate(() => {
            const calendarView = document.querySelector('.calendar-view');
            if (!calendarView) return { error: '.calendar-view not found' };

            // 모든 적용된 CSS 규칙 수집
            const allRules = [];
            const sheets = Array.from(document.styleSheets);

            // 인라인 스타일 확인
            const inlineStyle = calendarView.style.cssText;
            if (inlineStyle) {
                allRules.push({
                    type: 'inline',
                    selector: 'style attribute',
                    cssText: inlineStyle,
                    specificity: 1000 // 인라인은 최고 우선순위
                });
            }

            // 모든 CSS 규칙 검사
            sheets.forEach((sheet, sheetIndex) => {
                try {
                    const rules = Array.from(sheet.cssRules || sheet.rules || []);
                    rules.forEach((rule, ruleIndex) => {
                        if (rule.type === 1) { // CSSStyleRule
                            try {
                                if (calendarView.matches(rule.selectorText)) {
                                    allRules.push({
                                        type: 'stylesheet',
                                        selector: rule.selectorText,
                                        cssText: rule.style.cssText,
                                        sheet: sheet.href || `inline-${sheetIndex}`,
                                        ruleIndex: ruleIndex,
                                        specificity: calculateSpecificity(rule.selectorText)
                                    });
                                }
                            } catch (e) {
                                // selector matching 실패 시 무시
                            }
                        }
                    });
                } catch (e) {
                    // CORS 등으로 접근 불가능한 stylesheet 무시
                }
            });

            // CSS 변수와 computed style 분석
            const computedStyle = window.getComputedStyle(calendarView);
            const computedValues = {
                width: computedStyle.width,
                maxWidth: computedStyle.maxWidth,
                minWidth: computedStyle.minWidth,
                margin: computedStyle.margin,
                marginLeft: computedStyle.marginLeft,
                marginRight: computedStyle.marginRight,
                marginTop: computedStyle.marginTop,
                marginBottom: computedStyle.marginBottom,
                position: computedStyle.position,
                left: computedStyle.left,
                right: computedStyle.right,
                transform: computedStyle.transform,
                display: computedStyle.display,
                visibility: computedStyle.visibility,
                opacity: computedStyle.opacity
            };

            // 특별히 margin-right가 어디서 오는지 추적
            const marginRightTrace = [];
            allRules.forEach(rule => {
                if (rule.cssText.includes('margin') || rule.cssText.includes('margin-right')) {
                    marginRightTrace.push({
                        selector: rule.selector,
                        cssText: rule.cssText,
                        specificity: rule.specificity,
                        source: rule.sheet || 'inline'
                    });
                }
            });

            // CSS 우선순위 계산 함수
            function calculateSpecificity(selector) {
                let specificity = 0;
                const idCount = (selector.match(/#/g) || []).length;
                const classCount = (selector.match(/\./g) || []).length;
                const elementCount = (selector.match(/[a-zA-Z]/g) || []).length;

                specificity = idCount * 100 + classCount * 10 + elementCount;
                if (selector.includes('!important')) specificity += 10000;

                return specificity;
            }

            return {
                boundingBox: calendarView.getBoundingClientRect(),
                computedValues,
                allRules: allRules.sort((a, b) => b.specificity - a.specificity),
                marginRightTrace: marginRightTrace.sort((a, b) => b.specificity - a.specificity),
                totalRulesFound: allRules.length
            };
        });

        console.log('📊 CSS 자동 분석 결과:');
        console.log('='.repeat(80));

        console.log('\n🎯 캘린더 위치 정보:');
        if (cssAnalysis.boundingBox) {
            console.log(`위치: x=${cssAnalysis.boundingBox.x}, y=${cssAnalysis.boundingBox.y}`);
            console.log(`크기: ${cssAnalysis.boundingBox.width}×${cssAnalysis.boundingBox.height}`);
            console.log(`화면 내 표시: ${cssAnalysis.boundingBox.x >= 0 && cssAnalysis.boundingBox.x + cssAnalysis.boundingBox.width <= 375 ? '✅' : '❌'}`);
        }

        console.log('\n🔍 최종 계산된 CSS 값들:');
        Object.entries(cssAnalysis.computedValues).forEach(([prop, value]) => {
            console.log(`${prop}: ${value}`);
        });

        console.log('\n🚨 margin-right 추적 결과:');
        cssAnalysis.marginRightTrace.forEach((rule, index) => {
            console.log(`${index + 1}. [우선순위: ${rule.specificity}] ${rule.selector}`);
            console.log(`   소스: ${rule.source}`);
            console.log(`   CSS: ${rule.cssText}`);
            console.log('');
        });

        console.log('\n📋 모든 적용된 CSS 규칙 (우선순위 순):');
        cssAnalysis.allRules.slice(0, 10).forEach((rule, index) => {
            console.log(`${index + 1}. [우선순위: ${rule.specificity}] ${rule.selector}`);
            console.log(`   타입: ${rule.type}`);
            if (rule.sheet) console.log(`   소스: ${rule.sheet}`);
            console.log(`   CSS: ${rule.cssText.substring(0, 100)}${rule.cssText.length > 100 ? '...' : ''}`);
            console.log('');
        });

        console.log(`\n📊 총 ${cssAnalysis.totalRulesFound}개의 CSS 규칙이 .calendar-view에 적용됨`);

    } catch (error) {
        console.error('❌ CSS 분석 중 오류:', error);
    } finally {
        await browser.close();
    }
}

autoCssDebugger();