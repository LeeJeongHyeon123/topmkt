/**
 * 강의 상세 페이지 UI 복구 검증 스크립트
 * CSS 분리 작업 후 UI 복구 확인
 */

const playwright = require('playwright');

(async () => {
    console.log('🚀 강의 상세 페이지 UI 복구 검증 시작...\n');
    
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
        
        console.log('📄 페이지 로딩: https://www.topmktx.com/lectures/207');
        await page.goto('https://www.topmktx.com/lectures/207', {
            waitUntil: 'networkidle',
            timeout: 30000
        });
        
        // 스크린샷 저장
        await page.screenshot({
            path: 'lectures_ui_restored.png',
            fullPage: true
        });
        console.log('✅ 스크린샷 저장: lectures_ui_restored.png\n');
        
        // 주요 요소 확인
        console.log('🔍 UI 요소 검증 중...\n');
        
        // 1. 갤러리 그리드 레이아웃 확인
        const galleryItems = await page.$$('.gallery-item');
        console.log(`✓ 갤러리 아이템 수: ${galleryItems.length}개`);
        
        if (galleryItems.length > 0) {
            const firstItem = galleryItems[0];
            const boundingBox = await firstItem.boundingBox();
            console.log(`✓ 첫 번째 갤러리 아이템 크기: ${Math.round(boundingBox.width)}px × ${Math.round(boundingBox.height)}px`);
            
            // aspect-ratio 16:9 확인
            const ratio = boundingBox.width / boundingBox.height;
            const isCorrectRatio = Math.abs(ratio - (16/9)) < 0.1;
            console.log(`✓ 갤러리 비율: ${ratio.toFixed(2)} (16:9 = 1.78) ${isCorrectRatio ? '✅ 정상' : '❌ 비정상'}`);
        }
        
        // 2. 레이아웃 그리드 확인
        const lectureContent = await page.$('.lecture-content');
        if (lectureContent) {
            const gridStyle = await lectureContent.evaluate(el => {
                const styles = window.getComputedStyle(el);
                return {
                    display: styles.display,
                    gridTemplateColumns: styles.gridTemplateColumns
                };
            });
            console.log(`✓ 레이아웃 display: ${gridStyle.display}`);
            console.log(`✓ 레이아웃 grid-template-columns: ${gridStyle.gridTemplateColumns}`);
        }
        
        // 3. 액션 버튼 위치 확인
        const lectureActions = await page.$('.lecture-actions');
        if (lectureActions) {
            const actionStyle = await lectureActions.evaluate(el => {
                const styles = window.getComputedStyle(el);
                return {
                    position: styles.position,
                    top: styles.top,
                    right: styles.right
                };
            });
            console.log(`✓ 액션 버튼 position: ${actionStyle.position}`);
            console.log(`✓ 액션 버튼 위치: top=${actionStyle.top}, right=${actionStyle.right}`);
        }
        
        // 4. CSS 파일 로드 확인
        const cssLoaded = await page.evaluate(() => {
            const styles = Array.from(document.styleSheets);
            const detailStyles = styles.find(s => s.href && s.href.includes('detail-styles.css'));
            return detailStyles ? true : false;
        });
        console.log(`✓ detail-styles.css 로드: ${cssLoaded ? '✅ 성공' : '❌ 실패'}`);
        
        // 5. 콘솔 에러 확인
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
            }
        });
        
        await page.waitForTimeout(2000);
        
        if (consoleErrors.length > 0) {
            console.log('\n⚠️ 콘솔 에러:');
            consoleErrors.forEach(err => console.log(`  - ${err}`));
        } else {
            console.log('\n✅ 콘솔 에러 없음');
        }
        
        console.log('\n✨ UI 복구 검증 완료!\n');
        
    } catch (error) {
        console.error('❌ 검증 중 오류 발생:', error.message);
        process.exit(1);
    } finally {
        await browser.close();
    }
})();

