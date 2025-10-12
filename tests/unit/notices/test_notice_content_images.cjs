/**
 * 🚀 Ultra Think: 공지사항 본문 이미지 표시 확인 테스트
 */

const { chromium } = require('playwright');

async function testNoticeContentImages() {
    console.log('🚀 공지사항 본문 이미지 표시 확인 테스트');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });
    
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        console.log('📄 공지사항 10번 접근...');
        await page.goto('https://www.topmktx.com/notices/10', { 
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });
        
        await page.waitForTimeout(3000);
        
        // 본문 내용 영역 확인
        console.log('📝 본문 내용 영역 분석...');
        
        const contentArea = await page.$('.notice-content, .content, .notice-detail, article');
        if (contentArea) {
            console.log('✅ 본문 내용 영역 발견');
            
            // 본문 내 이미지 확인
            const contentImages = await contentArea.$$('img');
            console.log(`📸 본문 내 이미지 개수: ${contentImages.length}개`);
            
            // 각 이미지의 상세 정보
            for (let i = 0; i < contentImages.length; i++) {
                const img = contentImages[i];
                const src = await img.getAttribute('src');
                const alt = await img.getAttribute('alt');
                const className = await img.getAttribute('class');
                
                console.log(`   이미지 #${i + 1}:`);
                console.log(`     src: ${src}`);
                console.log(`     alt: ${alt}`);
                console.log(`     class: ${className}`);
            }
        } else {
            console.log('❌ 본문 내용 영역을 찾을 수 없음');
        }
        
        // 첨부 이미지 섹션 확인 (비교용)
        console.log('\n📎 첨부 이미지 섹션 분석...');
        const attachmentSection = await page.$('.notice-images, .attached-images');
        if (attachmentSection) {
            const attachmentImages = await attachmentSection.$$('img');
            console.log(`📸 첨부 이미지 개수: ${attachmentImages.length}개`);
        }
        
        // 전체 페이지의 모든 이미지 확인
        console.log('\n🔍 전체 페이지 이미지 분석...');
        const allImages = await page.$$eval('img', imgs => 
            imgs.filter(img => 
                img.src.includes('/assets/uploads/notices/') && 
                !img.src.includes('default')
            ).map(img => ({
                src: img.src,
                className: img.className,
                parent: img.parentElement.tagName + (img.parentElement.className ? '.' + img.parentElement.className : '')
            }))
        );
        
        console.log(`📊 공지사항 관련 이미지 총 ${allImages.length}개:`);
        allImages.forEach((img, index) => {
            console.log(`   ${index + 1}. ${img.src.split('/').pop()}`);
            console.log(`      위치: ${img.parent}`);
            console.log(`      클래스: ${img.className || '없음'}`);
        });
        
        // HTML 소스에서 img 태그 확인
        console.log('\n🔍 HTML 소스 img 태그 분석...');
        const htmlContent = await page.content();
        const imgTagMatches = htmlContent.match(/<img[^>]*>/g);
        if (imgTagMatches) {
            console.log(`📋 HTML의 img 태그 총 ${imgTagMatches.length}개:`);
            imgTagMatches.forEach((tag, index) => {
                if (tag.includes('/assets/uploads/notices/')) {
                    console.log(`   ${index + 1}. ${tag.substring(0, 100)}...`);
                }
            });
        }
        
        console.log('\n✅ 분석 완료');
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testNoticeContentImages();