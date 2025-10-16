/**
 * 강의 상세 페이지 UI 개선 검증 스크립트
 * 텍스트 오버플로우 및 레이아웃 개선 확인
 */

const playwright = require('playwright');

(async () => {
    console.log('🚀 강의 상세 페이지 UI 개선 검증 시작...\n');

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
            path: 'lectures_132_ui_improved.png',
            fullPage: true
        });
        console.log('✅ 전체 스크린샷 저장: lectures_132_ui_improved.png\n');

        // 주요 요소 확인
        console.log('🔍 UI 개선사항 검증 중...\n');

        // 1. 그리드 레이아웃 확인
        const lectureContent = await page.$('.lecture-content');
        if (lectureContent) {
            const gridInfo = await lectureContent.evaluate(el => {
                const styles = window.getComputedStyle(el);
                const rect = el.getBoundingClientRect();
                return {
                    display: styles.display,
                    gridTemplateColumns: styles.gridTemplateColumns,
                    width: rect.width,
                    height: rect.height
                };
            });
            console.log(`✓ 그리드 display: ${gridInfo.display}`);
            console.log(`✓ 그리드 grid-template-columns: ${gridInfo.gridTemplateColumns}`);
            console.log(`✓ 그리드 크기: ${Math.round(gridInfo.width)}px × ${Math.round(gridInfo.height)}px`);
        }

        // 2. 메인 컨텐츠 너비 확인
        const lectureMain = await page.$('.lecture-main');
        if (lectureMain) {
            const mainInfo = await lectureMain.evaluate(el => {
                const styles = window.getComputedStyle(el);
                const rect = el.getBoundingClientRect();
                return {
                    width: rect.width,
                    padding: styles.padding,
                    boxSizing: styles.boxSizing,
                    overflowWrap: styles.overflowWrap
                };
            });
            console.log(`✓ 메인 컨텐츠 너비: ${Math.round(mainInfo.width)}px`);
            console.log(`✓ 메인 컨텐츠 패딩: ${mainInfo.padding}`);
            console.log(`✓ 메인 컨텐츠 box-sizing: ${mainInfo.boxSizing}`);
            console.log(`✓ 메인 컨텐츠 overflow-wrap: ${mainInfo.overflowWrap}`);
        }

        // 3. 사이드바 너비 확인
        const lectureSidebar = await page.$('.lecture-sidebar');
        if (lectureSidebar) {
            const sidebarInfo = await lectureSidebar.evaluate(el => {
                const styles = window.getComputedStyle(el);
                const rect = el.getBoundingClientRect();
                return {
                    width: rect.width,
                    minWidth: styles.minWidth,
                    flexDirection: styles.flexDirection
                };
            });
            console.log(`✓ 사이드바 너비: ${Math.round(sidebarInfo.width)}px`);
            console.log(`✓ 사이드바 min-width: ${sidebarInfo.minWidth}`);
            console.log(`✓ 사이드바 flex-direction: ${sidebarInfo.flexDirection}`);
        }

        // 4. 텍스트 오버플로우 확인
        const descriptionContent = await page.$('.description-content');
        if (descriptionContent) {
            const textInfo = await descriptionContent.evaluate(el => {
                const styles = window.getComputedStyle(el);
                return {
                    wordWrap: styles.wordWrap,
                    overflowWrap: styles.overflowWrap,
                    wordBreak: styles.wordBreak,
                    width: el.getBoundingClientRect().width
                };
            });
            console.log(`✓ 텍스트 word-wrap: ${textInfo.wordWrap}`);
            console.log(`✓ 텍스트 overflow-wrap: ${textInfo.overflowWrap}`);
            console.log(`✓ 텍스트 word-break: ${textInfo.wordBreak}`);
            console.log(`✓ 텍스트 컨테이너 너비: ${Math.round(textInfo.width)}px`);
        }

        // 5. 전체 페이지 너비 활용률 확인
        const container = await page.$('.lecture-detail-container');
        if (container) {
            const containerInfo = await container.evaluate(el => {
                const rect = el.getBoundingClientRect();
                const styles = window.getComputedStyle(el);
                return {
                    width: rect.width,
                    maxWidth: styles.maxWidth,
                    padding: styles.padding
                };
            });
            console.log(`✓ 전체 컨테이너 너비: ${Math.round(containerInfo.width)}px`);
            console.log(`✓ 컨테이너 max-width: ${containerInfo.maxWidth}`);
            console.log(`✓ 컨테이너 패딩: ${containerInfo.padding}`);
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

        console.log('\n✨ UI 개선사항 검증 완료!\n');

    } catch (error) {
        console.error('❌ 검증 중 오류 발생:', error.message);
        process.exit(1);
    } finally {
        await browser.close();
    }
})();

