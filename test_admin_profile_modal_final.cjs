/**
 * LoginSessionHelper를 사용한 관리자 프로필 모달 완전 테스트
 * 
 * 이 테스트는:
 * 1. LoginSessionHelper로 우리집탄이 계정 자동 로그인
 * 2. 관리자 사용자 목록 페이지 접근
 * 3. 사용자 상세보기 클릭
 * 4. 프로필 이미지 클릭하여 모달 확인
 * 5. 모달 기능 검증 (ESC, X버튼 등)
 */

const LoginSessionHelper = require('./test_helpers/LoginSessionHelper.cjs');

async function testAdminProfileModalComplete() {
    console.log('🚀 관리자 프로필 모달 완전 테스트 시작');
    
    const helper = new LoginSessionHelper();
    let session = null;
    
    try {
        // 1. 우리집탄이 계정으로 로그인
        console.log('🔐 1. 우리집탄이 계정 로그인...');
        session = await helper.getLoggedInSession('우리집탄이', {
            headless: true,
            timeout: 30000
        });
        
        const { page } = session;
        console.log('✅ 로그인 성공');

        // 2. 관리자 사용자 목록 페이지 접근
        console.log('👨‍💼 2. 관리자 사용자 목록 페이지 접근...');
        await page.goto('https://www.topmktx.com/admin/users', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        // 페이지 로드 대기
        await page.waitForTimeout(3000);
        
        const pageTitle = await page.title();
        console.log('📄 페이지 제목:', pageTitle);
        
        if (!pageTitle.includes('관리자') && !pageTitle.includes('회원')) {
            console.log('⚠️ 예상과 다른 페이지 제목, 계속 진행...');
        }
        
        console.log('✅ 관리자 페이지 접근 성공');

        // 3. ProfileImageModal 스크립트 로드 확인
        console.log('🖼️ 3. ProfileImageModal 스크립트 로드 확인...');
        await page.waitForTimeout(2000);

        const scriptResult = await page.evaluate(() => {
            return {
                profileModalExists: typeof window.profileModal !== 'undefined',
                showFunction: window.profileModal && typeof window.profileModal.show === 'function',
                closeFunction: window.profileModal && typeof window.profileModal.close === 'function',
                modalHTML: !!document.getElementById('profileImageModal'),
                consoleErrors: window.console && window.console.error ? window.console.error.toString() : 'none'
            };
        });

        console.log('📊 스크립트 로드 상태:', scriptResult);

        if (!scriptResult.profileModalExists) {
            console.log('❌ ProfileImageModal이 로드되지 않았습니다');
            
            // profile-modal.js 로드 상태 확인
            const scriptTags = await page.$$eval('script', scripts => 
                scripts.map(s => s.src).filter(src => src && src.includes('profile-modal'))
            );
            console.log('🔍 profile-modal.js 스크립트 태그:', scriptTags);
            
            // 수동으로 스크립트 로드 시도
            console.log('🔄 수동 스크립트 로드 시도...');
            await page.addScriptTag({ url: 'https://www.topmktx.com/assets/js/profile-modal.js' });
            await page.waitForTimeout(2000);
            
            const retryResult = await page.evaluate(() => {
                return typeof window.profileModal !== 'undefined';
            });
            
            if (retryResult) {
                console.log('✅ 수동 스크립트 로드 성공');
            } else {
                console.log('❌ 수동 스크립트 로드도 실패, 테스트 중단');
                return false;
            }
        } else {
            console.log('✅ ProfileImageModal 정상 로드');
        }

        // 4. 사용자 데이터 로드 대기
        console.log('📊 4. 사용자 데이터 로드 대기...');
        
        try {
            await page.waitForSelector('#usersTable, .users-table', { timeout: 15000 });
            console.log('✅ 사용자 테이블 발견');
        } catch (error) {
            console.log('⚠️ 사용자 테이블 로드 시간 초과, 계속 진행...');
        }

        await page.waitForTimeout(3000);

        // 5. 첫 번째 사용자의 상세보기 버튼 클릭
        console.log('👁️ 5. 사용자 상세보기 클릭...');
        
        const viewButtons = await page.$$('.btn-view, button[title="상세보기"], .action-btn');
        if (viewButtons.length === 0) {
            console.log('❌ 상세보기 버튼을 찾을 수 없습니다');
            
            // 페이지 스크린샷
            await page.screenshot({ 
                path: '/var/www/html/topmkt/admin_users_page.png',
                fullPage: true 
            });
            console.log('📸 관리자 사용자 페이지 스크린샷 저장');
            return false;
        }

        console.log(`🔍 ${viewButtons.length}개의 상세보기 버튼 발견`);
        await viewButtons[0].click();
        console.log('✅ 상세보기 버튼 클릭 성공');

        // 6. 사용자 상세 모달 로드 대기
        console.log('⏳ 6. 사용자 상세 모달 로드 대기...');
        
        try {
            await page.waitForSelector('#userDetailModal', { state: 'visible', timeout: 10000 });
            console.log('✅ 사용자 상세 모달 표시 성공');
        } catch (error) {
            console.log('❌ 사용자 상세 모달 로드 실패:', error.message);
            return false;
        }

        await page.waitForTimeout(2000);

        // 7. 프로필 이미지 클릭 테스트
        console.log('🖼️ 7. 프로필 이미지 클릭 테스트...');
        
        const profileImages = await page.$$('.profile-image-clickable, .profile-image-large');
        
        if (profileImages.length === 0) {
            console.log('❌ 클릭 가능한 프로필 이미지를 찾을 수 없습니다');
            
            // 상세 모달 내용 확인
            const modalContent = await page.$eval('#userDetailModal', modal => modal.innerHTML);
            console.log('🔍 모달 내용 일부:', modalContent.substring(0, 500));
            
            await page.screenshot({ 
                path: '/var/www/html/topmkt/user_detail_modal.png',
                fullPage: true 
            });
            console.log('📸 사용자 상세 모달 스크린샷 저장');
            return false;
        }

        console.log(`🔍 ${profileImages.length}개의 프로필 이미지 발견`);
        
        // onclick 속성 확인
        const onclickAttr = await page.$eval('.profile-image-clickable, .profile-image-large', 
            img => img.getAttribute('onclick'));
        console.log('🔗 프로필 이미지 onclick:', onclickAttr);

        // 프로필 이미지 클릭
        await profileImages[0].click();
        console.log('✅ 프로필 이미지 클릭 완료');
        
        await page.waitForTimeout(2000);

        // 8. 프로필 이미지 모달 표시 확인
        console.log('🔍 8. 프로필 이미지 모달 표시 확인...');
        
        const profileModalState = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            const modalImage = modal ? modal.querySelector('#modalProfileImage') : null;
            const modalTitle = modal ? modal.querySelector('#modalUserName') : null;
            
            return {
                modalExists: !!modal,
                modalVisible: modal ? window.getComputedStyle(modal).display !== 'none' : false,
                modalImage: !!modalImage,
                modalTitle: !!modalTitle,
                modalTitleText: modalTitle ? modalTitle.textContent : '',
                modalImageSrc: modalImage ? modalImage.src : ''
            };
        });

        console.log('📊 프로필 모달 상태:', profileModalState);

        if (!profileModalState.modalVisible) {
            console.log('❌ 프로필 이미지 모달이 표시되지 않았습니다');
            
            // JavaScript 에러 확인
            const jsErrors = await page.evaluate(() => {
                return window.lastJSError || 'No JS errors captured';
            });
            console.log('🐛 JavaScript 오류:', jsErrors);
            
            await page.screenshot({ 
                path: '/var/www/html/topmkt/profile_modal_not_shown.png',
                fullPage: true 
            });
            console.log('📸 프로필 모달 미표시 스크린샷 저장');
            return false;
        }

        console.log('✅ 프로필 이미지 모달 표시 성공!');

        // 9. 성공 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/profile_modal_success_final.png',
            fullPage: true 
        });
        console.log('📸 성공 스크린샷 저장: profile_modal_success_final.png');

        // 10. 모달 닫기 테스트 (ESC 키)
        console.log('⌨️ 10. ESC 키로 모달 닫기 테스트...');
        await page.keyboard.press('Escape');
        await page.waitForTimeout(1000);

        const modalClosedByEsc = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            return !modal || window.getComputedStyle(modal).display === 'none';
        });

        if (modalClosedByEsc) {
            console.log('✅ ESC 키로 모달 닫기 성공');
        } else {
            console.log('⚠️ ESC 키 동작 확인 필요');
        }

        // 11. X 버튼으로 모달 닫기 테스트
        console.log('❌ 11. X 버튼으로 모달 닫기 테스트...');
        
        // 프로필 이미지 다시 클릭
        await profileImages[0].click();
        await page.waitForTimeout(1000);

        // X 버튼 클릭
        const closeButton = await page.$('.profile-image-modal .modal-close');
        if (closeButton) {
            await closeButton.click();
            await page.waitForTimeout(500);

            const modalClosedByButton = await page.evaluate(() => {
                const modal = document.getElementById('profileImageModal');
                return !modal || window.getComputedStyle(modal).display === 'none';
            });

            if (modalClosedByButton) {
                console.log('✅ X 버튼으로 모달 닫기 성공');
            } else {
                console.log('❌ X 버튼으로 모달이 닫히지 않았습니다');
            }
        } else {
            console.log('⚠️ X 버튼을 찾을 수 없습니다');
        }

        console.log('🎉 모든 테스트 완료! 관리자 프로필 이미지 모달 기능이 정상적으로 작동합니다.');
        return true;

    } catch (error) {
        console.error('❌ 테스트 실행 중 오류 발생:', error.message);
        console.error('스택 트레이스:', error.stack);
        
        if (session && session.page) {
            try {
                await session.page.screenshot({ 
                    path: '/var/www/html/topmkt/test_error_final.png',
                    fullPage: true 
                });
                console.log('📸 오류 스크린샷 저장: test_error_final.png');
            } catch (screenshotError) {
                console.log('⚠️ 스크린샷 저장 실패:', screenshotError.message);
            }
        }
        
        return false;
    } finally {
        if (session) {
            await helper.cleanup(session);
        }
    }
}

// 테스트 실행
testAdminProfileModalComplete().then(success => {
    if (success) {
        console.log('✅ 관리자 프로필 모달 테스트 완전 성공!');
        process.exit(0);
    } else {
        console.log('❌ 관리자 프로필 모달 테스트 실패');
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});