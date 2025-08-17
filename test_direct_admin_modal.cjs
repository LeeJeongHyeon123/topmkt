/**
 * 직접 관리자 모달 테스트
 * 
 * 실제 관리자 페이지에서 직접 모달을 생성하고
 * 현재 정렬 문제를 확인합니다.
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function testDirectAdminModal() {
    console.log('🎯 직접 관리자 모달 테스트 시작');
    
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

        // 2. 관리자 페이지 직접 접근
        console.log('👨‍💼 2. 관리자 사용자 목록 페이지 직접 접근...');
        await page.goto('https://www.topmktx.com/admin/users', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        // 로그인 상태 강제 설정
        await page.evaluate((userId) => {
            localStorage.setItem('user_id', userId.toString());
            localStorage.setItem('user_role', 'ROLE_ADMIN');
            localStorage.setItem('logged_in', 'true');
            
            window.currentUser = {
                id: userId,
                role: 'ROLE_ADMIN',
                logged_in: true
            };
        }, account.userId);
        
        await page.reload({ waitUntil: 'networkidle' });
        await page.waitForTimeout(5000);

        // 3. 현재 페이지 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/real_admin_page.png',
            fullPage: true 
        });
        console.log('📸 실제 관리자 페이지 스크린샷 저장');

        // 4. 현재 관리자 페이지 분석
        console.log('🔍 4. 현재 관리자 페이지 분석...');
        
        const pageAnalysis = await page.evaluate(() => {
            return {
                url: window.location.href,
                title: document.title,
                hasUserTable: !!document.querySelector('.users-table, table'),
                hasViewButtons: document.querySelectorAll('.btn-view, button[title="상세보기"]').length,
                hasUserRows: document.querySelectorAll('tr[data-user-id], tbody tr').length,
                allButtons: Array.from(document.querySelectorAll('button, .btn')).map(btn => btn.textContent?.trim()).filter(Boolean).slice(0, 10),
                scripts: Array.from(document.querySelectorAll('script')).map(s => s.src).filter(src => src),
                modalFunctions: {
                    openProfileImageModal: typeof window.openProfileImageModal === 'function',
                    createFallbackProfileModal: typeof window.createFallbackProfileModal === 'function',
                    profileModal: typeof window.profileModal !== 'undefined'
                }
            };
        });
        
        console.log('📊 페이지 분석 결과:', pageAnalysis);

        // 5. 강제로 사용자 상세 모달 생성 (실제 관리자 스타일로)
        console.log('🎭 5. 실제 스타일의 사용자 상세 모달 생성...');
        
        await page.evaluate(() => {
            // 실제 관리자 페이지의 renderUserDetail 함수 시뮬레이션
            function renderUserDetail(user) {
                const escapeHtml = (text) => {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                };
                
                const getRoleText = (role) => {
                    switch(role) {
                        case 'ROLE_ADMIN': return '관리자';
                        case 'ROLE_CORPORATE': return '기업';
                        case 'ROLE_USER': 
                        default: return '일반 사용자';
                    }
                };
                
                const getStatusText = (status) => {
                    switch(status) {
                        case 'active': return '활성';
                        case 'inactive': return '비활성';
                        case 'suspended': return '정지';
                        default: return status;
                    }
                };
                
                const profileImage = user.profile_image_original || user.profile_image || '/assets/uploads/default-avatar.png';
                
                return `
                <div id="userDetailModal" class="modal-overlay" style="
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                    background: rgba(0,0,0,0.6); display: flex; justify-content: center; 
                    align-items: center; z-index: 10000; backdrop-filter: blur(2px);
                ">
                    <div class="modal-content" style="
                        background: white; border-radius: 16px; max-width: 900px; width: 95%; 
                        max-height: 95vh; overflow-y: auto; box-shadow: 0 25px 80px rgba(0,0,0,0.4);
                    ">
                        <div class="modal-header" style="
                            padding: 25px 35px; border-bottom: 1px solid #e2e8f0; display: flex; 
                            justify-content: space-between; align-items: center; 
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                            color: white; border-radius: 16px 16px 0 0;
                        ">
                            <h2 style="margin: 0; font-size: 1.4em;">👤 사용자 상세 정보</h2>
                            <button class="modal-close" onclick="document.getElementById('userDetailModal').style.display='none'" 
                                    style="background: rgba(255,255,255,0.15); border: none; font-size: 24px; color: white; cursor: pointer; padding: 8px; border-radius: 50%;">&times;</button>
                        </div>
                        <div class="modal-body" style="padding: 35px;">
                            <div class="profile-image-container" style="text-align: center; margin-bottom: 30px;">
                                <img src="${profileImage}" alt="프로필 이미지" class="profile-image-large profile-image-clickable" 
                                     title="클릭하면 큰 이미지로 볼 수 있습니다" 
                                     onclick="openProfileImageModal('${user.profile_image_original || user.profile_image || profileImage}', '${escapeHtml(user.nickname)}')" 
                                     onerror="this.src='/assets/uploads/default-avatar.png'"
                                     style="width: 120px; height: 120px; border-radius: 50%; cursor: pointer; border: 4px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s ease; transform: scale(1);">
                                <h3 style="margin: 15px 0 10px 0; color: #2d3748;">${escapeHtml(user.nickname)}</h3>
                                <div style="margin-bottom: 30px;">
                                    <span style="background: #fbb6ce; color: #97266d; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">${getRoleText(user.role)}</span>
                                    <span style="background: #c6f6d5; color: #22543d; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; margin-left: 10px;">${getStatusText(user.status)}</span>
                                </div>
                                <div style="margin-top: 20px; padding: 20px; background: linear-gradient(135deg, #fff5f5, #fed7d7); border-radius: 12px; border: 2px dashed #fc8181;">
                                    <h4 style="color: #c53030; margin: 0 0 15px 0;">⚠️ 정렬 문제 확인 중</h4>
                                    <p style="margin: 0; color: #742a2a; font-size: 0.9em;">위의 프로필 이미지를 클릭하여 현재 정렬 상태를 확인하세요</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            }
            
            // 테스트 사용자 데이터
            const testUser = {
                id: 4,
                nickname: '우리집탄이',
                role: 'ROLE_ADMIN',
                status: 'active',
                profile_image: '/assets/uploads/default-avatar.png',
                profile_image_original: '/assets/uploads/default-avatar.png'
            };
            
            // 모달 생성
            const modalHTML = renderUserDetail(testUser);
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            console.log('✅ 실제 스타일 사용자 상세 모달 생성 완료');
        });

        // 6. 모달 생성 후 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/real_admin_modal_created.png',
            fullPage: true 
        });
        console.log('📸 실제 관리자 모달 생성 후 스크린샷 저장');

        // 7. 프로필 이미지 클릭
        console.log('🖼️ 7. 프로필 이미지 클릭 시도...');
        
        const profileImage = await page.$('.profile-image-clickable');
        if (profileImage) {
            console.log('✅ 프로필 이미지 발견, 클릭 진행...');
            await profileImage.click();
            await page.waitForTimeout(3000);
            
            // 8. 클릭 후 상태 확인
            const modalState = await page.evaluate(() => {
                const fallbackModal = document.getElementById('fallbackProfileModal');
                const originalModal = document.getElementById('profileImageModal');
                
                let activeModal = null;
                let modalType = 'none';
                
                if (fallbackModal && window.getComputedStyle(fallbackModal).display !== 'none') {
                    activeModal = fallbackModal;
                    modalType = 'fallback';
                } else if (originalModal && window.getComputedStyle(originalModal).display !== 'none') {
                    activeModal = originalModal;
                    modalType = 'original';
                }
                
                if (!activeModal) {
                    return { modalType: 'none', error: 'No modal found' };
                }
                
                const modalImage = activeModal.querySelector('img');
                const modalBody = activeModal.querySelector('.modal-body');
                
                return {
                    modalType: modalType,
                    hasImage: !!modalImage,
                    imageSrc: modalImage ? modalImage.src : null,
                    modalRect: activeModal.getBoundingClientRect(),
                    imageRect: modalImage ? modalImage.getBoundingClientRect() : null,
                    bodyStyles: modalBody ? {
                        textAlign: window.getComputedStyle(modalBody).textAlign,
                        display: window.getComputedStyle(modalBody).display,
                        justifyContent: window.getComputedStyle(modalBody).justifyContent,
                        alignItems: window.getComputedStyle(modalBody).alignItems,
                        padding: window.getComputedStyle(modalBody).padding
                    } : null,
                    imageStyles: modalImage ? {
                        display: window.getComputedStyle(modalImage).display,
                        margin: window.getComputedStyle(modalImage).margin,
                        objectFit: window.getComputedStyle(modalImage).objectFit,
                        maxWidth: window.getComputedStyle(modalImage).maxWidth,
                        maxHeight: window.getComputedStyle(modalImage).maxHeight
                    } : null
                };
            });
            
            console.log('📊 클릭 후 모달 상태:', modalState);
            
            // 9. 문제 있는 모달 스크린샷
            await page.screenshot({ 
                path: '/var/www/html/topmkt/real_admin_modal_issue_current.png',
                fullPage: true 
            });
            console.log('📸 현재 문제 상태 모달 스크린샷 저장');
            
            if (modalState.modalType !== 'none' && modalState.hasImage) {
                // 정렬 문제 진단
                const centerX = modalState.modalRect.width / 2;
                const imageCenterX = modalState.imageRect.left + (modalState.imageRect.width / 2) - modalState.modalRect.left;
                const alignmentOffset = Math.abs(centerX - imageCenterX);
                
                console.log(`🎯 정렬 분석:`);
                console.log(`  - 모달 중심: ${centerX}px`);
                console.log(`  - 이미지 중심: ${imageCenterX}px`);
                console.log(`  - 편차: ${alignmentOffset}px`);
                console.log(`  - Body 스타일: ${JSON.stringify(modalState.bodyStyles)}`);
                console.log(`  - 이미지 스타일: ${JSON.stringify(modalState.imageStyles)}`);
                
                if (alignmentOffset > 10) {
                    console.log('❌ 정렬 문제 확인! 이미지가 중앙에서 벗어남');
                    
                    // 10. 즉시 CSS 수정 적용
                    console.log('🔧 10. 즉시 CSS 수정 적용...');
                    
                    await page.evaluate(() => {
                        const modalBody = document.querySelector('#fallbackProfileModal .modal-body');
                        const modalImage = document.querySelector('#fallbackProfileModal img');
                        
                        if (modalBody) {
                            // 완벽한 중앙 정렬 CSS 적용
                            modalBody.style.display = 'flex';
                            modalBody.style.justifyContent = 'center';
                            modalBody.style.alignItems = 'center';
                            modalBody.style.textAlign = 'center';
                            modalBody.style.padding = '24px';
                            modalBody.style.minHeight = '200px';
                        }
                        
                        if (modalImage) {
                            modalImage.style.display = 'block';
                            modalImage.style.margin = '0 auto';
                            modalImage.style.objectFit = 'contain';
                            modalImage.style.maxWidth = '90vw';
                            modalImage.style.maxHeight = '80vh';
                            modalImage.style.width = 'auto';
                            modalImage.style.height = 'auto';
                        }
                        
                        console.log('✅ CSS 수정 적용 완료');
                    });
                    
                    await page.waitForTimeout(1000);
                    
                    // 11. 수정 후 스크린샷
                    await page.screenshot({ 
                        path: '/var/www/html/topmkt/real_admin_modal_fixed_now.png',
                        fullPage: true 
                    });
                    console.log('📸 CSS 수정 후 스크린샷 저장');
                    
                } else {
                    console.log('✅ 정렬이 정상적임');
                }
            }
            
        } else {
            console.log('❌ 프로필 이미지를 찾을 수 없음');
        }

        return {
            success: true,
            pageAnalysis: pageAnalysis
        };

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/real_admin_modal_error.png',
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
testDirectAdminModal().then(result => {
    if (result.success) {
        console.log('✅ 직접 관리자 모달 테스트 완료');
        console.log('📊 페이지 분석:', JSON.stringify(result.pageAnalysis, null, 2));
        process.exit(0);
    } else {
        console.log('❌ 테스트 실패:', result.error);
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});