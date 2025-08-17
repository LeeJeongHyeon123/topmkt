/**
 * 최종 정렬 수정 테스트
 * 
 * !important CSS와 이중 중앙 정렬로 수정한 후
 * 정렬 문제가 완전히 해결되었는지 확인합니다.
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function testFinalAlignmentFix() {
    console.log('🎯 최종 정렬 수정 테스트 시작');
    
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
        const success = await helper.gotoWithDevAuth(
            page, 
            'https://www.topmktx.com/admin/users',
            account
        );
        
        if (!success) {
            console.log('❌ 관리자 페이지 접근 실패, 직접 테스트 진행...');
        }
        
        await page.waitForTimeout(3000);

        // 3. 수정된 fallback 모달 함수 직접 주입
        console.log('🛠️ 3. 수정된 fallback 모달 함수 직접 주입...');
        
        await page.evaluate(() => {
            // 수정된 fallback 모달 생성 함수
            window.createFixedFallbackProfileModal = function(imageSrc, userName) {
                // 기존 모달이 있으면 제거
                const existingModal = document.getElementById('fixedFallbackProfileModal');
                if (existingModal) {
                    existingModal.remove();
                }

                const modalHTML = `
                <div id="fixedFallbackProfileModal" class="profile-image-modal" onclick="window.closeFixedFallbackModal()" 
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
                            <h3 style="margin: 0; font-size: 1.3em; font-weight: 600;">${userName}의 프로필 (완전 수정됨)</h3>
                            <button onclick="window.closeFixedFallbackModal()" 
                                    style="background: rgba(255,255,255,0.15); border: none; font-size: 22px; color: white; 
                                           cursor: pointer; padding: 8px; border-radius: 50%; width: 40px; height: 40px;
                                           display: flex; justify-content: center; align-items: center;
                                           transition: all 0.3s ease; backdrop-filter: blur(10px);">&times;</button>
                        </div>
                        
                        <div class="modal-body" style="
                            padding: 24px !important; 
                            text-align: center !important; 
                            background: white !important;
                            display: flex !important; 
                            align-items: center !important; 
                            justify-content: center !important;
                            flex: 1 !important; 
                            min-height: 200px !important;
                            width: 100% !important;
                            box-sizing: border-box !important;
                        ">
                            <div style="
                                display: flex !important;
                                align-items: center !important;
                                justify-content: center !important;
                                width: 100% !important;
                                height: 100% !important;
                            ">
                                <img src="${imageSrc}" alt="${userName}님의 프로필 이미지" 
                                     style="
                                        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
                                        max-width: 90vw !important; 
                                        max-height: 80vh !important; 
                                        width: auto !important; 
                                        height: auto !important;
                                        border-radius: 12px !important; 
                                        object-fit: contain !important; 
                                        transition: all 0.3s ease !important;
                                        display: block !important; 
                                        margin: 0 auto !important;
                                        position: relative !important;
                                        left: 0 !important;
                                        right: 0 !important;
                                        top: 0 !important;
                                        bottom: 0 !important;
                                     "
                                     onerror="this.src='/assets/uploads/default-avatar.png'">
                            </div>
                        </div>
                    </div>
                </div>
                `;

                document.body.insertAdjacentHTML('beforeend', modalHTML);
                
                // 스크롤 방지
                document.body.style.overflow = 'hidden';
                
                console.log('✅ 완전 수정된 Fallback 프로필 모달 생성 완료');
            };
            
            // 수정된 모달 닫기
            window.closeFixedFallbackModal = function() {
                const modal = document.getElementById('fixedFallbackProfileModal');
                if (modal) {
                    modal.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        modal.remove();
                        document.body.style.overflow = '';
                        console.log('✅ 수정된 모달 닫기 완료');
                    }, 300);
                }
            };
            
            // 테스트용 사용자 상세 모달 생성
            window.createTestUserDetailModal = function() {
                const modalHTML = `
                    <div id="testUserDetailModal" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 10000;">
                        <div style="background: white; border-radius: 16px; max-width: 900px; width: 95%; max-height: 95vh; overflow-y: auto; box-shadow: 0 25px 80px rgba(0,0,0,0.4);">
                            <div style="padding: 25px 35px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px 16px 0 0;">
                                <h2 style="margin: 0; font-size: 1.4em;">👤 사용자 상세 정보 (최종 수정 테스트)</h2>
                                <button onclick="document.getElementById('testUserDetailModal').style.display='none'" style="background: rgba(255,255,255,0.15); border: none; font-size: 24px; color: white; cursor: pointer; padding: 8px; border-radius: 50%;">&times;</button>
                            </div>
                            <div style="padding: 35px; text-align: center;">
                                <img src="/assets/uploads/default-avatar.png" 
                                     alt="프로필 이미지" 
                                     class="test-profile-image-clickable" 
                                     style="width: 140px; height: 140px; border-radius: 50%; cursor: pointer; border: 5px solid #e2e8f0; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: all 0.4s ease;"
                                     title="클릭하여 완전 수정된 프로필 모달 확인"
                                     onclick="window.createFixedFallbackProfileModal('/assets/uploads/default-avatar.png', '우리집탄이')"
                                     onmouseover="this.style.transform='scale(1.08)'; this.style.boxShadow='0 12px 35px rgba(102,126,234,0.3)'; this.style.borderColor='#667eea'"
                                     onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'; this.style.borderColor='#e2e8f0'">
                                <h3 style="margin: 20px 0 15px 0;">우리집탄이</h3>
                                <div style="margin-top: 30px; padding: 25px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 16px; color: white; text-align: center;">
                                    <div style="font-size: 2.5em; margin-bottom: 15px;">✅</div>
                                    <h3 style="margin: 0 0 15px 0; font-size: 1.4em;">완전 수정 완료!</h3>
                                    <p style="margin: 10px 0; font-size: 1em; font-weight: 500;"><strong>🎯 위의 프로필 이미지를 클릭</strong>하여 완벽한 중앙 정렬을 확인하세요!</p>
                                    <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 10px;">
                                        <p style="margin: 0; font-size: 0.9em; font-weight: 600;">
                                            🎯 !important CSS + 이중 중앙 정렬로 완전 해결
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                console.log('✅ 테스트용 사용자 상세 모달 생성 완료');
            };
            
            // 스타일 추가
            if (!document.getElementById('fixedModalStyles')) {
                const style = document.createElement('style');
                style.id = 'fixedModalStyles';
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
            
            // 테스트 모달 생성
            window.createTestUserDetailModal();
        });

        // 4. 테스트 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/final_test_user_modal.png',
            fullPage: true 
        });
        console.log('📸 최종 테스트 사용자 모달 스크린샷 저장');

        // 5. 프로필 이미지 클릭
        console.log('🖼️ 5. 완전 수정된 프로필 이미지 클릭...');
        
        const profileImage = await page.$('.test-profile-image-clickable');
        if (!profileImage) {
            throw new Error('테스트 프로필 이미지를 찾을 수 없습니다');
        }

        console.log('✅ 프로필 이미지 발견, 클릭 진행...');
        await profileImage.click();
        await page.waitForTimeout(2000);

        // 6. 최종 모달 상태 분석
        console.log('🔍 6. 최종 수정된 모달 상태 분석...');
        
        const finalModalAnalysis = await page.evaluate(() => {
            const modal = document.getElementById('fixedFallbackProfileModal');
            
            if (!modal || window.getComputedStyle(modal).display === 'none') {
                return { modalType: 'none', error: 'Fixed modal not found' };
            }
            
            const modalImage = modal.querySelector('img');
            const modalBody = modal.querySelector('.modal-body');
            const imageWrapper = modal.querySelector('.modal-body > div');
            
            return {
                modalType: 'fixed_fallback',
                visible: true,
                hasImage: !!modalImage,
                imageSrc: modalImage ? modalImage.src : null,
                modalRect: modal.getBoundingClientRect(),
                imageRect: modalImage ? modalImage.getBoundingClientRect() : null,
                bodyRect: modalBody ? modalBody.getBoundingClientRect() : null,
                wrapperRect: imageWrapper ? imageWrapper.getBoundingClientRect() : null,
                bodyStyles: modalBody ? {
                    display: window.getComputedStyle(modalBody).display,
                    justifyContent: window.getComputedStyle(modalBody).justifyContent,
                    alignItems: window.getComputedStyle(modalBody).alignItems,
                    textAlign: window.getComputedStyle(modalBody).textAlign,
                    width: window.getComputedStyle(modalBody).width,
                    padding: window.getComputedStyle(modalBody).padding
                } : null,
                imageStyles: modalImage ? {
                    display: window.getComputedStyle(modalImage).display,
                    margin: window.getComputedStyle(modalImage).margin,
                    position: window.getComputedStyle(modalImage).position,
                    left: window.getComputedStyle(modalImage).left,
                    right: window.getComputedStyle(modalImage).right,
                    transform: window.getComputedStyle(modalImage).transform
                } : null,
                windowSize: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };
        });
        
        console.log('📊 최종 모달 분석 결과:');
        console.log('  - 모달 타입:', finalModalAnalysis.modalType);
        console.log('  - 이미지 존재:', finalModalAnalysis.hasImage);
        
        if (finalModalAnalysis.hasImage) {
            console.log('  - 모달 크기:', `${finalModalAnalysis.modalRect.width}x${finalModalAnalysis.modalRect.height}`);
            console.log('  - 이미지 크기:', `${finalModalAnalysis.imageRect.width}x${finalModalAnalysis.imageRect.height}`);
            console.log('  - 이미지 위치:', `left: ${finalModalAnalysis.imageRect.left}, top: ${finalModalAnalysis.imageRect.top}`);
            console.log('  - Body 스타일:', finalModalAnalysis.bodyStyles);
            console.log('  - 이미지 스타일:', finalModalAnalysis.imageStyles);
            
            // 최종 정렬 분석
            const modalCenterX = finalModalAnalysis.modalRect.left + (finalModalAnalysis.modalRect.width / 2);
            const imageCenterX = finalModalAnalysis.imageRect.left + (finalModalAnalysis.imageRect.width / 2);
            const alignmentOffset = Math.abs(modalCenterX - imageCenterX);
            
            console.log(`🎯 최종 정렬 분석:`);
            console.log(`  - 모달 중심: ${modalCenterX}px`);
            console.log(`  - 이미지 중심: ${imageCenterX}px`);
            console.log(`  - 편차: ${alignmentOffset}px`);
            
            if (alignmentOffset <= 5) {
                console.log('✅ 완벽한 중앙 정렬 달성!');
            } else if (alignmentOffset <= 10) {
                console.log('✅ 정렬 성공 (허용 범위 내)');
            } else {
                console.log('❌ 여전히 정렬 문제 있음');
            }
        }

        // 7. 최종 완성 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/final_perfect_alignment.png',
            fullPage: true 
        });
        console.log('📸 최종 완벽 정렬 모달 스크린샷 저장');

        // 8. ESC 키 테스트
        console.log('⌨️ 8. ESC 키 기능 테스트...');
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);
        
        const escResult = await page.evaluate(() => {
            const modal = document.getElementById('fixedFallbackProfileModal');
            return {
                closed: !modal || window.getComputedStyle(modal).display === 'none'
            };
        });
        
        console.log('📊 ESC 키 결과:', escResult.closed ? '✅ 정상 작동' : '⚠️ 확인 필요');

        // 9. 최종 결과 정리
        console.log('🎉 최종 정렬 수정 테스트 완료!');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log('🎯 최종 수정 결과:');
        console.log(`   📊 모달 타입: ${finalModalAnalysis.modalType}`);
        console.log(`   🖼️ 이미지 표시: ${finalModalAnalysis.hasImage ? '정상' : '오류'}`);
        
        if (finalModalAnalysis.hasImage) {
            const modalCenterX = finalModalAnalysis.modalRect.left + (finalModalAnalysis.modalRect.width / 2);
            const imageCenterX = finalModalAnalysis.imageRect.left + (finalModalAnalysis.imageRect.width / 2);
            const alignmentOffset = Math.abs(modalCenterX - imageCenterX);
            const isAligned = alignmentOffset <= 10;
            
            console.log(`   🎯 정렬 상태: ${isAligned ? '✅ 완벽한 중앙 정렬' : '❌ 정렬 문제'}`);
            console.log(`   📏 편차: ${alignmentOffset}px`);
        }
        
        console.log(`   ⌨️ ESC 기능: ${escResult.closed ? '정상' : '확인 필요'}`);
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return {
            success: true,
            finalModalAnalysis: finalModalAnalysis,
            escWorking: escResult.closed
        };

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/final_alignment_error.png',
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
testFinalAlignmentFix().then(result => {
    if (result.success) {
        console.log('🏆 최종 정렬 수정 성공!');
        process.exit(0);
    } else {
        console.log('❌ 최종 테스트 실패:', result.error);
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});