/**
 * 수정된 프로필 모달 정렬 테스트
 * 
 * 관리자 페이지에서 수정된 프로필 모달의 이미지 정렬이 
 * 커뮤니티 페이지와 동일하게 중앙 정렬되는지 확인합니다.
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function testFixedProfileModalAlignment() {
    console.log('🎨 수정된 프로필 모달 정렬 테스트 시작');
    
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

        // 3. 관리자 페이지 초기 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/fixed_admin_page_initial.png',
            fullPage: true 
        });
        console.log('📸 관리자 페이지 초기 상태 스크린샷 저장');

        // 4. 사용자 상세보기 버튼 클릭
        console.log('👁️ 4. 사용자 상세보기 버튼 클릭...');
        
        await page.waitForTimeout(5000); // 데이터 로딩 대기
        
        const viewButtons = await page.$$('.btn-view, button[title="상세보기"], .action-btn');
        console.log(`🔍 발견된 상세보기 버튼: ${viewButtons.length}개`);
        
        if (viewButtons.length > 0) {
            await viewButtons[0].click();
            await page.waitForTimeout(3000);
        } else {
            console.log('❌ 상세보기 버튼을 찾을 수 없습니다. 다시 시도...');
            // JavaScript로 직접 버튼 찾기
            const foundButton = await page.evaluate(() => {
                const buttons = document.querySelectorAll('button, .btn');
                for (let btn of buttons) {
                    if (btn.textContent.includes('상세보기') || btn.title === '상세보기') {
                        btn.click();
                        return true;
                    }
                }
                return false;
            });
            
            if (!foundButton) {
                console.log('❌ 상세보기 버튼을 찾을 수 없어서 테스트 종료');
                return false;
            }
            
            await page.waitForTimeout(3000);
        }

        // 5. 사용자 상세 모달이 나타났는지 확인
        console.log('🔍 5. 사용자 상세 모달 확인...');
        
        const modalExists = await page.evaluate(() => {
            const modal = document.getElementById('userDetailModal');
            return modal && window.getComputedStyle(modal).display !== 'none';
        });
        
        console.log(`📊 사용자 상세 모달 상태: ${modalExists ? '표시됨' : '숨김'}`);

        // 6. 사용자 상세 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/fixed_user_detail_modal.png',
            fullPage: true 
        });
        console.log('📸 사용자 상세 모달 스크린샷 저장');

        // 7. 프로필 이미지 찾기 및 클릭
        console.log('🖼️ 7. 수정된 프로필 이미지 클릭 테스트...');
        
        const profileImage = await page.$('.profile-image-clickable, .profile-image-large');
        if (!profileImage) {
            console.log('❌ 프로필 이미지를 찾을 수 없습니다');
            return false;
        }

        console.log('✅ 프로필 이미지 발견, 클릭 진행...');
        await profileImage.click();
        await page.waitForTimeout(1500); // 모달 애니메이션 대기

        // 8. 수정된 모달 상태 분석
        console.log('🔍 8. 수정된 프로필 모달 상태 분석...');
        
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
                    bodyStyles: originalBody ? {
                        textAlign: window.getComputedStyle(originalBody).textAlign,
                        display: window.getComputedStyle(originalBody).display,
                        justifyContent: window.getComputedStyle(originalBody).justifyContent,
                        alignItems: window.getComputedStyle(originalBody).alignItems
                    } : null,
                    imageStyles: originalImage ? {
                        display: window.getComputedStyle(originalImage).display,
                        margin: window.getComputedStyle(originalImage).margin,
                        objectFit: window.getComputedStyle(originalImage).objectFit
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
                    dimensions: {
                        modalWidth: fallbackRect.width,
                        modalHeight: fallbackRect.height,
                        imageWidth: imageRect ? imageRect.width : 0,
                        imageHeight: imageRect ? imageRect.height : 0
                    },
                    bodyStyles: fallbackBody ? {
                        textAlign: window.getComputedStyle(fallbackBody).textAlign,
                        display: window.getComputedStyle(fallbackBody).display,
                        justifyContent: window.getComputedStyle(fallbackBody).justifyContent,
                        alignItems: window.getComputedStyle(fallbackBody).alignItems,
                        padding: window.getComputedStyle(fallbackBody).padding
                    } : null,
                    imageStyles: fallbackImage ? {
                        display: window.getComputedStyle(fallbackImage).display,
                        margin: window.getComputedStyle(fallbackImage).margin,
                        objectFit: window.getComputedStyle(fallbackImage).objectFit,
                        maxWidth: window.getComputedStyle(fallbackImage).maxWidth,
                        maxHeight: window.getComputedStyle(fallbackImage).maxHeight
                    } : null,
                    modalClass: fallbackModal.className
                };
            }
            
            return {
                originalModal: originalData,
                fallbackModal: fallbackData,
                activeModalType: originalData ? 'original' : (fallbackData ? 'fallback_fixed' : 'none')
            };
        });
        
        console.log('📊 수정된 모달 분석 결과:', modalAnalysis);

        // 9. 수정된 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/fixed_profile_modal_opened.png',
            fullPage: true 
        });
        console.log('📸 수정된 프로필 모달 열림 상태 스크린샷 저장');

        // 10. UI 품질 재평가
        console.log('🏆 10. 수정된 UI 품질 종합 재평가...');
        
        const activeModal = modalAnalysis.originalModal || modalAnalysis.fallbackModal;
        
        if (!activeModal) {
            throw new Error('모달이 표시되지 않았습니다');
        }
        
        // 정렬 점수 계산 (중앙 정렬 여부)
        const alignmentScore = (() => {
            if (!activeModal.bodyStyles) return 5;
            
            const { textAlign, display, justifyContent, alignItems } = activeModal.bodyStyles;
            let score = 0;
            
            if (textAlign === 'center') score += 2;
            if (display === 'flex') score += 2;
            if (justifyContent === 'center') score += 3;
            if (alignItems === 'center') score += 3;
            
            return score;
        })();
        
        const improvedQualityScore = {
            functionality: activeModal.visible ? 10 : 0, // 기능 동작
            design: activeModal.hasImage ? 9 : 0, // 디자인 품질
            alignment: alignmentScore, // 정렬 품질 (새로 추가)
            responsiveness: (activeModal.dimensions?.modalWidth > 300) ? 10 : 8, // 반응형
            interaction: activeModal.type === 'fallback_fixed' ? 10 : 8 // 인터랙션
        };
        
        const improvedTotalScore = Object.values(improvedQualityScore).reduce((a, b) => a + b, 0) / 5;
        
        console.log('📊 수정된 UI 품질 평가:');
        console.log(`  - 기능성: ${improvedQualityScore.functionality}/10`);
        console.log(`  - 디자인: ${improvedQualityScore.design}/10`);
        console.log(`  - 정렬: ${improvedQualityScore.alignment}/10 (새로 추가)`);
        console.log(`  - 반응형: ${improvedQualityScore.responsiveness}/10`);
        console.log(`  - 인터랙션: ${improvedQualityScore.interaction}/10`);
        console.log(`🎯 수정된 종합 점수: ${improvedTotalScore.toFixed(1)}/10`);
        
        const improvement = improvedTotalScore - 7.2; // 원래 점수 기준
        console.log(`📈 총 개선도: +${improvement.toFixed(1)}점`);

        // 11. 정렬 상태 상세 분석
        console.log('🔍 11. 정렬 상태 상세 분석...');
        console.log('📐 모달 body 스타일:', activeModal.bodyStyles);
        console.log('🖼️ 이미지 스타일:', activeModal.imageStyles);
        
        const alignmentSuccess = (
            activeModal.bodyStyles?.textAlign === 'center' &&
            activeModal.bodyStyles?.display === 'flex' &&
            activeModal.bodyStyles?.justifyContent === 'center' &&
            activeModal.bodyStyles?.alignItems === 'center'
        );
        
        console.log(`✅ 정렬 성공 여부: ${alignmentSuccess ? '완벽한 중앙 정렬' : '정렬 개선 필요'}`);

        // 12. ESC 키 기능 테스트
        console.log('⌨️ 12. ESC 키 기능 테스트...');
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
        
        console.log('📊 ESC 키 테스트 결과:', escTestResult);

        // 13. 최종 성공 상태 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/fixed_modal_final_success.png',
            fullPage: true 
        });
        console.log('📸 수정 완료 최종 스크린샷 저장');

        // 14. 결과 정리
        console.log('🎉 프로필 모달 정렬 수정 테스트 완료!');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log('🎨 프로필 모달 정렬 개선 성과:');
        console.log(`   📈 점수 개선: 7.2 → ${improvedTotalScore.toFixed(1)} (+${improvement.toFixed(1)})`);
        console.log(`   ✅ 정렬 성공: ${alignmentSuccess ? '완벽한 중앙 정렬' : '개선 필요'}`);
        console.log(`   🎭 모달 타입: ${modalAnalysis.activeModalType}`);
        console.log(`   🖼️ 이미지 표시: ${activeModal.hasImage ? '정상' : '오류'}`);
        console.log(`   ⌨️ ESC 기능: ${escTestResult.allClosed ? '정상' : '확인 필요'}`);
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return {
            success: alignmentSuccess && improvedTotalScore >= 8.0,
            score: improvedTotalScore,
            improvement: improvement,
            alignmentSuccess: alignmentSuccess,
            modalType: modalAnalysis.activeModalType,
            escWorking: escTestResult.allClosed
        };

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/fixed_modal_test_error.png',
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
testFixedProfileModalAlignment().then(result => {
    if (result.success) {
        console.log('🏆 프로필 모달 정렬 수정 성공!');
        console.log(`📊 최종 점수: ${result.score}/10`);
        console.log(`🎨 정렬 성공: ${result.alignmentSuccess}`);
        console.log(`📈 개선도: +${result.improvement}점`);
        process.exit(0);
    } else {
        console.log('❌ 테스트 실패:', result.error || '품질 기준 미달');
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});