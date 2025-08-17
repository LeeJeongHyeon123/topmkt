/**
 * 프로필 이미지 모달 JavaScript 기능 간단 테스트
 * 
 * HTML 파일에 profile-modal.js가 올바르게 포함되었는지 확인
 */

const { chromium } = require('playwright');

async function testProfileModalScript() {
    console.log('🚀 프로필 이미지 모달 스크립트 테스트 시작');
    
    const browser = await chromium.launch({ 
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // 1. list_direct.php 페이지에 직접 접근
        console.log('📋 1. list_direct.php 페이지 접근...');
        await page.goto('https://www.topmktx.com/src/views/admin/users/list_direct.php', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });

        await page.waitForTimeout(3000);

        // 2. profile-modal.js 스크립트 로드 확인
        console.log('🖼️ 2. profile-modal.js 스크립트 로드 확인...');
        const scriptResult = await page.evaluate(() => {
            return {
                profileModalExists: typeof window.profileModal !== 'undefined',
                profileModalType: typeof window.profileModal,
                showFunctionExists: window.profileModal && typeof window.profileModal.show === 'function',
                closeFunctionExists: window.profileModal && typeof window.profileModal.close === 'function',
                globalFunctionsExist: typeof window.showProfileImageModal === 'function'
            };
        });

        console.log('📊 스크립트 로드 결과:', scriptResult);

        if (!scriptResult.profileModalExists) {
            console.log('❌ window.profileModal이 존재하지 않습니다');
            return false;
        }

        if (!scriptResult.showFunctionExists) {
            console.log('❌ profileModal.show 함수가 존재하지 않습니다');
            return false;
        }

        console.log('✅ ProfileImageModal 스크립트 정상 로드');

        // 3. 페이지 내 프로필 이미지 모달 HTML 확인
        console.log('🔍 3. 프로필 이미지 모달 HTML 확인...');
        const modalHTML = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            return {
                modalExists: !!modal,
                modalImage: !!document.getElementById('modalProfileImage'),
                modalUserName: !!document.getElementById('modalUserName'),
                modalClose: !!modal && !!modal.querySelector('.modal-close')
            };
        });

        console.log('📊 모달 HTML 결과:', modalHTML);

        if (!modalHTML.modalExists) {
            console.log('❌ profileImageModal HTML이 생성되지 않았습니다');
            return false;
        }

        console.log('✅ 프로필 이미지 모달 HTML 구조 확인');

        // 4. 모달 생성 함수 테스트
        console.log('⚡ 4. 모달 생성 함수 테스트...');
        const modalTest = await page.evaluate(() => {
            try {
                // 테스트용 프로필 모달 호출
                if (window.profileModal && typeof window.profileModal.show === 'function') {
                    window.profileModal.show('/assets/uploads/default-avatar.png', '테스트 사용자', true);
                    
                    // 모달이 표시되었는지 확인
                    const modal = document.getElementById('profileImageModal');
                    const isVisible = modal && window.getComputedStyle(modal).display !== 'none';
                    
                    // 모달 닫기
                    if (window.profileModal.close) {
                        window.profileModal.close();
                    }
                    
                    return {
                        success: true,
                        modalShown: isVisible,
                        error: null
                    };
                } else {
                    return {
                        success: false,
                        error: 'profileModal.show 함수가 존재하지 않습니다'
                    };
                }
            } catch (error) {
                return {
                    success: false,
                    error: error.message
                };
            }
        });

        console.log('📊 모달 테스트 결과:', modalTest);

        if (!modalTest.success) {
            console.log('❌ 모달 기능 테스트 실패:', modalTest.error);
            return false;
        }

        console.log('✅ 프로필 이미지 모달 기능 테스트 성공');

        // 5. CSS 클래스 확인
        console.log('🎨 5. CSS 클래스 확인...');
        const cssCheck = await page.evaluate(() => {
            const styles = window.getComputedStyle(document.body);
            return {
                profileImageModalExists: !!document.querySelector('.profile-image-modal'),
                clickableClassExists: !!document.querySelector('.profile-image-clickable')
            };
        });

        console.log('📊 CSS 확인 결과:', cssCheck);

        console.log('🎉 모든 기본 테스트 통과! 프로필 이미지 모달 스크립트가 정상적으로 로드되었습니다.');
        return true;

    } catch (error) {
        console.error('❌ 테스트 실행 중 오류 발생:', error.message);
        
        // 스크린샷 캡처
        try {
            await page.screenshot({ 
                path: '/var/www/html/topmkt/profile_modal_script_test_error.png',
                fullPage: true 
            });
            console.log('📸 오류 스크린샷 저장: profile_modal_script_test_error.png');
        } catch (screenshotError) {
            console.log('⚠️ 스크린샷 저장 실패:', screenshotError.message);
        }
        
        return false;
    } finally {
        await browser.close();
    }
}

// 테스트 실행
testProfileModalScript().then(success => {
    if (success) {
        console.log('✅ 프로필 모달 스크립트 테스트 성공');
        process.exit(0);
    } else {
        console.log('❌ 프로필 모달 스크립트 테스트 실패');
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});