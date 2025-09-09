/**
 * 삭제 버튼 수정 테스트
 * 삭제 시 부적절한 "변경사항이 저장되지 않을 수 있습니다" 메시지가 나오지 않는지 확인
 */

const { chromium } = require('playwright');

async function testDeleteButtonFix() {
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'] 
    });
    
    try {
        const page = await browser.newPage();
        
        console.log('🔐 DevLoginHelper로 로그인 중...');
        await page.goto('https://www.topmktx.com/dev/login_helper.php?user_id=4');
        await page.waitForTimeout(2000);
        
        console.log('📝 테스트용 게시글 생성 중...');
        await page.goto('https://www.topmktx.com/community/write');
        await page.waitForTimeout(3000);
        
        // 테스트용 게시글 작성
        const testTitle = `삭제 테스트용 게시글 ${new Date().toISOString()}`;
        await page.fill('#title', testTitle);
        await page.click('.ql-editor');
        await page.type('.ql-editor', '이 게시글은 삭제 기능 테스트를 위해 생성되었습니다.');
        
        // 작성하기 버튼 클릭
        await page.click('#submitBtn');
        await page.waitForTimeout(3000);
        
        // 현재 URL에서 게시글 ID 추출
        const currentUrl = page.url();
        console.log(`📍 생성된 게시글 URL: ${currentUrl}`);
        
        // 수정 페이지로 이동
        const editButton = await page.$('a:has-text("수정")');
        if (editButton) {
            console.log('✏️ 수정 페이지로 이동 중...');
            await editButton.click();
            await page.waitForTimeout(3000);
            
            // 삭제 버튼 존재 확인
            const deleteButton = await page.$('#deleteBtn');
            if (deleteButton) {
                console.log('🗑️ 삭제 버튼을 찾았습니다.');
                
                // 삭제 버튼 클릭 시 나오는 다이얼로그 처리
                let allDialogMessages = [];
                let dialogCount = 0;
                let hasDeleteConfirmDialog = false;
                let hasInappropriateDialog = false;
                
                page.on('dialog', async dialog => {
                    dialogCount++;
                    const message = dialog.message();
                    allDialogMessages.push(message);
                    console.log(`📋 다이얼로그 ${dialogCount}: "${message}"`);
                    
                    if (message.includes('정말로 이 게시글을 삭제하시겠습니까?')) {
                        console.log('✅ 정상적인 삭제 확인 다이얼로그');
                        hasDeleteConfirmDialog = true;
                        await dialog.accept(); // 삭제 확인
                    } else if (message.includes('변경사항이 저장되지 않을 수 있습니다') || 
                              message.includes('작성 중인 내용이 있습니다')) {
                        console.log('❌ 부적절한 변경사항 경고 다이얼로그 발견!');
                        hasInappropriateDialog = true;
                        await dialog.accept();
                    } else {
                        console.log('📝 기타 다이얼로그 (삭제 완료 메시지 등)');
                        await dialog.accept();
                    }
                });
                
                // 콘솔 로그 캡처
                const consoleMessages = [];
                page.on('console', msg => {
                    if (msg.text().includes('삭제 진행') || msg.text().includes('beforeunload')) {
                        consoleMessages.push(msg.text());
                        console.log(`🔧 콘솔: ${msg.text()}`);
                    }
                });
                
                console.log('🗑️ 삭제 버튼 클릭 중...');
                
                // 삭제 버튼 클릭
                await deleteButton.click();
                
                // 페이지 변화를 기다림 (삭제 성공 시 리다이렉트)
                try {
                    await page.waitForNavigation({ timeout: 10000 });
                    console.log(`🎯 삭제 후 최종 URL: ${page.url()}`);
                } catch (e) {
                    console.log('⚠️ 네비게이션 대기 시간 초과 (삭제 실패 가능성)');
                }
                
                // 결과 분석
                console.log('\n📊 삭제 테스트 결과:');
                console.log(`   다이얼로그 총 개수: ${dialogCount}`);
                console.log(`   모든 다이얼로그: ${JSON.stringify(allDialogMessages)}`);
                console.log(`   콘솔 메시지 개수: ${consoleMessages.length}`);
                
                const hasBeforeunloadDisabled = consoleMessages.some(msg => 
                    msg.includes('삭제 진행: beforeunload 이벤트 무력화')
                );
                
                if (hasDeleteConfirmDialog && !hasInappropriateDialog && hasBeforeunloadDisabled) {
                    console.log('✅ SUCCESS: 삭제 기능이 올바르게 수정되었습니다!');
                    console.log('   - 적절한 삭제 확인 다이얼로그 표시됨');
                    console.log('   - 부적절한 변경사항 경고 없음');
                    console.log('   - beforeunload 이벤트 정상 무력화');
                    return { success: true, allDialogMessages, consoleMessages };
                } else {
                    console.log('❌ FAILED: 삭제 기능에 문제가 있습니다.');
                    if (!hasDeleteConfirmDialog) console.log('   - 삭제 확인 다이얼로그 없음');
                    if (hasInappropriateDialog) console.log('   - 부적절한 변경사항 경고 발견');
                    if (!hasBeforeunloadDisabled) console.log('   - beforeunload 이벤트 무력화 실패');
                    return { success: false, allDialogMessages, consoleMessages };
                }
                
            } else {
                console.log('❌ 삭제 버튼을 찾을 수 없습니다.');
                return { success: false, error: '삭제 버튼 없음' };
            }
            
        } else {
            console.log('❌ 수정 버튼을 찾을 수 없습니다.');
            return { success: false, error: '수정 버튼 없음' };
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
    testDeleteButtonFix().then(result => {
        console.log('\n🎉 최종 테스트 결과:');
        console.log(`   삭제 버튼 수정: ${result.success ? '✅ 성공' : '❌ 실패'}`);
        
        if (result.dialogMessage) {
            console.log(`   최종 다이얼로그: "${result.dialogMessage}"`);
        }
        
        process.exit(result.success ? 0 : 1);
    });
}