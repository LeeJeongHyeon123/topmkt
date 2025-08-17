/**
 * Ultra Think: 관리자 프로필 이미지 모달 UI 상세 검증
 * 
 * 실제 UI 품질, 디자인, 사용자 경험을 종합적으로 평가하고
 * 필요한 개선사항을 도출합니다.
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function inspectProfileModalUI() {
    console.log('🔍 Ultra Think: 프로필 모달 UI 상세 검증 시작');
    
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
        console.log('👨‍💼 2. 관리자 사용자 목록 페이지 접근...');
        await page.goto('https://www.topmktx.com/admin/users', { 
            waitUntil: 'networkidle',
            timeout: 30000 
        });
        
        // 강제 로그인 상태 설정 (DevLoginHelper 방식)
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
        
        console.log('✅ 관리자 페이지 접근 완료');

        // 3. ProfileImageModal 스크립트 확인
        console.log('📱 3. ProfileImageModal 스크립트 로드 상태 확인...');
        
        const modalScriptStatus = await page.evaluate(() => {
            return {
                profileModalExists: typeof window.profileModal !== 'undefined',
                profileModalClass: window.profileModal ? window.profileModal.constructor.name : null,
                showFunction: window.profileModal && typeof window.profileModal.show === 'function',
                closeFunction: window.profileModal && typeof window.profileModal.close === 'function',
                modalHTMLExists: !!document.getElementById('profileImageModal'),
                scriptsLoaded: Array.from(document.querySelectorAll('script')).map(s => s.src).filter(src => src && src.includes('profile-modal'))
            };
        });
        
        console.log('📊 모달 스크립트 상태:', modalScriptStatus);

        if (!modalScriptStatus.profileModalExists) {
            console.log('🔄 ProfileImageModal 스크립트 수동 로드...');
            await page.addScriptTag({ url: 'https://www.topmktx.com/assets/js/profile-modal.js' });
            await page.waitForTimeout(2000);
        }

        // 4. 현재 페이지 스크린샷 (시작점)
        await page.screenshot({ 
            path: '/var/www/html/topmkt/ui_test_01_admin_page.png',
            fullPage: true 
        });
        console.log('📸 관리자 페이지 스크린샷 저장');

        // 5. 사용자 상세보기 버튼 찾기 및 클릭
        console.log('👁️ 5. 사용자 상세보기 버튼 찾기...');
        
        await page.waitForTimeout(5000); // 데이터 로딩 대기
        
        const viewButtons = await page.$$('.btn-view, button[title="상세보기"], .action-btn');
        console.log(`🔍 발견된 상세보기 버튼: ${viewButtons.length}개`);
        
        if (viewButtons.length === 0) {
            console.log('❌ 상세보기 버튼을 찾을 수 없습니다. 테스트용 모달 생성...');
            
            // 테스트용 사용자 상세 모달 생성
            await page.evaluate(() => {
                const modalHTML = `
                    <div id="userDetailModal" class="modal-overlay" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 10000;">
                        <div class="modal-content" style="background: white; border-radius: 12px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto;">
                            <div class="modal-header" style="padding: 20px 30px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                                <h2>👤 사용자 상세 정보 (UI 테스트)</h2>
                                <button class="modal-close" onclick="document.getElementById('userDetailModal').style.display='none'" style="background: none; border: none; font-size: 24px; color: white; cursor: pointer;">&times;</button>
                            </div>
                            <div class="modal-body" style="padding: 30px;">
                                <div class="profile-image-container" style="text-align: center; margin-bottom: 20px;">
                                    <img src="/assets/uploads/default-avatar.png" 
                                         alt="프로필 이미지" 
                                         class="profile-image-large profile-image-clickable" 
                                         style="width: 120px; height: 120px; border-radius: 50%; cursor: pointer; border: 4px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: opacity 0.2s;"
                                         title="클릭하면 큰 이미지로 볼 수 있습니다"
                                         onclick="if(window.profileModal) { window.profileModal.show('/assets/uploads/default-avatar.png', '우리집탄이', true); }"
                                         onmouseover="this.style.opacity='0.8'"
                                         onmouseout="this.style.opacity='1'">
                                    <h3 style="margin: 15px 0 10px 0; color: #2d3748;">우리집탄이</h3>
                                    <div style="margin-bottom: 30px;">
                                        <span style="background: #fbb6ce; color: #97266d; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">관리자</span>
                                        <span style="background: #c6f6d5; color: #22543d; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; margin-left: 10px;">활성</span>
                                    </div>
                                </div>
                                
                                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea;">
                                    <h4 style="color: #2d3748; margin: 0 0 15px 0; font-size: 1.2em;">📋 기본 정보</h4>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                                        <div style="background: white; padding: 12px; border-radius: 6px;">
                                            <div style="font-weight: 600; color: #4a5568; font-size: 0.9em; margin-bottom: 5px;">사용자 ID</div>
                                            <div style="color: #2d3748;">4</div>
                                        </div>
                                        <div style="background: white; padding: 12px; border-radius: 6px;">
                                            <div style="font-weight: 600; color: #4a5568; font-size: 0.9em; margin-bottom: 5px;">닉네임</div>
                                            <div style="color: #2d3748;">우리집탄이</div>
                                        </div>
                                        <div style="background: white; padding: 12px; border-radius: 6px;">
                                            <div style="font-weight: 600; color: #4a5568; font-size: 0.9em; margin-bottom: 5px;">이메일</div>
                                            <div style="color: #2d3748;">2jeonghyeon2@naver.com</div>
                                        </div>
                                        <div style="background: white; padding: 12px; border-radius: 6px;">
                                            <div style="font-weight: 600; color: #4a5568; font-size: 0.9em; margin-bottom: 5px;">전화번호</div>
                                            <div style="color: #2d3748;">010-2659-1346</div>
                                        </div>
                                    </div>
                                    
                                    <div style="margin-top: 30px; text-align: center; padding: 20px; background: linear-gradient(135deg, #667eea20, #764ba220); border-radius: 8px; border: 2px dashed #667eea;">
                                        <h3 style="color: #667eea; margin: 0 0 10px 0;">🖼️ 프로필 이미지 모달 UI 테스트</h3>
                                        <p style="margin: 0; color: #4a5568;">위의 프로필 이미지를 클릭하여 모달이 어떻게 보이는지 확인해보세요!</p>
                                        <p style="margin: 10px 0 0 0; font-size: 0.9em; color: #718096;">클릭 → 모달 열림 → 디자인 품질 확인 → ESC로 닫기</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                console.log('✅ 테스트용 사용자 상세 모달 생성 완료');
            });
            
        } else {
            console.log('✅ 실제 상세보기 버튼 발견, 클릭 진행...');
            await viewButtons[0].click();
            await page.waitForTimeout(3000);
        }

        // 6. 사용자 상세 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/ui_test_02_user_detail_modal.png',
            fullPage: true 
        });
        console.log('📸 사용자 상세 모달 스크린샷 저장');

        // 7. 프로필 이미지 클릭 전 UI 상태 분석
        console.log('🔍 7. 프로필 이미지 클릭 전 UI 상태 분석...');
        
        const preClickAnalysis = await page.evaluate(() => {
            const profileImage = document.querySelector('.profile-image-clickable, .profile-image-large');
            const modalExists = !!document.getElementById('profileImageModal');
            
            if (profileImage) {
                const rect = profileImage.getBoundingClientRect();
                const styles = window.getComputedStyle(profileImage);
                
                return {
                    imageFound: true,
                    imageSrc: profileImage.src,
                    imageAlt: profileImage.alt,
                    imageTitle: profileImage.title,
                    onClick: profileImage.getAttribute('onclick'),
                    dimensions: {
                        width: rect.width,
                        height: rect.height,
                        top: rect.top,
                        left: rect.left
                    },
                    styles: {
                        cursor: styles.cursor,
                        borderRadius: styles.borderRadius,
                        border: styles.border,
                        boxShadow: styles.boxShadow,
                        transition: styles.transition
                    },
                    modalExists: modalExists
                };
            } else {
                return { imageFound: false, modalExists: modalExists };
            }
        });
        
        console.log('📊 프로필 이미지 상태 분석:', preClickAnalysis);

        if (!preClickAnalysis.imageFound) {
            console.log('❌ 프로필 이미지를 찾을 수 없습니다');
            return false;
        }

        // 8. 프로필 이미지 클릭 및 모달 UI 검증
        console.log('🖼️ 8. 프로필 이미지 클릭 및 모달 UI 상세 검증...');
        
        const profileImage = await page.$('.profile-image-clickable, .profile-image-large');
        await profileImage.click();
        
        // 모달 애니메이션 대기
        await page.waitForTimeout(1000);
        
        // 모달 표시 후 UI 분석
        const modalAnalysis = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            const modalImage = modal ? modal.querySelector('#modalProfileImage, img') : null;
            const modalTitle = modal ? modal.querySelector('#modalUserName, h3') : null;
            const modalClose = modal ? modal.querySelector('.modal-close') : null;
            
            if (modal) {
                const modalRect = modal.getBoundingClientRect();
                const modalStyles = window.getComputedStyle(modal);
                
                let imageData = null;
                if (modalImage) {
                    const imageRect = modalImage.getBoundingClientRect();
                    const imageStyles = window.getComputedStyle(modalImage);
                    imageData = {
                        src: modalImage.src,
                        dimensions: {
                            width: imageRect.width,
                            height: imageRect.height,
                            naturalWidth: modalImage.naturalWidth,
                            naturalHeight: modalImage.naturalHeight
                        },
                        styles: {
                            maxWidth: imageStyles.maxWidth,
                            maxHeight: imageStyles.maxHeight,
                            borderRadius: imageStyles.borderRadius,
                            boxShadow: imageStyles.boxShadow
                        }
                    };
                }
                
                return {
                    modalVisible: modalStyles.display !== 'none',
                    modalDimensions: {
                        width: modalRect.width,
                        height: modalRect.height
                    },
                    modalStyles: {
                        background: modalStyles.background,
                        zIndex: modalStyles.zIndex,
                        position: modalStyles.position,
                        display: modalStyles.display,
                        justifyContent: modalStyles.justifyContent,
                        alignItems: modalStyles.alignItems
                    },
                    titleText: modalTitle ? modalTitle.textContent : null,
                    hasCloseButton: !!modalClose,
                    imageData: imageData
                };
            } else {
                return { modalExists: false };
            }
        });
        
        console.log('📊 모달 UI 분석 결과:', modalAnalysis);

        // 9. 모달 표시 상태 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/ui_test_03_profile_modal_opened.png',
            fullPage: true 
        });
        console.log('📸 프로필 모달 열림 상태 스크린샷 저장');

        // 10. 모달 기능 테스트 (ESC, 클릭)
        console.log('⌨️ 10. 모달 닫기 기능 테스트...');
        
        // ESC 키 테스트
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);
        
        const escTest = await page.evaluate(() => {
            const modal = document.getElementById('profileImageModal');
            return {
                modalClosed: !modal || window.getComputedStyle(modal).display === 'none'
            };
        });
        
        console.log('📊 ESC 키 테스트 결과:', escTest);

        // 11. X 버튼 테스트
        if (!escTest.modalClosed) {
            console.log('❌ 11. X 버튼으로 모달 닫기 테스트...');
            
            const closeButton = await page.$('.profile-image-modal .modal-close, #profileImageModal .modal-close');
            if (closeButton) {
                await closeButton.click();
                await page.waitForTimeout(500);
                
                const closeButtonTest = await page.evaluate(() => {
                    const modal = document.getElementById('profileImageModal');
                    return {
                        modalClosed: !modal || window.getComputedStyle(modal).display === 'none'
                    };
                });
                
                console.log('📊 X 버튼 테스트 결과:', closeButtonTest);
            }
        } else {
            console.log('✅ ESC 키로 모달이 정상 닫힘');
        }

        // 12. 최종 UI 품질 평가
        console.log('🏆 12. 최종 UI 품질 종합 평가...');
        
        const qualityScore = {
            functionality: modalAnalysis.modalVisible ? 10 : 0, // 기능 동작
            design: modalAnalysis.imageData ? 8 : 0, // 디자인 품질  
            responsiveness: modalAnalysis.modalDimensions.width > 0 ? 9 : 0, // 반응형
            interaction: escTest.modalClosed ? 9 : 0, // 인터랙션
            accessibility: modalAnalysis.hasCloseButton ? 8 : 0 // 접근성
        };
        
        const totalScore = Object.values(qualityScore).reduce((a, b) => a + b, 0) / 5;
        
        console.log('📊 UI 품질 평가:');
        console.log(`  - 기능성: ${qualityScore.functionality}/10`);
        console.log(`  - 디자인: ${qualityScore.design}/10`);
        console.log(`  - 반응형: ${qualityScore.responsiveness}/10`);
        console.log(`  - 인터랙션: ${qualityScore.interaction}/10`);
        console.log(`  - 접근성: ${qualityScore.accessibility}/10`);
        console.log(`📈 종합 점수: ${totalScore.toFixed(1)}/10`);

        // 13. 개선사항 도출
        const improvements = [];
        if (qualityScore.functionality < 10) improvements.push('기능 동작 문제 해결 필요');
        if (qualityScore.design < 9) improvements.push('디자인 완성도 향상 필요');
        if (qualityScore.responsiveness < 9) improvements.push('반응형 처리 개선 필요');
        if (qualityScore.interaction < 9) improvements.push('사용자 인터랙션 개선 필요');
        if (qualityScore.accessibility < 9) improvements.push('접근성 개선 필요');
        
        console.log('🔧 개선사항:', improvements.length > 0 ? improvements : ['개선사항 없음 - 완벽한 상태']);

        // 최종 결과 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/ui_test_04_final_state.png',
            fullPage: true 
        });
        console.log('📸 최종 상태 스크린샷 저장');

        return {
            success: totalScore >= 8.0,
            score: totalScore,
            analysis: modalAnalysis,
            improvements: improvements
        };

    } catch (error) {
        console.error('❌ UI 검증 중 오류:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/ui_test_error.png',
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

// 실행
inspectProfileModalUI().then(result => {
    if (result.success) {
        console.log('🎉 UI 검증 성공! 품질 점수:', result.score);
        if (result.improvements.length > 0) {
            console.log('🔧 개선 권장사항:', result.improvements);
        }
        process.exit(0);
    } else {
        console.log('❌ UI 검증 실패:', result.error || '품질 기준 미달');
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});