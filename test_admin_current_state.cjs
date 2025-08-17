/**
 * 현재 관리자 페이지 상태 분석
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function testCurrentAdminPageState() {
    console.log('🔍 현재 관리자 페이지 상태 분석 시작...');
    
    const helper = new DevLoginHelper();
    let session = null;
    
    try {
        // 1. 로그인 세션 생성
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
        
        // 3. 현재 페이지 상태 분석
        console.log('📊 페이지 구조 분석...');
        
        const pageAnalysis = await page.evaluate(() => {
            return {
                title: document.title,
                url: window.location.href,
                hasUsersTable: !!document.querySelector('.users-table, table'),
                editButtonsCount: document.querySelectorAll('.btn-edit').length,
                userRowsCount: document.querySelectorAll('tbody tr').length,
                editFunctionExists: typeof window.editUser === 'function',
                
                // 편집 버튼 상세 정보
                editButtons: Array.from(document.querySelectorAll('.btn-edit')).map((btn, index) => ({
                    index: index,
                    title: btn.title,
                    onclick: btn.getAttribute('onclick'),
                    visible: window.getComputedStyle(btn).display !== 'none'
                })),
                
                // 사용자 데이터 샘플
                userRows: Array.from(document.querySelectorAll('tbody tr')).slice(0, 3).map((row, index) => {
                    const cells = row.querySelectorAll('td');
                    return {
                        index: index,
                        hasEditButton: !!row.querySelector('.btn-edit'),
                        cellCount: cells.length,
                        userId: row.dataset.userId || 'N/A'
                    };
                }),
                
                // JavaScript 함수들
                availableFunctions: [
                    typeof window.editUser,
                    typeof window.viewUserDetail,
                    typeof window.updateUserStatus,
                    typeof window.deleteUser
                ].map((type, i) => ({
                    name: ['editUser', 'viewUserDetail', 'updateUserStatus', 'deleteUser'][i],
                    exists: type === 'function'
                }))
            };
        });
        
        console.log('📋 페이지 분석 결과:');
        console.log('  - 제목:', pageAnalysis.title);
        console.log('  - 편집 버튼 수:', pageAnalysis.editButtonsCount);
        console.log('  - 사용자 행 수:', pageAnalysis.userRowsCount);
        console.log('  - editUser 함수 존재:', pageAnalysis.editFunctionExists);
        
        // 4. 편집 버튼 클릭 테스트
        if (pageAnalysis.editButtonsCount > 0) {
            console.log('🖱️ 편집 버튼 클릭 테스트...');
            
            // Alert 캡처 설정
            page.on('dialog', async dialog => {
                console.log('📢 Alert 메시지:', dialog.message());
                await dialog.accept();
            });
            
            // 첫 번째 편집 버튼 클릭
            await page.click('.btn-edit');
            await page.waitForTimeout(2000);
        }
        
        // 5. 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/admin_current_state.png',
            fullPage: true 
        });
        console.log('📸 현재 상태 스크린샷 저장');
        
        return {
            success: true,
            analysis: pageAnalysis
        };
        
    } catch (error) {
        console.error('❌ 분석 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/admin_state_error.png',
                fullPage: true 
            });
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
testCurrentAdminPageState().then(result => {
    if (result.success) {
        console.log('✅ 관리자 페이지 상태 분석 완료');
        console.log('📊 결과:', JSON.stringify(result.analysis, null, 2));
        process.exit(0);
    } else {
        console.log('❌ 분석 실패:', result.error);
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});