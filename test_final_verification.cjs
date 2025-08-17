const { chromium } = require('playwright');

async function finalVerification() {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    console.log('🎯 Ultra Think 최종 검증 테스트');
    console.log('=====================================');
    
    try {
        // 1. 공지사항 상세 페이지 접근
        console.log('📋 공지사항 10번 상세 페이지 접근...');
        await page.goto('https://www.topmktx.com/notices/10');
        await page.waitForLoadState('networkidle');
        
        // 페이지 제목 확인
        const title = await page.title();
        console.log('📄 페이지 제목:', title);
        
        // 첨부 이미지 개수 확인
        const imageHeader = await page.textContent('.attachments-header');
        console.log('📸 현재 첨부 이미지 상태:', imageHeader);
        
        // 첨부 이미지들 확인
        const attachmentItems = page.locator('.attachment-item');
        const imageCount = await attachmentItems.count();
        console.log(`🖼️ 실제 표시된 이미지 개수: ${imageCount}개`);
        
        if (imageCount > 0) {
            console.log('📂 각 이미지 정보:');
            for (let i = 0; i < imageCount; i++) {
                const item = attachmentItems.nth(i);
                const img = item.locator('img');
                const src = await img.getAttribute('src');
                const alt = await img.getAttribute('alt');
                console.log(`   ${i+1}. src: ${src}`);
                console.log(`      alt: ${alt}`);
            }
        }
        
        console.log('✅ 공지사항 상세 페이지 검증 완료');
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    }
    
    await browser.close();
    console.log('🏁 최종 검증 완료');
}

finalVerification();
