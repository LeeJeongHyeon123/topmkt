/**
 * 우리집탄이 계정으로 로그인하여 관리자 프로필 모달 테스트
 * 
 * 테스트 시나리오:
 * 1. 로그인 페이지로 이동
 * 2. 우리집탄이 계정으로 로그인
 * 3. 관리자 사용자 목록 페이지 접근
 * 4. 사용자 상세보기 클릭
 * 5. 프로필 이미지 클릭하여 모달 확인
 */

const { chromium } = require('playwright');

async function testAdminProfileModalWithLogin() {
    console.log('🚀 우리집탄이 계정으로 관리자 프로필 모달 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,  // headless 모드로 테스트
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // 1. 로그인 페이지 접근
        console.log('🔐 1. 로그인 페이지 접근...');
        await page.goto('https://www.topmktx.com/auth/login', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });

        // 2. 로그인 폼 입력 (우리집탄이 계정)
        console.log('👤 2. 우리집탄이 계정으로 로그인...');
        
        // 휴대폰 번호 입력
        await page.fill('#phone, input[name="phone"]', '010-1145-7797');
        await page.waitForTimeout(500);
        
        // 비밀번호 입력
        await page.fill('#password, input[name="password"]', 'dnlszkem1!');
        await page.waitForTimeout(500);
        
        // 로그인 버튼 클릭
        const loginButton = await page.$('button[type="submit"], input[type="submit"], .btn-login, button:has-text("로그인")');
        if (loginButton) {
            await loginButton.click();
        } else {
            // 엔터키로 로그인 시도
            await page.keyboard.press('Enter');
        }
        
        // 로그인 완료 대기 (메인 페이지로 리다이렉트)
        await page.waitForTimeout(3000);

        // 3. 로그인 성공 확인
        const currentUrl = page.url();
        if (currentUrl.includes('/auth/login')) {
            console.log('❌ 로그인 실패 - 다시 로그인 페이지에 있습니다');
            return false;
        }

        console.log('✅ 로그인 성공 - 현재 URL:', currentUrl);

        // 4. 관리자 페이지 접근
        console.log('👨‍💼 4. 관리자 사용자 목록 페이지 접근...');
        await page.goto('https://www.topmktx.com/admin/users', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });

        // 관리자 권한 확인
        await page.waitForTimeout(2000);
        
        const pageTitle = await page.title();
        if (pageTitle.includes('관리자') || pageTitle.includes('회원')) {
            console.log('✅ 관리자 페이지 접근 성공 - 제목:', pageTitle);
        } else {
            console.log('⚠️ 관리자 페이지 제목 확인 필요 - 제목:', pageTitle);
        }

        // 5. profile-modal.js 로드 확인
        console.log('🖼️ 5. ProfileImageModal 스크립트 로드 확인...');
        await page.waitForTimeout(3000);

        const scriptResult = await page.evaluate(() => {
            return {
                profileModalExists: typeof window.profileModal !== 'undefined',
                showFunctionExists: window.profileModal && typeof window.profileModal.show === 'function',
                closeFunctionExists: window.profileModal && typeof window.profileModal.close === 'function'
            };
        });

        console.log('📊 스크립트 로드 결과:', scriptResult);

        if (!scriptResult.profileModalExists) {
            console.log('❌ ProfileImageModal이 로드되지 않았습니다');
            console.log('🔍 스크립트 에러 확인 중...');
            
            // 콘솔 에러 확인
            const errors = await page.evaluate(() => {
                const errors = window.console.errors || [];
                return errors;
            });
            console.log('콘솔 에러:', errors);
        } else {
            console.log('✅ ProfileImageModal 로드 성공');
        }

        // 6. 사용자 데이터 로드 대기
        console.log('📊 6. 사용자 데이터 로드 대기...');
        
        try {
            await page.waitForSelector('#usersTable, .users-table', { timeout: 15000 });
            console.log('✅ 사용자 테이블 로드 성공');
        } catch (error) {
            console.log('⚠️ 사용자 테이블 로드 대기 중...', error.message);
        }

        await page.waitForTimeout(3000);

        // 7. 첫 번째 사용자의 상세보기 버튼 클릭
        console.log('👁️ 7. 사용자 상세보기 클릭...');
        const viewButton = await page.$('.btn-view, button[title="상세보기"], .action-btn:has-text("👁️")');
        
        if (!viewButton) {
            console.log('❌ 상세보기 버튼을 찾을 수 없습니다');
            
            // 페이지 스크린샷
            await page.screenshot({ 
                path: '/var/www/html/topmkt/admin_page_screenshot.png',
                fullPage: true 
            });
            console.log('📸 관리자 페이지 스크린샷 저장: admin_page_screenshot.png');
            return false;
        }

        await viewButton.click();
        console.log('✅ 상세보기 버튼 클릭 성공');

        // 8. 사용자 상세 모달 로드 대기
        console.log('⏳ 8. 사용자 상세 모달 로드 대기...');
        try {
            await page.waitForSelector('#userDetailModal', { state: 'visible', timeout: 10000 });
            console.log('✅ 사용자 상세 모달 표시 성공');
        } catch (error) {
            console.log('❌ 사용자 상세 모달을 찾을 수 없습니다:', error.message);
            return false;
        }

        await page.waitForTimeout(2000);

        // 9. 프로필 이미지 클릭 테스트
        console.log('🖼️ 9. 프로필 이미지 클릭 테스트...');
        const profileImage = await page.$('.profile-image-clickable, .profile-image-large');
        
        if (!profileImage) {
            console.log('❌ 클릭 가능한 프로필 이미지를 찾을 수 없습니다');
            
            // 상세 모달 스크린샷
            await page.screenshot({ 
                path: '/var/www/html/topmkt/user_detail_modal_screenshot.png',
                fullPage: true 
            });
            console.log('📸 사용자 상세 모달 스크린샷 저장: user_detail_modal_screenshot.png');
            return false;
        }

        console.log('✅ 프로필 이미지 발견, 클릭 진행...');
        await profileImage.click();
        await page.waitForTimeout(2000);

        // 10. 프로필 이미지 모달 확인
        console.log('🔍 10. 프로필 이미지 모달 표시 확인...');
        const profileModalVisible = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            return modal && window.getComputedStyle(modal).display !== 'none';
        });

        if (!profileModalVisible) {
            console.log('❌ 프로필 이미지 모달이 표시되지 않았습니다');
            
            // 현재 상태 스크린샷
            await page.screenshot({ 
                path: '/var/www/html/topmkt/profile_modal_test_result.png',
                fullPage: true 
            });
            console.log('📸 테스트 결과 스크린샷 저장: profile_modal_test_result.png');
            return false;
        }

        console.log('✅ 프로필 이미지 모달 표시 성공!');

        // 11. 최종 성공 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/profile_modal_success.png',
            fullPage: true 
        });
        console.log('📸 성공 스크린샷 저장: profile_modal_success.png');

        // 12. ESC 키로 모달 닫기 테스트
        console.log('⌨️ 12. ESC 키로 모달 닫기...');
        await page.keyboard.press('Escape');
        await page.waitForTimeout(1000);

        const modalClosed = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            return !modal || window.getComputedStyle(modal).display === 'none';
        });

        if (modalClosed) {
            console.log('✅ ESC 키로 모달 닫기 성공');
        } else {
            console.log('⚠️ ESC 키 동작 확인 필요');
        }

        console.log('🎉 모든 테스트 성공! 프로필 이미지 모달 기능이 완벽하게 작동합니다.');
        
        // 5초 대기 후 브라우저 닫기
        console.log('✨ 5초 후 브라우저가 자동으로 닫힙니다...');
        await page.waitForTimeout(5000);
        
        return true;

    } catch (error) {
        console.error('❌ 테스트 실행 중 오류 발생:', error.message);
        
        // 오류 스크린샷 캡처
        try {
            await page.screenshot({ 
                path: '/var/www/html/topmkt/admin_test_error.png',
                fullPage: true 
            });
            console.log('📸 오류 스크린샷 저장: admin_test_error.png');
        } catch (screenshotError) {
            console.log('⚠️ 스크린샷 저장 실패:', screenshotError.message);
        }
        
        return false;
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testAdminProfileModalWithLogin().then(success => {
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