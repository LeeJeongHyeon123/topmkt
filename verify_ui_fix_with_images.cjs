/**
 * 강의 상세 페이지 UI 복구 검증 스크립트 (이미지 포함)
 * CSS 분리 작업 후 UI 복구 확인 - 강의 132 (이미지 있음)
 */

const playwright = require('playwright');

(async () => {
    console.log('🚀 강의 상세 페이지 UI 복구 검증 시작 (강의 132 - 이미지 있음)...\n');
    
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
            path: 'lectures_132_ui_restored.png',
            fullPage: true
        });
        console.log('✅ 전체 스크린샷 저장: lectures_132_ui_restored.png');
        
        // 갤러리 부분만 스크린샷
        const gallery = await page.$('.lecture-gallery');
        if (gallery) {
            await gallery.screenshot({
                path: 'lectures_132_gallery.png'
            });
            console.log('✅ 갤러리 스크린샷 저장: lectures_132_gallery.png\n');
        }
        
        // 주요 요소 확인
        console.log('🔍 UI 요소 검증 중...\n');
        
        // 1. 갤러리 그리드 레이아웃 확인
        const galleryItems = await page.$$('.gallery-item');
        console.log(`✓ 갤러리 아이템 수: ${galleryItems.length}개`);
        
        if (galleryItems.length > 0) {
            // 첫 번째 아이템 크기
            const firstItem = galleryItems[0];
            const boundingBox = await firstItem.boundingBox();
            console.log(`✓ 첫 번째 갤러리 아이템 크기: ${Math.round(boundingBox.width)}px × ${Math.round(boundingBox.height)}px`);
            
            // aspect-ratio 16:9 확인
            const ratio = boundingBox.width / boundingBox.height;
            const isCorrectRatio = Math.abs(ratio - (16/9)) < 0.2;
            console.log(`✓ 갤러리 비율: ${ratio.toFixed(2)} (16:9 = 1.78) ${isCorrectRatio ? '✅ 정상' : '⚠️ 주의'}`);
            
            // 두 번째 아이템이 옆에 있는지 확인 (그리드 레이아웃)
            if (galleryItems.length > 1) {
                const secondItem = galleryItems[1];
                const secondBox = await secondItem.boundingBox();
                const isInSameRow = Math.abs(secondBox.y - boundingBox.y) < 10;
                console.log(`✓ 그리드 레이아웃: ${isInSameRow ? '✅ 정상 (다중 열)' : '❌ 비정상 (단일 열)'}`);
            }
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
            
            const is2ColumnLayout = gridStyle.gridTemplateColumns.split(' ').length === 2;
            console.log(`✓ 2단 레이아웃: ${is2ColumnLayout ? '✅ 정상' : '❌ 비정상'}`);
        }
        
        // 3. 액션 버튼 위치 확인
        const lectureActions = await page.$('.lecture-actions');
        if (lectureActions) {
            const actionStyle = await lectureActions.evaluate(el => {
                const styles = window.getComputedStyle(el);
                const rect = el.getBoundingClientRect();
                return {
                    position: styles.position,
                    top: styles.top,
                    right: styles.right,
                    actualTop: rect.top
                };
            });
            console.log(`✓ 액션 버튼 position: ${actionStyle.position}`);
            console.log(`✓ 액션 버튼 CSS 위치: top=${actionStyle.top}, right=${actionStyle.right}`);
            const isCorrectPosition = actionStyle.position === 'absolute' && actionStyle.actualTop < 150;
            console.log(`✓ 액션 버튼 배치: ${isCorrectPosition ? '✅ 정상 (상단)' : '⚠️ 주의'}`);
        }
        
        // 4. CSS 인라인 포함 확인
        const cssIncluded = await page.evaluate(() => {
            const styleTag = document.querySelector('style');
            if (styleTag) {
                const content = styleTag.textContent;
                return {
                    hasGallery: content.includes('.lecture-gallery'),
                    hasGalleryItem: content.includes('.gallery-item'),
                    hasActions: content.includes('.lecture-actions'),
                    totalLength: content.length
                };
            }
            return null;
        });
        
        if (cssIncluded) {
            console.log(`\n✓ CSS 인라인 포함: ${cssIncluded.totalLength.toLocaleString()} 문자`);
            console.log(`  - .lecture-gallery: ${cssIncluded.hasGallery ? '✅' : '❌'}`);
            console.log(`  - .gallery-item: ${cssIncluded.hasGalleryItem ? '✅' : '❌'}`);
            console.log(`  - .lecture-actions: ${cssIncluded.hasActions ? '✅' : '❌'}`);
        }
        
        console.log('\n✨ UI 복구 검증 완료!\n');
        
        // 최종 결과 요약
        console.log('📊 검증 결과 요약:');
        const allPassed = 
            galleryItems.length > 0 &&
            cssIncluded.hasGallery &&
            cssIncluded.hasGalleryItem &&
            cssIncluded.hasActions;
        
        if (allPassed) {
            console.log('🎉 모든 검증 통과! UI가 정상적으로 복구되었습니다.');
        } else {
            console.log('⚠️ 일부 항목 확인 필요');
        }
        
    } catch (error) {
        console.error('❌ 검증 중 오류 발생:', error.message);
        process.exit(1);
    } finally {
        await browser.close();
    }
})();

