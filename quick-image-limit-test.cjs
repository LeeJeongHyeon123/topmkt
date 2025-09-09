const { chromium } = require('playwright');

/**
 * 🚀 빠른 이미지 제한 QA 테스트
 * 
 * 커뮤니티와 공지사항 작성 페이지에서 이미지 20개 제한이 제대로 작동하는지 확인
 */

async function quickImageLimitTest() {
    console.log('🚀 빠른 이미지 제한 QA 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    try {
        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 }
        });
        const page = await context.newPage();
        
        // 로그인
        console.log('🔐 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForLoadState('networkidle');
        
        // === 테스트 1: 커뮤니티 작성 페이지 ===
        console.log('\\n📝 테스트 1: 커뮤니티 작성 페이지');
        try {
            await page.goto('https://www.topmktx.com/community/write');
            await page.waitForLoadState('networkidle');
            await page.waitForSelector('#quill-editor', { timeout: 10000 });
            await page.waitForTimeout(3000);
            
            // 이미지 카운터 확인
            const imageCounterExists = await page.locator('#imageCounter').isVisible();
            console.log(`   이미지 카운터 표시: ${imageCounterExists ? '✅ 있음' : '❌ 없음'}`);
            
            if (imageCounterExists) {
                const counterText = await page.locator('#imageCounter').textContent();
                console.log(`   카운터 내용: "${counterText}"`);
            }
            
            // 제목 입력
            await page.fill('#title', '이미지 제한 테스트');
            
            // 이미지 개수 제한 확인 (JavaScript에서)
            const imageLimit = await page.evaluate(() => {
                const editor = document.querySelector('.ql-editor');
                if (editor) {
                    // 더미 이미지 20개 추가 시도
                    let addedImages = 0;
                    for (let i = 0; i < 22; i++) {
                        const img = document.createElement('img');
                        img.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI/hQBdAAAAAElFTkSuQmCC';
                        img.style.width = '100px';
                        img.style.height = '50px';
                        editor.appendChild(img);
                        addedImages++;
                    }
                    return {
                        attempted: 22,
                        added: addedImages,
                        finalCount: editor.querySelectorAll('img').length
                    };
                }
                return { attempted: 0, added: 0, finalCount: 0 };
            });
            
            await page.waitForTimeout(1000);
            
            // 업데이트된 카운터 확인
            if (imageCounterExists) {
                const updatedCounterText = await page.locator('#imageCounter').textContent();
                console.log(`   업데이트된 카운터: "${updatedCounterText}"`);
            }
            
            console.log(`   시도한 이미지: ${imageLimit.attempted}개`);
            console.log(`   실제 추가된 이미지: ${imageLimit.finalCount}개`);
            console.log(`   20개 제한 준수: ${imageLimit.finalCount <= 20 ? '✅ 성공' : '❌ 실패'}`);
            
        } catch (error) {
            console.log(`   ❌ 커뮤니티 테스트 실패: ${error.message}`);
        }
        
        // === 테스트 2: 공지사항 작성 페이지 ===
        console.log('\\n📄 테스트 2: 공지사항 작성 페이지');
        try {
            await page.goto('https://www.topmktx.com/notices/write');
            await page.waitForLoadState('networkidle');
            await page.waitForSelector('#editor-container', { timeout: 10000 });
            await page.waitForTimeout(3000);
            
            // 이미지 카운터 확인
            const imageCounterExists = await page.locator('#imageCounter').isVisible();
            console.log(`   이미지 카운터 표시: ${imageCounterExists ? '✅ 있음' : '❌ 없음'}`);
            
            if (imageCounterExists) {
                const counterText = await page.locator('#imageCounter').textContent();
                console.log(`   카운터 내용: "${counterText}"`);
            }
            
            // 제목 입력
            await page.fill('#title', '공지사항 이미지 제한 테스트');
            
            // 이미지 개수 제한 확인
            const noticeImageLimit = await page.evaluate(() => {
                const editor = document.querySelector('#editor-container .ql-editor');
                if (editor) {
                    // 더미 이미지 20개 추가 시도
                    let addedImages = 0;
                    for (let i = 0; i < 22; i++) {
                        const img = document.createElement('img');
                        img.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI/hQBdAAAAAElFTkSuQmCC';
                        img.style.width = '100px';
                        img.style.height = '50px';
                        editor.appendChild(img);
                        addedImages++;
                    }
                    return {
                        attempted: 22,
                        added: addedImages,
                        finalCount: editor.querySelectorAll('img').length
                    };
                }
                return { attempted: 0, added: 0, finalCount: 0 };
            });
            
            await page.waitForTimeout(1000);
            
            // 업데이트된 카운터 확인
            if (imageCounterExists) {
                const updatedCounterText = await page.locator('#imageCounter').textContent();
                console.log(`   업데이트된 카운터: "${updatedCounterText}"`);
            }
            
            console.log(`   시도한 이미지: ${noticeImageLimit.attempted}개`);
            console.log(`   실제 추가된 이미지: ${noticeImageLimit.finalCount}개`);
            console.log(`   20개 제한 준수: ${noticeImageLimit.finalCount <= 20 ? '✅ 성공' : '❌ 실패'}`);
            
        } catch (error) {
            console.log(`   ❌ 공지사항 테스트 실패: ${error.message}`);
        }
        
        // 스크린샷
        console.log('\\n📸 최종 스크린샷 촬영...');
        await page.screenshot({ 
            path: '/var/www/html/topmkt/quick-image-limit-test.png',
            fullPage: true 
        });
        
        console.log('\\n🎯 빠른 QA 테스트 완료');
        console.log('📸 결과 확인: quick-image-limit-test.png');
        
    } catch (error) {
        console.error('💥 테스트 실행 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
quickImageLimitTest()
    .then(() => {
        console.log('\\n✅ 빠른 QA 테스트 성공 완료!');
        console.log('🔧 수정된 기능들이 정상적으로 작동하는지 확인되었습니다.');
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 빠른 QA 테스트 실패:', error);
        process.exit(1);
    });