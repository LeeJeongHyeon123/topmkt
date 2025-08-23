const { chromium } = require('playwright');

/**
 * 🔍 Ultra Think: 반응형 이미지 표시 테스트
 */

async function testResponsiveImage() {
    console.log('🔍 반응형 이미지 표시 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext();
        const page = await context.newPage();
        
        // 로그인
        console.log('🔐 DevLoginHelper로 로그인...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // 테스트할 화면 크기들
        const viewports = [
            { name: '데스크톱 (1920px)', width: 1920, height: 1080 },
            { name: '노트북 (1366px)', width: 1366, height: 768 },
            { name: '태블릿 (768px)', width: 768, height: 1024 },
            { name: '모바일 (375px)', width: 375, height: 667 },
            { name: '작은 모바일 (320px)', width: 320, height: 568 }
        ];
        
        let allPassed = true;
        const results = [];
        
        for (const viewport of viewports) {
            console.log(`\\n📱 ${viewport.name} 테스트...`);
            
            // 화면 크기 설정
            await page.setViewportSize({ width: viewport.width, height: viewport.height });
            
            // 게시글 1000010으로 이동
            await page.goto('https://www.topmktx.com/community/posts/1000010');
            await page.waitForLoadState('networkidle');
            await page.waitForTimeout(2000);
            
            // 이미지 분석
            const analysis = await page.evaluate(() => {
                const postImages = Array.from(document.querySelectorAll('.post-content img, .content-body img'))
                    .filter(img => img.closest('.post-content') || img.closest('.content-body'));
                
                if (postImages.length === 0) return null;
                
                const img = postImages[0];
                const rect = img.getBoundingClientRect();
                const computed = window.getComputedStyle(img);
                const container = img.closest('.post-content') || img.closest('.content-body');
                const containerRect = container.getBoundingClientRect();
                
                return {
                    naturalWidth: img.naturalWidth,
                    naturalHeight: img.naturalHeight,
                    displayWidth: rect.width,
                    displayHeight: rect.height,
                    containerWidth: containerRect.width,
                    cssMaxWidth: computed.maxWidth,
                    cssWidth: computed.width,
                    isOverflowing: rect.width > containerRect.width,
                    aspectRatio: rect.width / rect.height,
                    viewportWidth: window.innerWidth
                };
            });
            
            if (analysis) {
                const passed = !analysis.isOverflowing && analysis.displayWidth <= viewport.width;
                const status = passed ? '✅ PASS' : '❌ FAIL';
                
                console.log(`   ${status}`);
                console.log(`   - 컨테이너: ${Math.round(analysis.containerWidth)}px`);
                console.log(`   - 이미지 표시: ${Math.round(analysis.displayWidth)}px x ${Math.round(analysis.displayHeight)}px`);
                console.log(`   - 비율: ${analysis.aspectRatio.toFixed(2)}`);
                console.log(`   - 오버플로우: ${analysis.isOverflowing ? '❌ 있음' : '✅ 없음'}`);
                console.log(`   - CSS max-width: ${analysis.cssMaxWidth}`);
                
                if (!passed) {
                    allPassed = false;
                }
                
                results.push({
                    viewport: viewport.name,
                    width: viewport.width,
                    passed,
                    displayWidth: Math.round(analysis.displayWidth),
                    containerWidth: Math.round(analysis.containerWidth),
                    isOverflowing: analysis.isOverflowing
                });
                
                // 스크린샷 저장
                await page.screenshot({ 
                    path: `/var/www/html/topmkt/responsive-${viewport.width}px.png`,
                    fullPage: false
                });
                console.log(`   📸 스크린샷: responsive-${viewport.width}px.png`);
            } else {
                console.log('   ⚠️ 이미지를 찾을 수 없음');
                results.push({
                    viewport: viewport.name,
                    width: viewport.width,
                    passed: false,
                    error: 'Image not found'
                });
                allPassed = false;
            }
        }
        
        console.log('\\n📊 === 반응형 테스트 종합 결과 ===');
        
        results.forEach(result => {
            const status = result.passed ? '✅' : '❌';
            console.log(`${status} ${result.viewport} (${result.width}px)`);
            if (result.displayWidth) {
                console.log(`    표시 크기: ${result.displayWidth}px (컨테이너: ${result.containerWidth}px)`);
                console.log(`    오버플로우: ${result.isOverflowing ? '있음' : '없음'}`);
            }
            if (result.error) {
                console.log(`    오류: ${result.error}`);
            }
        });
        
        const passedCount = results.filter(r => r.passed).length;
        const totalCount = results.length;
        const successRate = (passedCount / totalCount * 100).toFixed(1);
        
        console.log(`\\n🏆 성공률: ${passedCount}/${totalCount} (${successRate}%)`);
        
        if (allPassed) {
            console.log('🎉 모든 화면 크기에서 정상적으로 표시됩니다!');
        } else {
            console.log('⚠️ 일부 화면 크기에서 문제가 있습니다.');
        }
        
        return { allPassed, results, successRate };
        
    } catch (error) {
        console.error('💥 테스트 실행 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
testResponsiveImage()
    .then((result) => {
        console.log('\\n🏁 === 반응형 테스트 완료 ===');
        
        if (result.allPassed) {
            console.log('🎊 축하합니다! 완벽한 반응형 이미지 표시가 구현되었습니다!');
            process.exit(0);
        } else {
            console.log('🔧 일부 개선이 필요합니다.');
            console.log(`성공률: ${result.successRate}%`);
            process.exit(1);
        }
    })
    .catch((error) => {
        console.error('💥 테스트 실패:', error);
        process.exit(1);
    });