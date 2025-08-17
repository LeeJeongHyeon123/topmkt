/**
 * 현재 모달 정렬 상태 확인
 * 
 * 실제로 관리자 페이지에서 현재 모달이 어떻게 보이는지
 * 스크린샷을 찍어서 정렬 문제를 정확히 파악합니다.
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function testCurrentModalAlignment() {
    console.log('🔍 현재 모달 정렬 상태 확인 시작');
    
    const helper = new DevLoginHelper();
    let session = null;
    
    try {
        // 1. 개발용 세션 생성
        console.log('🔐 1. 우리집탄이 개발용 세션 생성...');
        session = await helper.getDevSession('우리집탄이', {
            headless: true,
            timeout: 30000
        });
        
        const { page, account } = session;
        console.log('✅ 세션 생성 성공');

        // 2. 관리자 페이지 접근
        console.log('👨‍💼 2. 관리자 페이지 접근...');
        await page.goto('https://www.topmktx.com/admin/users', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        // 강제 로그인 상태 설정
        await page.evaluate((userId) => {
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('user_id', userId.toString());
                localStorage.setItem('user_role', 'ROLE_ADMIN');
                localStorage.setItem('logged_in', 'true');
            }
            
            if (typeof window !== 'undefined') {
                window.currentUser = {
                    id: userId,
                    role: 'ROLE_ADMIN',
                    logged_in: true
                };
            }
        }, account.userId);
        
        await page.reload({ waitUntil: 'networkidle' });
        await page.waitForTimeout(3000);

        // 3. 실제 사용자 상세보기 버튼 찾기
        console.log('👁️ 3. 실제 사용자 상세보기 시도...');
        
        // 다양한 방법으로 상세보기 버튼 찾기
        const buttonFound = await page.evaluate(() => {
            // 1. 일반적인 상세보기 버튼들
            const viewButtons = document.querySelectorAll('.btn-view, button[title="상세보기"], .action-btn, .btn-primary');
            if (viewButtons.length > 0) {
                console.log('✅ 상세보기 버튼 발견:', viewButtons.length, '개');
                viewButtons[0].click();
                return true;
            }
            
            // 2. 텍스트로 찾기
            const allButtons = document.querySelectorAll('button, .btn, a');
            for (let btn of allButtons) {
                if (btn.textContent && (btn.textContent.includes('상세') || btn.textContent.includes('보기') || btn.textContent.includes('View'))) {
                    console.log('✅ 텍스트 기반 상세보기 버튼 발견:', btn.textContent);
                    btn.click();
                    return true;
                }
            }
            
            // 3. 테이블 행 클릭 가능한지 확인
            const tableRows = document.querySelectorAll('tr[data-user-id], .user-row, tbody tr');
            if (tableRows.length > 1) { // 헤더 제외
                console.log('✅ 테이블 행 클릭 시도');
                tableRows[1].click(); // 첫 번째 데이터 행
                return true;
            }
            
            return false;
        });
        
        if (buttonFound) {
            console.log('✅ 상세보기 버튼 클릭 성공, 대기 중...');
            await page.waitForTimeout(3000);
        } else {
            console.log('❌ 상세보기 버튼을 찾을 수 없어 직접 모달 생성...');
            
            // 직접 사용자 상세 모달 생성
            await page.evaluate(() => {
                const modalHTML = `
                    <div id="userDetailModal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 10000;">
                        <div style="background: white; border-radius: 16px; max-width: 900px; width: 95%; max-height: 95vh; overflow-y: auto; box-shadow: 0 25px 80px rgba(0,0,0,0.4);">
                            <div style="padding: 25px 35px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px 16px 0 0;">
                                <h2 style="margin: 0; font-size: 1.4em;">👤 사용자 상세 정보 (실제 정렬 테스트)</h2>
                                <button onclick="document.getElementById('userDetailModal').style.display='none'" style="background: rgba(255,255,255,0.15); border: none; font-size: 24px; color: white; cursor: pointer; padding: 8px; border-radius: 50%;">&times;</button>
                            </div>
                            <div style="padding: 35px; text-align: center;">
                                <img src="/assets/uploads/default-avatar.png" 
                                     alt="프로필 이미지" 
                                     class="profile-image-clickable" 
                                     style="width: 140px; height: 140px; border-radius: 50%; cursor: pointer; border: 5px solid #e2e8f0; box-shadow: 0 8px 25px rgba(0,0,0,0.15);"
                                     title="클릭하여 프로필 모달 확인"
                                     onclick="openProfileImageModal('/assets/uploads/default-avatar.png', '우리집탄이')">
                                <h3 style="margin: 20px 0 15px 0;">우리집탄이</h3>
                                <p style="color: #667eea; font-weight: 600;">👆 프로필 이미지를 클릭하여 정렬 상태를 확인하세요</p>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHTML);
            });
        }

        // 4. 현재 페이지 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/current_user_detail_modal.png',
            fullPage: true 
        });
        console.log('📸 현재 사용자 상세 모달 스크린샷 저장');

        // 5. 프로필 이미지 클릭
        console.log('🖼️ 5. 프로필 이미지 클릭...');
        
        const profileImage = await page.$('.profile-image-clickable, .profile-image-large, img[onclick*="openProfileImageModal"]');
        if (profileImage) {
            console.log('✅ 프로필 이미지 발견, 클릭 진행...');
            await profileImage.click();
            await page.waitForTimeout(2000);
        } else {
            console.log('❌ 프로필 이미지를 찾을 수 없음, 직접 함수 호출...');
            await page.evaluate(() => {
                if (typeof window.openProfileImageModal === 'function') {
                    window.openProfileImageModal('/assets/uploads/default-avatar.png', '우리집탄이');
                } else {
                    // 강제로 fallback 모달 생성
                    window.createFallbackProfileModal('/assets/uploads/default-avatar.png', '우리집탄이');
                }
            });
            await page.waitForTimeout(2000);
        }

        // 6. 프로필 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/current_profile_modal_issue.png',
            fullPage: true 
        });
        console.log('📸 현재 프로필 모달 (문제 상태) 스크린샷 저장');

        // 7. 현재 모달 상태 분석
        console.log('🔍 7. 현재 모달 상태 분석...');
        
        const modalAnalysis = await page.evaluate(() => {
            const modals = {
                original: document.getElementById('profileImageModal'),
                fallback: document.getElementById('fallbackProfileModal'),
                manual: document.getElementById('manualFallbackModal')
            };
            
            let activeModal = null;
            let modalType = 'none';
            
            for (const [type, modal] of Object.entries(modals)) {
                if (modal && window.getComputedStyle(modal).display !== 'none') {
                    activeModal = modal;
                    modalType = type;
                    break;
                }
            }
            
            if (!activeModal) return { modalType: 'none' };
            
            const modalImage = activeModal.querySelector('img');
            const modalBody = activeModal.querySelector('.modal-body');
            
            return {
                modalType: modalType,
                visible: true,
                hasImage: !!modalImage,
                imageSrc: modalImage ? modalImage.src : null,
                modalRect: activeModal.getBoundingClientRect(),
                imageRect: modalImage ? modalImage.getBoundingClientRect() : null,
                bodyStyles: modalBody ? {
                    textAlign: window.getComputedStyle(modalBody).textAlign,
                    display: window.getComputedStyle(modalBody).display,
                    justifyContent: window.getComputedStyle(modalBody).justifyContent,
                    alignItems: window.getComputedStyle(modalBody).alignItems,
                    padding: window.getComputedStyle(modalBody).padding,
                    width: modalBody.getBoundingClientRect().width,
                    height: modalBody.getBoundingClientRect().height
                } : null,
                imageStyles: modalImage ? {
                    display: window.getComputedStyle(modalImage).display,
                    margin: window.getComputedStyle(modalImage).margin,
                    objectFit: window.getComputedStyle(modalImage).objectFit,
                    maxWidth: window.getComputedStyle(modalImage).maxWidth,
                    maxHeight: window.getComputedStyle(modalImage).maxHeight,
                    position: window.getComputedStyle(modalImage).position,
                    left: window.getComputedStyle(modalImage).left,
                    transform: window.getComputedStyle(modalImage).transform
                } : null,
                windowSize: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };
        });
        
        console.log('📊 현재 모달 상태 분석:');
        console.log('  - 모달 타입:', modalAnalysis.modalType);
        console.log('  - 이미지 존재:', modalAnalysis.hasImage);
        
        if (modalAnalysis.hasImage) {
            console.log('  - 모달 크기:', `${modalAnalysis.modalRect.width}x${modalAnalysis.modalRect.height}`);
            console.log('  - 이미지 크기:', `${modalAnalysis.imageRect.width}x${modalAnalysis.imageRect.height}`);
            console.log('  - 이미지 위치:', `left: ${modalAnalysis.imageRect.left}, top: ${modalAnalysis.imageRect.top}`);
            console.log('  - Body 스타일:', modalAnalysis.bodyStyles);
            console.log('  - 이미지 스타일:', modalAnalysis.imageStyles);
            
            // 정렬 문제 진단
            const centerX = modalAnalysis.modalRect.width / 2;
            const imageCenterX = modalAnalysis.imageRect.left + (modalAnalysis.imageRect.width / 2) - modalAnalysis.modalRect.left;
            const alignmentOffset = Math.abs(centerX - imageCenterX);
            
            console.log(`🎯 정렬 분석: 모달 중심=${centerX}, 이미지 중심=${imageCenterX}, 편차=${alignmentOffset}px`);
            
            if (alignmentOffset > 10) {
                console.log('❌ 정렬 문제 확인됨! 이미지가 중앙에서 벗어남');
            } else {
                console.log('✅ 정렬이 정상적임');
            }
        }

        return {
            success: modalAnalysis.modalType !== 'none',
            modalAnalysis: modalAnalysis
        };

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/current_modal_error.png',
                fullPage: true 
            });
            console.log('📸 오류 스크린샷 저장');
        }
        
        return { success: false, error: error.message };
    } finally {
        if (session) {
            await helper.cleanup(session);
        }
    }
}

// 테스트 실행
testCurrentModalAlignment().then(result => {
    if (result.success) {
        console.log('✅ 현재 모달 상태 확인 완료');
        process.exit(0);
    } else {
        console.log('❌ 확인 실패:', result.error);
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});