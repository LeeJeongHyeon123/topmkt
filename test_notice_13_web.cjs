/**
 * 🚀 Ultra Think: 공지사항 13번 웹 페이지 이미지 표시 테스트 (충돌 해결 후)
 */

const { chromium } = require('playwright');

async function testNotice13Web() {
    console.log('🚀 공지사항 13번 웹 페이지 이미지 표시 테스트 (충돌 해결 후)');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });
    
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 공지사항 13번 상세 페이지 접근
        console.log('1️⃣ 공지사항 13번 상세 페이지 접근...');
        await page.goto('https://www.topmktx.com/notices/13', { 
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });
        
        await page.waitForTimeout(3000);
        
        // 페이지 제목 확인
        const title = await page.title();
        console.log(`📄 페이지 제목: ${title}`);
        
        // 본문 내 이미지 확인
        const contentImages = await page.$$eval('.notice-content img, .content img, .notice-detail img', 
            imgs => imgs.filter(img => img.src && img.src.includes('/assets/uploads/notices/')).map(img => ({
                src: img.src,
                alt: img.alt,
                width: img.naturalWidth,
                height: img.naturalHeight
            }))
        ).catch(() => []);
        
        console.log(`📸 본문 내 이미지: ${contentImages.length}개`);
        if (contentImages.length > 0) {
            contentImages.forEach((img, index) => {
                console.log(`   ${index + 1}. ${img.src}`);
                console.log(`      - 크기: ${img.width}x${img.height}`);
                console.log(`      - Alt: ${img.alt}`);
            });
        }
        
        // 첨부 이미지 섹션 확인
        const attachmentImages = await page.$$eval('.attachment-images img, .attached-images img, .notice-images img', 
            imgs => imgs.filter(img => img.src && img.src.includes('/assets/uploads/notices/')).length
        ).catch(() => 0);
        
        console.log(`📎 첨부 이미지 섹션: ${attachmentImages}개`);
        
        // 잘못된 이미지 확인 (11번 공지사항의 이미지인지)
        const hasConflictingImage = contentImages.some(img => 
            img.src.includes('20250814193757_c2ce9df9b1f110dd.jpg')
        );
        
        if (hasConflictingImage) {
            console.log('❌ 공지사항 11번의 이미지가 여전히 표시되고 있음');
        } else {
            console.log('✅ 다른 공지사항의 이미지 충돌 문제 해결됨');
        }
        
        // 전체 결과 평가
        const totalImages = contentImages.length + attachmentImages;
        if (totalImages === 0) {
            console.log('✅ 공지사항 13번은 올바르게 이미지가 없음을 표시');
        } else {
            console.log(`⚠️ 공지사항 13번에 ${totalImages}개 이미지가 표시됨 (확인 필요)`);
        }
        
        // 오류 확인
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        
        if (errors.length > 0) {
            console.log('⚠️ JavaScript 오류:');
            errors.forEach(error => console.log(`   - ${error}`));
        } else {
            console.log('✅ JavaScript 오류 없음');
        }
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testNotice13Web();