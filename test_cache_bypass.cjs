/**
 * CSS 캐시를 우회해서 768px 가로 스크롤 테스트
 */

const puppeteer = require('puppeteer');

(async () => {
    console.log('🔄 CSS 캐시 우회 테스트');

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-cache']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 768, height: 1024 });

    // 캐시 비활성화
    await page.setCacheEnabled(false);

    try {
        // 강제 새로고침을 위한 타임스탬프
        const timestamp = Date.now();
        const url = `https://www.topmktx.com/events?year=2025&month=3&view=list&t=${timestamp}`;

        console.log(`🌐 캐시 우회 URL: ${url}`);

        await page.goto(url, {
            waitUntil: 'networkidle0',
            timeout: 15000
        });

        // 강제로 CSS 새로고침
        await page.evaluate(() => {
            // 모든 스타일시트에 타임스탬프 추가
            const links = document.querySelectorAll('link[rel="stylesheet"]');
            links.forEach(link => {
                const href = link.href;
                if (!href.includes('?t=')) {
                    link.href = href + '?t=' + Date.now();
                }
            });

            // 인라인 스타일 강제 재평가
            const layout = document.querySelector('.events-layout');
            if (layout) {
                layout.style.display = 'none';
                layout.offsetHeight; // 강제 리플로우
                layout.style.display = 'grid';
            }
        });

        // 잠시 대기 후 재측정
        await new Promise(resolve => setTimeout(resolve, 1000));

        const freshResults = await page.evaluate(() => {
            const container = document.querySelector('.events-container');
            const layout = document.querySelector('.events-layout');
            const sidebar = document.querySelector('.events-sidebar');

            const containerRect = container?.getBoundingClientRect();
            const layoutRect = layout?.getBoundingClientRect();
            const sidebarRect = sidebar?.getBoundingClientRect();

            const layoutComputed = layout ? window.getComputedStyle(layout) : null;

            // 모든 적용된 CSS 규칙 재수집
            const stylesheets = Array.from(document.styleSheets);
            const eventsLayoutRules = [];

            stylesheets.forEach(sheet => {
                try {
                    const rules = Array.from(sheet.cssRules || sheet.rules || []);
                    rules.forEach(rule => {
                        if (rule.selectorText && rule.selectorText.includes('events-layout')) {
                            const mediaQuery = rule.parentRule ? rule.parentRule.conditionText : 'none';
                            const applies = rule.parentRule ? rule.parentRule.media.matches || window.matchMedia(rule.parentRule.conditionText).matches : true;

                            eventsLayoutRules.push({
                                selector: rule.selectorText,
                                gridTemplateColumns: rule.style.gridTemplateColumns || 'not set',
                                mediaQuery: mediaQuery,
                                applies: applies
                            });
                        }
                    });
                } catch (e) {
                    // CORS 등으로 접근 불가한 스타일시트 무시
                }
            });

            return {
                container: {
                    width: containerRect?.width,
                    scrollWidth: container?.scrollWidth,
                    hasHorizontalScroll: container ? container.scrollWidth > container.clientWidth : null
                },
                layout: {
                    width: layoutRect?.width,
                    scrollWidth: layout?.scrollWidth,
                    gridTemplateColumns: layoutComputed?.gridTemplateColumns,
                    display: layoutComputed?.display
                },
                sidebar: {
                    width: sidebarRect?.width,
                    right: sidebarRect?.right,
                    withinScreen: sidebarRect ? sidebarRect.right <= 768 : null
                },
                eventsLayoutRules,
                mediaQuery768: window.matchMedia('(max-width: 768px)').matches,
                timestamp: Date.now()
            };
        });

        console.log('\n📊 캐시 우회 후 결과:');
        console.log(`   Container 가로 스크롤: ${freshResults.container.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
        console.log(`   Grid 템플릿 컬럼: ${freshResults.layout.gridTemplateColumns}`);
        console.log(`   사이드바 화면 내: ${freshResults.sidebar.withinScreen ? '✅ 예' : '❌ 아니오'} (right: ${freshResults.sidebar.right}px)`);
        console.log(`   768px 미디어쿼리: ${freshResults.mediaQuery768 ? '✅ 적용' : '❌ 미적용'}`);

        console.log('\n📋 발견된 CSS 규칙들:');
        freshResults.eventsLayoutRules.forEach((rule, index) => {
            const status = rule.applies ? '✅ 적용됨' : '❌ 미적용';
            console.log(`   ${index + 1}. ${rule.selector} (${status})`);
            console.log(`      grid-template-columns: ${rule.gridTemplateColumns}`);
            console.log(`      미디어쿼리: ${rule.mediaQuery}`);
        });

        // 성공 여부 판정
        const isFixed = !freshResults.container.hasHorizontalScroll &&
                       freshResults.sidebar.withinScreen &&
                       freshResults.layout.gridTemplateColumns !== '768px';

        console.log(`\n🎯 최종 결과: ${isFixed ? '✅ 가로 스크롤 문제 해결됨!' : '❌ 여전히 문제 있음'}`);

        // 성공했다면 스크린샷 촬영
        if (isFixed) {
            await page.screenshot({
                path: 'list-view-768px-success.png',
                fullPage: true
            });
            console.log('📸 성공 스크린샷: list-view-768px-success.png');
        }

    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
    }
})();