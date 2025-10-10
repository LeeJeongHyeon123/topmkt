const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 }
    });
    const page = await context.newPage();

    const consoleLogs = [];
    page.on('console', msg => {
        consoleLogs.push(msg.text());
        console.log('[CONSOLE]', msg.text());
    });

    await page.goto('https://www.topmktx.com/lectures/210', {
        waitUntil: 'domcontentloaded',
        timeout: 60000
    });

    // 이벤트 리스너 등록 대기
    await page.waitForTimeout(2000);

    console.log('\n=== 갤러리 아이템 확인 ===');
    
    // 갤러리 아이템 존재 여부 확인
    const galleryCount = await page.locator('.gallery-item').count();
    console.log('갤러리 아이템 개수:', galleryCount);

    if (galleryCount > 0) {
        // 첫 번째 갤러리 아이템의 data 속성 확인
        const firstGalleryData = await page.locator('.gallery-item').first().evaluate(el => {
            return {
                hasDataIndex: el.hasAttribute('data-image-index'),
                dataIndex: el.dataset.imageIndex,
                innerHTML: el.innerHTML.substring(0, 200)
            };
        });
        console.log('첫 번째 갤러리 아이템:', JSON.stringify(firstGalleryData, null, 2));
    }

    console.log('\n=== 이벤트 리스너 확인 ===');
    
    // 이벤트 리스너가 등록되었는지 확인
    const hasEventListener = await page.evaluate(() => {
        const item = document.querySelector('.gallery-item');
        if (!item) return { exists: false };
        
        // 클릭 시뮬레이션하여 이벤트 발생 확인
        let clicked = false;
        const originalOpenImageModal = window.openImageModal;
        window.openImageModal = function() {
            clicked = true;
            console.log('openImageModal 호출됨!', arguments);
            if (originalOpenImageModal) originalOpenImageModal.apply(this, arguments);
        };
        
        item.click();
        
        return {
            exists: true,
            clicked: clicked,
            hasDataIndex: item.hasAttribute('data-image-index'),
            dataIndex: item.dataset.imageIndex
        };
    });
    
    console.log('이벤트 리스너 결과:', JSON.stringify(hasEventListener, null, 2));

    // 모달 상태 확인
    const modalState = await page.evaluate(() => {
        const modal = document.getElementById('imageModal');
        return {
            exists: !!modal,
            display: modal ? modal.style.display : null,
            className: modal ? modal.className : null
        };
    });
    
    console.log('\n=== 모달 상태 ===');
    console.log(JSON.stringify(modalState, null, 2));

    await browser.close();

    console.log('\n=== 디버깅 완료 ===\n');
})();
