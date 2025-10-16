/**
 * 텍스트 오버플로우 및 가로 스크롤 문제 해결 검증
 */

const playwright = require('playwright');

(async () => {
    console.log('🚀 텍스트 오버플로우 및 가로 스크롤 문제 해결 검증...\n');

    const browser = await playwright.chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });

        const page = await context.newPage();

        console.log('📄 페이지 로딩: https://www.topmktx.com/lectures/132');
        await page.goto('https://www.topmktx.com/lectures/132', {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        // 페이지 로드 대기
        await page.waitForTimeout(2000);

        // 스크린샷 저장
        await page.screenshot({
            path: 'lectures_132_text_overflow_fixed.png',
            fullPage: true
        });
        console.log('✅ 전체 스크린샷 저장: lectures_132_text_overflow_fixed.png\n');

        // 주요 요소 확인
        console.log('🔍 텍스트 오버플로우 문제 해결 확인...\n');

        // 1. 페이지 전체 너비 확인 (가로 스크롤 여부)
        const pageInfo = await page.evaluate(() => {
            return {
                scrollWidth: document.body.scrollWidth,
                clientWidth: document.body.clientWidth,
                hasHorizontalScroll: document.body.scrollWidth > document.body.clientWidth,
                windowInnerWidth: window.innerWidth
            };
        });

        console.log(`✓ 페이지 전체 너비: ${pageInfo.scrollWidth}px`);
        console.log(`✓ 표시 영역 너비: ${pageInfo.clientWidth}px`);
        console.log(`✓ 가로 스크롤 존재: ${pageInfo.hasHorizontalScroll ? '❌ 있음' : '✅ 없음'}`);
        console.log(`✓ 창 너비: ${pageInfo.windowInnerWidth}px`);

        // 2. 텍스트 컨테이너 확인
        const descriptionContent = await page.$('.description-content');
        if (descriptionContent) {
            const textInfo = await descriptionContent.evaluate(el => {
                const rect = el.getBoundingClientRect();
                const styles = window.getComputedStyle(el);
                return {
                    width: rect.width,
                    height: rect.height,
                    wordWrap: styles.wordWrap,
                    overflowWrap: styles.overflowWrap,
                    wordBreak: styles.wordBreak,
                    hyphens: styles.hyphens,
                    maxWidth: styles.maxWidth
                };
            });
            console.log(`\n✓ 텍스트 컨테이너 너비: ${Math.round(textInfo.width)}px`);
            console.log(`✓ 텍스트 word-wrap: ${textInfo.wordWrap}`);
            console.log(`✓ 텍스트 overflow-wrap: ${textInfo.overflowWrap}`);
            console.log(`✓ 텍스트 word-break: ${textInfo.wordBreak}`);
            console.log(`✓ 텍스트 hyphens: ${textInfo.hyphens}`);
            console.log(`✓ 텍스트 max-width: ${textInfo.maxWidth}`);
        }

        // 3. 메인 컨텐츠 오버플로우 확인
        const lectureMain = await page.$('.lecture-main');
        if (lectureMain) {
            const mainInfo = await lectureMain.evaluate(el => {
                const styles = window.getComputedStyle(el);
                return {
                    overflowX: styles.overflowX,
                    width: el.getBoundingClientRect().width,
                    scrollWidth: el.scrollWidth,
                    hasOverflow: el.scrollWidth > el.getBoundingClientRect().width
                };
            });
            console.log(`\n✓ 메인 컨텐츠 overflow-x: ${mainInfo.overflowX}`);
            console.log(`✓ 메인 컨텐츠 너비: ${Math.round(mainInfo.width)}px`);
            console.log(`✓ 메인 컨텐츠 스크롤 너비: ${mainInfo.scrollWidth}px`);
            console.log(`✓ 메인 컨텐츠 오버플로우: ${mainInfo.hasOverflow ? '❌ 있음' : '✅ 없음'}`);
        }

        // 4. 전체 컨테이너 오버플로우 확인
        const container = await page.$('.lecture-detail-container');
        if (container) {
            const containerInfo = await container.evaluate(el => {
                const styles = window.getComputedStyle(el);
                return {
                    overflowX: styles.overflowX,
                    maxWidth: styles.maxWidth,
                    width: el.getBoundingClientRect().width
                };
            });
            console.log(`\n✓ 전체 컨테이너 overflow-x: ${containerInfo.overflowX}`);
            console.log(`✓ 전체 컨테이너 max-width: ${containerInfo.maxWidth}`);
            console.log(`✓ 전체 컨테이너 너비: ${Math.round(containerInfo.width)}px`);
        }

        // 5. 긴 텍스트가 있는 요소들 확인
        const textElements = await page.$$eval('*', elements => {
            return elements
                .filter(el => el.textContent && el.textContent.length > 100)
                .map(el => ({
                    tagName: el.tagName,
                    className: el.className,
                    textLength: el.textContent.length,
                    scrollWidth: el.scrollWidth,
                    clientWidth: el.clientWidth,
                    hasOverflow: el.scrollWidth > el.clientWidth
                }))
                .filter(el => el.hasOverflow);
        });

        if (textElements.length > 0) {
            console.log(`\n⚠️ 오버플로우가 있는 텍스트 요소들 (${textElements.length}개):`);
            textElements.forEach(el => {
                console.log(`  - ${el.tagName}.${el.className}: ${el.textLength}자`);
                console.log(`    너비: ${el.scrollWidth}px > ${el.clientWidth}px`);
            });
        } else {
            console.log('\n✅ 긴 텍스트 요소에서 오버플로우 없음');
        }

        // 6. 콘솔 에러 확인
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
            }
        });

        await page.waitForTimeout(1000);

        if (consoleErrors.length > 0) {
            console.log('\n⚠️ 콘솔 에러:');
            consoleErrors.forEach(err => console.log(`  - ${err}`));
        } else {
            console.log('\n✅ 콘솔 에러 없음');
        }

        console.log('\n✨ 텍스트 오버플로우 문제 해결 검증 완료!\n');

        // 최종 결과 요약
        const hasHorizontalScroll = pageInfo.hasHorizontalScroll;
        const hasTextOverflow = textElements.length > 0;

        if (!hasHorizontalScroll && !hasTextOverflow) {
            console.log('🎉 모든 문제 해결됨!');
        } else {
            console.log('⚠️ 아직 해결되지 않은 문제 있음');
        }

    } catch (error) {
        console.error('❌ 검증 중 오류 발생:', error.message);
        process.exit(1);
    } finally {
        await browser.close();
    }
})();

