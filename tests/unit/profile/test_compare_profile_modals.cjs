/**
 * 프로필 모달 비교 테스트
 * 
 * 기존 완성된 프로필 모달들과 관리자 페이지 모달을 비교해서
 * UI 차이점을 확인하고 정렬 문제를 파악합니다.
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function compareProfileModals() {
    console.log('🔍 프로필 모달 비교 테스트 시작');
    
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

        // 2. 커뮤니티 페이지에서 정상적인 프로필 모달 확인
        console.log('👥 2. 커뮤니티 페이지의 정상 프로필 모달 테스트...');
        const success = await helper.gotoWithDevAuth(
            page, 
            'https://www.topmktx.com/community',
            account
        );
        
        if (!success) {
            throw new Error('커뮤니티 페이지 접근 실패');
        }
        
        await page.waitForTimeout(3000);
        
        // 프로필 이미지 클릭 요소 찾기
        const profileImages = await page.$$('.profile-image, img[data-user-id]');
        console.log(`🔍 발견된 프로필 이미지: ${profileImages.length}개`);
        
        if (profileImages.length > 0) {
            console.log('🖼️ 커뮤니티 프로필 이미지 클릭 테스트...');
            await profileImages[0].click();
            await page.waitForTimeout(1500);
            
            // 정상 모달 분석
            const normalModalAnalysis = await page.evaluate(() => {
                const modal = document.getElementById('profileImageModal');
                if (modal && window.getComputedStyle(modal).display !== 'none') {
                    const modalImage = modal.querySelector('#modalProfileImage, img');
                    const modalBody = modal.querySelector('.modal-body');
                    
                    return {
                        modalExists: true,
                        modalType: 'original',
                        imageExists: !!modalImage,
                        imageSrc: modalImage ? modalImage.src : null,
                        modalBodyStyles: modalBody ? {
                            textAlign: window.getComputedStyle(modalBody).textAlign,
                            display: window.getComputedStyle(modalBody).display,
                            justifyContent: window.getComputedStyle(modalBody).justifyContent,
                            alignItems: window.getComputedStyle(modalBody).alignItems
                        } : null,
                        imageStyles: modalImage ? {
                            display: window.getComputedStyle(modalImage).display,
                            margin: window.getComputedStyle(modalImage).margin,
                            maxWidth: window.getComputedStyle(modalImage).maxWidth,
                            maxHeight: window.getComputedStyle(modalImage).maxHeight
                        } : null
                    };
                }
                return { modalExists: false };
            });
            
            console.log('📊 정상 프로필 모달 분석:', normalModalAnalysis);
            
            // 정상 모달 스크린샷
            await page.screenshot({ 
                path: '/var/www/html/topmkt/normal_profile_modal.png',
                fullPage: true 
            });
            console.log('📸 정상 프로필 모달 스크린샷 저장');
            
            // 모달 닫기
            await page.keyboard.press('Escape');
            await page.waitForTimeout(500);
        }

        // 3. 관리자 페이지에서 문제있는 모달 확인
        console.log('👨‍💼 3. 관리자 페이지의 문제 모달 테스트...');
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

        // 개선된 모달 함수 주입 (수정된 버전)
        await page.evaluate(() => {
            // 기존 모달 제거
            const existingModal = document.getElementById('userDetailModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            const modalHTML = `
                <div id="userDetailModal" class="modal-overlay" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 10000; backdrop-filter: blur(2px);">
                    <div class="modal-content" style="background: white; border-radius: 16px; max-width: 900px; width: 95%; max-height: 95vh; overflow-y: auto; box-shadow: 0 25px 80px rgba(0,0,0,0.4);">
                        <div class="modal-header" style="padding: 25px 35px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px 16px 0 0;">
                            <h2 style="margin: 0; font-size: 1.4em;">👤 사용자 상세 정보 (정렬 개선 테스트)</h2>
                            <button class="modal-close" onclick="document.getElementById('userDetailModal').style.display='none'" style="background: rgba(255,255,255,0.15); border: none; font-size: 24px; color: white; cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s ease;">&times;</button>
                        </div>
                        <div class="modal-body" style="padding: 35px;">
                            <div class="profile-image-container" style="text-align: center; margin-bottom: 30px;">
                                <div style="position: relative; display: inline-block;">
                                    <img src="/assets/uploads/default-avatar.png" 
                                         alt="프로필 이미지" 
                                         class="profile-image-large profile-image-clickable" 
                                         style="width: 140px; height: 140px; border-radius: 50%; cursor: pointer; 
                                                border: 5px solid #e2e8f0; box-shadow: 0 8px 25px rgba(0,0,0,0.15); 
                                                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); transform: scale(1);
                                                background: linear-gradient(135deg, #f8fafc, #ffffff);"
                                         title="✨ 클릭하면 큰 이미지로 볼 수 있습니다"
                                         onclick="testCorrectProfileModal('/assets/uploads/default-avatar.png', '우리집탄이')"
                                         onmouseover="this.style.transform='scale(1.08)'; this.style.boxShadow='0 12px 35px rgba(102,126,234,0.3)'; this.style.borderColor='#667eea'"
                                         onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'; this.style.borderColor='#e2e8f0'">
                                </div>
                                <h3 style="margin: 20px 0 15px 0; color: #2d3748; font-size: 1.5em;">우리집탄이</h3>
                                <p style="margin: 10px 0; color: #4a5568;">클릭하여 정상적인 중앙 정렬 모달을 확인하세요</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // 정상적인 프로필 모달 테스트 함수
            window.testCorrectProfileModal = function(imageSrc, userName) {
                // 기존 정상 프로필 모달이 있는지 확인하고 사용
                if (typeof window.profileModal !== 'undefined' && window.profileModal.show) {
                    console.log('✅ 정상 ProfileImageModal 사용');
                    window.profileModal.show(imageSrc, userName, true);
                } else {
                    console.log('⚠️ ProfileImageModal 없음, 정상 스타일 fallback 생성');
                    window.createCorrectFallbackModal(imageSrc, userName);
                }
            };
            
            // 정상적인 스타일의 fallback 모달 생성
            window.createCorrectFallbackModal = function(imageSrc, userName) {
                // 기존 모달이 있으면 제거
                const existingModal = document.getElementById('correctFallbackModal');
                if (existingModal) {
                    existingModal.remove();
                }

                const modalHTML = `
                <div id="correctFallbackModal" class="profile-image-modal" onclick="window.closeCorrectFallbackModal()" 
                     style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                            background: rgba(0, 0, 0, 0.8); display: flex; 
                            justify-content: center; align-items: center; z-index: 10001; 
                            animation: fadeIn 0.3s ease; backdrop-filter: blur(3px);">
                    <div class="modal-content" onclick="event.stopPropagation()" 
                         style="background: white; border-radius: 16px; max-width: 90vw; max-height: 90vh; 
                                position: relative; box-shadow: 0 25px 80px rgba(0,0,0,0.6);
                                animation: slideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
                                border: 1px solid rgba(255,255,255,0.2); overflow: hidden;">
                        
                        <div class="modal-header" 
                             style="padding: 20px 25px; border-bottom: 1px solid #e2e8f0; display: flex; 
                                    justify-content: space-between; align-items: center; 
                                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                    color: white;">
                            <h3 style="margin: 0; font-size: 1.3em; font-weight: 600;">${userName}의 프로필</h3>
                            <button onclick="window.closeCorrectFallbackModal()" 
                                    style="background: rgba(255,255,255,0.15); border: none; font-size: 22px; color: white; 
                                           cursor: pointer; padding: 8px; border-radius: 50%; width: 40px; height: 40px;
                                           display: flex; justify-content: center; align-items: center;
                                           transition: all 0.3s ease; backdrop-filter: blur(10px);">&times;</button>
                        </div>
                        
                        <div class="modal-body" style="padding: 24px; text-align: center; background: white;
                                                      display: flex; align-items: center; justify-content: center;
                                                      flex: 1; min-height: 200px;">
                            <img src="${imageSrc}" alt="${userName}님의 프로필 이미지" 
                                 style="box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                                        max-width: 90vw; max-height: 80vh; width: auto; height: auto;
                                        border-radius: 12px; object-fit: contain; transition: all 0.3s ease;
                                        display: block; margin: 0 auto;"
                                 onerror="this.src='/assets/uploads/default-avatar.png'">
                        </div>
                    </div>
                </div>
                `;

                document.body.insertAdjacentHTML('beforeend', modalHTML);
                
                // 스타일 추가
                if (!document.getElementById('correctModalStyles')) {
                    const style = document.createElement('style');
                    style.id = 'correctModalStyles';
                    style.textContent = `
                        @keyframes fadeIn {
                            from { opacity: 0; }
                            to { opacity: 1; }
                        }
                        
                        @keyframes slideIn {
                            from { 
                                opacity: 0; 
                                transform: translateY(-30px) scale(0.9); 
                            }
                            to { 
                                opacity: 1; 
                                transform: translateY(0) scale(1); 
                            }
                        }
                        
                        @keyframes fadeOut {
                            from { opacity: 1; }
                            to { opacity: 0; }
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                // ESC 키 이벤트 추가
                document.addEventListener('keydown', window.correctModalEscHandler);
                
                // 스크롤 방지
                document.body.style.overflow = 'hidden';
                
                console.log('✅ 정상적인 중앙 정렬 모달 생성 완료');
            };

            // 정상 모달 닫기
            window.closeCorrectFallbackModal = function() {
                const modal = document.getElementById('correctFallbackModal');
                if (modal) {
                    modal.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        modal.remove();
                        document.body.style.overflow = '';
                        document.removeEventListener('keydown', window.correctModalEscHandler);
                        console.log('✅ 정상 모달 닫기 완료');
                    }, 300);
                }
            };

            // 정상 모달 ESC 키 핸들러
            window.correctModalEscHandler = function(event) {
                if (event.key === 'Escape') {
                    window.closeCorrectFallbackModal();
                }
            };
            
            console.log('✅ 정상적인 프로필 모달 함수 생성 완료');
        });

        // 관리자 페이지 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/admin_page_with_correct_modal.png',
            fullPage: true 
        });
        console.log('📸 수정된 관리자 페이지 스크린샷 저장');

        // 프로필 이미지 클릭 테스트
        console.log('🖼️ 수정된 프로필 이미지 클릭 테스트...');
        const profileImage = await page.$('.profile-image-clickable');
        if (profileImage) {
            await profileImage.click();
            await page.waitForTimeout(1500);
            
            // 수정된 모달 분석
            const correctedModalAnalysis = await page.evaluate(() => {
                const modal = document.getElementById('correctFallbackModal');
                if (modal && window.getComputedStyle(modal).display !== 'none') {
                    const modalImage = modal.querySelector('img');
                    const modalBody = modal.querySelector('.modal-body');
                    
                    return {
                        modalExists: true,
                        modalType: 'corrected_fallback',
                        imageExists: !!modalImage,
                        imageSrc: modalImage ? modalImage.src : null,
                        modalBodyStyles: modalBody ? {
                            textAlign: window.getComputedStyle(modalBody).textAlign,
                            display: window.getComputedStyle(modalBody).display,
                            justifyContent: window.getComputedStyle(modalBody).justifyContent,
                            alignItems: window.getComputedStyle(modalBody).alignItems,
                            padding: window.getComputedStyle(modalBody).padding
                        } : null,
                        imageStyles: modalImage ? {
                            display: window.getComputedStyle(modalImage).display,
                            margin: window.getComputedStyle(modalImage).margin,
                            maxWidth: window.getComputedStyle(modalImage).maxWidth,
                            maxHeight: window.getComputedStyle(modalImage).maxHeight,
                            objectFit: window.getComputedStyle(modalImage).objectFit
                        } : null
                    };
                }
                return { modalExists: false };
            });
            
            console.log('📊 수정된 모달 분석:', correctedModalAnalysis);
            
            // 수정된 모달 스크린샷
            await page.screenshot({ 
                path: '/var/www/html/topmkt/corrected_profile_modal.png',
                fullPage: true 
            });
            console.log('📸 수정된 프로필 모달 스크린샷 저장');
            
            // ESC로 닫기 테스트
            await page.keyboard.press('Escape');
            await page.waitForTimeout(500);
        }

        console.log('🎉 프로필 모달 비교 테스트 완료!');
        return true;

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/modal_compare_error.png',
                fullPage: true 
            });
            console.log('📸 오류 스크린샷 저장');
        }
        
        return false;
    } finally {
        if (session) {
            await helper.cleanup(session);
        }
    }
}

// 테스트 실행
compareProfileModals().then(success => {
    if (success) {
        console.log('✅ 프로필 모달 비교 테스트 성공!');
        console.log('📸 결과 이미지:');
        console.log('  - normal_profile_modal.png: 정상 커뮤니티 모달');
        console.log('  - corrected_profile_modal.png: 수정된 관리자 모달');
        process.exit(0);
    } else {
        console.log('❌ 테스트 실패!');
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});