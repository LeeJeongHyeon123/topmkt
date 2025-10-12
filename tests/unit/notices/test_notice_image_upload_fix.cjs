const { chromium } = require('playwright');
const path = require('path');

async function testNoticeImageUploadFix() {
    const browser = await chromium.launch({ headless: false });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    console.log('🚀 Ultra Think 7단계: Playwright 테스트 시작');
    console.log('========================================');
    
    try {
        // 1. 공지사항 상세 페이지로 이동
        console.log('📋 1. 공지사항 10번 상세 페이지 접근...');
        await page.goto('https://www.topmktx.com/notices/10');
        await page.waitForLoadState('networkidle');
        
        // 현재 첨부 이미지 개수 확인
        const currentImageCount = await page.textContent('.attachments-header');
        console.log('✅ 현재 첨부 이미지 상태:', currentImageCount);
        
        // 2. 편집 페이지로 이동 (로그인 필요)
        console.log('✏️ 2. 편집 버튼 클릭 시도...');
        
        // 편집 버튼이 있는지 확인
        const editButton = page.locator('a[href*="/edit"]').first();
        const hasEditButton = await editButton.count() > 0;
        
        if (!hasEditButton) {
            console.log('⚠️ 편집 버튼이 없음 (로그인 필요할 수도 있음)');
            console.log('📋 직접 편집 URL로 접근 시도...');
            await page.goto('https://www.topmktx.com/notices/10/edit');
            await page.waitForLoadState('networkidle');
        } else {
            await editButton.click();
            await page.waitForLoadState('networkidle');
        }
        
        // 3. 편집 페이지 상태 확인
        const currentUrl = page.url();
        console.log('🌐 현재 URL:', currentUrl);
        
        if (currentUrl.includes('login')) {
            console.log('⚠️ 로그인 페이지로 리다이렉트됨 - 테스트를 위해 수동 로그인 필요');
            console.log('💡 브라우저가 열려있으니 수동으로 로그인 후 편집 페이지로 이동해주세요.');
            
            // 30초 대기 (수동 로그인 시간)
            console.log('⏳ 30초 대기 중... (수동 로그인용)');
            await page.waitForTimeout(30000);
        }
        
        // 4. 편집 페이지에서 기존 이미지 확인
        if (page.url().includes('/edit')) {
            console.log('✅ 편집 페이지 접근 성공');
            
            // 기존 이미지 섹션 확인
            const existingImages = page.locator('.existing-images .existing-image-item');
            const existingImageCount = await existingImages.count();
            console.log(`📸 기존 이미지 개수: ${existingImageCount}개`);
            
            if (existingImageCount > 0) {
                console.log('✅ 기존 이미지들이 편집 페이지에서 정상 표시됨');
                
                // 각 기존 이미지의 정보 출력
                for (let i = 0; i < existingImageCount; i++) {
                    const imageItem = existingImages.nth(i);
                    const imageId = await imageItem.getAttribute('data-image-id');
                    const imageSrc = await imageItem.locator('img').getAttribute('src');
                    console.log(`  - 이미지 #${i+1}: ID=${imageId}, src=${imageSrc}`);
                }
            } else {
                console.log('⚠️ 기존 이미지가 편집 페이지에서 표시되지 않음');
            }
            
            // 5. 새 이미지 업로드 섹션 확인
            const uploadArea = page.locator('#uploadArea');
            const hasUploadArea = await uploadArea.count() > 0;
            console.log(`📤 새 이미지 업로드 영역 존재: ${hasUploadArea ? '✅ 있음' : '❌ 없음'}`);
            
            if (hasUploadArea) {
                // 파일 입력 필드 확인
                const fileInput = page.locator('input[type="file"]#images');
                const hasFileInput = await fileInput.count() > 0;
                console.log(`📁 파일 입력 필드 존재: ${hasFileInput ? '✅ 있음' : '❌ 없음'}`);
                
                if (hasFileInput) {
                    console.log('✅ 이미지 업로드 인터페이스가 정상적으로 구성됨');
                    console.log('💡 실제 파일 업로드 테스트는 수동으로 진행하세요.');
                }
            }
            
            console.log('🎯 편집 페이지 분석 완료');
        } else {
            console.log('❌ 편집 페이지 접근 실패');
        }
        
    } catch (error) {
        console.error('❌ 테스트 중 오류 발생:', error.message);
    }
    
    console.log('========================================');
    console.log('🏁 Ultra Think 7단계 테스트 완료');
    console.log('💡 브라우저를 수동으로 닫아주세요.');
    
    // 브라우저를 자동으로 닫지 않음 (수동 테스트용)
    // await browser.close();
}

testNoticeImageUploadFix();
