/**
 * 🚀 Ultra Think: 공지사항 수정 시 이미지 개수 제한 테스트 (Playwright)
 */

const { chromium } = require('playwright');

async function testNoticeEditImageLimit() {
    console.log('🚀 공지사항 수정 시 이미지 개수 제한 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 공지사항 10번 상세 페이지로 이동
        console.log('📄 공지사항 10번 상세 페이지 접근 중...');
        await page.goto('https://www.topmktx.com/notices/10', { 
            waitUntil: 'networkidle',
            timeout: 10000
        });
        
        // 현재 표시된 이미지 개수 확인
        await page.waitForSelector('.notice-images', { timeout: 5000 });
        const currentImageCount = await page.$$eval('.notice-images img', imgs => imgs.length);
        console.log(`📸 현재 표시된 이미지 개수: ${currentImageCount}개`);
        
        // 수정하기 버튼 클릭 (로그인이 필요할 수 있음)
        const editButton = await page.$('a[href*="edit"], button:has-text("수정"), .edit-btn');
        if (!editButton) {
            console.log('⚠️ 수정 버튼을 찾을 수 없음 (로그인 필요하거나 권한 없음)');
            
            // 대신 직접 수정 페이지로 이동 시도
            console.log('🔄 직접 수정 페이지로 이동 시도...');
            await page.goto('https://www.topmktx.com/notices/10/edit', {
                waitUntil: 'networkidle',
                timeout: 10000
            });
        } else {
            console.log('✅ 수정 버튼 발견, 클릭...');
            await editButton.click();
            await page.waitForNavigation({ waitUntil: 'networkidle' });
        }
        
        // 수정 페이지 확인
        const isEditPage = await page.$('#editNoticeForm, form[action*="edit"]');
        if (!isEditPage) {
            console.log('❌ 수정 페이지로 이동하지 못했습니다.');
            return;
        }
        
        console.log('✅ 공지사항 수정 페이지 접근 성공');
        
        // 기존 이미지 개수 확인
        const existingImages = await page.$$('#existingImages .existing-image-item:not([style*="display: none"])');
        console.log(`📸 기존 이미지 개수: ${existingImages.length}개`);
        
        // 이미지 업로드 제한 테스트
        console.log('🧪 이미지 업로드 제한 테스트 시작...');
        
        // 테스트용 가짜 파일들 생성 (JavaScript에서)
        await page.evaluate(() => {
            // 가짜 파일들을 생성하여 제한 로직 테스트
            const fileInput = document.getElementById('images');
            if (fileInput) {
                // 6개의 가짜 파일 생성 (기존 + 6개 = 5개 초과)
                const fakeFiles = [];
                for (let i = 0; i < 6; i++) {
                    const blob = new Blob(['fake image data'], { type: 'image/jpeg' });
                    const file = new File([blob], `test-image-${i}.jpg`, { type: 'image/jpeg' });
                    fakeFiles.push(file);
                }
                
                // FileList 객체 생성
                const dt = new DataTransfer();
                fakeFiles.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
                
                // change 이벤트 트리거
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                
                console.log('테스트용 6개 파일 추가 완료');
                return true;
            }
            return false;
        });
        
        // 잠시 대기 후 오류 메시지 확인
        await page.waitForTimeout(2000);
        
        const errorMessage = await page.$('.error-message, .alert-danger');
        if (errorMessage) {
            const errorText = await errorMessage.textContent();
            console.log('🚫 예상된 오류 메시지:', errorText);
            console.log('✅ 이미지 개수 제한 기능이 정상 작동합니다!');
        } else {
            console.log('⚠️ 오류 메시지가 표시되지 않았습니다. 제한이 작동하지 않을 수 있습니다.');
        }
        
        // 현재 새로 선택된 이미지 미리보기 개수 확인
        const newImagePreviews = await page.$$('#newUploadedImages .existing-image-item');
        console.log(`📸 새 이미지 미리보기 개수: ${newImagePreviews.length}개`);
        
        console.log('🎯 테스트 완료!');
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testNoticeEditImageLimit();