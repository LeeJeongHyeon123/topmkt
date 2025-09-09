/**
 * 완전한 수정 모드 버튼 순서 테스트
 * 게시글 생성 → 수정 페이지 이동 → 버튼 순서 확인
 */

const { chromium } = require('playwright');

async function completeEditButtonTest() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('🔐 DevLoginHelper로 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        console.log('📝 커뮤니티 작성 페이지로 이동...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForTimeout(3000);
        
        // 1단계: 작성 모드 버튼 순서 확인
        console.log('🎯 1단계: 작성 모드 버튼 순서 확인...');
        
        let buttons = await page.$$eval('.form-buttons button', buttons => {
            return buttons.map((btn, index) => {
                const rect = btn.getBoundingClientRect();
                const text = btn.textContent.trim();
                const id = btn.id;
                
                return {
                    index,
                    text,
                    id,
                    position: {
                        left: Math.round(rect.left),
                        center: Math.round(rect.left + rect.width / 2)
                    }
                };
            });
        });
        
        console.log('📊 작성 모드 버튼 순서:');
        buttons.forEach((btn, idx) => {
            const position = idx === 0 ? '좌측' : '우측';
            console.log(`  ${position}: "${btn.text}" (ID: ${btn.id})`);
        });
        
        // 테스트용 게시글 작성
        console.log('\n📝 테스트 게시글 작성 중...');
        const testTitle = `수정 모드 버튼 테스트 ${new Date().toISOString()}`;
        await page.fill('#title', testTitle);
        await page.click('.ql-editor');
        await page.type('.ql-editor', '수정 모드에서 버튼 순서를 테스트하기 위한 게시글입니다.');
        
        // 작성하기 버튼 클릭
        await page.click('#submitBtn');
        await page.waitForTimeout(3000);
        
        // 현재 URL에서 게시글 ID 추출
        const currentUrl = page.url();
        console.log(`📍 작성된 게시글 URL: ${currentUrl}`);
        
        const postIdMatch = currentUrl.match(/\/posts\/(\d+)/);
        if (!postIdMatch) {
            // 커뮤니티 목록으로 이동했을 수도 있으니 다른 방법 시도
            console.log('🔍 게시글 목록에서 최신 게시글 찾기...');
            await page.goto('https://www.topmktx.com/community');
            await page.waitForTimeout(2000);
            
            // 첫 번째 게시글 클릭 (최신 게시글)
            const firstPost = await page.$('.post-item h3 a, .post-title a, h3 a');
            if (firstPost) {
                await firstPost.click();
                await page.waitForTimeout(2000);
            }
        }
        
        // 현재 URL 다시 확인
        const finalUrl = page.url();
        console.log(`📍 최종 URL: ${finalUrl}`);
        
        // 수정 버튼 찾아서 클릭
        console.log('✏️ 수정 버튼 클릭...');
        
        // 여러 가능한 수정 버튼 선택자 시도
        const editSelectors = [
            'a[href*="/community/edit/"]',
            '.btn-edit',
            'button:has-text("수정")',
            'a:has-text("수정")'
        ];
        
        let editButton = null;
        for (const selector of editSelectors) {
            try {
                editButton = await page.$(selector);
                if (editButton) {
                    console.log(`수정 버튼을 찾았습니다: ${selector}`);
                    break;
                }
            } catch (e) {
                continue;
            }
        }
        
        if (editButton) {
            await editButton.click();
            await page.waitForTimeout(3000);
            
            // 2단계: 수정 모드 버튼 순서 확인
            console.log('\n🎯 2단계: 수정 모드 버튼 순서 확인...');
            
            try {
                const editButtons = await page.$$eval('.form-buttons button', buttons => {
                    return buttons.map((btn, index) => {
                        const rect = btn.getBoundingClientRect();
                        const text = btn.textContent.trim();
                        const id = btn.id;
                        
                        return {
                            index,
                            text,
                            id,
                            position: {
                                left: Math.round(rect.left),
                                center: Math.round(rect.left + rect.width / 2)
                            }
                        };
                    });
                });
                
                // 위치별 정렬
                const sortedEditButtons = [...editButtons].sort((a, b) => a.position.center - b.position.center);
                
                console.log('📊 수정 모드 버튼 순서:');
                sortedEditButtons.forEach((btn, idx) => {
                    let position;
                    if (sortedEditButtons.length === 3) {
                        position = idx === 0 ? '좌측' : idx === 1 ? '중앙' : '우측';
                    } else {
                        position = idx === 0 ? '좌측' : '우측';
                    }
                    console.log(`  ${position}: "${btn.text}" (ID: ${btn.id})`);
                });
                
                // 기대하는 순서 확인
                const expectedOrder = ['🗑️ 삭제', '❌ 취소', '수정하기'];
                const actualOrder = sortedEditButtons.map(btn => btn.text);
                
                console.log('\n✅ 버튼 순서 검증:');
                console.log(`   기대 순서: [${expectedOrder.join(', ')}]`);
                console.log(`   실제 순서: [${actualOrder.join(', ')}]`);
                
                const isCorrectOrder = (
                    sortedEditButtons.length === 3 &&
                    sortedEditButtons[0].text.includes('삭제') && 
                    sortedEditButtons[1].text.includes('취소') && 
                    sortedEditButtons[2].text.includes('수정')
                );
                
                // 스크린샷 촬영
                await page.screenshot({ 
                    path: `/var/www/html/topmkt/complete-edit-button-test.png`,
                    fullPage: true
                });
                
                console.log('\n🎉 테스트 완료!');
                console.log('   스크린샷: complete-edit-button-test.png');
                
                if (isCorrectOrder) {
                    console.log('✅ SUCCESS: 수정 모드 버튼 순서가 올바릅니다!');
                    console.log('   - 삭제 버튼이 좌측에 위치');
                    console.log('   - 취소 버튼이 중앙에 위치'); 
                    console.log('   - 수정하기 버튼이 우측에 위치');
                } else {
                    console.log('❌ FAILED: 버튼 순서를 다시 확인해주세요.');
                }
                
                return {
                    success: isCorrectOrder,
                    editButtonOrder: actualOrder,
                    writeButtonOrder: buttons.map(btn => btn.text)
                };
                
            } catch (e) {
                console.log('❌ 수정 모드 버튼을 찾을 수 없습니다:', e.message);
                return { success: false, error: '수정 모드 버튼을 찾을 수 없음' };
            }
            
        } else {
            console.log('❌ 수정 버튼을 찾을 수 없습니다.');
            return { success: false, error: '수정 버튼을 찾을 수 없음' };
        }
        
    } catch (error) {
        console.error('❌ 테스트 실행 중 오류:', error);
        return { success: false, error: error.message };
    } finally {
        await browser.close();
    }
}

// 실행
if (require.main === module) {
    completeEditButtonTest().then(result => {
        console.log('\n📊 최종 테스트 결과:');
        console.log(`   수정 모드 버튼 순서: ${result.success ? '✅ 완벽' : '❌ 확인 필요'}`);
        if (result.writeButtonOrder) {
            console.log(`   작성 모드 순서: [${result.writeButtonOrder.join(', ')}]`);
        }
        if (result.editButtonOrder) {
            console.log(`   수정 모드 순서: [${result.editButtonOrder.join(', ')}]`);
        }
        process.exit(result.success ? 0 : 1);
    });
}