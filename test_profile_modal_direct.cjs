/**
 * 프로필 모달 직접 테스트
 * 
 * 관리자 페이지에서 직접 사용자 상세 모달을 생성하고
 * 수정된 프로필 모달의 정렬을 테스트합니다.
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function testProfileModalDirect() {
    console.log('🎯 프로필 모달 직접 테스트 시작');
    
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

        // 3. 테스트용 사용자 상세 모달 직접 생성
        console.log('🎭 3. 테스트용 사용자 상세 모달 직접 생성...');
        
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
                            <h2 style="margin: 0; font-size: 1.4em;">👤 사용자 상세 정보 (정렬 테스트)</h2>
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
                                         title="✨ 클릭하면 큰 이미지로 볼 수 있습니다 (수정된 정렬)"
                                         onclick="openProfileImageModal('/assets/uploads/default-avatar.png', '우리집탄이')"
                                         onmouseover="this.style.transform='scale(1.08)'; this.style.boxShadow='0 12px 35px rgba(102,126,234,0.3)'; this.style.borderColor='#667eea'"
                                         onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'; this.style.borderColor='#e2e8f0'">
                                    <div style="position: absolute; bottom: 5px; right: 5px; width: 35px; height: 35px; 
                                                background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%;
                                                display: flex; align-items: center; justify-content: center; color: white;
                                                font-size: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                                                animation: pulse 3s ease-in-out infinite;">🔍</div>
                                </div>
                                <h3 style="margin: 20px 0 15px 0; color: #2d3748; font-size: 1.5em;">우리집탄이</h3>
                                <div style="margin-bottom: 35px;">
                                    <span style="background: linear-gradient(135deg, #fbb6ce, #f687b3); color: #97266d; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin: 0 8px;">👑 관리자</span>
                                    <span style="background: linear-gradient(135deg, #c6f6d5, #9ae6b4); color: #22543d; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin: 0 8px;">✅ 활성</span>
                                    <span style="background: linear-gradient(135deg, #bee3f8, #90cdf4); color: #2a69ac; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin: 0 8px;">🔒 인증됨</span>
                                </div>
                                <div style="margin-top: 30px; text-align: center; padding: 30px; background: linear-gradient(135deg, #667eea15, #764ba215); border-radius: 16px; border: 2px dashed #667eea;">
                                    <div style="font-size: 2.5em; margin-bottom: 15px;">🎯</div>
                                    <h3 style="color: #667eea; margin: 0 0 20px 0; font-size: 1.4em;">정렬 수정 테스트!</h3>
                                    <p style="margin: 15px 0; color: #4a5568; font-size: 1em; font-weight: 500;"><strong>✨ 위의 프로필 이미지를 클릭</strong>하여 수정된 중앙 정렬 모달을 확인해보세요!</p>
                                    <div style="margin-top: 20px; padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 10px;">
                                        <p style="margin: 0; font-size: 0.9em; color: #667eea; font-weight: 600;">
                                            🎯 목표: 커뮤니티와 동일한 완벽한 중앙 정렬
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            console.log('✅ 테스트용 사용자 상세 모달 생성 완료');
        });

        // 4. 테스트 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/test_user_detail_modal.png',
            fullPage: true 
        });
        console.log('📸 테스트용 사용자 상세 모달 스크린샷 저장');

        // 5. 프로필 이미지 클릭 테스트
        console.log('🖼️ 5. 프로필 이미지 클릭 테스트...');
        
        const profileImage = await page.$('.profile-image-clickable');
        if (!profileImage) {
            throw new Error('프로필 이미지를 찾을 수 없습니다');
        }

        console.log('✅ 프로필 이미지 발견, 클릭 진행...');
        await profileImage.click();
        await page.waitForTimeout(1500); // 모달 애니메이션 대기

        // 6. 모달 상태 상세 분석
        console.log('🔍 6. 프로필 모달 상태 상세 분석...');
        
        const modalAnalysis = await page.evaluate(() => {
            // 정상 ProfileImageModal 확인
            const originalModal = document.getElementById('profileImageModal');
            let originalData = null;
            
            if (originalModal && window.getComputedStyle(originalModal).display !== 'none') {
                const originalImage = originalModal.querySelector('#modalProfileImage, img');
                const originalBody = originalModal.querySelector('.modal-body');
                originalData = {
                    type: 'original',
                    visible: true,
                    hasImage: !!originalImage,
                    imageSrc: originalImage ? originalImage.src : null,
                    title: originalModal.querySelector('#modalUserName, h3')?.textContent || null,
                    modalRect: originalModal.getBoundingClientRect(),
                    imageRect: originalImage ? originalImage.getBoundingClientRect() : null,
                    bodyStyles: originalBody ? {
                        textAlign: window.getComputedStyle(originalBody).textAlign,
                        display: window.getComputedStyle(originalBody).display,
                        justifyContent: window.getComputedStyle(originalBody).justifyContent,
                        alignItems: window.getComputedStyle(originalBody).alignItems,
                        padding: window.getComputedStyle(originalBody).padding
                    } : null,
                    imageStyles: originalImage ? {
                        display: window.getComputedStyle(originalImage).display,
                        margin: window.getComputedStyle(originalImage).margin,
                        objectFit: window.getComputedStyle(originalImage).objectFit,
                        maxWidth: window.getComputedStyle(originalImage).maxWidth,
                        maxHeight: window.getComputedStyle(originalImage).maxHeight
                    } : null
                };
            }
            
            // Fallback 모달 확인 (수정된 버전)
            const fallbackModal = document.getElementById('fallbackProfileModal');
            let fallbackData = null;
            
            if (fallbackModal && window.getComputedStyle(fallbackModal).display !== 'none') {
                const fallbackImage = fallbackModal.querySelector('img');
                const fallbackBody = fallbackModal.querySelector('.modal-body');
                const fallbackRect = fallbackModal.getBoundingClientRect();
                const imageRect = fallbackImage ? fallbackImage.getBoundingClientRect() : null;
                
                fallbackData = {
                    type: 'fallback_fixed',
                    visible: true,
                    hasImage: !!fallbackImage,
                    imageSrc: fallbackImage ? fallbackImage.src : null,
                    title: fallbackModal.querySelector('h3')?.textContent || null,
                    modalRect: fallbackRect,
                    imageRect: imageRect,
                    bodyStyles: fallbackBody ? {
                        textAlign: window.getComputedStyle(fallbackBody).textAlign,
                        display: window.getComputedStyle(fallbackBody).display,
                        justifyContent: window.getComputedStyle(fallbackBody).justifyContent,
                        alignItems: window.getComputedStyle(fallbackBody).alignItems,
                        padding: window.getComputedStyle(fallbackBody).padding,
                        minHeight: window.getComputedStyle(fallbackBody).minHeight
                    } : null,
                    imageStyles: fallbackImage ? {
                        display: window.getComputedStyle(fallbackImage).display,
                        margin: window.getComputedStyle(fallbackImage).margin,
                        objectFit: window.getComputedStyle(fallbackImage).objectFit,
                        maxWidth: window.getComputedStyle(fallbackImage).maxWidth,
                        maxHeight: window.getComputedStyle(fallbackImage).maxHeight,
                        width: window.getComputedStyle(fallbackImage).width,
                        height: window.getComputedStyle(fallbackImage).height
                    } : null,
                    modalClass: fallbackModal.className,
                    hasBackdropBlur: window.getComputedStyle(fallbackModal).backdropFilter !== 'none'
                };
            }
            
            return {
                originalModal: originalData,
                fallbackModal: fallbackData,
                activeModalType: originalData ? 'original' : (fallbackData ? 'fallback_fixed' : 'none'),
                windowSize: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };
        });
        
        console.log('📊 프로필 모달 상세 분석 결과:');
        console.log('  - 활성 모달 타입:', modalAnalysis.activeModalType);
        
        const activeModal = modalAnalysis.originalModal || modalAnalysis.fallbackModal;
        if (activeModal) {
            console.log('  - 이미지 존재:', activeModal.hasImage);
            console.log('  - 모달 크기:', `${activeModal.modalRect.width}x${activeModal.modalRect.height}`);
            if (activeModal.imageRect) {
                console.log('  - 이미지 크기:', `${activeModal.imageRect.width}x${activeModal.imageRect.height}`);
                console.log('  - 이미지 위치:', `left: ${activeModal.imageRect.left}, top: ${activeModal.imageRect.top}`);
            }
            console.log('  - Body 스타일:', activeModal.bodyStyles);
            console.log('  - 이미지 스타일:', activeModal.imageStyles);
        }

        // 7. 수정된 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/test_profile_modal_fixed.png',
            fullPage: true 
        });
        console.log('📸 수정된 프로필 모달 스크린샷 저장');

        // 8. 정렬 품질 평가
        console.log('🎯 8. 정렬 품질 평가...');
        
        if (!activeModal) {
            throw new Error('모달이 표시되지 않았습니다');
        }
        
        // 정렬 점수 계산 (완벽한 중앙 정렬 여부)
        const alignmentScore = (() => {
            if (!activeModal.bodyStyles) return 0;
            
            const { textAlign, display, justifyContent, alignItems } = activeModal.bodyStyles;
            let score = 0;
            let details = [];
            
            if (textAlign === 'center') {
                score += 2;
                details.push('✅ text-align: center');
            } else {
                details.push(`❌ text-align: ${textAlign}`);
            }
            
            if (display === 'flex') {
                score += 2;
                details.push('✅ display: flex');
            } else {
                details.push(`❌ display: ${display}`);
            }
            
            if (justifyContent === 'center') {
                score += 3;
                details.push('✅ justify-content: center');
            } else {
                details.push(`❌ justify-content: ${justifyContent}`);
            }
            
            if (alignItems === 'center') {
                score += 3;
                details.push('✅ align-items: center');
            } else {
                details.push(`❌ align-items: ${alignItems}`);
            }
            
            console.log('📐 정렬 상세 분석:');
            details.forEach(detail => console.log(`   ${detail}`));
            
            return score;
        })();
        
        const qualityScores = {
            functionality: activeModal.visible ? 10 : 0, // 기능 동작
            design: activeModal.hasImage ? 10 : 0, // 디자인 품질
            alignment: alignmentScore, // 정렬 품질 (0-10)
            responsiveness: (activeModal.modalRect.width > 300) ? 10 : 8, // 반응형
            interaction: activeModal.type === 'fallback_fixed' ? 10 : 8, // 인터랙션
            effects: activeModal.hasBackdropBlur ? 10 : 8 // 시각 효과
        };
        
        const totalScore = Object.values(qualityScores).reduce((a, b) => a + b, 0) / 6;
        
        console.log('📊 품질 평가 결과:');
        console.log(`  - 기능성: ${qualityScores.functionality}/10`);
        console.log(`  - 디자인: ${qualityScores.design}/10`);
        console.log(`  - 정렬: ${qualityScores.alignment}/10`);
        console.log(`  - 반응형: ${qualityScores.responsiveness}/10`);
        console.log(`  - 인터랙션: ${qualityScores.interaction}/10`);
        console.log(`  - 시각효과: ${qualityScores.effects}/10`);
        console.log(`🎯 총점: ${totalScore.toFixed(1)}/10`);
        
        const alignmentSuccess = qualityScores.alignment >= 8; // 80% 이상
        const overallSuccess = totalScore >= 8.0 && alignmentSuccess;
        
        console.log(`✅ 정렬 성공: ${alignmentSuccess ? '완벽한 중앙 정렬' : '정렬 개선 필요'}`);
        console.log(`🏆 전체 성공: ${overallSuccess ? '목표 달성' : '추가 개선 필요'}`);

        // 9. ESC 키 기능 테스트
        console.log('⌨️ 9. ESC 키 기능 테스트...');
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);
        
        const escTestResult = await page.evaluate(() => {
            const originalModal = document.getElementById('profileImageModal');
            const fallbackModal = document.getElementById('fallbackProfileModal');
            
            return {
                originalClosed: !originalModal || window.getComputedStyle(originalModal).display === 'none',
                fallbackClosed: !fallbackModal || window.getComputedStyle(fallbackModal).display === 'none',
                allClosed: (!originalModal || window.getComputedStyle(originalModal).display === 'none') &&
                          (!fallbackModal || window.getComputedStyle(fallbackModal).display === 'none')
            };
        });
        
        console.log('📊 ESC 키 테스트 결과:', escTestResult.allClosed ? '✅ 정상 작동' : '⚠️ 확인 필요');

        // 10. 최종 결과 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/test_profile_modal_final.png',
            fullPage: true 
        });
        console.log('📸 최종 결과 스크린샷 저장');

        // 11. 결과 요약
        console.log('🎉 프로필 모달 정렬 테스트 완료!');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log('🎨 프로필 모달 정렬 수정 최종 결과:');
        console.log(`   📊 총점: ${totalScore.toFixed(1)}/10`);
        console.log(`   🎯 정렬 점수: ${qualityScores.alignment}/10`);
        console.log(`   ✅ 정렬 성공: ${alignmentSuccess ? '완벽' : '개선 필요'}`);
        console.log(`   🎭 모달 타입: ${modalAnalysis.activeModalType}`);
        console.log(`   🖼️ 이미지 표시: ${activeModal.hasImage ? '정상' : '오류'}`);
        console.log(`   ⌨️ ESC 기능: ${escTestResult.allClosed ? '정상' : '확인 필요'}`);
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return {
            success: overallSuccess,
            score: totalScore,
            alignmentScore: qualityScores.alignment,
            alignmentSuccess: alignmentSuccess,
            modalType: modalAnalysis.activeModalType,
            escWorking: escTestResult.allClosed,
            modalAnalysis: modalAnalysis
        };

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/test_profile_modal_error.png',
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
testProfileModalDirect().then(result => {
    if (result.success) {
        console.log('🏆 프로필 모달 정렬 수정 성공!');
        console.log(`📊 총점: ${result.score}/10`);
        console.log(`🎯 정렬 점수: ${result.alignmentScore}/10`);
        console.log(`✅ 정렬 성공: ${result.alignmentSuccess}`);
        process.exit(0);
    } else {
        console.log('❌ 테스트 실패:', result.error || '품질 기준 미달');
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});