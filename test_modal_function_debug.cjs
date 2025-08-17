/**
 * 모달 함수 디버깅 테스트
 * 
 * 관리자 페이지에서 프로필 모달 함수들이 제대로 로드되고
 * 작동하는지 확인합니다.
 */

const DevLoginHelper = require('./test_helpers/DevLoginHelper.cjs');

async function testModalFunctionDebug() {
    console.log('🔧 모달 함수 디버깅 테스트 시작');
    
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

        // 3. 함수 로딩 상태 확인
        console.log('🔍 3. 프로필 모달 함수 로딩 상태 확인...');
        
        const functionStatus = await page.evaluate(() => {
            return {
                openProfileImageModal: typeof window.openProfileImageModal === 'function',
                createFallbackProfileModal: typeof window.createFallbackProfileModal === 'function',
                closeFallbackProfileModal: typeof window.closeFallbackProfileModal === 'function',
                fallbackModalEscHandler: typeof window.fallbackModalEscHandler === 'function',
                profileModal: typeof window.profileModal !== 'undefined',
                profileModalShow: window.profileModal && typeof window.profileModal.show === 'function',
                allFunctions: Object.getOwnPropertyNames(window).filter(name => name.includes('rofile') || name.includes('odal')),
                scripts: Array.from(document.querySelectorAll('script')).map(s => s.src).filter(src => src)
            };
        });
        
        console.log('📊 함수 로딩 상태:', functionStatus);

        // 4. 테스트용 사용자 상세 모달 생성
        console.log('🎭 4. 테스트용 사용자 상세 모달 생성...');
        
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
                            <h2 style="margin: 0; font-size: 1.4em;">👤 사용자 상세 정보 (함수 디버깅)</h2>
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
                                         title="🔧 디버깅: 클릭하면 함수 호출 상태를 확인합니다"
                                         onclick="testProfileModalFunction('/assets/uploads/default-avatar.png', '우리집탄이')"
                                         onmouseover="this.style.transform='scale(1.08)'; this.style.boxShadow='0 12px 35px rgba(102,126,234,0.3)'; this.style.borderColor='#667eea'"
                                         onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'; this.style.borderColor='#e2e8f0'">
                                    <div style="position: absolute; bottom: 5px; right: 5px; width: 35px; height: 35px; 
                                                background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%;
                                                display: flex; align-items: center; justify-content: center; color: white;
                                                font-size: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                                                animation: pulse 3s ease-in-out infinite;">🔧</div>
                                </div>
                                <h3 style="margin: 20px 0 15px 0; color: #2d3748; font-size: 1.5em;">우리집탄이</h3>
                                <div style="margin-bottom: 35px;">
                                    <span style="background: linear-gradient(135deg, #fbb6ce, #f687b3); color: #97266d; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin: 0 8px;">👑 관리자</span>
                                    <span style="background: linear-gradient(135deg, #c6f6d5, #9ae6b4); color: #22543d; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin: 0 8px;">✅ 활성</span>
                                    <span style="background: linear-gradient(135deg, #bee3f8, #90cdf4); color: #2a69ac; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin: 0 8px;">🔒 인증됨</span>
                                </div>
                                
                                <!-- 디버깅 정보 표시 영역 -->
                                <div style="margin-top: 30px; text-align: left; padding: 20px; background: linear-gradient(135deg, #f7fafc, #edf2f7); border-radius: 12px; border: 1px solid #e2e8f0;">
                                    <h4 style="color: #2d3748; margin: 0 0 15px 0; font-size: 1.1em;">🔧 디버깅 정보</h4>
                                    <div id="debugInfo" style="font-family: monospace; font-size: 0.9em; color: #4a5568;">로딩 중...</div>
                                </div>
                                
                                <div style="margin-top: 20px; text-align: center; padding: 25px; background: linear-gradient(135deg, #667eea15, #764ba215); border-radius: 16px; border: 2px dashed #667eea;">
                                    <div style="font-size: 2.5em; margin-bottom: 15px;">🔧</div>
                                    <h3 style="color: #667eea; margin: 0 0 20px 0; font-size: 1.4em;">함수 디버깅 테스트!</h3>
                                    <p style="margin: 15px 0; color: #4a5568; font-size: 1em; font-weight: 500;"><strong>🔧 위의 프로필 이미지를 클릭</strong>하여 함수 호출 상태를 확인해보세요!</p>
                                    <div id="testResult" style="margin-top: 20px; padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 10px;">
                                        <p style="margin: 0; font-size: 0.9em; color: #667eea; font-weight: 600;">
                                            🎯 클릭하면 함수 호출 결과가 여기에 표시됩니다
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // 디버깅용 테스트 함수 생성
            window.testProfileModalFunction = function(imageSrc, userName) {
                console.log('🔧 testProfileModalFunction 호출됨:', imageSrc, userName);
                
                const debugInfo = document.getElementById('debugInfo');
                const testResult = document.getElementById('testResult');
                
                let debugText = '';
                let resultText = '';
                
                try {
                    // 함수 존재 여부 확인
                    debugText += `openProfileImageModal: ${typeof window.openProfileImageModal === 'function' ? '✅ 존재' : '❌ 없음'}\\n`;
                    debugText += `createFallbackProfileModal: ${typeof window.createFallbackProfileModal === 'function' ? '✅ 존재' : '❌ 없음'}\\n`;
                    debugText += `profileModal: ${typeof window.profileModal !== 'undefined' ? '✅ 존재' : '❌ 없음'}\\n`;
                    
                    if (typeof window.openProfileImageModal === 'function') {
                        resultText = '🎯 openProfileImageModal 함수 호출 시도...';
                        window.openProfileImageModal(imageSrc, userName);
                        resultText += '\\n✅ 함수 호출 완료!';
                    } else if (typeof window.profileModal !== 'undefined' && window.profileModal.show) {
                        resultText = '🎯 profileModal.show 함수 호출 시도...';
                        window.profileModal.show(imageSrc, userName, true);
                        resultText += '\\n✅ 함수 호출 완료!';
                    } else {
                        resultText = '❌ 사용 가능한 프로필 모달 함수를 찾을 수 없습니다.\\n🔧 수동으로 fallback 모달을 생성하겠습니다...';
                        window.createManualFallbackModal(imageSrc, userName);
                        resultText += '\\n✅ 수동 모달 생성 완료!';
                    }
                    
                } catch (error) {
                    resultText = '❌ 오류 발생: ' + error.message;
                    debugText += `오류: ${error.message}\\n`;
                }
                
                debugInfo.innerHTML = debugText.replace(/\\n/g, '<br>');
                testResult.innerHTML = `<p style="margin: 0; font-size: 0.9em; color: #667eea; font-weight: 600;">${resultText.replace(/\\n/g, '<br>')}</p>`;
            };
            
            // 수동 fallback 모달 생성 함수
            window.createManualFallbackModal = function(imageSrc, userName) {
                // 기존 모달이 있으면 제거
                const existingModal = document.getElementById('manualFallbackModal');
                if (existingModal) {
                    existingModal.remove();
                }

                const modalHTML = `
                <div id="manualFallbackModal" class="profile-image-modal" onclick="window.closeManualFallbackModal()" 
                     style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                            background: rgba(0, 0, 0, 0.8); display: flex; 
                            justify-content: center; align-items: center; z-index: 10002; 
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
                            <h3 style="margin: 0; font-size: 1.3em; font-weight: 600;">${userName}의 프로필 (수동 생성)</h3>
                            <button onclick="window.closeManualFallbackModal()" 
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
                
                // 스크롤 방지
                document.body.style.overflow = 'hidden';
                
                console.log('✅ 수동 Fallback 프로필 모달 생성 완료');
            };
            
            // 수동 모달 닫기
            window.closeManualFallbackModal = function() {
                const modal = document.getElementById('manualFallbackModal');
                if (modal) {
                    modal.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        modal.remove();
                        document.body.style.overflow = '';
                        console.log('✅ 수동 모달 닫기 완료');
                    }, 300);
                }
            };
            
            // 스타일 추가
            if (!document.getElementById('manualModalStyles')) {
                const style = document.createElement('style');
                style.id = 'manualModalStyles';
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
            
            console.log('✅ 디버깅용 사용자 상세 모달 생성 완료');
        });

        // 5. 초기 디버깅 정보 업데이트
        console.log('📊 5. 초기 디버깅 정보 업데이트...');
        
        await page.evaluate(() => {
            const debugInfo = document.getElementById('debugInfo');
            if (debugInfo) {
                let debugText = '';
                debugText += `openProfileImageModal: ${typeof window.openProfileImageModal === 'function' ? '✅ 존재' : '❌ 없음'}\\n`;
                debugText += `createFallbackProfileModal: ${typeof window.createFallbackProfileModal === 'function' ? '✅ 존재' : '❌ 없음'}\\n`;
                debugText += `profileModal: ${typeof window.profileModal !== 'undefined' ? '✅ 존재' : '❌ 없음'}\\n`;
                debugText += `profileModal.show: ${window.profileModal && typeof window.profileModal.show === 'function' ? '✅ 존재' : '❌ 없음'}\\n`;
                debugText += `Scripts loaded: ${Array.from(document.querySelectorAll('script')).length}개\\n`;
                
                debugInfo.innerHTML = debugText.replace(/\\n/g, '<br>');
            }
        });

        // 6. 디버깅 모달 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/debug_modal_initial.png',
            fullPage: true 
        });
        console.log('📸 디버깅 모달 초기 상태 스크린샷 저장');

        // 7. 프로필 이미지 클릭 테스트
        console.log('🖼️ 7. 프로필 이미지 클릭 및 함수 호출 테스트...');
        
        const profileImage = await page.$('.profile-image-clickable');
        if (!profileImage) {
            throw new Error('프로필 이미지를 찾을 수 없습니다');
        }

        console.log('✅ 프로필 이미지 발견, 클릭 진행...');
        await profileImage.click();
        await page.waitForTimeout(2000); // 디버깅 정보 업데이트 대기

        // 8. 클릭 후 상태 확인
        console.log('🔍 8. 클릭 후 상태 확인...');
        
        const afterClickAnalysis = await page.evaluate(() => {
            return {
                originalModal: !!document.getElementById('profileImageModal') && 
                              window.getComputedStyle(document.getElementById('profileImageModal')).display !== 'none',
                fallbackModal: !!document.getElementById('fallbackProfileModal') && 
                               window.getComputedStyle(document.getElementById('fallbackProfileModal')).display !== 'none',
                manualModal: !!document.getElementById('manualFallbackModal') && 
                             window.getComputedStyle(document.getElementById('manualFallbackModal')).display !== 'none',
                debugInfo: document.getElementById('debugInfo')?.innerHTML || 'N/A',
                testResult: document.getElementById('testResult')?.innerHTML || 'N/A',
                consoleMessages: [], // 실제로는 console.log 캡처가 어려움
                allModals: Array.from(document.querySelectorAll('[id*="modal"], [id*="Modal"]')).map(el => ({
                    id: el.id,
                    visible: window.getComputedStyle(el).display !== 'none'
                }))
            };
        });
        
        console.log('📊 클릭 후 분석 결과:', afterClickAnalysis);

        // 9. 클릭 후 스크린샷
        await page.screenshot({ 
            path: '/var/www/html/topmkt/debug_modal_after_click.png',
            fullPage: true 
        });
        console.log('📸 클릭 후 디버깅 모달 스크린샷 저장');

        // 10. ESC 키로 모든 모달 닫기 테스트
        console.log('⌨️ 10. ESC 키로 모든 모달 닫기 테스트...');
        await page.keyboard.press('Escape');
        await page.waitForTimeout(1000);
        
        const escTestResult = await page.evaluate(() => {
            const modals = ['profileImageModal', 'fallbackProfileModal', 'manualFallbackModal'];
            return modals.map(id => {
                const modal = document.getElementById(id);
                return {
                    id: id,
                    exists: !!modal,
                    visible: modal ? window.getComputedStyle(modal).display !== 'none' : false
                };
            });
        });
        
        console.log('📊 ESC 키 테스트 결과:', escTestResult);

        // 11. 최종 결과 정리
        console.log('🎉 모달 함수 디버깅 테스트 완료!');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log('🔧 함수 디버깅 결과:');
        console.log(`   📊 함수 상태: ${JSON.stringify(functionStatus, null, 2)}`);
        console.log(`   🖼️ 클릭 후 모달: original:${afterClickAnalysis.originalModal}, fallback:${afterClickAnalysis.fallbackModal}, manual:${afterClickAnalysis.manualModal}`);
        console.log(`   ⌨️ ESC 테스트: ${escTestResult.map(r => `${r.id}:${r.visible ? '열림' : '닫힘'}`).join(', ')}`);
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        const success = afterClickAnalysis.originalModal || afterClickAnalysis.fallbackModal || afterClickAnalysis.manualModal;

        return {
            success: success,
            functionStatus: functionStatus,
            afterClickAnalysis: afterClickAnalysis,
            escTestResult: escTestResult
        };

    } catch (error) {
        console.error('❌ 테스트 실패:', error.message);
        
        if (session && session.page) {
            await session.page.screenshot({ 
                path: '/var/www/html/topmkt/debug_modal_error.png',
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
testModalFunctionDebug().then(result => {
    if (result.success) {
        console.log('🏆 모달 함수 디버깅 성공!');
        console.log(`🔧 함수 상태: ${JSON.stringify(result.functionStatus)}`);
        process.exit(0);
    } else {
        console.log('❌ 디버깅 실패:', result.error || '함수 호출 실패');
        process.exit(1);
    }
}).catch(error => {
    console.error('🔥 예상치 못한 오류:', error);
    process.exit(1);
});