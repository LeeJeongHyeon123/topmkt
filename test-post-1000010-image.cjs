const { chromium } = require('playwright');

/**
 * 🔍 Ultra Think: 게시글 1000010 이미지 표시 문제 분석
 */

async function testPost1000010Image() {
    console.log('🔍 게시글 1000010 이미지 표시 문제 분석 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 콘솔 로그 캡처
        page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log(`🔴 [페이지 에러] ${msg.text()}`);
            }
        });
        
        // 네트워크 에러 캡처
        page.on('response', response => {
            if (!response.ok() && response.url().includes('image')) {
                console.log(`❌ [이미지 로드 실패] ${response.url()} - ${response.status()}`);
            }
        });
        
        // 로그인
        console.log('🔐 DevLoginHelper로 로그인...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // 게시글 1000010으로 이동
        console.log('📄 게시글 1000010으로 이동...');
        await page.goto('https://www.topmktx.com/community/posts/1000010');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(3000);
        
        // 이미지 정보 상세 분석
        const imageAnalysis = await page.evaluate(() => {
            const images = document.querySelectorAll('img');
            const postContent = document.querySelector('.post-content') || document.querySelector('[class*="content"]');
            
            const imageData = Array.from(images).map((img, index) => {
                const rect = img.getBoundingClientRect();
                const computed = window.getComputedStyle(img);
                
                return {
                    index,
                    src: img.src,
                    alt: img.alt || '',
                    naturalWidth: img.naturalWidth,
                    naturalHeight: img.naturalHeight,
                    displayWidth: rect.width,
                    displayHeight: rect.height,
                    aspectRatio: img.naturalWidth / img.naturalHeight,
                    cssWidth: computed.width,
                    cssHeight: computed.height,
                    cssMaxWidth: computed.maxWidth,
                    cssMaxHeight: computed.maxHeight,
                    cssObjectFit: computed.objectFit,
                    cssDisplay: computed.display,
                    isVisible: rect.width > 0 && rect.height > 0,
                    isLoaded: img.complete,
                    isInPostContent: postContent ? postContent.contains(img) : false,
                    className: img.className,
                    style: img.getAttribute('style') || '',
                    parentTag: img.parentElement?.tagName,
                    parentClass: img.parentElement?.className || ''
                };
            });
            
            return {
                totalImages: images.length,
                postContentExists: !!postContent,
                imageData,
                pageTitle: document.title,
                postContentHTML: postContent ? postContent.innerHTML.substring(0, 500) + '...' : 'No content found'
            };
        });
        
        console.log('\\n📊 === 이미지 분석 결과 ===');
        console.log(`총 이미지 개수: ${imageAnalysis.totalImages}개`);
        console.log(`게시글 콘텐츠 존재: ${imageAnalysis.postContentExists}`);
        console.log(`페이지 제목: ${imageAnalysis.pageTitle}`);
        
        imageAnalysis.imageData.forEach((img, index) => {
            console.log(`\\n🖼️ 이미지 ${index + 1}:`);
            console.log(`  - 소스: ${img.src}`);
            console.log(`  - 원본 크기: ${img.naturalWidth} x ${img.naturalHeight}`);
            console.log(`  - 표시 크기: ${img.displayWidth} x ${img.displayHeight}`);
            console.log(`  - 비율: ${img.aspectRatio?.toFixed(2)}`);
            console.log(`  - CSS 크기: ${img.cssWidth} x ${img.cssHeight}`);
            console.log(`  - Max 크기: ${img.cssMaxWidth} x ${img.cssMaxHeight}`);
            console.log(`  - Object-fit: ${img.cssObjectFit}`);
            console.log(`  - 가시성: ${img.isVisible ? '✅ 보임' : '❌ 안 보임'}`);
            console.log(`  - 로드 상태: ${img.isLoaded ? '✅ 완료' : '❌ 실패'}`);
            console.log(`  - 게시글 내부: ${img.isInPostContent ? '✅ 예' : '❌ 아니오'}`);
            console.log(`  - 스타일: ${img.style}`);
            console.log(`  - 부모: ${img.parentTag}.${img.parentClass}`);
        });
        
        // 게시글 내 이미지만 필터링
        const postImages = imageAnalysis.imageData.filter(img => img.isInPostContent);
        console.log(`\\n📝 게시글 내 이미지: ${postImages.length}개`);
        
        if (postImages.length > 0) {
            const mainImage = postImages[0];
            console.log('\\n⚠️ === 문제 진단 ===');
            
            // 가로/세로 비율 분석
            if (mainImage.aspectRatio > 2) {
                console.log('🔍 매우 넓은 이미지 (가로 >> 세로) 감지');
            } else if (mainImage.aspectRatio < 0.5) {
                console.log('🔍 매우 긴 이미지 (세로 >> 가로) 감지');
            }
            
            // 크기 문제 분석
            if (mainImage.displayWidth > 1000 || mainImage.displayHeight > 800) {
                console.log('🔍 매우 큰 이미지 표시 감지');
            }
            
            if (mainImage.displayWidth < 100 || mainImage.displayHeight < 100) {
                console.log('🔍 매우 작은 이미지 표시 감지');
            }
            
            // CSS 문제 분석
            if (mainImage.cssMaxWidth === 'none' && mainImage.naturalWidth > 800) {
                console.log('🔍 max-width 제한 없음 - 이미지가 컨테이너를 넘칠 수 있음');
            }
        }
        
        // 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/test-post-1000010-image.png',
            fullPage: true 
        });
        
        console.log('\\n📸 스크린샷 저장: test-post-1000010-image.png');
        
        return imageAnalysis;
        
    } catch (error) {
        console.error('💥 분석 실행 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testPost1000010Image()
    .then((result) => {
        console.log('\\n🏁 === 분석 완료 ===');
        const postImages = result.imageData.filter(img => img.isInPostContent);
        
        if (postImages.length === 0) {
            console.log('⚠️ 게시글 내 이미지를 찾을 수 없습니다.');
        } else {
            console.log(`📊 게시글 내 ${postImages.length}개 이미지 분석 완료`);
            
            // 문제가 있는 이미지 찾기
            const problematicImages = postImages.filter(img => 
                !img.isVisible || !img.isLoaded || 
                img.aspectRatio > 3 || img.aspectRatio < 0.3 ||
                img.displayWidth > 1200 || img.displayHeight > 1000
            );
            
            if (problematicImages.length > 0) {
                console.log(`❌ ${problematicImages.length}개의 문제 이미지 발견`);
                console.log('🔧 CSS 수정이 필요합니다.');
            } else {
                console.log('✅ 모든 이미지가 정상적으로 표시됩니다.');
            }
        }
        
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 분석 실패:', error);
        process.exit(1);
    });