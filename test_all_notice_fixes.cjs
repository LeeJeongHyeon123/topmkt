/**
 * 🚀 Ultra Think: 공지사항 관련 모든 수정사항 종합 테스트
 */

const { chromium } = require('playwright');

async function testAllNoticeFixes() {
    console.log('🚀 공지사항 관련 모든 수정사항 종합 테스트');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });
    
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 테스트 1: 공지사항 작성 페이지 접근 및 작성하기 버튼 확인
        console.log('1️⃣ 공지사항 작성 페이지 테스트...');
        try {
            await page.goto('https://www.topmktx.com/notices/write', { 
                waitUntil: 'domcontentloaded',
                timeout: 30000
            });
            
            const submitButton = await page.$('#submitBtn, button[type="submit"]');
            if (submitButton) {
                const buttonText = await submitButton.textContent();
                console.log('✅ 작성하기 버튼 발견:', buttonText.trim());
                
                // JavaScript 오류 확인
                const errors = [];
                page.on('pageerror', error => errors.push(error.message));
                await page.waitForTimeout(2000);
                
                if (errors.length === 0) {
                    console.log('✅ JavaScript 오류 없음');
                } else {
                    console.log('❌ JavaScript 오류:', errors);
                }
            } else {
                console.log('❌ 작성하기 버튼을 찾을 수 없음');
            }
        } catch (e) {
            console.log('❌ 작성 페이지 접근 실패:', e.message);
        }
        
        // 테스트 2: 공지사항 10번 상세 페이지 - 본문 이미지 확인
        console.log('\n2️⃣ 공지사항 10번 본문 이미지 표시 테스트...');
        try {
            await page.goto('https://www.topmktx.com/notices/10', { 
                waitUntil: 'domcontentloaded',
                timeout: 30000
            });
            
            await page.waitForTimeout(3000);
            
            // 본문 내 이미지 확인
            const contentImages = await page.$$eval('.notice-content img, .content img', 
                imgs => imgs.filter(img => img.src.includes('/assets/uploads/notices/')).length
            );
            
            console.log(`📸 본문 내 이미지: ${contentImages}개`);
            
            // 첨부 이미지 확인  
            const attachmentText = await page.$eval('h3, h4, .section-title', el => {
                if (el.textContent.includes('첨부 이미지')) {
                    return el.textContent;
                }
                return null;
            }).catch(() => null);
            
            if (attachmentText) {
                console.log('📎 첨부 이미지 섹션:', attachmentText);
            }
            
            if (contentImages >= 4) {
                console.log('✅ 본문 이미지 정상 표시');
            } else {
                console.log('⚠️ 본문 이미지 표시 문제 가능');
            }
        } catch (e) {
            console.log('❌ 상세 페이지 접근 실패:', e.message);
        }
        
        // 테스트 3: 공지사항 10번 수정 페이지 - 에디터 이미지 아이콘 및 기존 이미지 확인
        console.log('\n3️⃣ 공지사항 10번 수정 페이지 테스트...');
        try {
            await page.goto('https://www.topmktx.com/notices/10/edit', { 
                waitUntil: 'domcontentloaded',
                timeout: 30000
            });
            
            await page.waitForTimeout(5000);
            
            // Quill 에디터 툴바에서 이미지 버튼 확인
            const imageButton = await page.$('.ql-toolbar .ql-image');
            if (imageButton) {
                console.log('✅ 에디터 이미지 버튼 발견');
            } else {
                console.log('❌ 에디터 이미지 버튼 없음');
            }
            
            // 에디터 내 기존 이미지 확인
            const editorImages = await page.$$eval('#editor-container img, .ql-editor img', 
                imgs => imgs.filter(img => img.src && img.src.includes('/assets/uploads/notices/')).length
            );
            
            console.log(`📝 에디터 내 기존 이미지: ${editorImages}개`);
            
            if (editorImages >= 4) {
                console.log('✅ 수정 페이지 기존 이미지 정상 표시');
            } else {
                console.log('⚠️ 수정 페이지 기존 이미지 표시 문제');
            }
            
            // 기존 첨부 이미지 섹션 확인
            const existingImages = await page.$$('#existingImages .existing-image-item');
            console.log(`📎 기존 첨부 이미지: ${existingImages.length}개`);
            
        } catch (e) {
            console.log('❌ 수정 페이지 접근 실패 (로그인 필요하거나 권한 없음):', e.message);
        }
        
        console.log('\n✅ 종합 테스트 완료');
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testAllNoticeFixes();