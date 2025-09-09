const { chromium } = require('playwright');

/**
 * 🎯 포괄적 이미지 제한 QA 테스트 (6개 페이지)
 * 
 * 모든 텍스트 에디터 페이지에서 20개 이미지 제한과 UI 카운터가 정상 작동하는지 검증
 */

async function comprehensiveImageLimitQA() {
    console.log('🔍 포괄적 이미지 제한 QA 테스트 시작');
    console.log('📋 테스트 대상: 6개 페이지의 20개 이미지 제한 + UI 카운터');
    
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
        
        const testPages = [
            {
                name: 'community/write.php',
                url: 'https://www.topmktx.com/community/write',
                editorSelector: '.ql-editor',
                titleField: '#title',
                titleValue: '[QA] 커뮤니티 이미지 제한 테스트',
                status: 'Unknown'
            },
            {
                name: 'notices/write.php', 
                url: 'https://www.topmktx.com/notices/write',
                editorSelector: '.ql-editor',
                titleField: '#title',
                titleValue: '[QA] 공지사항 작성 이미지 제한 테스트',
                status: 'Unknown'
            },
            {
                name: 'notices/edit.php',
                url: 'https://www.topmktx.com/notices/19/edit',
                editorSelector: '.ql-editor',
                titleField: null, // 편집 페이지는 기존 제목 사용
                titleValue: null,
                status: 'Unknown'
            },
            {
                name: 'events/create.php',
                url: 'https://www.topmktx.com/events/create',
                editorSelector: '#quill-editor .ql-editor',
                titleField: '#title',
                titleValue: '[QA] 행사 생성 이미지 제한 테스트',
                status: 'Unknown'
            },
            {
                name: 'events/edit.php',
                url: 'https://www.topmktx.com/events/199/edit',
                editorSelector: '.ql-editor', 
                titleField: null, // 편집 페이지는 기존 제목 사용
                titleValue: null,
                status: 'Unknown'
            },
            {
                name: 'user/edit.php',
                url: 'https://www.topmktx.com/profile/edit',
                editorSelector: '#bio-editor .ql-editor',
                titleField: null, // 프로필 페이지는 제목 없음
                titleValue: null,
                status: 'Unknown',
                note: '⚠️ 프로필 자기소개 - 이미지 첨부 적절성 검토 필요'
            }
        ];
        
        let totalPassedPages = 0;
        let totalFailedPages = 0;
        
        for (let i = 0; i < testPages.length; i++) {
            const testPage = testPages[i];
            console.log(`\\n🔍 ${i + 1}/6 - ${testPage.name} 테스트 중...`);
            
            try {
                // 페이지 이동
                await page.goto(testPage.url);
                await page.waitForLoadState('networkidle');
                await page.waitForTimeout(3000); // 에디터 초기화 대기
                
                // 제목 입력 (해당하는 경우)
                if (testPage.titleField && testPage.titleValue) {
                    await page.fill(testPage.titleField, testPage.titleValue);
                }
                
                // 1. 이미지 카운터 UI 확인
                const imageCounterExists = await page.locator('#imageCounter').isVisible();
                console.log(`   📊 이미지 카운터 UI: ${imageCounterExists ? '✅ 있음' : '❌ 없음'}`);
                
                // 2. 에디터 존재 확인
                const editorExists = await page.locator(testPage.editorSelector).isVisible();
                console.log(`   📝 에디터: ${editorExists ? '✅ 있음' : '❌ 없음'}`);
                
                // 3. 이미지 22개 추가 테스트 (DOM 직접 조작)
                const imageTestResult = await page.evaluate(() => {
                    const editor = document.querySelector('.ql-editor');
                    if (!editor) return { success: false, message: '에디터 없음' };
                    
                    // 22개 이미지 추가
                    let addedCount = 0;
                    for (let i = 0; i < 22; i++) {
                        const img = document.createElement('img');
                        img.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI/hQBdAAAAAElFTkSuQmCC';
                        img.style.width = '50px';
                        img.style.height = '30px';
                        img.setAttribute('data-test-image', i + 1);
                        editor.appendChild(img);
                        addedCount++;
                    }
                    
                    // Quill text-change 이벤트 트리거 (만약 있다면)
                    if (window.quill) {
                        window.quill.update();
                    }
                    
                    return { success: true, added: addedCount };
                });
                
                console.log(`   📷 이미지 추가 시도: ${imageTestResult.added || 0}개`);
                
                // 4. 자동 제거 기능 대기
                await page.waitForTimeout(2000);
                
                // 5. 최종 이미지 개수 확인
                const finalResult = await page.evaluate(() => {
                    const editor = document.querySelector('.ql-editor');
                    if (!editor) return { totalImages: 0, testImages: 0 };
                    
                    const allImages = editor.querySelectorAll('img');
                    const testImages = editor.querySelectorAll('img[data-test-image]');
                    
                    return {
                        totalImages: allImages.length,
                        testImages: testImages.length
                    };
                });
                
                // 6. 이미지 카운터 텍스트 확인
                let counterText = '';
                try {
                    counterText = await page.locator('#imageCounter').textContent();
                } catch (e) {
                    counterText = '카운터 없음';
                }
                
                // 결과 판정
                const passed = (
                    imageCounterExists && 
                    editorExists && 
                    finalResult.totalImages <= 20 &&
                    counterText.includes('/')
                );
                
                if (passed) {
                    totalPassedPages++;
                    testPage.status = '✅ PASS';
                    console.log(`   🎉 결과: PASS`);
                } else {
                    totalFailedPages++;
                    testPage.status = '❌ FAIL';
                    console.log(`   💥 결과: FAIL`);
                }
                
                console.log(`   📊 최종 이미지: ${finalResult.totalImages}개 (≤20 ${finalResult.totalImages <= 20 ? '✅' : '❌'})`);
                console.log(`   📋 카운터 표시: "${counterText}"`);
                
            } catch (error) {
                console.error(`   💥 ${testPage.name} 테스트 오류:`, error.message);
                totalFailedPages++;
                testPage.status = '💥 ERROR';
            }
        }
        
        // 최종 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/comprehensive-image-limit-qa.png',
            fullPage: true 
        });
        
        // === 최종 결과 출력 ===
        console.log('\\n🏆 === 포괄적 QA 테스트 최종 결과 ===');
        console.log(`📊 총 테스트: ${testPages.length}개 페이지`);
        console.log(`✅ 성공: ${totalPassedPages}개`);
        console.log(`❌ 실패: ${totalFailedPages}개`);
        console.log(`📈 성공률: ${Math.round((totalPassedPages / testPages.length) * 100)}%`);
        
        console.log('\\n📋 === 페이지별 상세 결과 ===');
        testPages.forEach((page, index) => {
            console.log(`${index + 1}. ${page.name}: ${page.status}`);
        });
        
        // 성공/실패 기준
        const successRate = (totalPassedPages / testPages.length) * 100;
        if (successRate >= 100) {
            console.log('\\n🎉 === 완벽한 성공! ===');
            console.log('✅ 모든 페이지에서 20개 이미지 제한이 정상 작동합니다.');
            console.log('✅ 모든 페이지에서 이미지 카운터 UI가 표시됩니다.');
            console.log('✅ 사용자 요구사항이 완벽히 구현되었습니다.');
        } else if (successRate >= 80) {
            console.log('\\n⚠️ === 부분적 성공 ===');
            console.log(`✅ ${totalPassedPages}개 페이지는 정상 작동합니다.`);
            console.log(`❌ ${totalFailedPages}개 페이지에서 문제가 발견되었습니다.`);
        } else {
            console.log('\\n💥 === 추가 작업 필요 ===');
            console.log(`❌ 다수의 페이지에서 문제가 발견되었습니다.`);
        }
        
        console.log(`\\n📸 상세 결과: comprehensive-image-limit-qa.png`);
        
        return {
            totalPages: testPages.length,
            passedPages: totalPassedPages,
            failedPages: totalFailedPages,
            successRate: successRate,
            allPassed: successRate === 100
        };
        
    } catch (error) {
        console.error('💥 포괄적 QA 테스트 실행 중 오류:', error);
        throw error;
    } finally {
        await browser.close();
    }
}

// 실행
comprehensiveImageLimitQA()
    .then((result) => {
        console.log('\\n🚀 === QA 테스트 완료 ===');
        if (result.allPassed) {
            console.log('🎊 축하합니다! 모든 페이지에서 이미지 제한 기능이 완벽하게 작동합니다!');
            process.exit(0);
        } else {
            console.log('⚠️ 일부 페이지에서 개선이 필요합니다.');
            process.exit(1);
        }
    })
    .catch((error) => {
        console.error('💥 포괄적 QA 테스트 실패:', error);
        process.exit(1);
    });