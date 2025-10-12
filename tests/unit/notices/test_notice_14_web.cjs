/**
 * 🚀 Ultra Think: 공지사항 14번 웹 페이지 상세 분석
 */

const { chromium } = require('playwright');

async function testNotice14Web() {
    console.log('🚀 공지사항 14번 웹 페이지 상세 분석');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });
    
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });
        
        const page = await context.newPage();
        
        // 공지사항 14번 상세 페이지 접근
        console.log('1️⃣ 공지사항 14번 상세 페이지 접근...');
        await page.goto('https://www.topmktx.com/notices/14', { 
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });
        
        await page.waitForTimeout(3000);
        
        // 페이지 제목 및 기본 정보 확인
        const title = await page.title();
        const pageText = await page.textContent('body');
        console.log(`📄 페이지 제목: ${title}`);
        
        // HTML 내용 확인
        const contentHtml = await page.$eval('.notice-content, .content, .notice-detail', 
            el => el ? el.innerHTML : null
        ).catch(() => null);
        
        if (contentHtml) {
            console.log('📝 본문 HTML 내용:');
            console.log(contentHtml.substring(0, 500) + '...');
        }
        
        // 모든 이미지 태그 확인 (src 있는 것과 없는 것 모두)
        const allImages = await page.$$eval('img', 
            imgs => imgs.map(img => ({
                src: img.src || 'NO_SRC',
                alt: img.alt || 'NO_ALT',
                className: img.className || 'NO_CLASS',
                outerHTML: img.outerHTML.substring(0, 200)
            }))
        );
        
        console.log(`📸 페이지 내 모든 이미지 태그: ${allImages.length}개`);
        allImages.forEach((img, index) => {
            console.log(`   ${index + 1}. ${img.outerHTML}`);
            if (img.src !== 'NO_SRC' && img.src.includes('/assets/uploads/')) {
                console.log(`      ⚠️ 업로드 이미지 발견: ${img.src}`);
            }
        });
        
        // 빈 img 태그 확인
        const emptyImages = await page.$$eval('img:not([src])', 
            imgs => imgs.length
        ).catch(() => 0);
        
        const emptyOrBrokenImages = await page.$$eval('img[src=""], img:not([src])', 
            imgs => imgs.map(img => img.outerHTML.substring(0, 100))
        ).catch(() => []);
        
        console.log(`🔍 빈 이미지 태그: ${emptyImages}개`);
        if (emptyOrBrokenImages.length > 0) {
            console.log('   빈 이미지 태그들:');
            emptyOrBrokenImages.forEach((html, index) => {
                console.log(`   ${index + 1}. ${html}`);
            });
        }
        
        // 첨부 이미지 섹션 확인
        const attachmentSection = await page.$eval('.attachment-images, .attached-images, .notice-images', 
            el => el ? el.innerHTML : null
        ).catch(() => null);
        
        if (attachmentSection) {
            console.log('📎 첨부 이미지 섹션:');
            console.log(attachmentSection.substring(0, 300));
        } else {
            console.log('📎 첨부 이미지 섹션: 없음');
        }
        
        // 페이지에서 "이미지" 관련 텍스트 찾기
        const hasImageText = pageText.includes('이미지') || pageText.includes('첨부') || pageText.includes('사진');
        console.log(`🔍 페이지에 이미지 관련 텍스트 존재: ${hasImageText ? '있음' : '없음'}`);
        
        // JavaScript 오류 확인
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        
        if (errors.length > 0) {
            console.log('⚠️ JavaScript 오류:');
            errors.forEach(error => console.log(`   - ${error}`));
        } else {
            console.log('✅ JavaScript 오류 없음');
        }
        
        // 결론
        if (emptyImages > 0) {
            console.log('🎯 결론: 공지사항 14번에 빈 img 태그가 있지만 실제 업로드된 이미지 파일이 없음');
            console.log('💡 사용자가 이미지를 첨부하려 했지만 업로드가 실패했을 가능성');
        } else {
            console.log('🎯 결론: 공지사항 14번에는 이미지 태그 자체가 없음');
        }
        
    } catch (error) {
        console.error('❌ 테스트 중 오류:', error.message);
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testNotice14Web();