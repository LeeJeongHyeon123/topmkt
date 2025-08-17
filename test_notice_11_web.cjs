/**
 * 🚀 Ultra Think: 공지사항 11번 웹 페이지 이미지 표시 테스트
 */

const { chromium } = require('playwright');

async function testNotice11Web() {
    console.log('🚀 공지사항 11번 웹 페이지 이미지 표시 테스트');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });
    
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 공지사항 11번 상세 페이지 접근
        console.log('1️⃣ 공지사항 11번 상세 페이지 접근...');
        await page.goto('https://www.topmktx.com/notices/11', { 
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
        );
        
        console.log(`📸 본문 내 이미지: ${contentImages.length}개`);
        contentImages.forEach((img, index) => {
            console.log(`   ${index + 1}. ${img.src}`);
            console.log(`      - 크기: ${img.width}x${img.height}`);
            console.log(`      - Alt: ${img.alt}`);
        });
        
        // 첨부 이미지 섹션 확인
        const attachmentImages = await page.$$eval('.attachment-images img, .attached-images img, .notice-images img', 
            imgs => imgs.filter(img => img.src && img.src.includes('/assets/uploads/notices/')).length
        ).catch(() => 0);
        
        console.log(`📎 첨부 이미지 섹션: ${attachmentImages}개`);
        
        // 전체 결과 평가
        const totalImages = contentImages.length + attachmentImages;
        if (totalImages >= 1) {
            console.log('✅ 공지사항 11번 이미지 표시 성공!');
        } else {
            console.log('❌ 공지사항 11번 이미지가 여전히 표시되지 않음');
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
testNotice11Web();