/**
 * 관리자 사용자 상세 모달 - 프로필 이미지 클릭 기능 테스트
 * 
 * 테스트 목적:
 * 1. 관리자 페이지 접근 가능성 확인
 * 2. 사용자 상세 모달 정상 동작 확인
 * 3. 프로필 이미지 클릭 시 ProfileImageModal 정상 동작 확인
 * 4. 모달 닫기 기능 확인
 * 5. ESC 키 동작 확인
 */

const { chromium } = require('playwright');

async function testAdminProfileModal() {
    console.log('🚀 관리자 프로필 모달 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // 1. 관리자 페이지 접근 테스트
        console.log('📋 1. 관리자 사용자 목록 페이지 접근...');
        await page.goto('https://www.topmktx.com/admin/users', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });

        // 페이지 로드 대기
        await page.waitForTimeout(3000);

        // 로그인 상태 확인
        const isLoggedIn = await page.evaluate(() => {
            return !window.location.href.includes('/auth/login');
        });

        if (!isLoggedIn) {
            console.log('❌ 관리자 로그인이 필요합니다');
            return false;
        }

        console.log('✅ 관리자 페이지 접근 성공');

        // 2. 사용자 데이터 로드 대기
        console.log('📊 2. 사용자 데이터 로드 대기...');
        await page.waitForSelector('#usersTable', { timeout: 10000 });
        await page.waitForTimeout(2000);

        // 3. 첫 번째 사용자의 상세보기 버튼 찾기
        console.log('👁️ 3. 사용자 상세보기 테스트...');
        const viewButton = await page.$('.btn-view');
        if (!viewButton) {
            console.log('❌ 상세보기 버튼을 찾을 수 없습니다');
            return false;
        }

        // 상세보기 클릭
        await viewButton.click();
        
        // 사용자 상세 모달 로드 대기
        await page.waitForSelector('#userDetailModal', { state: 'visible', timeout: 10000 });
        await page.waitForTimeout(2000);

        console.log('✅ 사용자 상세 모달 표시 성공');

        // 4. ProfileImageModal 스크립트 로드 확인
        console.log('🖼️ 4. ProfileImageModal 로드 확인...');
        const profileModalExists = await page.evaluate(() => {
            return typeof window.profileModal !== 'undefined' && window.profileModal !== null;
        });

        if (!profileModalExists) {
            console.log('❌ ProfileImageModal이 로드되지 않았습니다');
            return false;
        }

        console.log('✅ ProfileImageModal 로드 확인');

        // 5. 프로필 이미지 클릭 테스트
        console.log('👤 5. 프로필 이미지 클릭 테스트...');
        const profileImage = await page.$('.profile-image-clickable');
        if (!profileImage) {
            console.log('❌ 클릭 가능한 프로필 이미지를 찾을 수 없습니다');
            return false;
        }

        // 이미지 클릭
        await profileImage.click();
        await page.waitForTimeout(1000);

        // 프로필 이미지 모달 표시 확인
        const profileModalVisible = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            return modal && window.getComputedStyle(modal).display !== 'none';
        });

        if (!profileModalVisible) {
            console.log('❌ 프로필 이미지 모달이 표시되지 않았습니다');
            return false;
        }

        console.log('✅ 프로필 이미지 모달 표시 성공');

        // 6. 프로필 이미지 모달 내용 확인
        console.log('🔍 6. 프로필 이미지 모달 내용 확인...');
        const modalContent = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            const modalImage = modal ? modal.querySelector('#modalProfileImage') : null;
            const modalTitle = modal ? modal.querySelector('#modalUserName') : null;
            
            return {
                hasModal: !!modal,
                hasImage: !!modalImage,
                hasTitle: !!modalTitle,
                imageLoaded: modalImage ? modalImage.style.display !== 'none' : false,
                titleText: modalTitle ? modalTitle.textContent : ''
            };
        });

        console.log('📊 모달 내용:', modalContent);

        if (!modalContent.hasModal || !modalContent.hasImage) {
            console.log('❌ 프로필 이미지 모달 내용이 올바르지 않습니다');
            return false;
        }

        console.log('✅ 프로필 이미지 모달 내용 확인 성공');

        // 7. 모달 닫기 테스트 (ESC 키)
        console.log('⌨️ 7. ESC 키로 모달 닫기 테스트...');
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);

        const modalClosedByEsc = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            return !modal || window.getComputedStyle(modal).display === 'none';
        });

        if (!modalClosedByEsc) {
            console.log('❌ ESC 키로 모달이 닫히지 않았습니다');
            return false;
        }

        console.log('✅ ESC 키로 모달 닫기 성공');

        // 8. X 버튼으로 모달 닫기 테스트
        console.log('❌ 8. X 버튼으로 모달 닫기 테스트...');
        
        // 프로필 이미지 다시 클릭
        await profileImage.click();
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
                return false;
            }
        } else {
            console.log('⚠️ X 버튼을 찾을 수 없습니다 (무시 가능)');
        }

        // 9. 사용자 상세 모달 닫기
        console.log('🚪 9. 사용자 상세 모달 닫기...');
        const userModalCloseButton = await page.$('#userDetailModal .modal-close');
        if (userModalCloseButton) {
            await userModalCloseButton.click();
            await page.waitForTimeout(1000);
        }

        console.log('🎉 모든 테스트 통과! 프로필 이미지 모달 기능이 정상적으로 작동합니다.');
        return true;

    } catch (error) {
        console.error('❌ 테스트 실행 중 오류 발생:', error.message);
        
        // 스크린샷 캡처
        try {
            await page.screenshot({ 
                path: '/var/www/html/topmkt/admin_profile_modal_test_error.png',
                fullPage: true 
            });
            console.log('📸 오류 스크린샷 저장: admin_profile_modal_test_error.png');
        } catch (screenshotError) {
            console.log('⚠️ 스크린샷 저장 실패:', screenshotError.message);
        }
        
        return false;
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testAdminProfileModal().then(success => {
    if (success) {
        console.log('✅ 관리자 프로필 모달 테스트 성공');
        process.exit(0);
    } else {
        console.log('❌ 관리자 프로필 모달 테스트 실패');
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});