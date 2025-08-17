/**
 * 관리자 사용자 편집 기능 테스트
 * DevLoginHelper로 우리집탄이 계정 로그인 후 편집 기능 검증
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function testAdminEditUser() {
    console.log('🧪 관리자 사용자 편집 기능 테스트 시작...');
    
    const helper = new DevLoginHelper();
    let session = null;
    
    try {
        // 1. 우리집탄이 계정으로 로그인
        console.log('🔐 우리집탄이 계정으로 로그인...');
        session = await helper.getDevSession('우리집탄이', { 
            headless: true,
            timeout: 30000 
        });
        
        const { page } = session;
        
        // 2. 관리자 페이지 접근
        console.log('👨‍💼 관리자 페이지 접근...');
        await page.goto('https://www.topmktx.com/admin/users', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        await page.waitForTimeout(3000);
        
        // 3. 페이지 상태 확인
        console.log('📊 페이지 상태 확인...');
        
        const pageState = await page.evaluate(() => {
            return {
                title: document.title,
                url: window.location.href,
                hasEditButtons: document.querySelectorAll('.btn-edit').length,
                hasUsersTable: !!document.querySelector('.users-table'),
                editFunctionExists: typeof window.editUser === 'function'
            };
        });
        
        console.log('📋 페이지 상태:', pageState);
        
        if (!pageState.hasEditButtons) {
            throw new Error('편집 버튼이 페이지에 없습니다');
        }
        
        // 4. 사용자 목록 로드 대기
        console.log('📋 사용자 목록 로드 대기...');
        await page.waitForFunction(() => {
            const table = document.querySelector('.users-table tbody');
            return table && table.children.length > 0;
        }, { timeout: 15000 });
        
        // 5. 첫 번째 편집 버튼 클릭 (사용자 ID 5가 아닌 다른 사용자)
        console.log('🖱️ 편집 버튼 클릭...');
        
        // Dialog 이벤트 핸들러 설정 (alert 처리)
        page.on('dialog', async dialog => {
            console.log('📢 Dialog:', dialog.message());
            await dialog.accept();
        });
        
        // 첫 번째 편집 버튼 찾기 및 클릭
        const editButtonClicked = await page.evaluate(() => {
            const editButtons = document.querySelectorAll('.btn-edit');
            if (editButtons.length > 0) {
                // 첫 번째 편집 버튼의 onclick 속성에서 userId 추출
                const onclickText = editButtons[0].getAttribute('onclick');
                const userIdMatch = onclickText ? onclickText.match(/editUser\\((\\d+)\\)/) : null;
                const userId = userIdMatch ? userIdMatch[1] : null;
                
                console.log('클릭할 사용자 ID:', userId);
                editButtons[0].click();
                
                return { clicked: true, userId: userId };
            }
            return { clicked: false, userId: null };
        });
        
        console.log('🖱️ 편집 버튼 클릭 결과:', editButtonClicked);
        
        // 6. 편집 모달 로드 대기
        console.log('⏳ 편집 모달 로드 대기...');
        await page.waitForSelector('#editUserModal', { timeout: 10000 });
        
        // 7. 편집 모달 상태 확인
        const modalState = await page.evaluate(() => {
            const modal = document.getElementById('editUserModal');
            const form = document.getElementById('editUserForm');
            
            if (!modal || !form) {
                return { exists: false };
            }
            
            return {
                exists: true,
                visible: window.getComputedStyle(modal).display !== 'none',
                hasNicknameField: !!document.getElementById('edit_nickname'),
                hasEmailField: !!document.getElementById('edit_email'),
                hasBioField: !!document.getElementById('edit_bio'),
                hasStatusField: !!document.getElementById('edit_status'),
                hasRoleField: !!document.getElementById('edit_role'),
                hasSaveButton: !!document.getElementById('saveEditBtn'),
                
                // 현재 값들 확인
                currentValues: {
                    nickname: document.getElementById('edit_nickname')?.value || '',
                    email: document.getElementById('edit_email')?.value || '',
                    bio: document.getElementById('edit_bio')?.value || '',
                    status: document.getElementById('edit_status')?.value || '',
                    role: document.getElementById('edit_role')?.value || ''
                }
            };
        });
        
        console.log('📊 편집 모달 상태:', modalState);
        
        if (!modalState.exists) {
            throw new Error('편집 모달이 생성되지 않았습니다');
        }
        
        // 8. 편집 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/admin_edit_modal.png',
            fullPage: true 
        });
        console.log('📸 편집 모달 스크린샷 저장');
        
        // 9. 테스트 편집 수행
        console.log('✏️ 테스트 편집 수행...');
        
        const testEdit = await page.evaluate(() => {
            // 닉네임을 테스트용으로 변경
            const nicknameField = document.getElementById('edit_nickname');
            const bioField = document.getElementById('edit_bio');
            const reasonField = document.getElementById('edit_status_reason');
            
            if (nicknameField) {
                const originalNickname = nicknameField.value;
                const testNickname = originalNickname + '_테스트' + Date.now().toString().slice(-3);
                nicknameField.value = testNickname;
                console.log('닉네임 변경:', originalNickname, '→', testNickname);
            }
            
            if (bioField) {
                bioField.value = '관리자 테스트 편집 - ' + new Date().toLocaleString();
            }
            
            if (reasonField) {
                reasonField.value = '자동 테스트에 의한 편집';
            }
            
            return {
                nicknameChanged: !!nicknameField,
                bioChanged: !!bioField,
                reasonAdded: !!reasonField
            };
        });
        
        console.log('📝 테스트 편집 결과:', testEdit);
        
        // 10. 저장 버튼 클릭
        console.log('💾 저장 버튼 클릭...');
        
        // 네트워크 요청 대기
        const saveResponsePromise = page.waitForResponse(response => 
            response.url().includes('/admin/users/') && 
            response.url().includes('/edit') &&
            response.request().method() === 'POST'
        );
        
        await page.click('#saveEditBtn');
        
        // 11. 저장 응답 확인
        const saveResponse = await saveResponsePromise;
        const responseData = await saveResponse.json();
        
        console.log('📊 저장 응답:', responseData);
        
        // 12. 최종 결과 확인
        await page.waitForTimeout(2000);
        
        const finalState = await page.evaluate(() => {
            const modal = document.getElementById('editUserModal');
            return {
                modalClosed: !modal || window.getComputedStyle(modal).display === 'none',
                pageTitle: document.title,
                currentUrl: window.location.href
            };
        });
        
        console.log('🏁 최종 상태:', finalState);
        
        // 13. 최종 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/admin_edit_complete.png',
            fullPage: true 
        });
        console.log('📸 편집 완료 스크린샷 저장');
        
        return {
            success: true,
            pageState,
            modalState,
            testEdit,
            saveResponse: responseData,
            finalState
        };
        
    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/admin_edit_error.png',
                fullPage: true 
            });
            console.log('📸 오류 스크린샷 저장');
        }
        
        return { 
            success: false, 
            error: error.message 
        };
    } finally {
        if (session) {
            await helper.cleanup(session);
        }
    }
}

// 테스트 실행
testAdminEditUser().then(result => {
    if (result.success) {
        console.log('✅ 관리자 사용자 편집 기능 테스트 성공!');
        console.log('📊 테스트 결과:');
        console.log('  - 페이지 로드:', result.pageState?.hasEditButtons ? '성공' : '실패');
        console.log('  - 모달 생성:', result.modalState?.exists ? '성공' : '실패');
        console.log('  - 편집 수행:', result.testEdit?.nicknameChanged ? '성공' : '실패');
        console.log('  - 저장 응답:', result.saveResponse?.success ? '성공' : '실패');
        console.log('  - 모달 닫기:', result.finalState?.modalClosed ? '성공' : '실패');
        process.exit(0);
    } else {
        console.log('❌ 테스트 실패:', result.error);
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});